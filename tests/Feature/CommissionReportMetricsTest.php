<?php

namespace Tests\Feature;

use App\Models\CommissionAgreement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionReportMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_report_page_shows_commission_summary_metrics(): void
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
            'name' => 'Crescent Ridge',
            'slug' => 'crescent-ridge',
            'description' => 'Test property',
            'address' => 'Baguio',
            'city' => 'Baguio',
            'province' => 'Benguet',
            'price' => 7000000,
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
            'amount_due' => 250000,
            'agent_amount' => 150000,
            'broker_amount' => 100000,
            'amount_paid' => 250000,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $agreement->payments()->create([
            'reservation_id' => null,
            'due_date' => '2026-03-15',
            'amount_due' => 150000,
            'agent_amount' => 90000,
            'broker_amount' => 60000,
            'amount_paid' => 0,
            'payment_status' => 'disputed',
            'dispute_reason' => 'Need corrected receipt',
        ]);

        $this->actingAs($broker)
            ->get(route('broker.reports.index'))
            ->assertOk()
            ->assertSee('Commission Expected')
            ->assertSee('Commission Paid')
            ->assertSee('Commission Disputed')
            ->assertSee('₱400,000.00');
    }
}
