<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Lot;
use App\Models\Property;
use App\Models\Sale;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The agent's Site Visits, Clients, Properties and Reports pages, and the
 * agent portal's dark mode switch.
 */
class AgentPagesRedesignTest extends TestCase
{
    use RefreshDatabase;

    private User $broker;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->agent = User::factory()->create(['role' => 'agent', 'broker_id' => $this->broker->id, 'is_active' => true, 'is_approved' => true]);
        $this->actingAs($this->agent);
    }

    private function listing(array $overrides = []): Property
    {
        return Property::create(array_merge(['broker_id' => $this->agent->id, 'name' => 'Vista Townhomes', 'slug' => uniqid('vista-'), 'type' => 'House and Lot', 'status' => 'available'], $overrides));
    }

    private function client(string $first, string $stage = 'pending'): Client
    {
        return Client::create(['broker_id' => $this->agent->id, 'first_name' => $first, 'last_name' => 'Buyer', 'email' => strtolower($first) . '@example.com', 'password' => 'x', 'status' => 'active', 'qualification_status' => $stage]);
    }

    public function test_agent_pages_have_the_dark_mode_switch(): void
    {
        $this->get(route('agent.dashboard'))
            ->assertOk()
            ->assertSee('agent-portal', false)
            ->assertSee('agent-main', false)
            ->assertSee('Switch color mode');
    }

    public function test_site_visits_show_upcoming_visits_soonest_first_with_quick_actions(): void
    {
        $property = $this->listing();
        $ana = $this->client('Ana');
        $ben = $this->client('Ben');
        SiteVisit::create(['broker_id' => $this->agent->id, 'client_id' => $ben->id, 'property_id' => $property->id, 'scheduled_at' => now()->addDays(3)->setTime(14, 0), 'status' => 'confirmed']);
        $soon = SiteVisit::create(['broker_id' => $this->agent->id, 'client_id' => $ana->id, 'property_id' => $property->id, 'scheduled_at' => now()->addDay()->setTime(9, 0), 'status' => 'pending']);
        SiteVisit::create(['broker_id' => $this->agent->id, 'client_id' => $this->client('Old')->id, 'property_id' => $property->id, 'scheduled_at' => now()->subWeek(), 'status' => 'completed']);

        $this->get(route('agent.site-visits.index'))
            ->assertOk()
            ->assertSeeInOrder(['Tomorrow', 'Ana Buyer', 'Ben Buyer'])
            ->assertDontSee('Old Buyer')
            ->assertSee(route('agent.site-visits.status', $soon), false)
            ->assertSee('Waiting to confirm');

        $this->get(route('agent.site-visits.index', ['when' => 'past']))->assertSee('Old Buyer')->assertDontSee('Ana Buyer');
    }

    public function test_clients_can_be_filtered_by_status_with_counts(): void
    {
        $this->client('Ana', 'approved');
        $this->client('Ben');
        $this->client('Cara');

        $this->get(route('agent.clients.index'))
            ->assertSee('Ana Buyer')->assertSee('Ben Buyer')
            ->assertSee('data-lead-score', false);

        $this->get(route('agent.clients.index', ['stage' => 'approved']))
            ->assertSee('Ana Buyer')
            ->assertDontSee('Ben Buyer')
            ->assertSee('aria-current="page"', false);
    }

    public function test_property_cards_say_what_each_listing_still_needs(): void
    {
        $this->listing(['name' => 'Bare Listing']);
        $this->listing([
            'name' => 'Complete Listing', 'price' => 3600000, 'featured_image' => 'https://example.com/a.jpg',
            'latitude' => 14.5, 'longitude' => 121.0, 'reservation_fee' => 20000,
        ]);
        $lots = $this->listing(['name' => 'Lot Project', 'type' => 'Lot Only']);
        Lot::create(['property_id' => $lots->id, 'lot_number' => 'Blk 1 Lot 1', 'status' => 'available', 'price' => 900000]);
        Lot::create(['property_id' => $lots->id, 'lot_number' => 'Blk 1 Lot 2', 'status' => 'available', 'price' => 750000]);

        $this->get(route('agent.properties.index'))
            ->assertOk()
            ->assertSee('Missing:</span> Photo, Price, Map pin, Payment terms', false)
            ->assertSee('Listing details complete')
            ->assertSee('₱750,000')
            ->assertSee(route('agent.lots.index', ['property_id' => $lots->id]), false);

        $this->get(route('agent.properties.index', ['search' => 'COMPLETE']))->assertSee('Complete Listing')->assertDontSee('Bare Listing');
    }

    public function test_reports_count_the_year_from_inquiry_to_sale(): void
    {
        $property = $this->listing(['name' => 'Popular Place']);
        Inquiry::create(['broker_id' => $this->agent->id, 'property_id' => $property->id, 'message' => 'Hi', 'email' => 'a@example.com', 'status' => 'new']);
        Inquiry::create(['broker_id' => $this->agent->id, 'property_id' => $property->id, 'message' => 'Hi', 'email' => 'b@example.com', 'status' => 'contacted']);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => now()->startOfMonth(), 'selling_price' => 2500000, 'agent_commission' => 77777, 'status' => 'pending']);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'sold_at' => now()->subYear(), 'selling_price' => 1000000, 'status' => 'pending']);

        $this->get(route('agent.reports.index'))
            ->assertOk()
            ->assertSee('₱2,500,000')
            ->assertSee('50%')
            ->assertSee('Popular Place')
            ->assertSee('1 inquiry</span> from', false)
            ->assertDontSee('77,777');

        $this->get(route('agent.reports.index', ['year' => now()->subYear()->year]))
            ->assertOk()
            ->assertSee('₱1,000,000')
            ->assertDontSee('₱2,500,000');
    }
}
