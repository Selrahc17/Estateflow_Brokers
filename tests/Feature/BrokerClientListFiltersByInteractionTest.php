<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerClientListFiltersByInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_client_list_only_shows_clients_with_interaction(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $silentClient = Client::create([
            'broker_id' => $broker->id,
            'first_name' => 'Silent',
            'last_name' => 'Client',
            'email' => 'silent@example.com',
            'phone' => '09170000000',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $property = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Sample Property',
            'slug' => 'sample-property',
            'description' => 'A sample property.',
            'address' => '123 Sample St',
            'city' => 'Cebu City',
            'province' => 'Cebu',
            'type' => 'House and Lot',
            'price' => 5000000,
            'status' => 'available',
            'featured_image' => 'https://example.com/property.jpg',
        ]);

        $activeClient = Client::create([
            'broker_id' => $broker->id,
            'first_name' => 'Active',
            'last_name' => 'Client',
            'email' => 'active@example.com',
            'phone' => '09170000001',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        Inquiry::create([
            'user_id' => null,
            'property_id' => $property->id,
            'broker_id' => $broker->id,
            'message' => 'Interested in the property.',
            'phone' => '09170000001',
            'email' => $activeClient->email,
            'status' => 'new',
        ]);

        $this->actingAs($broker);

        $response = $this->get(route('broker.clients.index'));

        $response->assertStatus(200);
        $response->assertSee($activeClient->first_name . ' ' . $activeClient->last_name);
        $response->assertDontSee($silentClient->first_name . ' ' . $silentClient->last_name);
    }
}
