<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Lot Availability tiles on the buyer's property page show each lot's size,
 * with the price on hover, because lots in one project can differ.
 */
class ClientLotAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_available_lot_shows_its_area_and_price(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        $property = Property::create([
            'broker_id' => $agent->id, 'name' => 'Villa Cassandra I', 'slug' => 'villa-cassandra-i',
            'type' => 'Subdivision Lot Only', 'status' => 'available', 'price' => 1320000,
        ]);
        Lot::create(['property_id' => $property->id, 'lot_number' => 'Blk 24 Lot 4', 'square_meters' => 151, 'price' => 1585500, 'status' => 'available']);
        Lot::create(['property_id' => $property->id, 'lot_number' => 'Blk 20 Lot 32', 'square_meters' => 120, 'price' => 1320000, 'status' => 'available']);
        Lot::create(['property_id' => $property->id, 'lot_number' => 'Blk 1 Lot 1', 'square_meters' => 120, 'price' => 999999, 'status' => 'sold']);

        $this->get(route('client.property.show', $property->slug))
            ->assertOk()
            ->assertSee('Lot Availability')
            ->assertSeeInOrder(['Blk 24 Lot 4', '151 sqm', 'Blk 24 Lot 4 — 151 sqm · ₱1,585,500'], false)
            ->assertSeeInOrder(['Blk 20 Lot 32', '120 sqm', 'Blk 20 Lot 32 — 120 sqm · ₱1,320,000'], false)
            ->assertDontSee('Blk 1 Lot 1');
    }

    public function test_a_lot_without_a_size_or_price_still_shows(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        $property = Property::create([
            'broker_id' => $agent->id, 'name' => 'Sabang Lots', 'slug' => 'sabang-lots',
            'type' => 'Subdivision Lot Only', 'status' => 'available',
        ]);
        Lot::create(['property_id' => $property->id, 'lot_number' => 'Blk 2 Lot 45', 'status' => 'available']);

        $this->get(route('client.property.show', $property->slug))
            ->assertOk()
            ->assertSee('Blk 2 Lot 45 — Available', false)
            ->assertDontSee(' sqm');
    }
}
