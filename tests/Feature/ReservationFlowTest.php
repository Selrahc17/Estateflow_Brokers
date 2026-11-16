<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Client;
use App\Models\Lot;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Agents reserve for any of their clients. A buyer's request does not hold
 * the lot until the agent confirms it, and client statuses block nothing.
 */
class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private Property $sabang;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->agent = User::factory()->create(['name' => 'Ryan Yasona', 'role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        $this->sabang = Property::create(['broker_id' => $this->agent->id, 'name' => 'Sabang Subdivision Lots', 'slug' => 'sabang', 'type' => 'Subdivision Lot Only', 'status' => 'available']);
        foreach ([['Blk 2 Lot 45', 360000], ['Blk 2 Lot 46', 360000], ['Blk 15 Lot 2', 421200]] as [$number, $price]) {
            Lot::create(['property_id' => $this->sabang->id, 'lot_number' => $number, 'square_meters' => 72, 'price' => $price, 'status' => 'available']);
        }
        // A brand-new client, still "Pending".
        $this->client = Client::create(['broker_id' => $this->agent->id, 'first_name' => 'Pedro', 'last_name' => 'Cruz', 'email' => 'pedro@example.com', 'password' => 'x', 'status' => 'active', 'qualification_status' => 'pending']);
    }

    private function lot(string $number): Lot
    {
        return Lot::where('lot_number', $number)->firstOrFail();
    }

    private function buyerWithRecord(): User
    {
        $buyer = User::factory()->create(['name' => 'Ana Reyes', 'role' => 'client', 'is_active' => true, 'is_approved' => true]);
        Client::create(['user_id' => $buyer->id, 'broker_id' => $this->agent->id, 'first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => $buyer->email, 'password' => 'x', 'status' => 'active']);

        return $buyer;
    }

    public function test_the_form_lists_every_client_and_groups_lots_by_project_with_prices(): void
    {
        $this->actingAs($this->agent)->get(route('agent.reservations.create'))
            ->assertOk()
            ->assertSee('Pedro Cruz')
            ->assertSee('<optgroup label="Sabang Subdivision Lots (3 available)">', false)
            ->assertSeeInOrder(['Blk 2 Lot 45 · 72 sqm · ₱360,000', 'Blk 2 Lot 46', 'Blk 15 Lot 2 · 72 sqm · ₱421,200'])
            ->assertDontSee('Lot Blk')
            ->assertSee('Filled in from the lot');
    }

    public function test_an_agent_with_no_clients_or_lots_is_told_what_to_do(): void
    {
        $fresh = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);

        $this->actingAs($fresh)->get(route('agent.reservations.create'))
            ->assertSee('You have no clients yet.')
            ->assertSee('No lots are available to reserve.');
    }

    public function test_an_agent_can_reserve_for_a_pending_client(): void
    {
        $lot = $this->lot('Blk 2 Lot 45');

        $this->actingAs($this->agent)->post(route('agent.reservations.store'), [
            'client_id' => $this->client->id, 'lot_id' => $lot->id, 'total_price' => 360000, 'payment_schedule' => 'monthly', 'payment_terms_months' => 12,
        ])->assertSessionHasNoErrors()->assertRedirect(route('agent.reservations.index'));

        $this->assertSame('pending', Reservation::sole()->status);
        $this->assertSame('reserved', $lot->fresh()->status);
    }

    public function test_a_buyer_request_waits_for_the_agent_and_cannot_be_sent_twice(): void
    {
        $buyer = $this->buyerWithRecord();
        $lot = $this->lot('Blk 2 Lot 45');

        $this->actingAs($buyer)->post(route('client.account.reservation.store', $lot))
            ->assertSessionHas('success', 'Reservation request sent for Blk 2 Lot 45. Ryan Yasona will confirm it with you.');
        $this->post(route('client.account.reservation.store', $lot))
            ->assertSessionHas('error', 'You already asked for Blk 2 Lot 45. The agent will confirm it with you.');

        $request = Reservation::sole();
        $this->assertSame(['requested', '360000.00'], [$request->status, $request->total_price]);
        $this->assertSame('available', $lot->fresh()->status);
        $this->assertSame('New reservation request', AppNotification::where('user_id', $this->agent->id)->sole()->title);

        $this->get(route('client.account.reservation'))->assertSee('Waiting for agent')->assertSee('Blk 2 Lot 45')->assertDontSee('Lot Blk');
    }

    public function test_confirming_holds_the_lot_and_a_second_request_for_it_is_refused(): void
    {
        $lot = $this->lot('Blk 2 Lot 45');
        $first = Reservation::create(['client_id' => $this->client->id, 'broker_id' => $this->agent->id, 'lot_id' => $lot->id, 'reservation_code' => 'RES-ONE', 'status' => 'requested', 'total_price' => 360000]);
        $other = Client::create(['broker_id' => $this->agent->id, 'first_name' => 'Maria', 'last_name' => 'Santos', 'email' => 'maria@example.com', 'password' => 'x', 'status' => 'active']);
        $second = Reservation::create(['client_id' => $other->id, 'broker_id' => $this->agent->id, 'lot_id' => $lot->id, 'reservation_code' => 'RES-TWO', 'status' => 'requested', 'total_price' => 360000]);
        $this->actingAs($this->agent);

        $this->get(route('agent.reservations.show', $first))->assertSee('Request from the buyer')->assertSee('Confirm and Reserve Lot');

        $this->patch(route('agent.reservations.status', $first), ['status' => 'confirmed'])->assertSessionHas('success', 'Reservation confirmed. The lot is now reserved.');
        $this->assertSame('reserved', $lot->fresh()->status);

        $this->patch(route('agent.reservations.status', $second), ['status' => 'confirmed'])->assertSessionHasErrors(['status' => 'Blk 2 Lot 45 is already reserved for someone else.']);
        $this->assertSame('requested', $second->fresh()->status);

        // Declining the second request leaves the first one's hold alone.
        $this->patch(route('agent.reservations.status', $second), ['status' => 'cancelled']);
        $this->assertSame(['cancelled', 'reserved'], [$second->fresh()->status, $lot->fresh()->status]);
    }

    public function test_cancelling_frees_the_lot_and_completing_records_the_sale(): void
    {
        $free = $this->lot('Blk 2 Lot 46');
        $sold = $this->lot('Blk 15 Lot 2');
        $free->update(['status' => 'reserved']);
        $sold->update(['status' => 'reserved']);
        $toCancel = Reservation::create(['client_id' => $this->client->id, 'broker_id' => $this->agent->id, 'lot_id' => $free->id, 'reservation_code' => 'RES-C', 'status' => 'pending', 'total_price' => 1]);
        $toComplete = Reservation::create(['client_id' => $this->client->id, 'broker_id' => $this->agent->id, 'lot_id' => $sold->id, 'reservation_code' => 'RES-D', 'status' => 'confirmed', 'total_price' => 1]);
        $this->actingAs($this->agent);

        $this->patch(route('agent.reservations.status', $toCancel), ['status' => 'cancelled']);
        $this->patch(route('agent.reservations.status', $toComplete), ['status' => 'completed']);

        $this->assertSame('available', $free->fresh()->status);
        $this->assertSame('sold', $sold->fresh()->status);
        $this->assertSame(1, Sale::where('lot_id', $sold->id)->count(), 'A completed reservation is a sale for the broker');
    }

    public function test_the_list_filters_search_and_puts_requests_first(): void
    {
        Reservation::create(['client_id' => $this->client->id, 'broker_id' => $this->agent->id, 'lot_id' => $this->lot('Blk 2 Lot 46')->id, 'reservation_code' => 'RES-PENDING', 'status' => 'pending', 'total_price' => 1]);
        $maria = Client::create(['broker_id' => $this->agent->id, 'first_name' => 'Maria', 'last_name' => 'Santos', 'email' => 'maria@example.com', 'password' => 'x', 'status' => 'active']);
        Reservation::create(['client_id' => $maria->id, 'broker_id' => $this->agent->id, 'lot_id' => $this->lot('Blk 2 Lot 45')->id, 'reservation_code' => 'RES-REQUEST', 'status' => 'requested', 'total_price' => 1]);
        $this->actingAs($this->agent);

        $this->get(route('agent.reservations.index'))
            ->assertSeeInOrder(['Requests from Buyers', '1', 'Pending', '1'])
            ->assertSeeInOrder(['RES-REQUEST', 'RES-PENDING'])
            ->assertSee('Requested by buyer');
        $this->get(route('agent.reservations.index', ['status' => 'pending']))->assertSee('RES-PENDING')->assertDontSee('RES-REQUEST');
        $this->get(route('agent.reservations.index', ['search' => 'maria santos']))->assertSee('RES-REQUEST')->assertDontSee('RES-PENDING');
        $this->get(route('agent.reservations.index', ['search' => 'BLK 2 LOT 46']))->assertSee('RES-PENDING')->assertDontSee('RES-REQUEST');
    }

    public function test_client_statuses_no_longer_block_site_visits_or_approval(): void
    {
        $this->actingAs($this->agent)->post(route('agent.site-visits.store'), [
            'client_id' => $this->client->id, 'property_id' => $this->sabang->id, 'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'), 'status' => 'pending',
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, $this->client->siteVisits()->count(), 'A pending client can be booked for a site visit');

        $this->patch(route('agent.clients.qualification-status', $this->client), ['qualification_status' => 'approved'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $this->client->fresh()->qualification_status);
    }
}
