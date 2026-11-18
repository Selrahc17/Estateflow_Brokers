<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    public const HISTORY_LIMIT = 10;

    private string $apiKey;
    private string $model;

    private string $endpoint;

    public function __construct()
    {
        $this->apiKey = (string) config('services.ai.key');
        $this->model = (string) config('services.ai.model');
        $this->endpoint = rtrim((string) config('services.ai.base_url'), '/') . '/chat/completions';
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history  Earlier turns, oldest first.
     */
    public function chat(string $userMessage, int $userId, string $role = 'client', array $history = []): string
    {
        if (empty($this->apiKey)) {
            return $this->fallbackResponse($userMessage);
        }

        try {
            $user = $userId ? User::find($userId) : null;
            $messages = [['role' => 'system', 'content' => app(AIContext::class)->build($user, $role, $userMessage)]];

            foreach (array_slice($history, -self::HISTORY_LIMIT) as $turn) {
                if (in_array($turn['role'] ?? null, ['user', 'assistant'], true) && filled($turn['content'] ?? null)) {
                    $messages[] = ['role' => $turn['role'], 'content' => mb_substr((string) $turn['content'], 0, 2000)];
                }
            }
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->endpoint, [
                    'model'       => $this->model,
                    // Reasoning models spend part of this budget thinking before they answer.
                    'max_tokens'  => 1200,
                    'temperature' => 0.3,
                    'messages'    => $messages,
                ]);

            if ($response->successful()) {
                $reply = trim((string) $response->json('choices.0.message.content'));

                return $reply !== '' ? $reply : $this->fallbackResponse($userMessage);
            }

            Log::warning('AI provider error', ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 500)]);
            return $this->fallbackResponse($userMessage);
        } catch (\Throwable $e) {
            Log::error('AI provider request failed', ['error' => $e->getMessage()]);
            return $this->fallbackResponse($userMessage);
        }
    }

    public function scoreLeads(array $leads): array
    {
        // Score leads locally based on activity signals — no API call needed
        return collect($leads)->map(function ($lead) {
            $score = 0;
            $score += min(($lead['inquiry_count'] ?? 0) * 15, 40);
            $score += ($lead['has_reservation'] ?? false) ? 30 : 0;
            $score += ($lead['has_site_visit'] ?? false) ? 20 : 0;
            $score += ($lead['days_since_last_contact'] ?? 99) < 7 ? 10 : 0;

            return array_merge($lead, [
                'score' => min($score, 100),
                'priority' => $score >= 70 ? 'hot' : ($score >= 40 ? 'warm' : 'cold'),
            ]);
        })->sortByDesc('score')->values()->all();
    }

    /** Whether the last recommendProperties() call was ranked by the AI provider. */
    public bool $lastRankedByAi = false;

    public function recommendProperties(array $preferences): Collection
    {
        $this->lastRankedByAi = false;

        $properties = Property::where('status', 'available')
            ->withCount(['lots' => fn($query) => $query->where('status', 'available')])
            ->latest()
            ->take(50)
            ->get();

        if ($properties->isEmpty()) {
            return $properties;
        }

        if (!empty($this->apiKey)) {
            try {
                $catalog = $properties->map(fn($property) => [
                    'id' => $property->id,
                    'name' => $property->name,
                    'type' => $property->type,
                    'city' => $property->city,
                    'province' => $property->province,
                    'price' => $property->price,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    'description' => $property->description,
                ])->values()->all();

                $response = Http::withToken($this->apiKey)
                    ->timeout(15)
                    ->post($this->endpoint, [
                        'model' => $this->model,
                        'temperature' => 0,
                        'messages' => [
                            ['role' => 'system', 'content' => 'Rank property IDs for a buyer. Return only a JSON array of IDs, best match first.'],
                            ['role' => 'user', 'content' => json_encode(['preferences' => $preferences, 'properties' => $catalog])],
                        ],
                    ]);

                $content = $response->json('choices.0.message.content');
                $ids = is_string($content) ? json_decode(trim($content), true) : null;
                if (is_array($ids)) {
                    // A closure, because filter() passes the key too and is_int() takes one argument.
                    $order = collect($ids)->filter(fn ($id) => is_int($id))->values();
                    $ranked = $properties->sortBy(fn($property) => $order->search($property->id) === false ? PHP_INT_MAX : $order->search($property->id));
                    $this->lastRankedByAi = true;
                    return $ranked->values();
                }
            } catch (\Throwable $e) {
                Log::warning('AI property recommendation failed', ['error' => $e->getMessage()]);
            }
        }

        return $properties->sortByDesc(function ($property) use ($preferences) {
            $score = 0;
            if (!empty($preferences['type']) && $property->type === $preferences['type']) $score += 30;
            if (!empty($preferences['city']) && str_contains(strtolower($property->city ?? ''), strtolower($preferences['city']))) $score += 25;
            if (!empty($preferences['province']) && str_contains(strtolower($property->province ?? ''), strtolower($preferences['province']))) $score += 20;
            if (!empty($preferences['max_price']) && $property->price <= $preferences['max_price']) $score += 20;
            if (!empty($preferences['bedrooms']) && $property->bedrooms >= $preferences['bedrooms']) $score += 10;
            return $score;
        })->values();
    }

    /**
     * A short buyer-friendly comparison of listings. Returns null when AI is
     * off or fails, so the caller can fall back to a plain summary.
     *
     * @param  array<int, array<string, string>>  $facts  One set of labelled facts per listing.
     */
    public function compareProperties(array $facts, ?string $needs = null): ?string
    {
        if (empty($this->apiKey) || count($facts) < 2) {
            return null;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(20)
                ->post($this->endpoint, [
                    'model' => $this->model,
                    'temperature' => 0.3,
                    'max_tokens' => 400,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You help home buyers in the Philippines compare property listings. '
                            .'Use only the facts given; if something is missing, say it is not listed. Prices are in Philippine pesos. '
                            .'Write 3 to 5 short sentences in plain English, no headings or tables: what sets each listing apart, '
                            .'which suits which kind of buyer, and, if the buyer shared their needs, which fits them best and why. '
                            .'Do not invent amenities, distances, or prices, and do not promise anything on the agent\'s behalf.'],
                        ['role' => 'user', 'content' => json_encode([
                            'buyer_needs' => filled($needs) ? $needs : 'not given',
                            'listings' => $facts,
                        ], JSON_UNESCAPED_UNICODE)],
                    ],
                ]);

            $text = $response->successful() ? trim((string) $response->json('choices.0.message.content')) : '';

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            Log::warning('AI property comparison failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function fallbackResponse(string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'document') || str_contains($message, 'requirement')) {
            return 'Buying requirements are handled by the developer. Send an inquiry on the property and an agent will connect you with the developer\'s coordinator.';
        }
        if (str_contains($message, 'reservation') || str_contains($message, 'lot')) {
            return 'Your reservation details are available in the My Reservation section of your dashboard.';
        }
        if (str_contains($message, 'broker') || str_contains($message, 'contact')) {
            return 'You can find your broker\'s contact details on your property listing page after logging in.';
        }
        if (str_contains($message, 'property') || str_contains($message, 'available')) {
            return 'Browse all available properties at our Properties page. You can filter by location, type, and price.';
        }

        return 'Thank you for your message! For detailed assistance, please visit the relevant section in your account or contact your broker directly.';
    }
}
