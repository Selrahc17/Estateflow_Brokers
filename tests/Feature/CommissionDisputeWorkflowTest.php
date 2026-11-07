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

    /**
     * @return array{0: User, 1: User, 2: CommissionAgreement, 3: \App\Models\CommissionPayment}
     */
    private function paymentAwaitingReview(): array
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
            'due_date' => now()->toDateString(),
            'amount_due' => 200000,
            'agent_amount' => 120000,
            'broker_amount' => 80000,
            'amount_paid' => 0,
            'payment_status' => 'sent',
        ]);

        return [$broker, $agent, $agreement, $payment];
    }

    public function test_agent_can_dispute_a_payment_and_the_broker_can_read_the_reason(): void
    {
        [$broker, $agent, $agreement, $payment] = $this->paymentAwaitingReview();
        $reason = 'The receipt is blurry and the amount looks wrong.';

        $this->actingAs($agent)
            ->post(route('agent.commission.update-payment', $agreement), [
                'payment_id' => $payment->id,
                'payment_status' => 'disputed',
                'payment_message' => $reason,
            ])
            ->assertRedirect(route('agent.commission.show', $agreement));

        $this->assertDatabaseHas('commission_payments', ['id' => $payment->id, 'payment_status' => 'disputed']);
        $this->assertDatabaseHas('commission_payment_notes', ['commission_payment_id' => $payment->id, 'sender_type' => 'agent', 'message' => $reason]);

        $this->actingAs($broker)
            ->get(route('broker.commissions.show', $agreement))
            ->assertOk()
            ->assertSee($reason);
    }

    public function test_broker_payment_action_only_records_payments_with_proof(): void
    {
        [$broker, , $agreement, $payment] = $this->paymentAwaitingReview();

        $this->actingAs($broker)
            ->post(route('broker.commissions.update-payment', $agreement), [
                'payment_status' => 'disputed',
                'payment_message' => 'Proof submitted is incomplete.',
            ])
            ->assertSessionHasErrors(['payment_id', 'amount_paid', 'payment_method', 'proof']);

        $this->assertDatabaseHas('commission_payments', ['id' => $payment->id, 'payment_status' => 'sent']);
    }
}
