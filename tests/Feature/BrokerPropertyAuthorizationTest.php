<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerPropertyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_cannot_view_or_edit_another_brokers_property(): void
    {
        $owner = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $otherBroker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $property = Property::create([
            'broker_id' => $owner->id,
            'name' => 'Private Listing',
            'slug' => 'private-listing',
            'type' => 'House and Lot',
            'status' => 'available',
        ]);

        $this->actingAs($otherBroker)
            ->get(route('broker.properties.show', $property))
            ->assertForbidden();
        $this->actingAs($otherBroker)
            ->get(route('broker.properties.edit', $property))
            ->assertForbidden();
    }

    public function test_broker_cannot_edit_or_delete_another_brokers_lot(): void
    {
        $owner = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $otherBroker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $property = Property::create([
            'broker_id' => $owner->id,
            'name' => 'Private Listing',
            'slug' => 'private-listing',
            'type' => 'House and Lot',
            'status' => 'available',
        ]);
        $lot = Lot::create([
            'property_id' => $property->id,
            'lot_number' => 'A-1',
            'status' => 'available',
        ]);

        $this->actingAs($otherBroker)
            ->get(route('broker.lots.edit', $lot))
            ->assertForbidden();
        $this->actingAs($otherBroker)
            ->delete(route('broker.lots.destroy', $lot))
            ->assertForbidden();
    }
}
