<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PropertyCompareTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->agent = User::factory()->create(['role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        config(['services.ai.key' => null]);
    }

    private function listing(string $name, array $extra = []): Property
    {
        return Property::create($extra + ['broker_id' => $this->agent->id, 'name' => $name, 'slug' => str($name)->slug(), 'type' => 'House and Lot', 'status' => 'available']);
    }

    public function test_guests_can_pick_properties_and_see_them_side_by_side(): void
    {
        $vista = $this->listing('Vista Townhomes', ['price' => 3600000, 'bedrooms' => 3, 'floor_area' => 80, 'city' => 'Calamba', 'province' => 'Laguna']);
        $lake = $this->listing('Lakeview Homes', ['price' => 2900000, 'bedrooms' => 2, 'floor_area' => 95, 'city' => 'Sta. Rosa', 'province' => 'Laguna']);

        $this->post(route('client.compare.toggle', $vista))->assertSessionHas('success', 'Vista Townhomes added. Add one more property to compare.');
        $this->get(route('client.compare'))->assertSee('Add one more property to compare');

        $this->post(route('client.compare.toggle', $lake))->assertSessionHas('success', 'Lakeview Homes added. You are comparing 2 properties.');

        $this->get(route('client.compare'))
            ->assertOk()
            ->assertSeeInOrder(['Vista Townhomes', 'Lakeview Homes'])
            ->assertSee('₱2,900,000')
            ->assertSee('Calamba, Laguna')
            ->assertSee('Best')
            ->assertViewHas('sections', function ($sections) {
                $price = collect($sections['Price and payment'])->firstWhere('label', 'Starting price');
                $floor = collect($sections['Size and rooms'])->firstWhere('label', 'Floor area');

                return $price['best'] === [1] && $floor['best'] === [1] && $floor['values'] === ['80 sqm', '95 sqm'];
            });
    }

    public function test_only_four_properties_can_be_compared(): void
    {
        foreach (['A One', 'B Two', 'C Three', 'D Four'] as $name) {
            $this->post(route('client.compare.toggle', $this->listing($name)));
        }

        $this->post(route('client.compare.toggle', $this->listing('E Five')))
            ->assertSessionHas('error', 'You can compare up to 4 properties. Remove one first.');
        $this->assertCount(4, session('compare'));
    }

    public function test_pressing_compare_again_removes_it_and_hidden_listings_drop_out(): void
    {
        $vista = $this->listing('Vista Townhomes');
        $lake = $this->listing('Lakeview Homes');
        $this->post(route('client.compare.toggle', $vista));
        $this->post(route('client.compare.toggle', $lake));

        $this->post(route('client.compare.toggle', $vista))->assertSessionHas('success', 'Vista Townhomes was removed from your comparison.');
        $this->assertSame([$lake->id], session('compare'));

        $lake->update(['status' => 'hidden']);
        $this->get(route('client.compare'))->assertSee('Pick properties to compare');
        $this->assertSame([], session('compare'));
    }

    public function test_without_ai_the_summary_uses_the_numbers(): void
    {
        $this->post(route('client.compare.toggle', $this->listing('Vista Townhomes', ['price' => 3600000, 'bedrooms' => 3])));
        $this->post(route('client.compare.toggle', $this->listing('Lakeview Homes', ['price' => 2900000, 'bedrooms' => 2])));

        $this->followingRedirects()->post(route('client.compare.summary'), ['needs' => 'family of 4'])
            ->assertSee('Quick summary')
            ->assertSee('Lakeview Homes has the lowest starting price (₱2,900,000).')
            ->assertSee('Vista Townhomes has the most bedrooms (3).');
    }

    public function test_with_ai_the_summary_comes_from_the_provider_with_the_buyers_needs(): void
    {
        config(['services.ai.key' => 'test-key', 'services.ai.base_url' => 'https://api.groq.com/openai/v1', 'services.ai.model' => 'llama-test']);
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'Lakeview suits a smaller budget; Vista has room for a family.']]]])]);

        $this->post(route('client.compare.toggle', $this->listing('Vista Townhomes', ['price' => 3600000])));
        $this->post(route('client.compare.toggle', $this->listing('Lakeview Homes', ['price' => 2900000])));

        $this->followingRedirects()->post(route('client.compare.summary'), ['needs' => 'family of 4'])
            ->assertSee('AI summary')
            ->assertSee('Lakeview suits a smaller budget; Vista has room for a family.');

        Http::assertSent(fn ($request) => str_contains($request->body(), 'family of 4') && str_contains($request->body(), 'Lakeview Homes'));
    }

    public function test_the_summary_needs_two_properties(): void
    {
        $this->post(route('client.compare.toggle', $this->listing('Vista Townhomes')));

        $this->post(route('client.compare.summary'))->assertSessionHas('error', 'Add at least two properties to compare first.');
    }

    public function test_clients_can_add_from_their_saved_listings(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true, 'is_approved' => true]);
        $saved = $this->listing('Saved Place');
        Favorite::create(['user_id' => $client->id, 'property_id' => $saved->id]);

        $this->actingAs($client)->get(route('client.compare'))
            ->assertSee('Add from your saved and recently viewed properties')
            ->assertSee('Saved Place');
    }

    public function test_the_property_page_offers_compare_and_the_bar_counts_picks(): void
    {
        $vista = $this->listing('Vista Townhomes');

        $this->get(route('client.property.show', $vista->slug))->assertSee('⇄ Compare')->assertDontSee('data-compare-bar', false);

        $this->post(route('client.compare.toggle', $vista));
        $this->get(route('client.property.show', $vista->slug))->assertSee('✓ In compare')->assertSee('data-compare-bar', false);
    }
}
