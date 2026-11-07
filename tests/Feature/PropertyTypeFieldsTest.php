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
    public function test_new_land_types_can_be_saved_and_the_old_farm_type_is_gone(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);

        foreach (['Subdivision Lot Only', 'Farm Lot'] as $type) {
            $this->actingAs($agent)->post(route('agent.properties.store'), ['name' => $type, 'type' => $type, 'status' => 'available'])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('properties', ['name' => $type, 'type' => $type]);
        }

        $this->post(route('agent.properties.store'), ['name' => 'Old', 'type' => 'Farm', 'status' => 'available'])->assertSessionHasErrors('type');
        $this->get(route('agent.properties.create'))->assertSee('Subdivision Lot Only')->assertSee('Farm Lot');
    }

    public function test_existing_farm_listings_move_to_the_new_name(): void
    {
        $id = \Illuminate\Support\Facades\DB::table('properties')->insertGetId(['name' => 'Old Farm', 'slug' => 'old-farm', 'type' => 'Farm', 'status' => 'available']);

        (require database_path('migrations/2026_11_09_000001_rename_farm_property_type.php'))->up();

        $this->assertSame('Farm Lot', \Illuminate\Support\Facades\DB::table('properties')->where('id', $id)->value('type'));
    }
}
