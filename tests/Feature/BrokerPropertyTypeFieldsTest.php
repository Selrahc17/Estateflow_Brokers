<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerPropertyTypeFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_house_and_lot_form_shows_relevant_fields(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($broker)->get(route('broker.properties.create'));

        $response->assertStatus(200);
        $response->assertSee('Bedrooms');
        $response->assertSee('Bathrooms');
        $response->assertSee('Floor Area');
        $response->assertSee('Lot Area');
    }

    public function test_lot_only_form_shows_lot_specific_fields(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($broker)->get(route('broker.properties.create'));

        $response->assertStatus(200);
        $response->assertSee('Lot Area');
        $response->assertSee('Frontage');
    }
}
