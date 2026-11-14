<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Services\AIContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The details agents post on Facebook: payment terms, cash discount, transfer fee,
 * selling stage, amenities, highlights, landmarks, house models and requirements.
 * All of them are optional.
 */
class PropertySellingDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
    }

    private function listing(array $overrides = []): array
    {
        return array_merge(['name' => 'Galicia Homes', 'type' => 'House and Lot', 'status' => 'available'], $overrides);
    }

    /** The Mendez, Cavite post: Type A, ₱3.6M, ₱20,000 reservation, 25% DP, ₱100,000 cash discount, 8% transfer fee. */
    private function fullListing(): array
    {
        return $this->listing([
            'price' => '3600000',
            'selling_stage' => 'pre_selling',
            'reservation_fee' => '20000',
            'downpayment_percent' => '25',
            'payment_options' => ['full_cash', 'bank_financing'],
            'installment_terms' => 'Downpayment payable in 12 months',
            'cash_discount_type' => 'amount',
            'cash_discount' => '100000',
            'transfer_fee' => 'excluded',
            'transfer_fee_percent' => '8',
            'amenities' => ['Clubhouse', 'Swimming Pool'],
            'amenities_other' => 'Pavilion, Clubhouse',
            'selling_points' => ['Flood-free', 'No house design restrictions'],
            'selling_points_other' => '',
            'landmarks' => [
                ['name' => 'Tagaytay Rotonda', 'travel_time' => '15–20 mins'],
                ['name' => '', 'travel_time' => ''],
                ['name' => 'SLEX', 'travel_time' => '30–36 mins'],
            ],
            'house_models' => [
                ['name' => 'Type A', 'position' => 'Inner Unit', 'lot_area' => '52', 'floor_area' => '60', 'bedrooms' => '3', 'bathrooms' => '2', 'parking_slots' => '1', 'price' => '3600000', 'features' => 'Living, dining, kitchen and service area'],
                ['name' => '', 'position' => '', 'lot_area' => '', 'floor_area' => '', 'bedrooms' => '', 'bathrooms' => '', 'parking_slots' => '', 'price' => '', 'features' => ''],
            ],
            'buyer_requirements' => '2 valid IDs',
        ]);
    }

    public function test_an_agent_can_save_every_selling_detail(): void
    {
        $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->fullListing())->assertSessionHasNoErrors();

        $property = Property::firstOrFail();
        $this->assertSame('pre_selling', $property->selling_stage);
        $this->assertSame('20000.00', $property->reservation_fee);
        $this->assertSame(['full_cash', 'bank_financing'], $property->payment_options);
        $this->assertSame(['Clubhouse', 'Swimming Pool', 'Pavilion'], $property->amenities, 'Typed amenities are added once');
        $this->assertSame(['Flood-free', 'No house design restrictions'], $property->selling_points);
        $this->assertCount(2, $property->landmarks, 'Blank landmark rows are dropped');
        $this->assertCount(1, $property->house_models, 'Blank model rows are dropped');
        $this->assertSame('Type A', $property->house_models[0]['name']);
        $this->assertSame(3500000.0, $property->cashPrice());
        $this->assertSame(900000.0, $property->downpaymentAmount());
        $this->assertSame('Price is exclusive of the 8% transfer fee', $property->transfer_fee_label);
    }

    public function test_every_selling_detail_is_optional(): void
    {
        $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->listing())->assertSessionHasNoErrors();

        $property = Property::firstOrFail();
        foreach (['selling_stage', 'reservation_fee', 'payment_options', 'cash_discount', 'transfer_fee', 'amenities', 'selling_points', 'landmarks', 'house_models', 'buyer_requirements'] as $field) {
            $this->assertNull($property->{$field}, "{$field} stays empty");
        }
        $this->assertFalse($property->hasPaymentTerms());

        auth()->logout();
        $this->get(route('client.property.show', $property->slug))
            ->assertOk()
            ->assertDontSee('Price and Payment Terms')
            ->assertDontSee('House Models')
            ->assertDontSee('Nearby Landmarks')
            ->assertDontSee('Amenities and Highlights');
    }

    public function test_buyers_see_the_terms_models_amenities_and_landmarks(): void
    {
        $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->fullListing());
        $property = Property::firstOrFail();

        auth()->logout();
        $this->get(route('client.property.show', $property->slug))
            ->assertOk()
            ->assertSeeInOrder(['Price and Payment Terms', 'Pre-selling', 'Total Contract Price', '₱3,600,000', 'Reservation Fee', '₱20,000', 'Downpayment', '25%', '₱900,000', 'Less ₱100,000', 'Net price', '₱3,500,000'])
            ->assertSee('Bank Financing')
            ->assertSee('Price is exclusive of the 8% transfer fee')
            ->assertSee('2 valid IDs')
            ->assertSeeInOrder(['House Models', 'Type A', 'Inner Unit', '52 m² lot · 60 m² floor · 3 bedrooms · 2 T&amp;B · 1-car garage', 'Full cash: ₱3,500,000'], false)
            ->assertSeeInOrder(['Amenities and Highlights', 'Clubhouse', 'Pavilion', 'Flood-free'])
            ->assertSeeInOrder(['Nearby Landmarks', 'Tagaytay Rotonda', '15–20 mins', 'SLEX']);
    }

    public function test_a_percent_discount_and_seller_paid_transfer_fee(): void
    {
        $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->listing([
            'price' => '1000000', 'cash_discount_type' => 'percent', 'cash_discount' => '10', 'transfer_fee' => 'seller', 'transfer_fee_percent' => '8',
        ]))->assertSessionHasNoErrors();

        $property = Property::firstOrFail();
        $this->assertSame(900000.0, $property->cashPrice());
        $this->assertSame('10%', $property->cash_discount_label);
        $this->assertNull($property->transfer_fee_percent, 'The percent only applies when the fee is not included');
        $this->assertSame('All-in: the seller shoulders the transfer fee', $property->transfer_fee_label);
    }

    public function test_bad_values_are_refused(): void
    {
        $this->actingAs($this->agent);

        $this->post(route('agent.properties.store'), $this->listing(['cash_discount_type' => 'percent', 'cash_discount' => '150']))->assertSessionHasErrors('cash_discount');
        $this->post(route('agent.properties.store'), $this->listing(['cash_discount' => '5000']))->assertSessionHasErrors('cash_discount_type');
        $this->post(route('agent.properties.store'), $this->listing(['payment_options' => ['barter']]))->assertSessionHasErrors('payment_options.0');
        $this->post(route('agent.properties.store'), $this->listing(['selling_stage' => 'sold_out']))->assertSessionHasErrors('selling_stage');
        $this->post(route('agent.properties.store'), $this->listing(['house_models' => [['name' => '', 'price' => '2500000']]]))->assertSessionHasErrors('house_models.0.name');
        $this->assertSame(0, Property::count());
    }

    public function test_editing_can_change_and_clear_the_details(): void
    {
        $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->fullListing());
        $property = Property::firstOrFail();

        $this->get(route('agent.properties.edit', $property))
            ->assertOk()
            ->assertSee('Selling Stage and Payment Terms')
            ->assertSee('value="20000.00"', false)
            ->assertSee('Pavilion');

        // Unticking everything and emptying the fields clears them.
        $this->put(route('agent.properties.update', $property), $this->listing(['reservation_fee' => '15000', 'landmarks' => [['name' => 'Aguinaldo Highway', 'travel_time' => '5–8 mins']]]))
            ->assertSessionHasNoErrors();

        $property->refresh();
        $this->assertSame('15000.00', $property->reservation_fee);
        $this->assertSame([['name' => 'Aguinaldo Highway', 'travel_time' => '5–8 mins']], $property->landmarks);
        $this->assertNull($property->amenities);
        $this->assertNull($property->house_models);
        $this->assertNull($property->cash_discount);
        $this->assertNull($property->selling_stage);
    }

    public function test_the_add_form_shows_the_new_sections(): void
    {
        $this->actingAs($this->agent)->get(route('agent.properties.create'))
            ->assertOk()
            ->assertSeeInOrder(['Specifications', 'Selling Stage and Payment Terms', 'Amenities and Highlights', 'Nearby Landmarks', 'House Models', 'Location', 'Photos'])
            ->assertSee('Pag-IBIG Financing')
            ->assertSee('No house design restrictions');
    }

    public function test_a_listing_priced_only_by_model_shows_the_lowest_model_price(): void
    {
        $property = Property::create(['broker_id' => $this->agent->id, 'name' => 'Galicia Homes', 'slug' => 'galicia-homes', 'type' => 'House and Lot', 'status' => 'available',
            'house_models' => [['name' => 'Type B', 'price' => 4200000], ['name' => 'Type A', 'price' => 3600000]]]);

        $this->get(route('client.property.show', $property->slug))->assertSeeInOrder(['Starting at', '₱3,600,000']);
    }

    public function test_the_ai_chat_knows_the_payment_terms(): void
    {
        $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->fullListing());

        $context = app(AIContext::class)->build(null, 'guest', 'magkano reservation sa Galicia Homes?');

        $this->assertStringContainsString('reservation ₱20,000', $context);
        $this->assertStringContainsString('25% downpayment', $context);
        $this->assertStringContainsString('Type A ₱3,600,000', $context);
    }

    public function test_listing_cards_show_the_stage_and_reservation_fee(): void
    {
        Property::create(['broker_id' => $this->agent->id, 'name' => 'Galicia Homes', 'slug' => 'galicia-homes', 'type' => 'House and Lot', 'status' => 'available',
            'selling_stage' => 'pre_selling', 'reservation_fee' => 20000, 'house_models' => [['name' => 'Type A', 'price' => 3600000]]]);
        Property::create(['broker_id' => $this->agent->id, 'name' => 'Plain Listing', 'slug' => 'plain-listing', 'type' => 'House and Lot', 'status' => 'available', 'price' => 1500000]);

        $this->get(route('client.properties'))
            ->assertSeeInOrder(['Pre-selling', 'Galicia Homes', 'Starting at', '₱3,600,000', '₱20,000 reservation'])
            ->assertSee('₱1,500,000');
        $this->assertSame(1, substr_count($this->get(route('client.properties'))->getContent(), ' reservation</p>'), 'Only the listing with a fee shows one');
    }

    public function test_the_pre_selling_and_ready_for_occupancy_links_filter_by_stage(): void
    {
        foreach (['Sunrise Pre-selling' => 'pre_selling', 'Harbor Ready Homes' => 'ready_for_occupancy', 'Plain Listing' => null] as $name => $stage) {
            Property::create(['broker_id' => $this->agent->id, 'name' => $name, 'slug' => str($name)->slug(), 'type' => 'House and Lot', 'status' => 'available', 'selling_stage' => $stage]);
        }

        $this->get(route('client.properties', ['status' => 'Pre-Selling']))->assertSee('Sunrise Pre-selling')->assertDontSee('Harbor Ready Homes')->assertDontSee('Plain Listing');
        $this->get(route('client.properties', ['status' => 'RFO']))->assertSee('Harbor Ready Homes')->assertDontSee('Sunrise Pre-selling');
        $this->get(route('client.properties'))->assertSee('Sunrise Pre-selling')->assertSee('Harbor Ready Homes')->assertSee('Plain Listing');
    }
}
