<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSidebarTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true, 'is_approved' => true]);
    }

    public function test_admin_sidebar_keeps_every_link_in_its_section(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Panel')
            ->assertSeeInOrder([
                'Overview', 'Dashboard', 'Reports &amp; Analytics',
                'Management', 'User Management', 'Broker Management', 'Property Management', 'Reservations', 'Documents', 'Feedback', 'Contact Messages', 'Notifications',
                'System', 'Audit Logs', 'Backups', 'System Settings',
            ], false);
    }

    public function test_admin_sidebar_links_to_every_section(): void
    {
        $response = $this->actingAs($this->user('admin'))->get(route('admin.dashboard'));

        foreach (['dashboard', 'reports', 'users', 'brokers', 'properties', 'reservations', 'documents', 'feedback', 'contact-messages', 'notifications', 'audit', 'backups', 'settings'] as $page) {
            $response->assertSee('href="'.route("admin.$page").'"', false);
        }
    }

    public function test_broker_sidebar_keeps_every_link_in_its_section(): void
    {
        $this->actingAs($this->user('broker'))
            ->get(route('broker.dashboard'))
            ->assertOk()
            ->assertSee('Broker Portal')
            ->assertSeeInOrder([
                'Dashboard', 'Agent Performance', 'Commission', 'Agent Property Lists',
                'Management', 'Agent Management', 'Reports and Analytics',
                'Team', 'Clients', 'Inquiries', 'Reservations',
                'Messages', 'Message', 'Notification',
                'Oversight', 'Audit Logs', 'Profile Settings',
            ]);
    }

    public function test_active_link_is_marked_on_nested_pages(): void
    {
        $admin = $this->user('admin');
        $current = 'aria-current="page"';

        $this->actingAs($admin)->get(route('admin.users.create'))->assertSee($current, false);
        $this->actingAs($this->user('broker'))->get(route('broker.agents.index'))->assertSee($current, false);
    }

    public function test_admin_sign_out_asks_for_confirmation_first(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('admin.dashboard'))
            ->assertSee('Stay Signed In')
            ->assertSee('<button type="button" @click="$refs.signOut.showModal()"', false);
    }

    public function test_admin_and_broker_share_the_dark_mode_toggle(): void
    {
        foreach (['admin' => 'admin.dashboard', 'broker' => 'broker.dashboard'] as $role => $route) {
            $this->actingAs($this->user($role))
                ->get(route($route))
                ->assertOk()
                ->assertSee("localStorage.getItem('darkMode') === 'true'", false)
                ->assertSee("localStorage.setItem('darkMode', darkMode)", false)
                ->assertSee('dark:bg-stone-950', false);
        }
    }

    public function test_admin_badges_only_show_when_there_is_something_to_review(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('admin.dashboard'))
            ->assertDontSee('bg-amber-600 px-1.5', false);

        User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => false]);

        $this->get(route('admin.dashboard'))->assertSee('bg-amber-600 px-1.5', false);
    }
}
