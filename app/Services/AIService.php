<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiKey;
    private string $model = 'gpt-3.5-turbo';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
    }

    public function chat(string $userMessage, int $userId, string $role = 'client'): string
    {
        if (empty($this->apiKey)) {
            return $this->fallbackResponse($userMessage);
        }

        $systemPrompt = $this->buildSystemPrompt($userId, $role);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => $this->model,
                    'max_tokens'  => 400,
                    'temperature' => 0.7,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userMessage],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? $this->fallbackResponse($userMessage);
            }

            Log::warning('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->fallbackResponse($userMessage);
        } catch (\Throwable $e) {
            Log::error('OpenAI request failed', ['error' => $e->getMessage()]);
            return $this->fallbackResponse($userMessage);
        }
    }

    public function generatePropertyDescription(array $specs): string
    {
        if (empty($this->apiKey)) {
            return '';
        }

        $details = collect($specs)->filter()->map(fn($v, $k) => "$k: $v")->implode(', ');

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'      => $this->model,
                    'max_tokens' => 250,
                    'messages'   => [
                        ['role' => 'system', 'content' => 'You are a professional real estate copywriter in the Philippines. Write compelling, concise property descriptions in 2-3 sentences.'],
                        ['role' => 'user',   'content' => "Write a property listing description for: $details"],
                    ],
                ]);

            return $response->successful()
                ? ($response->json('choices.0.message.content') ?? '')
                : '';
        } catch (\Throwable $e) {
            return '';
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

    public function recommendProperties(array $preferences): Collection
    {
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
                    ->post('https://api.openai.com/v1/chat/completions', [
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
                    $order = collect($ids)->filter('is_int')->values();
                    $ranked = $properties->sortBy(fn($property) => $order->search($property->id) === false ? PHP_INT_MAX : $order->search($property->id));
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

    private function buildSystemPrompt(int $userId, string $role): string
    {
        $context = "You are EstateFlow AI, a helpful real estate assistant for a Philippine property portal. ";
        $context .= "Be concise, friendly, and professional. Answer in 1-3 sentences unless more detail is needed. ";
        $context .= "You help with property inquiries, reservation details, site visits, and document requirements. ";
        $context .= "If asked something outside real estate, politely redirect to relevant topics.";

        if ($role === 'client') {
            $reservation = Reservation::whereHas('client', fn($q) => $q->where('user_id', $userId))
                ->with('lot.property')
                ->latest()
                ->first();

            if ($reservation) {
                $context .= " The client has a reservation: {$reservation->reservation_code}, ";
                $context .= "status: {$reservation->status}, ";
                $context .= "property: " . ($reservation->lot?->property?->name ?? 'N/A') . ", ";
                $context .= "lot: " . ($reservation->lot?->lot_number ?? 'N/A') . ".";
            }
        }

        return $context;
    }

    private function fallbackResponse(string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'document')) {
            return 'You can upload and view your documents in the My Documents section of your account.';
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
