<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin Contact Messages page works like an inbox: new messages first.
 */
class AdminContactMessageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true]);
    }

    private function message(array $overrides = []): ContactMessage
    {
        return ContactMessage::create($overrides + [
            'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => 'juan@example.com', 'phone' => '0917 123 4567',
            'subject' => 'General Inquiry', 'message' => 'I would like to learn more about your properties.', 'status' => 'new',
        ]);
    }

    public function test_new_messages_are_shown_first_with_counts(): void
    {
        $this->message();
        $this->message(['first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => 'ana@example.com', 'status' => 'handled']);

        $this->actingAs($this->admin)->get(route('admin.contact-messages'))
            ->assertOk()
            ->assertViewHas('status', 'new')
            ->assertViewHas('counts', fn ($counts) => $counts['new'] === 1 && $counts['handled'] === 1 && $counts['all'] === 2)
            ->assertSee('Juan Dela Cruz')
            ->assertDontSee('Ana Reyes')
            ->assertSee('Mark as handled');

        $this->get(route('admin.contact-messages', ['status' => 'handled']))
            ->assertSee('Ana Reyes')
            ->assertSee('Reopen')
            ->assertDontSee('Juan Dela Cruz');
    }

    public function test_each_message_offers_email_and_phone_replies(): void
    {
        $this->message(['subject' => 'Reservation Question']);

        $this->actingAs($this->admin)->get(route('admin.contact-messages'))
            ->assertSee('mailto:juan@example.com?subject=Re%3A%20Reservation%20Question%20-%20EstateFlow', false)
            ->assertSee('href="tel:09171234567"', false)
            ->assertSee('Reply by email');
    }

    public function test_search_and_subject_filters_narrow_the_list(): void
    {
        $this->message();
        $this->message(['first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => 'ana@example.com', 'subject' => 'Complaint / Feedback', 'message' => 'The agent never called me back.']);

        $this->actingAs($this->admin)->get(route('admin.contact-messages', ['q' => 'called me back']))
            ->assertSee('Ana Reyes')->assertDontSee('Juan Dela Cruz');

        $this->get(route('admin.contact-messages', ['subject' => 'General Inquiry']))
            ->assertSee('Juan Dela Cruz')->assertDontSee('Ana Reyes');

        $this->get(route('admin.contact-messages', ['q' => 'nobody']))
            ->assertSee('No messages match your search');
    }

    public function test_a_message_can_be_handled_and_reopened(): void
    {
        $message = $this->message();

        $this->actingAs($this->admin)->from(route('admin.contact-messages'))
            ->post(route('admin.contact-messages.handle', $message))
            ->assertSessionHas('success', 'Message from Juan Dela Cruz marked as handled.');
        $this->assertSame('handled', $message->fresh()->status);

        $this->followingRedirects()->from(route('admin.contact-messages'))
            ->post(route('admin.contact-messages.reopen', $message))
            ->assertSee('Message from Juan Dela Cruz moved back to New.')
            ->assertSee('Juan Dela Cruz');
        $this->assertSame('new', $message->fresh()->status);
    }

    public function test_an_empty_inbox_says_so(): void
    {
        $this->actingAs($this->admin)->get(route('admin.contact-messages'))
            ->assertSee("You're all caught up", false);
    }

    public function test_only_admins_can_open_or_change_messages(): void
    {
        $message = $this->message();
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);

        $this->actingAs($broker)->get(route('admin.contact-messages'))->assertForbidden();
        $this->post(route('admin.contact-messages.reopen', $message))->assertForbidden();
    }
}
