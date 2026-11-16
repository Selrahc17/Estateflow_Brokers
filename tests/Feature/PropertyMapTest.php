<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The map on the buyer's property page reads its data from attributes on the map
 * element, so the listing's text never ends up inside the page's JavaScript.
 */
class PropertyMapTest extends TestCase
{
    use RefreshDatabase;

    private function listing(array $attributes): Property
    {
        $agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);

        return Property::create([
            'broker_id' => $agent->id, 'slug' => 'map-test', 'type' => 'Subdivision Lot Only', 'status' => 'available',
            ...$attributes,
        ]);
    }

    public function test_the_map_gets_its_pin_and_popup_text_from_data_attributes(): void
    {
        $property = $this->listing([
            'name' => 'Sabang `${alert(1)}` Lots', 'city' => 'Pagsanjan', 'province' => 'Laguna',
            'latitude' => 14.2542, 'longitude' => 121.4354,
        ]);

        $html = $this->get(route('client.property.show', $property->slug))->assertOk()->getContent();

        $this->assertStringContainsString('id="property-map"', $html);
        $this->assertMatchesRegularExpression('/data-lat="14\.2542(0*)" data-lng="121\.4354(0*)"/', $html);
        $this->assertStringContainsString('data-name="Sabang `${alert(1)}` Lots"', $html);
        $this->assertStringContainsString('data-place="Pagsanjan, Laguna"', $html);

        $script = substr($html, strpos($html, 'leaflet.js'));
        $this->assertStringNotContainsString('alert(1)', $script);
        $this->assertStringNotContainsString('14.2542', $script);
    }

    public function test_a_listing_without_coordinates_has_no_map(): void
    {
        $property = $this->listing(['name' => 'No Pin Lots']);

        $this->get(route('client.property.show', $property->slug))
            ->assertOk()
            ->assertDontSee('id="property-map"', false);
    }
}
