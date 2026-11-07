<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyTypeFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_house_and_lot_form_shows_relevant_fields(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($agent)->get(route('agent.properties.create'));

        $response->assertStatus(200);
        $response->assertSee('Bedrooms');
        $response->assertSee('Bathrooms');
        $response->assertSee('Floor Area');
        $response->assertSee('Lot Area');
    }

    public function test_lot_only_form_shows_lot_specific_fields(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($agent)->get(route('agent.properties.create'));

        $response->assertStatus(200);
        $response->assertSee('Lot Area');
        $response->assertSee('Frontage');
    }
}
