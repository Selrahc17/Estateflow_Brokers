<?php

namespace Tests\Feature;

use App\Services\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The AI provider is chosen in .env. These tests never contact a real provider.
 */
class AIProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_test_run_never_sees_a_real_key(): void
    {
        $this->assertEmpty(config('services.ai.key'));
        $this->assertEmpty(config('services.openai.key'));
    }

    private function useProvider(?string $key, string $baseUrl = 'https://api.groq.com/openai/v1', string $model = 'llama-test'): void
    {
        config(['services.ai.key' => $key, 'services.ai.base_url' => $baseUrl, 'services.ai.model' => $model]);
    }

    public function test_chat_uses_the_configured_provider_model_and_key(): void
    {
        $this->useProvider('test-key');
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'Hello from the provider']]]])]);

        $reply = app(AIService::class)->chat('Hi', 0, 'guest');

        $this->assertSame('Hello from the provider', $reply);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
            && $request['model'] === 'llama-test'
            && $request->hasHeader('Authorization', 'Bearer test-key'));
    }

    public function test_a_trailing_slash_in_the_base_url_is_fine(): void
    {
        $this->useProvider('test-key', 'https://api.openai.com/v1/');
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'ok']]]])]);

        app(AIService::class)->chat('Hi', 0, 'guest');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.openai.com/v1/chat/completions');
    }

    public function test_without_a_key_it_answers_from_built_in_replies_and_calls_nobody(): void
    {
        $this->useProvider('');
        Http::fake();

        $reply = app(AIService::class)->chat('Where are my documents?', 0, 'guest');

        $this->assertStringContainsString('handled by the developer', $reply);
        Http::assertNothingSent();
    }

    public function test_a_provider_error_such_as_no_credits_falls_back_instead_of_crashing(): void
    {
        $this->useProvider('test-key');
        Http::fake(['*' => Http::response(['error' => ['code' => 'credit_balance_exhausted']], 429)]);

        $reply = app(AIService::class)->chat('Is the lot available?', 0, 'guest');

        $this->assertNotSame('', $reply);
    }

    public function test_the_property_description_helper_uses_the_same_provider(): void
    {
        $this->useProvider('test-key');
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'A bright family home.']]]])]);

        $this->assertSame('A bright family home.', app(AIService::class)->generatePropertyDescription(['type' => 'House and Lot']));
    }
}
