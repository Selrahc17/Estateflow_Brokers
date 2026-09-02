<?php

namespace Tests\Feature;

use App\Models\CommissionAgreement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionAgreementScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_quarterly_payment_schedule_from_the_agreement(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $agent = User::factory()->create([
            'role' => 'agent',
            'broker_id' => $broker->id,
        ]);

        $property = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Sunrise Residences',
            'slug' => 'sunrise-residences',
            'description' => 'Sample property',
            'address' => 'Davao City',
            'city' => 'Davao',
            'province' => 'Davao del Sur',
            'price' => 2500000,
            'status' => 'available',
        ]);

        $agreement = CommissionAgreement::create([
            'broker_id' => $broker->id,
            'agent_id' => $agent->id,
            'property_id' => $property->id,
            'commission_rate' => 5,
            'broker_share' => 40,
            'agent_share' => 60,
            'payment_schedule' => 'quarterly',
            'payment_day' => 15,
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $agreement->generateScheduledPayments();

        $this->assertDatabaseCount('commission_payments', 2);

        $this->assertDatabaseHas('commission_payments', [
            'commission_agreement_id' => $agreement->id,
            'due_date' => '2026-03-15 00:00:00',
        ]);
        $this->assertDatabaseHas('commission_payments', [
            'commission_agreement_id' => $agreement->id,
            'due_date' => '2026-06-15 00:00:00',
        ]);
    }
}
