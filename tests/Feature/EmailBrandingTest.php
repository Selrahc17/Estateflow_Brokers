<?php

namespace Tests\Feature;

use App\Notifications\AgentInvitation;
use App\Notifications\ResetPasswordNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The emails the system sends carry the EstateFlow look and wording.
 */
class EmailBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_the_estateflow_reset_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@example.com']);

        $this->post(route('auth.forgot.post'), ['email' => 'ana@example.com']);
        $this->app->terminate();

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $mail = $notification->toMail($user);

            return $mail->subject === 'Reset your EstateFlow password'
                && str_contains($mail->actionUrl, '/reset-password/' . $notification->token)
                && str_contains($mail->actionUrl, urlencode('ana@example.com'));
        });
    }

    public function test_the_reset_email_looks_like_estateflow(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com']);
        $html = (string) (new ResetPasswordNotification('token-123'))->toMail($user)->render();

        $this->assertStringContainsString('Estate', $html);
        $this->assertStringContainsString('Flow', $html);
        $this->assertStringContainsString('Reset Password', $html);
        $this->assertStringContainsString('The EstateFlow Team', $html);
        $this->assertStringContainsString('background-color: #1A6B79', $html);
        $this->assertStringContainsString('/reset-password/token-123', $html);
        $this->assertStringNotContainsString('Laravel', $html);
    }

    public function test_the_agent_invitation_looks_like_estateflow(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $html = (string) (new AgentInvitation('Jose Dela Cruz', 'https://example.test/invitations/abc', now()->addDays(7)))->toMail($agent)->render();

        $this->assertStringContainsString('Jose Dela Cruz has invited you to join their team', $html);
        $this->assertStringContainsString('Accept Invitation', $html);
        $this->assertStringContainsString('background-color: #1A6B79', $html);
        $this->assertStringNotContainsString('Laravel', $html);
    }
}
