<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Property;
use App\Models\PropertyReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private User $client;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->agent = User::factory()->create(['name' => 'Ryan Agent', 'role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        $this->client = User::factory()->create(['name' => 'Ana Maria Reyes', 'role' => 'client', 'is_active' => true, 'is_approved' => true]);
        $this->property = Property::create(['broker_id' => $this->agent->id, 'name' => 'Vista Townhomes', 'slug' => 'vista', 'type' => 'House and Lot', 'status' => 'available', 'price' => 3600000]);
    }

    public function test_a_client_posts_a_review_and_the_agent_is_told(): void
    {
        $this->actingAs($this->client)
            ->post(route('client.account.reviews.store', $this->property), ['rating' => 4, 'comment' => 'Quiet street and helpful agent.'])
            ->assertRedirect(route('client.property.show', 'vista').'#reviews')
            ->assertSessionHas('success', 'Thanks! Your review is now posted.');

        $this->assertDatabaseHas('property_reviews', ['property_id' => $this->property->id, 'user_id' => $this->client->id, 'rating' => 4]);
        $this->assertSame(1, AppNotification::where('user_id', $this->agent->id)->where('type', 'property_review')->count());

        $this->get(route('client.property.show', 'vista'))
            ->assertSee('Your review')
            ->assertSee('Update review');

        // Other visitors see only the first name and last initial.
        auth()->logout();
        $this->get(route('client.property.show', 'vista'))
            ->assertSee('Quiet street and helpful agent.')
            ->assertSee('Ana R.')
            ->assertDontSee('Ana Maria Reyes')
            ->assertSee('4.0');
    }

    public function test_posting_again_updates_the_same_review(): void
    {
        $this->actingAs($this->client)->post(route('client.account.reviews.store', $this->property), ['rating' => 2]);
        $this->post(route('client.account.reviews.store', $this->property), ['rating' => 5, 'comment' => 'Changed my mind after the site visit.'])
            ->assertSessionHas('success', 'Your review has been updated.');

        $this->assertSame(1, PropertyReview::count());
        $this->assertSame(5, PropertyReview::sole()->rating);
        $this->assertSame(1, AppNotification::where('type', 'property_review')->count(), 'Only the first review notifies the agent');
    }

    public function test_a_rating_is_required_and_must_be_one_to_five(): void
    {
        $this->actingAs($this->client)->from(route('client.property.show', 'vista'))
            ->post(route('client.account.reviews.store', $this->property), ['rating' => 6])
            ->assertSessionHasErrors(['rating' => 'Choose a star rating from 1 to 5.']);

        $this->post(route('client.account.reviews.store', $this->property), ['comment' => 'No stars'])
            ->assertSessionHasErrors('rating');

        $this->assertSame(0, PropertyReview::count());
    }

    public function test_a_client_can_delete_their_review(): void
    {
        PropertyReview::create(['property_id' => $this->property->id, 'user_id' => $this->client->id, 'rating' => 3]);

        $this->actingAs($this->client)->delete(route('client.account.reviews.destroy', $this->property))
            ->assertSessionHas('success', 'Your review has been removed.');

        $this->assertSame(0, PropertyReview::count());
    }

    public function test_guests_and_non_clients_cannot_review(): void
    {
        $this->get(route('client.property.show', 'vista'))->assertSee('to leave a review')->assertDontSee('Post review');
        $this->post(route('client.account.reviews.store', $this->property), ['rating' => 5])->assertRedirect(route('auth.login'));

        $this->actingAs($this->agent)->post(route('client.account.reviews.store', $this->property), ['rating' => 5])->assertForbidden();
        $this->assertSame(0, PropertyReview::count());
    }

    public function test_hidden_reviews_leave_the_page_and_the_average(): void
    {
        $other = User::factory()->create(['name' => 'Paolo Santiago', 'role' => 'client']);
        PropertyReview::create(['property_id' => $this->property->id, 'user_id' => $this->client->id, 'rating' => 5, 'comment' => 'Lovely place']);
        $rude = PropertyReview::create(['property_id' => $this->property->id, 'user_id' => $other->id, 'rating' => 1, 'comment' => 'Rude words here']);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true]);
        $this->actingAs($admin)->patch(route('admin.reviews.hide', $rude))->assertSessionHas('success');
        $this->assertNotNull($rude->fresh()->hidden_at);

        auth()->logout();
        $this->get(route('client.property.show', 'vista'))
            ->assertSee('Lovely place')
            ->assertDontSee('Rude words here')
            ->assertSee('5.0')
            ->assertSee('(1 review)');

        $this->actingAs($other)->get(route('client.property.show', 'vista'))
            ->assertSee('Rude words here')
            ->assertSee('An admin has hidden this review');

        $this->actingAs($admin)->patch(route('admin.reviews.restore', $rude));
        $this->assertNull($rude->fresh()->hidden_at);
    }

    public function test_the_admin_page_lists_and_filters_reviews(): void
    {
        $other = User::factory()->create(['name' => 'Paolo Santiago', 'role' => 'client']);
        PropertyReview::create(['property_id' => $this->property->id, 'user_id' => $this->client->id, 'rating' => 5, 'comment' => 'Lovely place']);
        PropertyReview::create(['property_id' => $this->property->id, 'user_id' => $other->id, 'rating' => 2, 'comment' => 'Too far from the highway', 'hidden_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true]);

        $this->actingAs($admin)->get(route('admin.reviews'))
            ->assertOk()
            ->assertViewHas('counts', ['all' => 2, 'visible' => 1, 'hidden' => 1])
            ->assertSee('Lovely place')
            ->assertSee('Too far from the highway');

        $this->get(route('admin.reviews', ['status' => 'hidden']))->assertSee('Too far from the highway')->assertDontSee('Lovely place');
        $this->get(route('admin.reviews', ['rating' => 5]))->assertSee('Lovely place')->assertDontSee('Too far from the highway');
        $this->get(route('admin.reviews', ['q' => 'highway']))->assertSee('Paolo Santiago')->assertDontSee('Lovely place');
    }

    public function test_listing_cards_show_the_average_rating(): void
    {
        PropertyReview::create(['property_id' => $this->property->id, 'user_id' => $this->client->id, 'rating' => 4]);

        $this->get(route('client.properties'))->assertSee('4.0')->assertSee('(1)');
    }
}
