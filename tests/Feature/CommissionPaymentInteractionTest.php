<?php

namespace Tests\Feature;

use App\Models\CommissionAgreement;
use App\Models\CommissionPayment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CommissionPaymentInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_confirm_payment_and_leave_a_note(): void
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
            'name' => 'Cedar Heights',
            'slug' => 'cedar-heights',
            'description' => 'Sample property',
            'address' => 'Cebu City',
            'city' => 'Cebu',
            'province' => 'Cebu',
            'price' => 3000000,
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
            'amount_due' => 150000,
            'agent_amount' => 90000,
            'broker_amount' => 60000,
            'amount_paid' => 0,
            'payment_status' => 'sent',
        ]);

        $this->actingAs($agent)
            ->post(route('agent.commission.update-payment', $agreement), [
                'payment_id' => $payment->id,
                'payment_status' => 'confirmed',
                'payment_message' => 'I received the payout and confirmed the transfer.',
            ])
            ->assertRedirect(route('agent.commission.show', $agreement));

        $this->assertDatabaseHas('commission_payments', [
            'id' => $payment->id,
            'payment_status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('commission_payment_notes', [
            'commission_payment_id' => $payment->id,
            'sender_type' => 'agent',
            'message' => 'I received the payout and confirmed the transfer.',
        ]);
    }

    public function test_broker_detail_page_lists_each_monthly_payment_with_amount_and_submit_action(): void
    {
        $this->travelTo('2026-02-15 09:00:00');

        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $agent = User::factory()->create([
            'role' => 'agent',
            'broker_id' => $broker->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $property = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Cedar Heights',
            'slug' => 'cedar-heights-monthly',
            'description' => 'Sample property',
            'address' => 'Cebu City',
            'city' => 'Cebu',
            'province' => 'Cebu',
            'price' => 3000000,
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

        $first = $agreement->payments()->create([
            'reservation_id' => null,
            'due_date' => '2026-02-15',
            'amount_due' => 150000,
            'agent_amount' => 90000,
            'broker_amount' => 60000,
            'amount_paid' => 0,
            'payment_status' => 'pending',
        ]);

        $second = $agreement->payments()->create([
            'reservation_id' => null,
            'due_date' => '2026-03-15',
            'amount_due' => 150000,
            'agent_amount' => 90000,
            'broker_amount' => 60000,
            'amount_paid' => 0,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($broker)
            ->get(route('broker.commissions.index'));

        $response->assertOk();
        $response->assertSee(route('broker.commissions.show', $agreement));
        $response->assertSee(route('broker.commissions.pay', $agreement));

        $this->actingAs($broker)
            ->get(route('broker.commissions.show', $agreement))
            ->assertOk()
            ->assertSee('Open payment form')
            ->assertDontSee('Submit Payment');

        $this->actingAs($broker)
            ->get(route('broker.commissions.pay', $agreement))
            ->assertOk()
            ->assertSee('Amount Due')
            ->assertSee('Submit Payment')
            ->assertSee('Amount Paid')
            ->assertSee('Payment Method')
            ->assertSee('Payment Proof')
            ->assertSee('150000')
            ->assertSee((string) $first->id)
            ->assertSee('Monthly Amount Due');

        $this->actingAs($broker)
            ->post(route('broker.commissions.update-payment', $agreement), [
                'payment_id' => $first->id,
                'amount_paid' => 150000,
                'payment_method' => 'gcash',
                'proof' => UploadedFile::fake()->create('payment-proof.jpg', 100, 'image/jpeg'),
                'payment_message' => 'Monthly commission payment sent through GCash.',
            ])
            ->assertRedirect(route('broker.commissions.show', $agreement));

        $this->assertDatabaseHas('commission_payments', [
            'id' => $first->id,
            'payment_status' => 'paid',
            'amount_paid' => 150000,
            'payment_method' => 'gcash',
        ]);

        $this->actingAs($broker)
            ->get(route('broker.commissions.show', $agreement))
            ->assertOk()
            ->assertDontSee('Pay This Month');

    }
}
