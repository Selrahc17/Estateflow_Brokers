<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private function failLogin(string $email): \Illuminate\Testing\TestResponse
    {
        return $this->from(route('auth.login'))->post(route('auth.login.post'), [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    public function test_wrong_password_shows_a_clear_message(): void
    {
        $user = User::factory()->create();

        $this->failLogin($user->email)
            ->assertSessionHasErrors(['email' => 'Incorrect email or password. Please try again.']);
    }

    public function test_login_is_locked_after_five_attempts_with_a_form_error(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->failLogin($user->email)
                ->assertSessionHasErrors(['email' => 'Incorrect email or password. Please try again.']);
        }

        $response = $this->failLogin($user->email);

        $response->assertRedirect(route('auth.login'));
        $this->assertStringContainsString('Too many attempts', session('errors')->first('email'));
    }

    public function test_login_limit_does_not_block_a_different_email(): void
    {
        $user = User::factory()->create(['password' => 'password123', 'role' => 'client', 'is_active' => true]);

        foreach (range(1, 6) as $attempt) {
            $this->failLogin('someone-else@example.com');
        }

        $this->post(route('auth.login.post'), ['email' => $user->email, 'password' => 'password123'])
            ->assertRedirect(route('client.properties'));
    }

    public function test_forgot_password_is_rate_limited(): void
    {
        foreach (range(1, 3) as $attempt) {
            $this->from(route('auth.forgot'))->post(route('auth.forgot.post'), ['email' => 'a@example.com']);
        }

        $this->from(route('auth.forgot'))->post(route('auth.forgot.post'), ['email' => 'a@example.com']);

        $this->assertStringContainsString('Too many attempts', session('errors')->first('email'));
    }

    public function test_contact_form_is_rate_limited(): void
    {
        $message = [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '09171234567',
            'subject' => 'General Inquiry',
            'message' => 'I would like to learn more about your properties.',
        ];

        foreach (range(1, 5) as $attempt) {
            $this->from(route('client.contact'))->post(route('client.contact.store'), $message);
        }

        $this->from(route('client.contact'))->post(route('client.contact.store'), $message);

        $this->assertDatabaseCount('contact_messages', 5);
        $this->assertStringContainsString('Too many attempts', session('errors')->first('email'));
    }

    public function test_array_email_input_does_not_crash_the_limiter(): void
    {
        $this->from(route('auth.login'))
            ->post(route('auth.login.post'), ['email' => ['a@example.com'], 'password' => 'x'])
            ->assertSessionHasErrors('email');
    }
}
