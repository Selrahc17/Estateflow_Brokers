<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The agent's Clients page lists every client the agent has, including ones
 * added by hand that have no inquiry, visit or reservation yet.
 */
class AgentClientListTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
    }

    public function test_a_client_added_by_hand_shows_up_right_away(): void
    {
        $this->actingAs($this->agent)
            ->post(route('agent.clients.store'), ['first_name' => 'Pedro', 'last_name' => 'Walk-in', 'email' => 'pedro@example.com', 'phone' => '09171234567'])
            ->assertRedirect(route('agent.clients.index'));

        $this->followingRedirects()->get(route('agent.clients.index'))
            ->assertSee('Client added successfully.')
            ->assertSee('Pedro')
            ->assertSee('pedro@example.com');
    }

    public function test_search_ignores_capitals_and_matches_full_name_email_or_phone(): void
    {
        Client::create(['broker_id' => $this->agent->id, 'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => 'juan@example.com', 'phone' => '09998887777', 'password' => 'x', 'status' => 'active']);
        Client::create(['broker_id' => $this->agent->id, 'first_name' => 'Maria', 'last_name' => 'Santos', 'email' => 'maria@example.com', 'password' => 'x', 'status' => 'active']);
        $this->actingAs($this->agent);

        foreach (['juan dela cruz', 'DELA', 'JUAN@EXAMPLE', '0999888'] as $term) {
            $this->get(route('agent.clients.index', ['search' => $term]))->assertSee('juan@example.com')->assertDontSee('maria@example.com');
        }
    }

    public function test_other_agents_clients_are_not_listed(): void
    {
        $other = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        Client::create(['broker_id' => $other->id, 'first_name' => 'Hidden', 'last_name' => 'Buyer', 'email' => 'hidden@example.com', 'password' => 'x', 'status' => 'active']);
        Client::create(['broker_id' => $this->agent->id, 'first_name' => 'Mine', 'last_name' => 'Buyer', 'email' => 'mine@example.com', 'password' => 'x', 'status' => 'active']);

        $this->actingAs($this->agent)->get(route('agent.clients.index'))
            ->assertSee('mine@example.com')
            ->assertDontSee('hidden@example.com');
    }
}
