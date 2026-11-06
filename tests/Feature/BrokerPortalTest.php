<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\CommissionAgreement;
use App\Models\Document;
use App\Models\Inquiry;
use App\Models\Lot;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\AgentInvitation;
use App\Services\AgentInvitationService;
use App\Services\BrokerComplianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class BrokerPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $broker;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Storage::fake('local');

        $this->broker = $this->makeBroker();
        $this->actingAs($this->broker);
    }

    private function makeBroker(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
            'phone' => '09171234567',
            'prc_id_expires_at' => now()->addYear(),
            'dhsud_registration_expires_at' => now()->addYear(),
        ], $overrides));
    }

    private function makeAgent(?User $broker = null, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'agent',
            'broker_id' => ($broker ?? $this->broker)->id,
            'is_active' => true,
            'is_approved' => true,
            'prc_id_expires_at' => now()->addYear(),
        ], $overrides));
    }

    private function makeClient(User $agent, array $overrides = []): Client
    {
        return Client::create(array_merge([
            'broker_id' => $agent->id,
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret-password',
            'status' => 'active',
        ], $overrides));
    }

    private function makeProperty(User $agent): Property
    {
        return Property::create([
            'broker_id' => $agent->id,
            'name' => 'Vista Homes',
            'slug' => fake()->unique()->slug(),
            'address' => 'Quezon City',
            'status' => 'available',
        ]);
    }

    private function inviteAgent(string $email = 'new-agent@example.com'): string
    {
        return $this->post(route('broker.agents.store'), ['name' => 'New Agent', 'email' => $email])
            ->assertRedirect(route('broker.agents.index'))
            ->assertSessionHas('invite_link')
            ->getSession()
            ->get('invite_link');
    }

    private function acceptancePayload(array $overrides = []): array
    {
        return array_merge([
            'license_number' => 'ACC-1001',
            'prc_id_expires_at' => now()->addYear()->toDateString(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'privacy_consent' => '1',
        ], $overrides);
    }

    public function test_every_broker_page_renders(): void
    {
        $agent = $this->makeAgent();
        $client = $this->makeClient($agent);
        $property = $this->makeProperty($agent);
        Inquiry::create(['broker_id' => $agent->id, 'property_id' => $property->id, 'message' => 'Is this available?', 'email' => $client->email, 'status' => 'new']);

        foreach ([
            route('broker.dashboard'),
            route('broker.performance'),
            route('broker.property-lists'),
            route('broker.commissions.index'),
            route('broker.commissions.create'),
            route('broker.reports.index'),
            route('broker.messages.index'),
            route('broker.notifications.index'),
            route('broker.settings.index'),
            route('broker.audit.index'),
            route('broker.agents.index'),
            route('broker.agents.edit', $agent),
            route('broker.team.clients'),
            route('broker.team.clients.show', $client),
            route('broker.team.inquiries'),
            route('broker.team.reservations'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_inviting_an_agent_creates_a_pending_account_and_a_link(): void
    {
        $link = $this->inviteAgent();

        $agent = User::where('email', 'new-agent@example.com')->firstOrFail();

        $this->assertSame('agent', $agent->role);
        $this->assertSame($this->broker->id, $agent->broker_id);
        $this->assertFalse((bool) $agent->is_approved);
        $this->assertTrue($agent->isInvited());
        $this->assertStringContainsString('/invitations/', $link);
        $this->assertStringNotContainsString((string) $agent->invitation_token, $link);
        Notification::assertSentTo($agent, AgentInvitation::class);

        $this->get(route('broker.agents.index'))->assertSee('Invited')->assertSee('Resend Invite');
    }

    public function test_failed_invitation_does_not_leave_an_account_behind(): void
    {
        $this->mock(AgentInvitationService::class)
            ->shouldReceive('issue')
            ->andThrow(new RuntimeException('Could not save the invitation.'));

        $this->post(route('broker.agents.store'), ['name' => 'New Agent', 'email' => 'new-agent@example.com'])
            ->assertServerError();

        $this->assertDatabaseMissing('users', ['email' => 'new-agent@example.com']);
    }

    public function test_pending_agent_with_no_link_can_be_sent_a_new_one(): void
    {
        $agent = $this->makeAgent(null, ['is_approved' => false, 'invitation_token' => null]);

        $this->get(route('broker.agents.index'))->assertSee('Resend Invite');

        $this->post(route('broker.agents.invitation', $agent))->assertSessionHas('invite_link');
        $this->assertNotNull($agent->fresh()->invitation_token);
    }

    public function test_invited_agent_can_finish_their_account_and_sign_in(): void
    {
        $link = $this->inviteAgent();
        $agent = User::where('email', 'new-agent@example.com')->firstOrFail();
        Auth::logout();

        $this->get($link)->assertOk()->assertSee("Team", false)->assertSee($agent->email);

        $this->post($link, $this->acceptancePayload())->assertRedirect(route('agent.dashboard'));

        $agent->refresh();
        $this->assertAuthenticatedAs($agent);
        $this->assertTrue((bool) $agent->is_approved);
        $this->assertFalse($agent->isInvited());
        $this->assertSame('ACC-1001', $agent->license_number);
        $this->assertNotNull($agent->privacy_consented_at);
        $this->assertTrue(Auth::validate(['email' => $agent->email, 'password' => 'password123']));
        $this->assertSame(1, AppNotification::where('user_id', $this->broker->id)->where('type', 'agent_invitation')->count());

        Auth::logout();
        $this->get($link)->assertStatus(410)->assertSee('Invitation Unavailable');
    }

    public function test_invitation_requires_consent_accreditation_and_matching_passwords(): void
    {
        $link = $this->inviteAgent();
        Auth::logout();

        $this->from($link)->post($link, [
            'password' => 'password123',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['license_number', 'prc_id_expires_at', 'password', 'privacy_consent']);

        $this->assertGuest();
    }

    public function test_expired_or_replaced_invitation_stops_working(): void
    {
        $link = $this->inviteAgent();
        $agent = User::where('email', 'new-agent@example.com')->firstOrFail();

        $this->post(route('broker.agents.invitation', $agent))->assertSessionHas('invite_link');
        Auth::logout();

        $this->get($link)->assertStatus(410);
        $this->post($link, $this->acceptancePayload())->assertRedirect($link);
        $this->assertFalse((bool) $agent->fresh()->is_approved);

        $agent->forceFill(['invitation_token' => hash('sha256', 'old'), 'invitation_expires_at' => now()->subMinute()])->save();
        $this->get(route('auth.invitation.show', 'old'))->assertStatus(410);
    }

    public function test_invited_agent_cannot_reach_the_agent_portal_yet(): void
    {
        $this->inviteAgent();
        $agent = User::where('email', 'new-agent@example.com')->firstOrFail();

        $this->actingAs($agent)->get(route('agent.dashboard'))->assertRedirect(route('auth.login'));
    }

    public function test_broker_cannot_resend_another_brokers_invitation(): void
    {
        $other = $this->makeBroker();
        $agent = $this->makeAgent($other, ['is_approved' => false, 'invitation_token' => hash('sha256', 'x'), 'invitation_expires_at' => now()->addDay()]);

        $this->post(route('broker.agents.invitation', $agent))->assertNotFound();
    }

    public function test_agent_limit_blocks_new_invitations(): void
    {
        config(['brokerage.max_agents_per_broker' => 2]);
        $this->makeAgent();
        $this->makeAgent();

        $this->from(route('broker.agents.index'))
            ->post(route('broker.agents.store'), ['name' => 'One Too Many', 'email' => 'extra@example.com'])
            ->assertSessionHasErrors(['email' => 'You have reached the limit of 2 agents per broker.']);

        $this->assertDatabaseMissing('users', ['email' => 'extra@example.com']);
        $this->get(route('broker.agents.index'))->assertSee('2 of 2 agents')->assertDontSee('Send Invitation');
    }

    public function test_agent_status_filters_match_their_counts(): void
    {
        $this->makeAgent(null, ['name' => 'Active Agent']);
        $this->makeAgent(null, ['name' => 'Suspended Agent', 'is_active' => false]);
        $this->makeAgent(null, ['name' => 'Renewal Agent', 'prc_id_expires_at' => today()->addDays(10)]);
        $this->makeAgent(null, ['name' => 'Waiting Agent', 'is_approved' => false, 'invitation_token' => hash('sha256', 'w'), 'invitation_expires_at' => now()->addDay()]);
        $this->makeAgent($this->makeBroker(), ['name' => 'Outside Agent']);

        $this->get(route('broker.agents.index'))
            ->assertSee('Active Agent')
            ->assertDontSee('Outside Agent')
            ->assertViewHas('summary', ['active' => 2, 'invited' => 1, 'suspended' => 1, 'renewal' => 1]);

        $this->get(route('broker.agents.index', ['status' => 'invited']))->assertSee('Waiting Agent')->assertDontSee('Renewal Agent');
        $this->get(route('broker.agents.index', ['status' => 'suspended']))->assertSee('Suspended Agent')->assertDontSee('Waiting Agent');
        $this->get(route('broker.agents.index', ['status' => 'renewal']))->assertSee('Renewal Agent')->assertDontSee('Waiting Agent');
        $this->get(route('broker.agents.index', ['status' => 'active']))->assertSee('Active Agent')->assertDontSee('Suspended Agent');
        $this->get(route('broker.agents.index', ['status' => 'not-a-status']))->assertSee('Suspended Agent')->assertSee('Waiting Agent');
    }

    public function test_agent_search_keeps_the_selected_status(): void
    {
        $this->makeAgent(null, ['name' => 'Rizal One']);
        $this->makeAgent(null, ['name' => 'Rizal Two', 'is_active' => false]);

        $this->get(route('broker.agents.index', ['status' => 'suspended', 'search' => 'Rizal']))
            ->assertSee('Rizal Two')
            ->assertDontSee('Rizal One');

        $this->get(route('broker.agents.index', ['status' => 'suspended', 'search' => 'Nobody']))
            ->assertSee('No agents match this view');
    }

    public function test_invite_errors_reopen_the_dialog(): void
    {
        $this->makeAgent(null, ['email' => 'taken@example.com']);

        $this->from(route('broker.agents.index'))
            ->followingRedirects()
            ->post(route('broker.agents.store'), ['name' => 'Copy', 'email' => 'taken@example.com'])
            ->assertSee('An account with this email already exists.')
            ->assertSee('$refs.invite.showModal()', false);

        $this->get(route('broker.agents.index'))->assertDontSee('$refs.invite.showModal())', false);
    }

    public function test_limit_error_does_not_try_to_open_a_missing_dialog(): void
    {
        config(['brokerage.max_agents_per_broker' => 1]);
        $this->makeAgent();

        $this->from(route('broker.agents.index'))
            ->followingRedirects()
            ->post(route('broker.agents.store'), ['name' => 'Extra', 'email' => 'extra@example.com'])
            ->assertSee('You have reached the limit of 1 agents per broker.')
            ->assertDontSee('x-ref="invite"', false)
            ->assertDontSee('$nextTick', false);
    }

    public function test_team_pages_show_only_this_brokers_agents(): void
    {
        $agent = $this->makeAgent();
        $otherAgent = $this->makeAgent($this->makeBroker());

        $mine = $this->makeClient($agent, ['first_name' => 'Mine']);
        $theirs = $this->makeClient($otherAgent, ['first_name' => 'Theirs']);

        $property = $this->makeProperty($agent);
        $otherProperty = $this->makeProperty($otherAgent);
        Inquiry::create(['broker_id' => $agent->id, 'property_id' => $property->id, 'message' => 'My team inquiry', 'email' => 'a@example.com', 'status' => 'new']);
        Inquiry::create(['broker_id' => $otherAgent->id, 'property_id' => $otherProperty->id, 'message' => 'Other team inquiry', 'email' => 'b@example.com', 'status' => 'new']);

        $lot = Lot::create(['property_id' => $property->id, 'lot_number' => 'A1', 'status' => 'reserved']);
        $otherLot = Lot::create(['property_id' => $otherProperty->id, 'lot_number' => 'B1', 'status' => 'reserved']);
        Reservation::create(['client_id' => $mine->id, 'broker_id' => $agent->id, 'lot_id' => $lot->id, 'reservation_code' => 'RES-MINE', 'status' => 'pending', 'total_price' => 100]);
        Reservation::create(['client_id' => $theirs->id, 'broker_id' => $otherAgent->id, 'lot_id' => $otherLot->id, 'reservation_code' => 'RES-OTHER', 'status' => 'pending', 'total_price' => 100]);

        $this->get(route('broker.team.clients'))->assertSee('Mine')->assertDontSee('Theirs');
        $this->get(route('broker.team.inquiries'))->assertSee('My team inquiry')->assertDontSee('Other team inquiry');
        $this->get(route('broker.team.reservations'))->assertSee('RES-MINE')->assertDontSee('RES-OTHER');
        $this->get(route('broker.team.clients.show', $mine))->assertOk()->assertSee('RES-MINE');
        $this->get(route('broker.team.clients.show', $theirs))->assertNotFound();

        // Choosing another broker's agent in the filter shows nothing rather than leaking.
        $this->get(route('broker.team.clients', ['agent' => $otherAgent->id]))->assertDontSee('Theirs')->assertSee('No clients found.');
    }

    public function test_team_pages_filter_by_agent_and_status(): void
    {
        $first = $this->makeAgent();
        $second = $this->makeAgent();
        $this->makeClient($first, ['first_name' => 'Firstclient', 'qualification_status' => 'approved']);
        $this->makeClient($second, ['first_name' => 'Secondclient', 'qualification_status' => 'pending']);

        $this->get(route('broker.team.clients', ['agent' => $first->id]))->assertSee('Firstclient')->assertDontSee('Secondclient');
        $this->get(route('broker.team.clients', ['status' => 'pending']))->assertSee('Secondclient')->assertDontSee('Firstclient');
    }

    public function test_team_pages_are_read_only_and_broker_only(): void
    {
        $agent = $this->makeAgent();
        $client = $this->makeClient($agent);

        $this->put(route('broker.team.clients.show', $client))->assertStatus(405);
        $this->delete(route('broker.team.clients.show', $client))->assertStatus(405);

        $this->actingAs($agent)->get(route('broker.team.clients'))->assertForbidden();
    }

    public function test_renewal_states_use_a_sixty_day_warning(): void
    {
        $compliance = app(BrokerComplianceService::class);

        $this->assertSame('missing', $compliance->renewalState(null));
        $this->assertSame('expired', $compliance->renewalState(today()->subDay()));
        $this->assertSame('expiring', $compliance->renewalState(today()));
        $this->assertSame('expiring', $compliance->renewalState(today()->addDays(60)));
        $this->assertSame('valid', $compliance->renewalState(today()->addDays(61)));
        $this->assertSame('Expires today', $compliance->renewalLabel(today()));
        $this->assertSame('Expires in 12 days', $compliance->renewalLabel(today()->addDays(12)));
    }

    public function test_dashboard_warns_about_broker_and_agent_renewals(): void
    {
        $this->broker->update(['prc_id_expires_at' => today()->subDay()]);
        $this->makeAgent(null, ['name' => 'Soon Agent', 'prc_id_expires_at' => today()->addDays(10)]);
        $this->makeAgent(null, ['name' => 'Fine Agent', 'prc_id_expires_at' => today()->addYear()]);

        $this->get(route('broker.dashboard'))
            ->assertSee('Renewals Needing Attention')
            ->assertSee('Your PRC ID')
            ->assertSee('Soon Agent', false)
            ->assertSee('Expires in 10 days');

        $renewals = app(BrokerComplianceService::class)->renewalsNeedingAttention($this->broker->fresh());
        $this->assertSame('expired', $renewals->first()['state']);
        $this->assertCount(2, $renewals);
    }

    public function test_dashboard_hides_the_renewal_card_when_nothing_needs_attention(): void
    {
        $this->get(route('broker.dashboard'))->assertDontSee('Renewals Needing Attention');
    }

    public function test_broker_can_update_license_details_after_approval(): void
    {
        $this->from(route('broker.settings.index'))->put(route('broker.settings.license'), [
            'license_number' => 'PRC-2',
            'prc_id_expires_at' => now()->addYears(3)->toDateString(),
            'dhsud_registration_number' => 'DHSUD-2',
            'dhsud_registration_expires_at' => now()->addYear()->toDateString(),
            'tin' => '123-456-789-000',
        ])->assertSessionHasNoErrors();

        $this->assertSame('PRC-2', $this->broker->fresh()->license_number);
        $this->assertSame('09171234567', $this->broker->fresh()->phone);
    }

    public function test_broker_can_replace_a_verified_document_for_admin_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $document = Document::create(['broker_id' => $this->broker->id, 'name' => 'Bond', 'type' => 'bond', 'file_path' => 'broker-compliance/old.pdf', 'status' => 'verified']);

        $this->from(route('broker.settings.index'))->post(route('broker.settings.documents.store'), [
            'type' => 'bond',
            'file' => UploadedFile::fake()->create('renewed-bond.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->assertSame('pending', $document->fresh()->status);
        $this->assertTrue((bool) $this->broker->fresh()->is_approved);
        $this->assertSame(1, AppNotification::where('user_id', $admin->id)->where('type', 'broker_renewal')->count());
    }

    public function test_broker_cannot_download_another_brokers_document(): void
    {
        $document = Document::create(['broker_id' => $this->makeBroker()->id, 'name' => 'Bond', 'type' => 'bond', 'file_path' => 'x.pdf', 'status' => 'verified']);

        $this->get(route('broker.settings.documents.download', $document))->assertNotFound();
    }

    public function test_agent_edit_records_accreditation_renewal(): void
    {
        $agent = $this->makeAgent();

        $this->put(route('broker.agents.update', $agent), [
            'name' => $agent->name,
            'email' => $agent->email,
            'license_number' => 'ACC-2',
            'prc_id_expires_at' => now()->addYears(2)->toDateString(),
        ])->assertRedirect(route('broker.agents.index'));

        $this->assertSame('ACC-2', $agent->fresh()->license_number);
    }

    public function test_performance_page_shows_real_commission(): void
    {
        $agent = $this->makeAgent();
        $agreement = CommissionAgreement::create([
            'broker_id' => $this->broker->id,
            'agent_id' => $agent->id,
            'commission_rate' => 5,
            'agent_share' => 60,
            'broker_share' => 40,
            'payment_schedule' => 'monthly',
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);
        $agreement->payments()->create(['due_date' => now()->toDateString(), 'amount_due' => 1000, 'agent_amount' => 600, 'broker_amount' => 400, 'amount_paid' => 1000, 'payment_status' => 'paid']);
        $agreement->payments()->create(['due_date' => now()->addMonth()->toDateString(), 'amount_due' => 1000, 'agent_amount' => 600, 'broker_amount' => 400, 'amount_paid' => 0, 'payment_status' => 'pending']);

        $this->get(route('broker.performance'))
            ->assertSee('₱600.00 paid')
            ->assertSee('of ₱1,200.00 expected')
            ->assertDontSee('Not tracked');
    }

    public function test_pending_invites_card_counts_invited_agents(): void
    {
        $this->inviteAgent();
        $this->makeAgent();

        $this->get(route('broker.dashboard'))
            ->assertSee('Pending Invites')
            ->assertDontSee('Pending Approval')
            ->assertViewHas('stats', fn (array $stats) => $stats['pending_invites'] === 1 && $stats['active_agents'] === 1);
    }

    public function test_audit_log_includes_the_broker_filters_and_paginates(): void
    {
        $agent = $this->makeAgent();
        $outsider = $this->makeAgent($this->makeBroker());

        $log = fn (User $actor, string $action, string $description) => AuditLog::create([
            'actor_id' => $actor->id, 'actor_role' => $actor->role, 'method' => 'POST', 'action' => $action, 'description' => $description,
        ]);

        $log($this->broker, 'Updated', 'broker own action');
        $log($agent, 'Deleted', 'agent action');
        $log($outsider, 'Updated', 'outsider action');

        $this->get(route('broker.audit.index'))
            ->assertSee('Broker own action')
            ->assertSee('Agent action')
            ->assertDontSee('Outsider action');

        $this->get(route('broker.audit.index', ['actor' => $agent->id]))->assertSee('Agent action')->assertDontSee('Broker own action');
        $this->get(route('broker.audit.index', ['action' => 'Deleted']))->assertSee('Agent action')->assertDontSee('Broker own action');
        $this->get(route('broker.audit.index', ['actor' => $outsider->id]))->assertDontSee('Outsider action');

        foreach (range(1, 30) as $i) {
            $log($agent, 'Updated', "entry {$i}");
        }

        $this->get(route('broker.audit.index'))->assertViewHas('logs', fn ($logs) => $logs->count() === 25 && $logs->total() === 32);
    }
}
