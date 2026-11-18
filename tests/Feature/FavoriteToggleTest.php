<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_can_save_and_unsave_a_property(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        $client = User::factory()->create(['role' => 'client', 'is_active' => true, 'is_approved' => true]);
        $property = Property::create(['broker_id' => $agent->id, 'name' => 'Vista Townhomes', 'slug' => 'vista', 'type' => 'House and Lot', 'status' => 'available']);

        $this->actingAs($client)->from(route('client.property.show', 'vista'))
            ->post(route('client.account.favorites.toggle', $property))
            ->assertRedirect();
        $this->assertDatabaseHas('favorites', ['user_id' => $client->id, 'property_id' => $property->id]);
        $this->get(route('client.property.show', 'vista'))->assertSee('Saved');

        $this->post(route('client.account.favorites.toggle', $property));
        $this->assertDatabaseMissing('favorites', ['user_id' => $client->id, 'property_id' => $property->id]);
    }
}
