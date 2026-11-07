<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * An inquiry is an early sales contact. It stays separate from a Client until the
 * assigned agent decides otherwise.
 */
class InquiryLeadTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private User $otherAgent;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = $this->agentFor('Broker One');
        $this->otherAgent = $this->agentFor('Broker Two');
        $this->property = Property::create(['broker_id' => $this->agent->id, 'name' => 'Vista Homes', 'slug' => 'vista-homes', 'type' => 'House and Lot', 'status' => 'available']);
    }

    private function agentFor(string $brokerName): User
    {
        $broker = User::factory()->create(['name' => $brokerName, 'role' => 'broker', 'is_active' => true, 'is_approved' => true]);

        return User::factory()->create(['role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
    }

    private function guestInquiry(array $overrides = []): Inquiry
    {
        $this->post(route('client.property.inquire', $this->property), array_merge([
            'name' => 'Jane Buyer',
            'message' => 'Is this property still available?',
            'phone' => '09171234567',
            'email' => 'jane@example.com',
        ], $overrides))->assertSessionHasNoErrors();

        return Inquiry::latest('id')->first();
    }

    public function test_guest_inquiry_saves_the_buyer_details_on_the_lead(): void
    {
        $inquiry = $this->guestInquiry(['facebook' => 'Jane Buyer FB']);

        $this->assertSame('Jane Buyer', $inquiry->contact_name);
        $this->assertSame('09171234567', $inquiry->phone);
        $this->assertSame('jane@example.com', $inquiry->email);
        $this->assertSame('Jane Buyer FB', $inquiry->facebook);
        $this->assertSame($this->property->id, $inquiry->property_id);
        $this->assertSame($this->agent->id, $inquiry->broker_id);
    }

    public function test_facebook_is_optional_but_the_name_is_not(): void
    {
        $this->assertNull($this->guestInquiry()->facebook);

        $this->post(route('client.property.inquire', $this->property), [
            'message' => 'Is this property still available?',
            'phone' => '09171234567',
            'email' => 'noname@example.com',
        ])->assertSessionHasErrors('name');
    }

    public function test_an_inquiry_does_not_create_a_client(): void
    {
        $this->guestInquiry();

        $this->assertDatabaseMissing('clients', ['email' => 'jane@example.com']);
    }

    public function test_a_guest_cannot_move_or_change_an_existing_client(): void
    {
        $client = Client::create(['broker_id' => $this->otherAgent->id, 'first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => 'ana@example.com', 'phone' => '09170000000', 'password' => 'secret', 'status' => 'active']);

        $this->guestInquiry(['email' => 'ana@example.com', 'phone' => '09999999999']);

        $client->refresh();
        $this->assertSame($this->otherAgent->id, $client->broker_id);
        $this->assertSame('09170000000', $client->phone);
    }

    public function test_a_logged_in_client_stays_with_their_agent(): void
    {
        $user = User::factory()->create(['role' => 'client', 'is_active' => true, 'is_approved' => true]);
        $client = Client::create(['user_id' => $user->id, 'broker_id' => $this->otherAgent->id, 'first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => $user->email, 'password' => 'secret', 'status' => 'active']);

        $this->actingAs($user)->post(route('client.property.inquire', $this->property), [
            'message' => 'Is this property still available?',
            'phone' => '09171234567',
            'email' => $user->email,
        ])->assertRedirect();

        $this->assertSame($this->otherAgent->id, $client->fresh()->broker_id);
        $this->assertDatabaseHas('inquiries', ['user_id' => $user->id, 'contact_name' => $user->name, 'broker_id' => $this->agent->id]);
    }

    public function test_the_agent_can_turn_a_lead_into_a_client(): void
    {
        $inquiry = $this->guestInquiry();

        $this->actingAs($this->agent)->get(route('agent.inquiries.show', $inquiry))->assertSee('Add as Client');
        $this->post(route('agent.inquiries.client', $inquiry))->assertRedirect();

        $this->assertDatabaseHas('clients', ['email' => 'jane@example.com', 'broker_id' => $this->agent->id, 'first_name' => 'Jane', 'last_name' => 'Buyer', 'phone' => '09171234567']);
        $this->get(route('agent.inquiries.show', $inquiry))->assertSee('View Client')->assertDontSee('Add as Client');
    }

    public function test_adding_the_same_lead_twice_keeps_one_client(): void
    {
        $inquiry = $this->guestInquiry();

        $this->actingAs($this->agent)->post(route('agent.inquiries.client', $inquiry));
        $this->post(route('agent.inquiries.client', $inquiry))->assertRedirect();

        $this->assertSame(1, Client::where('email', 'jane@example.com')->count());
    }

    public function test_the_agent_can_claim_a_registered_buyer_with_no_agent(): void
    {
        $client = Client::create(['first_name' => 'New', 'last_name' => 'Member', 'email' => 'member@example.com', 'password' => 'secret', 'status' => 'active']);
        $inquiry = $this->guestInquiry(['email' => 'member@example.com']);

        $this->actingAs($this->agent)->post(route('agent.inquiries.client', $inquiry))->assertRedirect(route('agent.clients.show', $client));

        $this->assertSame($this->agent->id, $client->fresh()->broker_id);
    }

    public function test_a_client_of_another_team_is_refused_without_naming_the_team(): void
    {
        $client = Client::create(['broker_id' => $this->otherAgent->id, 'first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => 'ana@example.com', 'password' => 'secret', 'status' => 'active']);
        $inquiry = $this->guestInquiry(['email' => 'ana@example.com']);

        $this->actingAs($this->agent)
            ->from(route('agent.inquiries.show', $inquiry))
            ->post(route('agent.inquiries.client', $inquiry))
            ->assertRedirect(route('agent.inquiries.show', $inquiry))
            ->assertSessionHas('error');

        $this->assertSame($this->otherAgent->id, $client->fresh()->broker_id);
    }

    public function test_a_deleted_client_with_that_email_does_not_crash_the_action(): void
    {
        Client::create(['broker_id' => $this->agent->id, 'first_name' => 'Old', 'last_name' => 'Client', 'email' => 'jane@example.com', 'password' => 'secret', 'status' => 'active'])->delete();
        $inquiry = $this->guestInquiry();

        $this->actingAs($this->agent)
            ->post(route('agent.inquiries.client', $inquiry))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_another_agent_cannot_turn_the_lead_into_a_client(): void
    {
        $inquiry = $this->guestInquiry();

        $this->actingAs($this->otherAgent)->post(route('agent.inquiries.client', $inquiry))->assertForbidden();

        $this->assertDatabaseMissing('clients', ['email' => 'jane@example.com']);
    }

    public function test_only_real_facebook_addresses_become_links(): void
    {
        $this->assertSame('https://www.facebook.com/jane', (new Inquiry(['facebook' => 'https://www.facebook.com/jane']))->facebook_url);
        $this->assertNull((new Inquiry(['facebook' => 'javascript:alert(1)']))->facebook_url);
        $this->assertNull((new Inquiry(['facebook' => 'https://evil.example/facebook.com/']))->facebook_url);
        $this->assertNull((new Inquiry(['facebook' => 'Jane Buyer']))->facebook_url);
    }

    public function test_admin_sees_inquiries_from_every_broker(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true]);
        $otherListing = Property::create(['broker_id' => $this->otherAgent->id, 'name' => 'Harbor View', 'slug' => 'harbor-view', 'type' => 'Lot Only', 'status' => 'available']);
        $this->guestInquiry();
        Inquiry::create(['broker_id' => $this->otherAgent->id, 'property_id' => $otherListing->id, 'contact_name' => 'Pedro Buyer', 'message' => 'Hello there', 'email' => 'pedro@example.com', 'status' => 'contacted']);

        $this->actingAs($admin)->get(route('admin.inquiries'))
            ->assertOk()
            ->assertSee('Jane Buyer')
            ->assertSee('Pedro Buyer')
            ->assertSee('Broker One')
            ->assertSee('Broker Two');

        $this->get(route('admin.inquiries', ['broker' => $this->otherAgent->broker_id]))->assertSee('Pedro Buyer')->assertDontSee('Jane Buyer');
        $this->get(route('admin.inquiries', ['status' => 'contacted']))->assertSee('Pedro Buyer')->assertDontSee('Jane Buyer');
        $this->get(route('admin.inquiries', ['search' => 'jane@']))->assertSee('Jane Buyer')->assertDontSee('Pedro Buyer');
    }

    public function test_brokers_and_agents_cannot_open_the_admin_inquiries_page(): void
    {
        $this->actingAs($this->agent)->get(route('admin.inquiries'))->assertStatus(403);
        $this->actingAs($this->agent->broker)->get(route('admin.inquiries'))->assertStatus(403);
    }

    public function test_a_broker_sees_the_buyer_name_and_only_their_own_team(): void
    {
        $this->guestInquiry(['facebook' => 'Jane Buyer FB']);

        $this->actingAs($this->agent->broker)->get(route('broker.team.inquiries'))->assertSee('Jane Buyer')->assertSee('Jane Buyer FB');
        $this->actingAs($this->otherAgent->broker)->get(route('broker.team.inquiries'))->assertDontSee('Jane Buyer');
    }
}
