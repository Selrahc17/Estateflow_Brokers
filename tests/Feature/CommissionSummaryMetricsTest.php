<?php

namespace Tests\Feature;

use App\Models\CommissionAgreement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionSummaryMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_commission_index_shows_summary_metrics_for_expected_paid_and_disputed_amounts(): void
    {
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $agent = User::factory()->create([
            'role' => 'agent',
            'broker_id' => $broker->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $property = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Bay View',
            'slug' => 'bay-view',
            'description' => 'Test property',
            'address' => 'Iloilo',
            'city' => 'Iloilo',
            'province' => 'Iloilo',
            'price' => 6000000,
            'status' => 'available',
        ]);

        $agreement = CommissionAgreement::create([
            'broker_id' => $broker->id,
            'agent_id' => $agent->id,
            'property_id' => $property->id,
            'commission_rate' => 5,
            'broker_share' => 40,
            'agent_share' => 60,
            'payment_schedule' => 'monthly',
            'payment_day' => 15,
            'start_date' => '2026-01-01',
            'status' => 'active',
        ]);

        $agreement->payments()->create([
            'reservation_id' => null,
            'due_date' => '2026-02-15',
            'amount_due' => 200000,
            'agent_amount' => 120000,
            'broker_amount' => 80000,
            'amount_paid' => 0,
            'payment_status' => 'disputed',
            'dispute_reason' => 'Incomplete proof',
        ]);

        $agreement->payments()->create([
            'reservation_id' => null,
            'due_date' => '2026-03-15',
            'amount_due' => 100000,
            'agent_amount' => 60000,
            'broker_amount' => 40000,
            'amount_paid' => 100000,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($broker)
            ->get(route('broker.commissions.index'))
            ->assertOk()
            ->assertSee('Total Expected')
            ->assertSee('Paid')
            ->assertSee('Disputed')
            ->assertSee('₱300,000.00');
    }

    public function test_broker_commission_create_page_shows_agent_listed_properties(): void
    {
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $agent = User::factory()->create([
            'role' => 'agent',
            'broker_id' => $broker->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        Property::create([
            'broker_id' => $agent->id,
            'name' => 'Agent Posted Villa',
            'slug' => 'agent-posted-villa',
            'description' => 'Property posted by agent',
            'address' => 'Dumaguete',
            'city' => 'Dumaguete',
            'province' => 'Negros Oriental',
            'price' => 3500000,
            'status' => 'available',
        ]);

        $this->actingAs($broker)
            ->get(route('broker.commissions.create'))
            ->assertOk()
            ->assertSee('Agent Posted Villa');
    }

    public function test_broker_commission_index_does_not_duplicate_same_property_entries(): void
    {
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $agent = User::factory()->create([
            'role' => 'agent',
            'broker_id' => $broker->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $property = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Bay View',
            'slug' => 'bay-view-duplicate',
            'description' => 'Test property',
            'address' => 'Iloilo',
            'city' => 'Iloilo',
            'province' => 'Iloilo',
            'price' => 6000000,
            'status' => 'available',
        ]);

        CommissionAgreement::create([
            'broker_id' => $broker->id,
            'agent_id' => $agent->id,
            'property_id' => $property->id,
            'commission_rate' => 5,
            'broker_share' => 40,
            'agent_share' => 60,
            'payment_schedule' => 'monthly',
            'payment_day' => 15,
            'start_date' => '2026-01-01',
            'status' => 'active',
        ]);

        CommissionAgreement::create([
            'broker_id' => $broker->id,
            'agent_id' => $agent->id,
            'property_id' => $property->id,
            'commission_rate' => 7,
            'broker_share' => 35,
            'agent_share' => 65,
            'payment_schedule' => 'monthly',
            'payment_day' => 15,
            'start_date' => '2026-02-01',
            'status' => 'active',
        ]);

        $response = $this->actingAs($broker)
            ->get(route('broker.commissions.index'));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'Bay View'));
    }


    public function test_broker_can_save_commission_agreement_for_agent_posted_property(): void
    {
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $agent = User::factory()->create([
            'role' => 'agent',
            'broker_id' => $broker->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $property = Property::create([
            'broker_id' => $agent->id,
            'name' => 'Agent Posted Villa 2',
            'slug' => 'agent-posted-villa-2',
            'description' => 'Property posted by agent',
            'address' => 'Bacolod',
            'city' => 'Bacolod',
            'province' => 'Negros Occidental',
            'price' => 4200000,
            'status' => 'available',
        ]);

        $payload = [
            'agent_id' => $agent->id,
            'property_id' => $property->id,
            'commission_rate' => 5,
            'agent_share' => 60,
            'broker_share' => 40,
            'payment_schedule' => 'monthly',
            'payment_day' => 15,
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-31',
        ];

        $this->actingAs($broker)
            ->post(route('broker.commissions.store'), $payload)
            ->assertRedirect(route('broker.commissions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('commission_agreements', [
            'broker_id' => $broker->id,
            'agent_id' => $agent->id,
            'property_id' => $property->id,
            'commission_rate' => 5.00,
            'agent_share' => 60.00,
            'broker_share' => 40.00,
        ]);
    }
}
