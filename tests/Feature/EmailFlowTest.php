<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

/**
 * Start to finish, as a person would: the email really goes out, and the link
 * inside it works. Emails stay in memory here, so no real inbox gets them.
 */
class EmailFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, Email> */
    private function sentEmails(): array
    {
        return collect(app('mailer')->getSymfonyTransport()->messages())
            ->map(fn ($sent) => $sent->getOriginalMessage())
            ->all();
    }

    private function buttonLink(Email $email): string
    {
        preg_match('/<a href="([^"]+)" class="button button-primary"/', (string) $email->getHtmlBody(), $match);
        $this->assertNotEmpty($match, 'The email has no button link');

        return html_entity_decode($match[1]);
    }

    public function test_forgot_password_email_arrives_and_its_link_resets_the_password(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com', 'role' => 'client', 'is_active' => true, 'is_approved' => true]);

        $this->post(route('auth.forgot.post'), ['email' => 'ana@example.com'])->assertRedirect();
        $this->app->terminate();

        $emails = $this->sentEmails();
        $this->assertCount(1, $emails);
        $this->assertSame('ana@example.com', $emails[0]->getTo()[0]->getAddress());
        $this->assertSame('Reset your EstateFlow password', $emails[0]->getSubject());

        $link = $this->buttonLink($emails[0]);
        $this->get($link)->assertOk();

        parse_str((string) parse_url($link, PHP_URL_QUERY), $query);
        $this->post(route('auth.reset.post'), [
            'token' => basename((string) parse_url($link, PHP_URL_PATH)),
            'email' => $query['email'],
            'password' => 'brand-new-pass1',
            'password_confirmation' => 'brand-new-pass1',
        ])->assertRedirect(route('auth.login'));

        $this->assertTrue(Auth::validate(['email' => 'ana@example.com', 'password' => 'brand-new-pass1']));
        $this->assertNotNull($user->fresh());
    }

    public function test_agent_invitation_email_arrives_and_its_link_sets_up_the_account(): void
    {
        Storage::fake('local');
        $broker = User::factory()->create(['name' => 'Jose Dela Cruz', 'role' => 'broker', 'is_active' => true, 'is_approved' => true, 'phone' => '09171234567', 'prc_id_expires_at' => now()->addYear(), 'dhsud_registration_expires_at' => now()->addYear()]);

        $this->actingAs($broker)->post(route('broker.agents.store'), ['name' => 'New Agent', 'email' => 'new-agent@example.com'])->assertRedirect();

        $emails = $this->sentEmails();
        $this->assertCount(1, $emails);
        $this->assertSame('new-agent@example.com', $emails[0]->getTo()[0]->getAddress());
        $this->assertStringContainsString('Jose Dela Cruz has invited you', (string) $emails[0]->getHtmlBody());

        $link = $this->buttonLink($emails[0]);
        Auth::logout();
        $this->get($link)->assertOk();

        $this->post($link, [
            'license_number' => 'ACC-1001',
            'prc_id_expires_at' => now()->addYear()->toDateString(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'privacy_consent' => '1',
        ])->assertRedirect(route('agent.dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'new-agent@example.com')->firstOrFail());
    }
}
