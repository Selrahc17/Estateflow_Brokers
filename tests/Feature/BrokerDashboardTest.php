<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_only_sees_agents_assigned_to_that_broker(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $otherBroker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $assignedAgent = User::factory()->create([
            'role' => 'agent',
            'broker_id' => $broker->id,
            'is_active' => true,
            'is_approved' => true,
        ]);
        $otherAgent = User::factory()->create([
            'role' => 'agent',
            'broker_id' => $otherBroker->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->actingAs($broker)
            ->get(route('broker.dashboard'))
            ->assertOk()
            ->assertSee($assignedAgent->email)
            ->assertDontSee($otherAgent->email);
    }

    public function test_agent_cannot_access_broker_dashboard(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->actingAs($agent)
            ->get(route('broker.dashboard'))
            ->assertForbidden();
    }
}
