<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\PropertyView;
use App\Models\User;
use App\Services\PropertyRecommender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyRecommendationTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.ai.key' => null]);
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->agent = User::factory()->create(['role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        $this->client = User::factory()->create(['role' => 'client', 'is_active' => true, 'is_approved' => true]);
    }

    private function listing(string $name, array $extra = []): Property
    {
        return Property::create($extra + ['broker_id' => $this->agent->id, 'name' => $name, 'slug' => str($name)->slug(), 'type' => 'House and Lot', 'status' => 'available']);
    }

    public function test_a_new_client_sees_popular_listings_and_how_to_get_personal_picks(): void
    {
        $popular = $this->listing('Crowd Favorite');
        $quiet = $this->listing('Quiet Listing');
        foreach (User::factory()->count(3)->create(['role' => 'client']) as $fan) {
            Favorite::create(['user_id' => $fan->id, 'property_id' => $popular->id]);
        }

        $page = $this->actingAs($this->client)->get(route('client.account.home'))->assertOk();

        $page->assertSee('Popular right now')
            ->assertSee('Save or view a few properties')
            ->assertSee('Find Your Match')
            ->assertSeeInOrder(['Crowd Favorite', 'Quiet Listing'])
            ->assertViewHas('recommendations', fn ($recs) => $recs['mode'] === 'popular');
    }

    public function test_saved_and_viewed_listings_shape_the_picks_and_are_not_repeated(): void
    {
        $saved = $this->listing('Saved Laguna Home', ['city' => 'Calamba', 'province' => 'Laguna', 'price' => 3000000, 'bedrooms' => 3]);
        $viewed = $this->listing('Viewed Laguna Home', ['city' => 'Calamba', 'province' => 'Laguna', 'price' => 3200000, 'bedrooms' => 3]);
        $match = $this->listing('New Calamba House', ['city' => 'Calamba', 'province' => 'Laguna', 'price' => 3100000, 'bedrooms' => 3]);
        $this->listing('Makati Condo', ['type' => 'Condominium', 'city' => 'Makati', 'province' => 'Metro Manila', 'price' => 9000000, 'bedrooms' => 1]);
        Favorite::create(['user_id' => $this->client->id, 'property_id' => $saved->id]);
        PropertyView::record($this->client, $viewed);

        $recs = app(PropertyRecommender::class)->for($this->client);

        $this->assertSame('personal', $recs['mode']);
        $this->assertSame($match->id, $recs['properties']->first()->id);
        $this->assertFalse($recs['properties']->contains('id', $saved->id));
        $this->assertFalse($recs['properties']->contains('id', $viewed->id));
        $this->assertSame(['type' => 'House and Lot', 'city' => 'Calamba', 'province' => 'Laguna'], array_intersect_key($recs['preferences'], array_flip(['type', 'city', 'province'])));

        $this->actingAs($this->client)->get(route('client.account.home'))
            ->assertSee('Recommended for you')
            ->assertSee('1 saved, 1 viewed')
            ->assertSee('New Calamba House');
    }

    public function test_with_ai_the_provider_ranks_the_picks_and_they_are_labelled(): void
    {
        $saved = $this->listing('Saved Home', ['city' => 'Calamba']);
        $first = $this->listing('Second By Rules', ['city' => 'Calamba']);
        $aiPick = $this->listing('AI Favourite', ['city' => 'Batangas']);
        Favorite::create(['user_id' => $this->client->id, 'property_id' => $saved->id]);

        config(['services.ai.key' => 'test-key', 'services.ai.base_url' => 'https://api.groq.com/openai/v1', 'services.ai.model' => 'llama-test']);
        \Illuminate\Support\Facades\Http::fake(['api.groq.com/*' => \Illuminate\Support\Facades\Http::response(['choices' => [['message' => ['content' => json_encode([$aiPick->id, $first->id, $saved->id])]]]])]);

        $recs = app(PropertyRecommender::class)->for($this->client);

        $this->assertTrue($recs['ai']);
        $this->assertSame(['AI Favourite', 'Second By Rules'], $recs['properties']->pluck('name')->all());

        $this->actingAs($this->client)->get(route('client.account.home'))->assertSee('AI picks');
    }

    public function test_without_ai_the_picks_are_not_labelled_as_ai(): void
    {
        $saved = $this->listing('Saved Home', ['city' => 'Calamba']);
        $this->listing('Nearby Home', ['city' => 'Calamba']);
        Favorite::create(['user_id' => $this->client->id, 'property_id' => $saved->id]);

        $this->actingAs($this->client)->get(route('client.account.home'))
            ->assertSee('Recommended for you')
            ->assertDontSee('AI picks');
    }

    public function test_asking_about_a_listing_also_counts(): void
    {
        $asked = $this->listing('Asked Townhouse', ['type' => 'Townhouse', 'city' => 'Tagaytay']);
        $this->listing('Another Tagaytay Townhouse', ['type' => 'Townhouse', 'city' => 'Tagaytay']);
        Inquiry::create(['user_id' => $this->client->id, 'property_id' => $asked->id, 'broker_id' => $this->agent->id, 'message' => 'Is this still available?', 'phone' => '09171234567', 'email' => 'a@example.com', 'status' => 'new']);

        $recs = app(PropertyRecommender::class)->for($this->client);

        $this->assertSame('personal', $recs['mode']);
        $this->assertSame('Another Tagaytay Townhouse', $recs['properties']->first()->name);
    }

    public function test_opening_a_listing_records_a_view_for_clients_only(): void
    {
        $listing = $this->listing('Vista Townhomes');

        $this->get(route('client.property.show', $listing->slug));
        $this->assertSame(0, PropertyView::count());

        $this->actingAs($this->client)->get(route('client.property.show', $listing->slug));
        $this->get(route('client.property.show', $listing->slug));
        $this->assertSame(2, PropertyView::sole()->visits);

        $this->actingAs($this->agent)->get(route('client.property.show', $listing->slug));
        $this->assertSame(1, PropertyView::count());
    }
}
