<?php

namespace Tests\Feature;

use App\Models\CommissionAgreement;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionReservationValueTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_the_reservation_total_price_when_generating_commission_payments(): void
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
            'name' => 'Venice Residences',
            'slug' => 'venice-residences',
            'description' => 'Test property',
            'address' => 'Makati',
            'city' => 'Makati',
            'province' => 'Metro Manila',
            'price' => 5000000,
            'status' => 'available',
        ]);

        $reservation = Reservation::create([
            'client_id' => null,
            'agent_id' => $agent->id,
            'lot_id' => null,
            'broker_id' => $broker->id,
            'reservation_code' => 'RES-TEST-001',
            'status' => 'confirmed',
            'total_price' => 8000000,
            'down_payment' => 2000000,
            'payment_schedule' => 'monthly',
            'payment_terms_months' => 12,
            'reserved_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $agreement = CommissionAgreement::create([
            'broker_id' => $broker->id,
            'agent_id' => $agent->id,
            'property_id' => $property->id,
            'reservation_id' => $reservation->id,
            'commission_rate' => 5,
            'broker_share' => 40,
            'agent_share' => 60,
            'payment_schedule' => 'monthly',
            'payment_day' => 15,
            'start_date' => '2026-01-01',
            'status' => 'active',
        ]);

        $agreement->generateScheduledPayments();

        $payment = $agreement->payments()->first();

        $this->assertNotNull($payment);
        $this->assertSame('400000.00', number_format((float) $payment->amount_due, 2, '.', ''));
        $this->assertSame('240000.00', number_format((float) $payment->agent_amount, 2, '.', ''));
        $this->assertSame('160000.00', number_format((float) $payment->broker_amount, 2, '.', ''));
    }
}
