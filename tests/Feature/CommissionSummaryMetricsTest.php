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
}
