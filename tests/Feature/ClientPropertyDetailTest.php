<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPropertyDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_related_properties_are_shown_on_the_detail_page(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $currentProperty = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Luxury Family House',
            'slug' => 'luxury-family-house',
            'description' => 'A beautiful family home.',
            'address' => '123 Main Street',
            'city' => 'Cebu City',
            'province' => 'Cebu',
            'type' => 'House and Lot',
            'price' => 15000000,
            'status' => 'available',
            'featured_image' => 'https://example.com/house.jpg',
        ]);

        $relatedProperty = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Cozy Townhouse',
            'slug' => 'cozy-townhouse',
            'description' => 'Another great property.',
            'address' => '456 Side Street',
            'city' => 'Cebu City',
            'province' => 'Cebu',
            'type' => 'House and Lot',
            'price' => 12000000,
            'status' => 'available',
            'featured_image' => 'https://example.com/townhouse.jpg',
        ]);

        $response = $this->get(route('client.property.show', $currentProperty->slug));

        $response->assertStatus(200);
        $response->assertSee('More Properties');
        $response->assertSee($relatedProperty->name);
        $response->assertSee(route('client.property.show', $relatedProperty->slug));
    }
}
