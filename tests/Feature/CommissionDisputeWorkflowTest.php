<?php

namespace Tests\Feature;

use App\Models\CommissionAgreement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionDisputeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_can_mark_a_payment_as_disputed_with_a_reason(): void
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
            'name' => 'Harbor View',
            'slug' => 'harbor-view',
            'description' => 'Sample property',
            'address' => 'Bacolod',
            'city' => 'Bacolod',
            'province' => 'Negros Occidental',
            'price' => 4000000,
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

        $payment = $agreement->payments()->create([
            'reservation_id' => null,
            'due_date' => '2026-02-15',
            'amount_due' => 200000,
            'agent_amount' => 120000,
            'broker_amount' => 80000,
            'amount_paid' => 0,
            'payment_status' => 'sent',
        ]);

        $this->actingAs($broker)
            ->post(route('broker.commissions.update-payment', $agreement), [
                'payment_status' => 'disputed',
                'payment_message' => 'Proof submitted is incomplete.',
                'dispute_reason' => 'Agent submitted a blurry receipt and missing details.',
            ])
            ->assertRedirect(route('broker.commissions.show', $agreement));

        $this->assertDatabaseHas('commission_payments', [
            'id' => $payment->id,
            'payment_status' => 'disputed',
            'dispute_reason' => 'Agent submitted a blurry receipt and missing details.',
        ]);
    }
}
