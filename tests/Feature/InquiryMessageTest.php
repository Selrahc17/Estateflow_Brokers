<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The inquiry box starts with a ready-made message, so a buyer can send an
 * inquiry with one tap, and can still change it.
 */
class InquiryMessageTest extends TestCase
{
    use RefreshDatabase;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $agent = User::factory()->create(['name' => 'Ryan Yasona', 'role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        $this->property = Property::create(['broker_id' => $agent->id, 'name' => 'Conchita Ville', 'slug' => 'conchita-ville', 'type' => 'Subdivision Lot Only', 'status' => 'available']);
    }

    private function expected(): string
    {
        return e("Hi Ryan! I'm interested in learning more about this property (Conchita Ville). Please contact me with more information.");
    }

    public function test_guests_see_the_message_already_filled_in(): void
    {
        $this->get(route('client.property.show', $this->property->slug))
            ->assertOk()
            ->assertSee('>' . $this->expected() . '</textarea>', false);
    }

    public function test_signed_in_buyers_see_it_too(): void
    {
        $buyer = User::factory()->create(['role' => 'client', 'is_active' => true, 'is_approved' => true]);

        $this->actingAs($buyer)->get(route('client.property.show', $this->property->slug))
            ->assertOk()
            ->assertSee('>' . $this->expected() . '</textarea>', false);
    }

    public function test_it_greets_the_listing_agent_or_falls_back_politely(): void
    {
        $this->assertStringStartsWith('Hi Ryan!', $this->property->inquiryMessage());

        $this->property->broker->forceFill(['name' => ''])->save();
        $this->assertStringStartsWith('Hi there!', $this->property->fresh()->inquiryMessage());
    }

    public function test_what_the_buyer_typed_is_kept_when_the_form_comes_back_with_an_error(): void
    {
        $this->from(route('client.property.show', $this->property->slug))
            ->post(route('client.property.inquire', $this->property), ['message' => 'Is Block 5 still available?'])
            ->assertSessionHasErrors();

        $this->get(route('client.property.show', $this->property->slug))
            ->assertSee('>Is Block 5 still available?</textarea>', false)
            ->assertDontSee('>' . $this->expected() . '</textarea>', false);
    }
}
