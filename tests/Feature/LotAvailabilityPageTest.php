<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\Property;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The agent's Lot Availability page: one project at a time, lots grouped by
 * block in number order, and one-tap status changes.
 */
class LotAvailabilityPageTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private Property $sabang;

    protected function setUp(): void
    {
        parent::setUp();

        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->agent = User::factory()->create(['role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        Property::create(['broker_id' => $this->agent->id, 'name' => 'Single House', 'slug' => 'single', 'type' => 'House and Lot', 'status' => 'available']);
        $this->sabang = Property::create(['broker_id' => $this->agent->id, 'name' => 'Sabang Subdivision Lots', 'slug' => 'sabang', 'type' => 'Subdivision Lot Only', 'status' => 'available']);
        foreach ([['Blk 15 Lot 2', 81, 421200, 'available'], ['Blk 2 Lot 46', 72, 360000, 'sold'], ['Blk 2 Lot 45', 72, 360000, 'available'], ['Blk 15 Lot 10', 80, 400000, 'reserved'], ['Blk 2 Lot 100', 72, 360000, 'available']] as [$number, $sqm, $price, $status]) {
            Lot::create(['property_id' => $this->sabang->id, 'lot_number' => $number, 'square_meters' => $sqm, 'price' => $price, 'status' => $status]);
        }
    }

    public function test_it_opens_on_the_project_with_lots_grouped_by_block_in_number_order(): void
    {
        $this->actingAs($this->agent)->get(route('agent.lots.index'))
            ->assertOk()
            ->assertViewHas('selected', fn ($selected) => $selected?->is($this->sabang))
            ->assertViewHas('lots', fn ($lots) => $lots->pluck('lot_number')->all() === ['Blk 2 Lot 45', 'Blk 2 Lot 46', 'Blk 2 Lot 100', 'Blk 15 Lot 2', 'Blk 15 Lot 10'])
            ->assertSeeInOrder(['Block 2', '45', '46', '100', 'Block 15', '2', '10'])
            ->assertSee('2 of 3 available')
            ->assertSeeInOrder(['Available', '3', 'of 5', 'Reserved', '1', 'Sold', '1', 'Value still available', '₱1,141,200']);
    }

    public function test_the_list_view_filters_by_status_and_finds_a_lot(): void
    {
        $this->actingAs($this->agent);

        $this->get(route('agent.lots.index', ['property_id' => $this->sabang->id, 'view' => 'list', 'status' => 'available']))
            ->assertViewHas('lots', fn ($lots) => $lots->pluck('lot_number')->all() === ['Blk 2 Lot 45', 'Blk 2 Lot 100', 'Blk 15 Lot 2'])
            ->assertSee('₱5,200');

        $this->get(route('agent.lots.index', ['property_id' => $this->sabang->id, 'view' => 'list', 'q' => 'blk 15 lot 1']))
            ->assertViewHas('lots', fn ($lots) => $lots->pluck('lot_number')->all() === ['Blk 15 Lot 10']);

        $this->get(route('agent.lots.index', ['property_id' => $this->sabang->id, 'q' => 'nothing']))
            ->assertSee('No lots match these filters');
    }

    public function test_a_project_without_lots_explains_when_lots_are_needed(): void
    {
        $single = Property::where('name', 'Single House')->first();

        $this->actingAs($this->agent)->get(route('agent.lots.index', ['property_id' => $single->id]))
            ->assertSee('Single House has no lots yet')
            ->assertSee("A single house or lot doesn't need any", false);
    }

    public function test_changing_a_status_to_sold_and_back_updates_the_commission_records(): void
    {
        $lot = Lot::where('lot_number', 'Blk 2 Lot 45')->first();
        $this->actingAs($this->agent);

        $this->patch(route('agent.lots.status', $lot), ['status' => 'sold'])->assertSessionHas('success');
        $this->assertSame('sold', $lot->fresh()->status);
        $this->assertSame(1, Sale::where('lot_id', $lot->id)->count());

        $this->patch(route('agent.lots.status', $lot), ['status' => 'reserved']);
        $this->assertSame('reserved', $lot->fresh()->status);
        $this->assertSame(0, Sale::where('lot_id', $lot->id)->count(), 'An untouched record is removed when Sold is undone');

        $this->patch(route('agent.lots.status', $lot), ['status' => 'gone'])->assertSessionHasErrors('status');
    }

    public function test_agents_only_see_and_change_their_own_lots(): void
    {
        $other = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        $lot = Lot::where('lot_number', 'Blk 2 Lot 45')->first();

        $this->actingAs($other)->get(route('agent.lots.index'))->assertDontSee('Sabang Subdivision Lots')->assertSee('No projects yet');
        $this->actingAs($other)->get(route('agent.lots.index', ['property_id' => $this->sabang->id]))->assertDontSee('Blk 2 Lot 45');
        $this->patch(route('agent.lots.status', $lot), ['status' => 'sold'])->assertForbidden();
        $this->assertSame('available', $lot->fresh()->status);
    }
}
