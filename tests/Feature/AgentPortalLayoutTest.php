<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Client;
use App\Models\CommissionAgreement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentPortalLayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    /**
     * The label of the sidebar link marked as the current page.
     */
    private function activeLinks(string $html): array
    {
        preg_match_all('/aria-label="([^"]+)"\s+aria-current="page"/', $html, $matches);

        return $matches[1];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->agent = User::factory()->create(['role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        $this->actingAs($this->agent);
    }

    private function property(): Property
    {
        return Property::create([
            'broker_id' => $this->agent->id,
            'name' => 'Vista Homes',
            'slug' => 'vista-homes',
            'address' => 'Quezon City',
            'type' => 'House and Lot',
            'status' => 'available',
        ]);
    }

    private function listing(array $overrides = []): array
    {
        return array_merge(['name' => 'Vista Homes', 'type' => 'House and Lot', 'status' => 'available'], $overrides);
    }

    public function test_sidebar_groups_links_and_marks_properties_active_on_the_add_page(): void
    {
        $response = $this->get(route('agent.properties.create'))
            ->assertOk()
            ->assertSee('Agent Portal')
            ->assertSeeInOrder(['Overview', 'Dashboard', 'Reports', 'Listings', 'Properties', 'Lot Availability', 'Clients and Sales', 'Inquiries', 'My Commission', 'Communication', 'Messages', 'AI Assistant']);

        $this->assertSame(['Properties'], $this->activeLinks($response->getContent()));
        $response->assertDontSee('aria-label="Add Property"', false);
    }

    public function test_commission_stays_highlighted_on_its_detail_page(): void
    {
        $agreement = CommissionAgreement::create([
            'broker_id' => $this->agent->broker_id,
            'agent_id' => $this->agent->id,
            'commission_rate' => 5,
            'agent_share' => 60,
            'broker_share' => 40,
            'payment_schedule' => 'monthly',
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->get(route('agent.commission.show', $agreement))->assertOk();

        $this->assertSame(['My Commission'], $this->activeLinks($response->getContent()));
    }

    public function test_sidebar_shows_unread_notification_count(): void
    {
        AppNotification::create(['user_id' => $this->agent->id, 'type' => 'broker', 'title' => 'Hello', 'is_read' => false]);
        AppNotification::create(['user_id' => $this->agent->id, 'type' => 'broker', 'title' => 'Again', 'is_read' => false]);

        $this->get(route('agent.dashboard'))->assertSee('leading-5 text-white">2</span>', false);
    }

    public function test_page_scripts_now_reach_the_browser(): void
    {
        $describeScript = "querySelector('[data-ai-describe]')?.addEventListener";

        $this->get(route('agent.properties.create'))->assertSee($describeScript, false)->assertSee('data-ai-status', false);
        $this->get(route('agent.properties.edit', $this->property()))->assertSee($describeScript, false)->assertSee('data-ai-status', false);
        $this->get(route('agent.clients.index'))->assertSee(route('agent.ai.leads'), false);
    }

    public function test_ai_description_returns_nothing_when_no_key_is_set(): void
    {
        config(['services.openai.key' => null]);

        $this->postJson(route('agent.ai.describe'), ['name' => 'Vista Homes'])
            ->assertOk()
            ->assertJson(['description' => '']);
    }

    public function test_lead_scoring_works_for_the_agents_own_client(): void
    {
        $client = Client::create([
            'broker_id' => $this->agent->id,
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'email' => 'ana@example.com',
            'password' => 'secret',
            'status' => 'active',
        ]);

        $this->postJson(route('agent.ai.leads'), ['client_id' => $client->id])
            ->assertOk()
            ->assertJsonStructure(['score', 'priority']);
    }

    public function test_form_keeps_status_and_map_pin_after_a_validation_error(): void
    {
        $this->from(route('agent.properties.create'))
            ->followingRedirects()
            ->post(route('agent.properties.store'), $this->listing([
                'name' => '',
                'status' => 'coming_soon',
                'latitude' => '14.5995',
                'longitude' => '120.9842',
                'address' => "St. Mary's Road",
            ]))
            ->assertSee('Please check the field marked below.')
            ->assertSee('<option value="coming_soon" selected>', false)
            ->assertSee('14.5995')
            ->assertSee('St. Mary\u0027s Road', false);
    }

    public function test_listing_still_saves_from_the_new_form(): void
    {
        $this->post(route('agent.properties.store'), $this->listing([
            'status' => 'coming_soon',
            'price' => 2500000,
            'bedrooms' => 3,
            'lot_area' => 120,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'city' => 'Manila',
        ]))->assertRedirect(route('agent.properties.index'));

        $this->assertDatabaseHas('properties', [
            'name' => 'Vista Homes',
            'broker_id' => $this->agent->id,
            'status' => 'coming_soon',
            'bedrooms' => 3,
            'lot_area' => 120,
            'city' => 'Manila',
        ]);
    }

    public function test_both_property_forms_use_the_shared_address_mapping(): void
    {
        foreach ([route('agent.properties.create'), route('agent.properties.edit', $this->property())] as $url) {
            $this->get($url)
                ->assertSee('window.philippineProvinceAndRegion(addr)', false)
                ->assertDontSee('addr.state_district', false);
        }
    }

    public function test_every_specification_field_is_on_the_form(): void
    {
        $this->get(route('agent.properties.create'))
            ->assertSee('Lot Area (sqm)')
            ->assertSee('Frontage (m)')
            ->assertSee('Bedrooms')
            ->assertSee('Create Property');
    }
}
