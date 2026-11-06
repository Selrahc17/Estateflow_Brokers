<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignOutTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, bool $approved = true): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true, 'is_approved' => $approved]);
    }

    public function test_brokers_and_agents_see_a_confirmation_on_the_sign_in_page(): void
    {
        foreach (['broker', 'agent'] as $role) {
            $this->actingAs($this->user($role))
                ->followingRedirects()
                ->post(route('auth.logout'))
                ->assertOk()
                ->assertSee('You have been signed out.')
                ->assertSee('Welcome Back');

            $this->assertGuest();
        }
    }

    public function test_clients_and_admins_keep_their_existing_sign_out(): void
    {
        foreach (['client', 'admin'] as $role) {
            $this->actingAs($this->user($role))
                ->post(route('auth.logout'))
                ->assertRedirect('/')
                ->assertSessionMissing('success');

            $this->assertGuest();
        }
    }

    public function test_sign_out_prompt_is_on_every_broker_and_agent_entry_point(): void
    {
        $this->actingAs($this->user('broker'))->get(route('broker.dashboard'))->assertSee('Stay Signed In');
        $this->actingAs($this->user('agent'))->get(route('agent.dashboard'))->assertSee('Stay Signed In');
        $this->actingAs($this->user('broker', approved: false))->get(route('broker.application.show'))->assertSee('Stay Signed In');
    }

    public function test_dialog_cancel_button_does_not_submit_the_form(): void
    {
        $this->actingAs($this->user('agent'))
            ->get(route('agent.dashboard'))
            ->assertSee('<button type="button" @click="$refs.signOut.close()"', false);
    }
}
