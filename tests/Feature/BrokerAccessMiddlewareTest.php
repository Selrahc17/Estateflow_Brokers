<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerAccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_unapproved_broker_cannot_access_broker_routes(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => false,
        ]);

        $this->actingAs($broker)
            ->get(route('broker.dashboard'))
            ->assertRedirect(route('auth.login'));
    }

    public function test_suspended_broker_cannot_access_broker_routes(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_active' => false,
            'is_approved' => true,
        ]);

        $this->actingAs($broker)
            ->get(route('broker.dashboard'))
            ->assertRedirect(route('auth.login'));
    }
}
