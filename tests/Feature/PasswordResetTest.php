<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_form_submits_the_email_field(): void
    {
        $this->get(route('auth.forgot'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertDontSee('Create Account');
    }

    public function test_user_can_request_a_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from(route('auth.forgot'))
            ->post(route('auth.forgot.post'), ['email' => $user->email])
            ->assertRedirect(route('auth.forgot'))
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_reply_as_a_registered_one(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $message = "If that email is registered, we've sent a password reset link.";

        foreach ([$user->email, 'nobody@example.com'] as $email) {
            $this->from(route('auth.forgot'))
                ->post(route('auth.forgot.post'), ['email' => $email])
                ->assertRedirect(route('auth.forgot'))
                ->assertSessionHas('success', $message)
                ->assertSessionHasNoErrors();
        }

        Notification::assertSentTimes(ResetPassword::class, 1);
    }

    public function test_repeat_request_for_a_registered_email_reads_the_same(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        foreach (range(1, 2) as $attempt) {
            $this->from(route('auth.forgot'))
                ->post(route('auth.forgot.post'), ['email' => $user->email])
                ->assertSessionHasNoErrors()
                ->assertSessionHas('success');
        }
    }

    public function test_reset_page_gives_one_message_for_unknown_email_and_bad_link(): void
    {
        $user = User::factory()->create();

        foreach ([$user->email, 'nobody@example.com'] as $email) {
            $this->from(route('password.reset', ['token' => 'bad-token']))
                ->post(route('auth.reset.post'), [
                    'token' => 'bad-token',
                    'email' => $email,
                    'password' => 'new-password123',
                    'password_confirmation' => 'new-password123',
                ])
                ->assertSessionHasErrors(['email' => 'This reset link is invalid or has expired.']);
        }
    }

    public function test_user_can_reset_their_password_and_sign_in(): void
    {
        $user = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('value="'.$token.'"', false)
            ->assertSee('value="'.$user->email.'"', false)
            ->assertDontSee('Create Account');

        $this->post(route('auth.reset.post'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect(route('auth.login'));

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'new-password123']));
    }

    public function test_mismatched_passwords_show_an_error_on_the_reset_page(): void
    {
        $user = User::factory()->create();
        $url = route('password.reset', ['token' => 'any-token', 'email' => $user->email]);

        $this->from($url)
            ->followingRedirects()
            ->post(route('auth.reset.post'), [
                'token' => 'any-token',
                'email' => $user->email,
                'password' => 'new-password123',
                'password_confirmation' => 'different-password',
            ])
            ->assertSee('The passwords do not match.');
    }

    public function test_expired_link_explains_the_problem_on_the_reset_page(): void
    {
        $user = User::factory()->create();
        $url = route('password.reset', ['token' => 'expired-token', 'email' => $user->email]);

        $this->from($url)
            ->followingRedirects()
            ->post(route('auth.reset.post'), [
                'token' => 'expired-token',
                'email' => $user->email,
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])
            ->assertSee('This reset link is invalid or has expired.')
            ->assertSee('Request a new link');
    }
}
