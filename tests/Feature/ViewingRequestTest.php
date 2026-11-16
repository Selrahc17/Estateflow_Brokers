<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Buyers can send an inquiry or schedule a viewing, and always see that it
 * went through. Agents get both in one Inquiries page.
 */
class ViewingRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create(['name' => 'Ryan Yasona', 'role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        $this->property = Property::create(['broker_id' => $this->agent->id, 'name' => 'Conchita Ville', 'slug' => 'conchita-ville', 'type' => 'Subdivision Lot Only', 'status' => 'available']);
    }

    private function guestForm(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Buyer', 'email' => 'jane@example.com', 'phone' => '09171234567',
            'message' => $this->property->inquiryMessage(),
        ], $overrides);
    }

    public function test_a_guest_sees_a_pop_up_after_sending_an_inquiry(): void
    {
        $this->post(route('client.property.inquire', $this->property), $this->guestForm())
            ->assertRedirect(route('client.property.show', $this->property->slug));

        $this->get(route('client.property.show', $this->property->slug))
            ->assertSee('data-flash-toast', false)
            ->assertSee('Inquiry sent! Ryan Yasona will contact you soon.');

        $inquiry = Inquiry::sole();
        $this->assertSame(['inquiry', null], [$inquiry->type, $inquiry->preferred_viewing_at]);
    }

    public function test_a_guest_can_schedule_a_viewing_with_a_preferred_date_and_time(): void
    {
        $day = now()->addDays(3)->toDateString();

        $this->post(route('client.property.inquire', $this->property), $this->guestForm([
            'type' => 'viewing', 'message' => $this->property->viewingMessage(), 'preferred_date' => $day, 'preferred_time' => '14:30',
        ]))->assertSessionHasNoErrors()->assertSessionHas('success', 'Viewing request sent! Ryan Yasona will confirm the schedule with you soon.');

        $inquiry = Inquiry::sole();
        $this->assertSame('viewing', $inquiry->type);
        $this->assertSame("{$day} 14:30", $inquiry->preferred_viewing_at->format('Y-m-d H:i'));
        $this->assertStringContainsString("I'm interested in viewing this property (Conchita Ville)", $inquiry->message);

        $notice = AppNotification::where('user_id', $this->agent->id)->sole();
        $this->assertSame('New viewing request', $notice->title);
        $this->assertStringContainsString('would like to view Conchita Ville on', $notice->message);
    }

    public function test_a_viewing_needs_a_future_date_and_time(): void
    {
        $send = fn (array $fields) => $this->post(route('client.property.inquire', $this->property), $this->guestForm(['type' => 'viewing'] + $fields));

        $send([])->assertSessionHasErrors(['preferred_date', 'preferred_time']);
        $send(['preferred_date' => now()->subDay()->toDateString(), 'preferred_time' => '10:00'])->assertSessionHasErrors('preferred_date');
        $send(['preferred_date' => now()->toDateString(), 'preferred_time' => now()->subHour()->format('H:i')])->assertSessionHasErrors('preferred_time');
        $this->assertSame(0, Inquiry::count());
    }

    public function test_a_plain_inquiry_ignores_any_leftover_date_fields(): void
    {
        $this->post(route('client.property.inquire', $this->property), $this->guestForm(['type' => 'inquiry', 'preferred_date' => now()->addDay()->toDateString(), 'preferred_time' => '10:00']))
            ->assertSessionHasNoErrors();

        $this->assertSame(['inquiry', null], [Inquiry::sole()->type, Inquiry::sole()->preferred_viewing_at]);
    }

    public function test_the_property_page_offers_both_tabs_and_fills_in_a_signed_in_buyers_email(): void
    {
        $this->get(route('client.property.show', $this->property->slug))
            ->assertSee('Send Inquiry')->assertSee('Schedule Viewing')
            ->assertSee('Preferred Date')->assertSee('Preferred Time')
            ->assertSee('Please let me know your available viewing times.')
            ->assertDontSee('Request a site visit');

        $buyer = User::factory()->create(['email' => 'kurt@example.com', 'phone' => '09998887777', 'role' => 'client', 'is_active' => true, 'is_approved' => true]);
        $this->actingAs($buyer)->get(route('client.property.show', $this->property->slug))
            ->assertSee('value="kurt@example.com"', false)
            ->assertSee('value="09998887777"', false);
    }

    public function test_a_signed_in_buyer_can_schedule_a_viewing_too(): void
    {
        $buyer = User::factory()->create(['role' => 'client', 'is_active' => true, 'is_approved' => true]);

        $this->actingAs($buyer)->post(route('client.account.inquiries.store', $this->property->id), [
            'type' => 'viewing', 'email' => $buyer->email, 'phone' => '09171234567', 'message' => $this->property->viewingMessage(),
            'preferred_date' => now()->addDay()->toDateString(), 'preferred_time' => '09:00',
        ])->assertSessionHasNoErrors();

        $this->assertSame([$buyer->id, 'viewing'], [Inquiry::sole()->user_id, Inquiry::sole()->type]);
    }

    public function test_a_failed_viewing_request_reopens_on_the_viewing_tab(): void
    {
        $this->from(route('client.property.show', $this->property->slug))
            ->post(route('client.property.inquire', $this->property), $this->guestForm(['type' => 'viewing']));

        $this->get(route('client.property.show', $this->property->slug))
            ->assertSee('<input type="hidden" name="type" :value="mode" value="viewing">', false)
            ->assertSee('Choose the day you would like to view the property.');
    }

    public function test_the_agent_page_lists_viewing_requests_with_search_and_filters(): void
    {
        $other = Property::create(['broker_id' => $this->agent->id, 'name' => 'Vista Townhomes', 'slug' => 'vista', 'type' => 'House and Lot', 'status' => 'available']);
        Inquiry::create(['broker_id' => $this->agent->id, 'property_id' => $this->property->id, 'type' => 'viewing', 'preferred_viewing_at' => now()->addDays(2)->setTime(14, 30), 'contact_name' => 'Jane Buyer', 'email' => 'jane@example.com', 'phone' => '0917', 'message' => 'Viewing please', 'status' => 'new']);
        Inquiry::create(['broker_id' => $this->agent->id, 'property_id' => $other->id, 'contact_name' => 'Mark Asker', 'email' => 'mark@example.com', 'phone' => '0918', 'message' => 'Price?', 'status' => 'contacted']);
        Inquiry::create(['broker_id' => User::factory()->create(['role' => 'agent'])->id, 'property_id' => $other->id, 'contact_name' => 'Someone Elses Lead', 'email' => 's@example.com', 'message' => 'Hi', 'status' => 'new']);
        $this->actingAs($this->agent);

        $this->get(route('agent.inquiries.index'))
            ->assertOk()
            ->assertSeeInOrder(['All Inquiries', '2', 'New, Not Yet Answered', '1', 'Open Viewing Requests', '1'])
            ->assertSee('Jane Buyer')->assertSee('Mark Asker')->assertDontSee('Someone Elses Lead')
            ->assertSee(now()->addDays(2)->format('D, M j') . ' · 2:30 PM');

        $this->get(route('agent.inquiries.index', ['type' => 'viewing']))->assertSee('Jane Buyer')->assertDontSee('Mark Asker');
        $this->get(route('agent.inquiries.index', ['q' => 'VISTA']))->assertSee('Mark Asker')->assertDontSee('Jane Buyer');
        $this->get(route('agent.inquiries.index', ['status' => 'contacted']))->assertSee('Mark Asker')->assertDontSee('Jane Buyer');
        $this->get(route('agent.inquiries.index', ['q' => 'nobody']))->assertSee('No inquiries match these filters');
    }

    public function test_a_viewing_request_leads_to_a_pre_filled_site_visit(): void
    {
        $at = now()->addDays(2)->setTime(14, 30);
        $inquiry = Inquiry::create(['broker_id' => $this->agent->id, 'property_id' => $this->property->id, 'type' => 'viewing', 'preferred_viewing_at' => $at, 'contact_name' => 'Jane Buyer', 'email' => 'jane@example.com', 'phone' => '0917', 'message' => 'Viewing please', 'status' => 'new']);
        $this->actingAs($this->agent);

        $this->get(route('agent.inquiries.show', $inquiry))
            ->assertSee('Viewing request')
            ->assertSee($at->format('l, F j, Y · g:i A'))
            ->assertSee('Add as Client to Schedule');

        $client = Client::create(['broker_id' => $this->agent->id, 'first_name' => 'Jane', 'last_name' => 'Buyer', 'email' => 'jane@example.com', 'password' => 'x', 'status' => 'active']);
        $link = route('agent.site-visits.create', ['client_id' => $client->id, 'property_id' => $this->property->id, 'inquiry_id' => $inquiry->id, 'scheduled_at' => $at->format('Y-m-d\TH:i')]);
        $this->get(route('agent.inquiries.show', $inquiry))->assertSee(e($link), false);

        $this->get($link)
            ->assertSee('<option value="' . $client->id . '" selected', false)
            ->assertSee('<option value="' . $this->property->id . '" selected', false)
            ->assertSee('value="' . $at->format('Y-m-d\TH:i') . '"', false)
            ->assertSee('Jane Buyer - Conchita Ville');
    }

    public function test_pages_with_their_own_banner_do_not_get_a_second_pop_up(): void
    {
        $this->withSession(['success' => 'Message sent.'])->get(route('client.contact'))
            ->assertDontSee('data-flash-toast', false);
        $this->withSession(['success' => 'Saved.'])->get(route('client.properties'))
            ->assertSee('data-flash-toast', false);
    }
}
