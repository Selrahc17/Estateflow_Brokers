<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Lot;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReservationPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $bea;

    private User $ryan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true]);
        $this->bea = User::factory()->create(['name' => 'Bea Broker', 'role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->ryan = User::factory()->create(['name' => 'Ryan Agent', 'role' => 'agent', 'broker_id' => $this->bea->id, 'is_active' => true, 'is_approved' => true]);
    }

    private function reservation(string $status, array $overrides = []): Reservation
    {
        $agent = $overrides['agent'] ?? $this->ryan;
        $property = Property::firstOrCreate(['slug' => 'sabang-'.$agent->id], ['broker_id' => $agent->id, 'name' => $overrides['property'] ?? 'Sabang Subdivision Lots', 'type' => 'Subdivision Lot Only', 'status' => 'available']);
        $lot = Lot::create(['property_id' => $property->id, 'lot_number' => 'Blk 1 Lot '.fake()->unique()->numberBetween(1, 999), 'status' => in_array($status, ['pending', 'confirmed'], true) ? 'reserved' : ($status === 'completed' ? 'sold' : 'available')]);
        $client = Client::create(['broker_id' => $agent->id, 'first_name' => $overrides['first'] ?? 'Ana', 'last_name' => 'Reyes', 'email' => fake()->unique()->safeEmail(), 'password' => 'x', 'status' => 'active']);

        return Reservation::create(['client_id' => $client->id, 'broker_id' => $agent->id, 'lot_id' => $lot->id, 'reservation_code' => 'RES-'.fake()->unique()->numerify('#####'), 'status' => $status, 'total_price' => 360000]);
    }

    public function test_the_list_names_the_agent_and_their_broker_and_counts_each_status(): void
    {
        $this->reservation('requested');
        $this->reservation('confirmed');

        $this->actingAs($this->admin)->get(route('admin.reservations'))
            ->assertOk()
            ->assertSee('Agent / Broker')
            ->assertSee('Ryan Agent')
            ->assertSee('Bea Broker')
            ->assertSee('Requested by buyer')
            ->assertViewHas('counts', fn ($counts) => $counts['requested'] === 1 && $counts['confirmed'] === 1 && $counts['all'] === 2);
    }

    public function test_status_tabs_search_and_broker_filter_narrow_the_list(): void
    {
        $requested = $this->reservation('requested', ['first' => 'Liza']);
        $otherBroker = User::factory()->create(['name' => 'Carlo Broker', 'role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $otherAgent = User::factory()->create(['name' => 'Mia Agent', 'role' => 'agent', 'broker_id' => $otherBroker->id, 'is_active' => true, 'is_approved' => true]);
        $other = $this->reservation('confirmed', ['agent' => $otherAgent, 'property' => 'Lakeview Homes', 'first' => 'Paolo']);

        $this->actingAs($this->admin)->get(route('admin.reservations', ['status' => 'requested']))
            ->assertSee($requested->reservation_code)->assertDontSee($other->reservation_code);

        $this->get(route('admin.reservations', ['search' => 'Lakeview']))
            ->assertSee($other->reservation_code)->assertDontSee($requested->reservation_code);

        $this->get(route('admin.reservations', ['broker' => $otherBroker->id]))
            ->assertSee($other->reservation_code)->assertDontSee($requested->reservation_code);

        $this->get(route('admin.reservations', ['search' => 'nobody']))
            ->assertSee('No reservations match your search');
    }

    public function test_a_buyer_request_offers_confirm_or_decline_instead_of_a_status_dropdown(): void
    {
        $reservation = $this->reservation('requested');

        $this->actingAs($this->admin)->get(route('admin.reservations.show', $reservation))
            ->assertOk()
            ->assertSee('Confirm request')
            ->assertSee('Decline request')
            ->assertDontSee('Mark as sold')
            ->assertDontSee('<select name="status"', false)
            ->assertSee('Ryan Agent')
            ->assertSee('Bea Broker');
    }

    public function test_a_completed_sale_offers_no_actions(): void
    {
        $reservation = $this->reservation('completed');

        $this->actingAs($this->admin)->get(route('admin.reservations.show', $reservation))
            ->assertSee('This sale is complete and can no longer be changed.')
            ->assertDontSee('Cancel reservation')
            ->assertDontSee('Mark as sold');
    }

    public function test_confirming_says_what_happened_to_the_lot(): void
    {
        $reservation = $this->reservation('requested');

        $this->actingAs($this->admin)->followingRedirects()
            ->from(route('admin.reservations.show', $reservation))
            ->patch(route('admin.reservations.status', $reservation), ['status' => 'confirmed'])
            ->assertSee("Reservation {$reservation->reservation_code} confirmed. The lot is now reserved.")
            ->assertSee('Mark as sold');

        $this->assertSame('reserved', $reservation->lot->fresh()->status);
    }
}
