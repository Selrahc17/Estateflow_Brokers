<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\Lot;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * One buyer can work with agents from different Brokers. Each agent keeps a
 * separate record, so one team's review never counts for another.
 */
class BuyerWithSeveralAgentsTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private array $teamA;

    private array $teamB;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        $this->buyer = User::factory()->create(['name' => 'Ana Reyes', 'role' => 'client', 'email' => 'ana@example.com', 'is_active' => true, 'is_approved' => true]);
        $this->teamA = $this->team('A', 'approved');
        $this->teamB = $this->team('B', 'pending');
    }

    private function team(string $tag, string $review): array
    {
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $agent = User::factory()->create(['name' => "Agent {$tag}", 'role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        $client = Client::create(['user_id' => $this->buyer->id, 'broker_id' => $agent->id, 'first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => 'ana@example.com', 'password' => 'secret', 'status' => 'active', 'qualification_status' => $review]);
        $property = Property::create(['broker_id' => $agent->id, 'name' => "Listing {$tag}", 'slug' => "listing-{$tag}", 'type' => 'Lot Only', 'status' => 'available']);
        $lot = Lot::create(['property_id' => $property->id, 'lot_number' => "L{$tag}", 'status' => 'available']);

        return compact('broker', 'agent', 'client', 'property', 'lot');
    }

    public function test_the_same_buyer_can_be_a_client_of_two_agents(): void
    {
        $this->assertSame(2, Client::where('email', 'ana@example.com')->count());
        $this->assertSame(2, $this->buyer->clientProfiles()->count());
    }

    public function test_one_teams_approval_does_not_let_the_buyer_reserve_with_another(): void
    {
        $this->actingAs($this->buyer)->post(route('client.account.reservation.store', $this->teamA['lot']))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', ['lot_id' => $this->teamA['lot']->id, 'client_id' => $this->teamA['client']->id, 'broker_id' => $this->teamA['agent']->id]);

        $this->post(route('client.account.reservation.store', $this->teamB['lot']))->assertSessionHasErrors('reservation');
        $this->assertDatabaseMissing('reservations', ['lot_id' => $this->teamB['lot']->id]);
    }

    public function test_a_buyer_with_no_record_for_that_agent_is_told_to_inquire_first(): void
    {
        $outsider = $this->team('C', 'approved');
        $outsider['client']->forceDelete();

        $this->actingAs($this->buyer)
            ->post(route('client.account.reservation.store', $outsider['lot']))
            ->assertSessionHasErrors(['reservation' => 'Send an inquiry for this property first. Its agent needs to review your requirements before you can reserve.']);
        $this->post(route('client.account.site-visits.store', $outsider['property']), ['scheduled_at' => now()->addDay()->toDateTimeString()])
            ->assertSessionHasErrors('site_visit');
    }

    public function test_site_visits_use_the_review_of_that_listings_agent(): void
    {
        $this->teamB['client']->update(['qualification_status' => 'under_review']);

        $this->actingAs($this->buyer)->post(route('client.account.site-visits.store', $this->teamB['property']), ['scheduled_at' => now()->addDay()->toDateTimeString()])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('site_visits', ['client_id' => $this->teamB['client']->id, 'broker_id' => $this->teamB['agent']->id]);

        $this->post(route('client.account.site-visits.store', $this->teamA['property']), ['scheduled_at' => now()->addDay()->toDateTimeString()])->assertSessionHasErrors('site_visit');
    }

    public function test_the_portal_lists_reservations_and_visits_from_every_agent(): void
    {
        Reservation::create(['client_id' => $this->teamA['client']->id, 'broker_id' => $this->teamA['agent']->id, 'lot_id' => $this->teamA['lot']->id, 'reservation_code' => 'RES-AAA', 'status' => 'pending', 'total_price' => 1]);
        Reservation::create(['client_id' => $this->teamB['client']->id, 'broker_id' => $this->teamB['agent']->id, 'lot_id' => $this->teamB['lot']->id, 'reservation_code' => 'RES-BBB', 'status' => 'confirmed', 'total_price' => 1]);
        $visit = SiteVisit::create(['client_id' => $this->teamB['client']->id, 'property_id' => $this->teamB['property']->id, 'broker_id' => $this->teamB['agent']->id, 'scheduled_at' => now()->addDay(), 'status' => 'pending']);

        $this->actingAs($this->buyer)->get(route('client.account.reservation'))->assertOk()->assertSee('Listing A')->assertSee('Listing B');
        $this->get(route('client.account.site-visits'))->assertOk()->assertSee('Listing B');
        $this->get(route('client.account.home'))->assertOk()->assertSee('With Agent A')->assertSee('With Agent B')->assertSee('Approved')->assertSee('Pending');
        $this->patch(route('client.account.site-visits.cancel', $visit))->assertRedirect();
        $this->assertSame('cancelled', $visit->fresh()->status);
    }

    public function test_a_buyer_without_any_record_can_still_open_their_lists(): void
    {
        $newcomer = User::factory()->create(['role' => 'client', 'is_active' => true, 'is_approved' => true]);

        $this->actingAs($newcomer)->get(route('client.account.reservation'))->assertOk();
        $this->get(route('client.account.site-visits'))->assertOk();
        $this->get(route('client.account.home'))->assertOk();
    }

    public function test_profile_changes_reach_every_agents_record(): void
    {
        $this->actingAs($this->buyer)->post(route('client.account.profile.update'), [
            'first_name' => 'Ana Maria',
            'last_name' => 'Reyes',
            'email' => 'ana@example.com',
            'phone' => '09998887777',
            'civil_status' => 'married',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, Client::where('civil_status', 'married')->count());

        $this->assertSame(2, Client::where('first_name', 'Ana Maria')->where('phone', '09998887777')->count());
    }

    public function test_the_buyer_can_download_documents_from_any_of_their_records_but_no_one_elses(): void
    {
        Storage::disk('local')->put('documents/mine.pdf', 'id');
        $mine = Document::create(['client_id' => $this->teamB['client']->id, 'name' => 'ID', 'type' => 'id', 'file_path' => 'documents/mine.pdf', 'status' => 'pending']);
        $stranger = Client::create(['broker_id' => $this->teamB['agent']->id, 'first_name' => 'Other', 'last_name' => 'Buyer', 'email' => 'other@example.com', 'password' => 'secret', 'status' => 'active']);
        $theirs = Document::create(['client_id' => $stranger->id, 'name' => 'ID', 'type' => 'id', 'file_path' => 'documents/mine.pdf', 'status' => 'pending']);

        $this->actingAs($this->buyer)->get(route('client.account.documents.download', $mine))->assertOk();
        $this->get(route('client.account.documents.download', $theirs))->assertForbidden();
    }

    public function test_an_agent_can_add_a_buyer_another_team_already_has(): void
    {
        $this->actingAs($this->teamA['agent'])->post(route('agent.clients.store'), ['first_name' => 'Pedro', 'last_name' => 'Cruz', 'email' => 'pedro@example.com'])->assertRedirect(route('agent.clients.index'))->assertSessionHasNoErrors();
        $this->actingAs($this->teamB['agent'])->post(route('agent.clients.store'), ['first_name' => 'Pedro', 'last_name' => 'Cruz', 'email' => 'pedro@example.com'])->assertRedirect(route('agent.clients.index'))->assertSessionHasNoErrors();
        $this->post(route('agent.clients.store'), ['first_name' => 'Pedro', 'last_name' => 'Cruz', 'email' => 'pedro@example.com'])->assertSessionHasErrors('email');

        $this->assertSame(2, Client::where('email', 'pedro@example.com')->count());
    }

    public function test_each_agent_sees_only_their_own_record_of_the_buyer(): void
    {
        $this->actingAs($this->teamA['agent'])->get(route('agent.clients.show', $this->teamA['client']))->assertOk();
        $this->get(route('agent.clients.show', $this->teamB['client']))->assertForbidden();
        $this->actingAs($this->teamA['broker'])->get(route('broker.team.clients.show', $this->teamB['client']))->assertNotFound();
    }
}
