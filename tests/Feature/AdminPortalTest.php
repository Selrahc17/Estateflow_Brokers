<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\Feedback;
use App\Models\Lot;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        Storage::fake('supabase');

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true, 'password' => 'admin-password']);
        $this->actingAs($this->admin);
    }

    private function agent(): User
    {
        return User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
    }

    private function property(?User $agent = null, array $overrides = []): Property
    {
        return Property::create(array_merge([
            'broker_id' => ($agent ?? $this->agent())->id,
            'name' => 'Vista Homes',
            'slug' => fake()->unique()->slug(),
            'address' => 'Quezon City',
            'type' => 'House and Lot',
            'status' => 'available',
        ], $overrides));
    }

    private function reservation(string $status = 'pending', string $lotStatus = 'reserved'): Reservation
    {
        $agent = $this->agent();
        $user = User::factory()->create(['role' => 'client']);
        $client = Client::create(['user_id' => $user->id, 'broker_id' => $agent->id, 'first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => fake()->unique()->safeEmail(), 'password' => 'secret', 'status' => 'active']);
        $lot = Lot::create(['property_id' => $this->property($agent)->id, 'lot_number' => 'A1', 'status' => $lotStatus]);

        return Reservation::create(['client_id' => $client->id, 'broker_id' => $agent->id, 'lot_id' => $lot->id, 'reservation_code' => 'RES-'.fake()->unique()->numerify('#####'), 'status' => $status, 'total_price' => 1000000]);
    }

    private function pendingBroker(): User
    {
        return User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => false]);
    }

    private function backup(): string
    {
        return app(BackupService::class)->create();
    }

    public function test_every_admin_page_renders(): void
    {
        $reservation = $this->reservation();
        Payment::create(['reservation_id' => $reservation->id, 'client_id' => $reservation->client_id, 'broker_id' => $reservation->broker_id, 'payment_code' => 'PAY-1', 'amount' => 100, 'payment_type' => 'down_payment', 'status' => 'pending']);
        $broker = $this->pendingBroker();
        $property = $this->property();

        foreach ([
            route('admin.dashboard'), route('admin.reports'), route('admin.users'), route('admin.brokers'),
            route('admin.brokers.show', $broker), route('admin.properties'), route('admin.properties.show', $property),
            route('admin.reservations'), route('admin.reservations.show', $reservation), route('admin.documents'),
            route('admin.feedback'), route('admin.contact-messages'), route('admin.notifications'),
            route('admin.audit'), route('admin.backups'), route('admin.settings'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_sidebar_links_feedback_contact_messages_and_backups_with_counts(): void
    {
        Feedback::create(['user_id' => $this->admin->id, 'type' => 'general', 'subject' => 'Hi', 'message' => 'Hello', 'status' => 'pending']);
        ContactMessage::create(['first_name' => 'A', 'last_name' => 'B', 'email' => 'a@example.com', 'subject' => 'S', 'message' => 'M', 'status' => 'new']);

        $this->get(route('admin.dashboard'))
            ->assertSee(route('admin.feedback'), false)
            ->assertSee(route('admin.contact-messages'), false)
            ->assertSee(route('admin.backups'), false)
            ->assertSeeInOrder(['Feedback</span>', '>1</span>', 'Contact Messages</span>', '>1</span>'], false);
    }

    public function test_property_page_handles_duplicates_without_database_specific_sql(): void
    {
        $this->property(null, ['name' => 'Twin', 'city' => 'Manila', 'province' => 'Metro Manila']);
        $this->property(null, ['name' => 'Twin', 'city' => 'Manila', 'province' => 'Metro Manila']);
        $this->property(null, ['name' => 'Pin A', 'latitude' => 14.5, 'longitude' => 121.0]);
        $this->property(null, ['name' => 'Pin B', 'latitude' => 14.5, 'longitude' => 121.0]);
        $this->property(null, ['name' => 'Unique']);

        $this->get(route('admin.properties'))
            ->assertOk()
            ->assertSee('Potential Duplicates Found (2)')
            ->assertViewHas('duplicateGroups', fn (array $groups) => count($groups) === 2);
    }

    public function test_hidden_listing_leaves_the_public_site_and_the_agent_cannot_undo_it(): void
    {
        $agent = $this->agent();
        $property = $this->property($agent, ['name' => 'Hide Me', 'slug' => 'hide-me']);

        $this->patch(route('admin.properties.status', $property), ['status' => 'hidden'])->assertSessionHasNoErrors();

        $this->assertSame(1, AppNotification::where('user_id', $agent->id)->where('type', 'property_moderated')->count());

        auth()->logout();
        $this->get(route('client.property.show', 'hide-me'))->assertNotFound();

        $this->actingAs($agent)->put(route('agent.properties.update', $property), [
            'name' => 'Hide Me', 'type' => 'House and Lot', 'status' => 'available',
        ])->assertSessionHasNoErrors();

        $this->assertSame('hidden', $property->fresh()->status);
    }

    public function test_admin_can_keep_sold_and_coming_soon_statuses(): void
    {
        $property = $this->property(null, ['status' => 'sold']);

        $this->patch(route('admin.properties.status', $property), ['status' => 'coming_soon'])->assertSessionHasNoErrors();
        $this->assertSame('coming_soon', $property->fresh()->status);
        $this->get(route('admin.properties'))->assertSee('<option value="coming_soon" selected>', false);
    }

    public function test_confirming_a_reservation_notifies_the_agent_and_client(): void
    {
        $reservation = $this->reservation();

        $this->from(route('admin.reservations.show', $reservation))
            ->patch(route('admin.reservations.status', $reservation), ['status' => 'confirmed'])
            ->assertRedirect(route('admin.reservations.show', $reservation));

        $this->assertSame('confirmed', $reservation->fresh()->status);
        $this->assertSame(2, AppNotification::where('type', 'reservation_status')->count());
    }

    public function test_completing_and_cancelling_update_the_lot(): void
    {
        $completed = $this->reservation('confirmed');
        $cancelled = $this->reservation('confirmed');

        $this->patch(route('admin.reservations.status', $completed), ['status' => 'completed']);
        $this->patch(route('admin.reservations.status', $cancelled), ['status' => 'cancelled']);

        $this->assertSame('sold', $completed->lot->fresh()->status);
        $this->assertSame('available', $cancelled->lot->fresh()->status);
    }

    public function test_reservation_rules_protect_sold_and_retaken_lots(): void
    {
        $sold = $this->reservation('completed', 'sold');
        $this->from(route('admin.reservations.show', $sold))
            ->patch(route('admin.reservations.status', $sold), ['status' => 'cancelled'])
            ->assertSessionHasErrors('status');
        $this->assertSame('sold', $sold->lot->fresh()->status);

        $retaken = $this->reservation('cancelled', 'reserved');
        $this->from(route('admin.reservations.show', $retaken))
            ->patch(route('admin.reservations.status', $retaken), ['status' => 'confirmed'])
            ->assertSessionHasErrors('status');
        $this->assertSame('cancelled', $retaken->fresh()->status);
    }

    public function test_reservation_search_works(): void
    {
        $reservation = $this->reservation();
        $other = $this->reservation();
        $reservation->client->update(['first_name' => 'Findme']);

        $this->get(route('admin.reservations', ['search' => 'Findme']))
            ->assertSee($reservation->reservation_code)
            ->assertDontSee($other->reservation_code);
    }

    public function test_document_decisions_keep_the_uploader_and_notify_the_owner(): void
    {
        $broker = $this->pendingBroker();
        $document = Document::create(['broker_id' => $broker->id, 'uploaded_by' => $broker->id, 'name' => 'Bond', 'type' => 'bond', 'file_path' => 'x.pdf', 'status' => 'pending']);

        $this->post(route('admin.documents.verify', $document));
        $this->assertSame('verified', $document->fresh()->status);
        $this->assertSame($broker->id, $document->fresh()->uploaded_by);

        $this->post(route('admin.documents.reject', $document), ['notes' => 'Blurry copy']);
        $this->assertSame('rejected', $document->fresh()->status);
        $this->assertSame('Blurry copy', $document->fresh()->notes);

        $this->post(route('admin.documents.request-more', $document), ['notes' => 'Back page too']);
        $this->assertSame('needs_more', $document->fresh()->status);

        $this->assertSame(3, AppNotification::where('user_id', $broker->id)->where('type', 'document_review')->count());
    }

    public function test_rejecting_a_document_needs_a_reason(): void
    {
        $document = Document::create(['broker_id' => $this->pendingBroker()->id, 'name' => 'Bond', 'type' => 'bond', 'file_path' => 'x.pdf', 'status' => 'pending']);

        $this->from(route('admin.documents'))->post(route('admin.documents.reject', $document))->assertSessionHasErrors('notes');
        $this->assertSame('pending', $document->fresh()->status);
    }

    public function test_broker_is_told_about_approval_and_rejection(): void
    {
        $broker = $this->pendingBroker();
        $broker->update([
            'phone' => '09171234567', 'license_number' => 'L-1', 'tin' => '123-456-789',
            'prc_id_expires_at' => now()->addYear(), 'dhsud_registration_number' => 'D-1', 'dhsud_registration_expires_at' => now()->addYear(),
        ]);

        foreach (array_keys(Document::BROKER_COMPLIANCE_TYPES) as $type) {
            Document::create(['broker_id' => $broker->id, 'name' => $type, 'type' => $type, 'file_path' => 'x.pdf', 'status' => 'verified']);
        }

        $this->patch(route('admin.brokers.reject', $broker), ['reason' => 'Bond has expired'])->assertSessionHasNoErrors();
        $this->assertFalse((bool) $broker->fresh()->is_approved);

        $this->actingAs($broker->fresh())->get(route('broker.application.show'))->assertSee('Bond has expired');

        $this->actingAs($this->admin)->patch(route('admin.brokers.approve', $broker))->assertSessionHasNoErrors();
        $this->assertTrue((bool) $broker->fresh()->is_approved);
        $this->assertSame(1, AppNotification::where('user_id', $broker->id)->where('type', 'broker_approved')->count());
    }

    public function test_rejecting_a_broker_needs_a_reason(): void
    {
        $broker = $this->pendingBroker();

        $this->from(route('admin.brokers.show', $broker))
            ->patch(route('admin.brokers.reject', $broker))
            ->assertSessionHasErrors('reason');
    }

    public function test_pending_broker_page_has_a_reject_button(): void
    {
        $this->get(route('admin.brokers.show', $this->pendingBroker()))->assertSee('Reject Application');
    }

    public function test_broker_created_from_user_management_still_needs_approval(): void
    {
        $this->post(route('admin.users.store'), [
            'name' => 'New Broker', 'email' => 'new-broker@example.com', 'role' => 'broker',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertFalse((bool) User::where('email', 'new-broker@example.com')->value('is_approved'));
    }

    public function test_feedback_page_shows_real_data_and_resolves(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $feedback = Feedback::create(['user_id' => $client->id, 'type' => 'general', 'subject' => 'Slow replies', 'message' => 'Agents take long', 'rating' => 2, 'status' => 'pending']);

        $this->get(route('admin.feedback'))
            ->assertSee('Slow replies')
            ->assertSee('2.0 ★')
            ->assertDontSee('128');

        $this->post(route('admin.feedback.resolve', $feedback))->assertSessionHasNoErrors();

        $this->assertSame('resolved', $feedback->fresh()->status);
        $this->assertNotNull($feedback->fresh()->resolved_at);
        $this->assertSame(1, AppNotification::where('user_id', $client->id)->where('type', 'feedback_resolved')->count());
        $this->get(route('admin.feedback', ['status' => 'pending']))->assertDontSee('Slow replies');
    }

    public function test_contact_message_can_be_handled(): void
    {
        $message = ContactMessage::create(['first_name' => 'A', 'last_name' => 'B', 'email' => 'a@example.com', 'subject' => 'S', 'message' => 'M', 'status' => 'new']);

        $this->post(route('admin.contact-messages.handle', $message))->assertSessionHasNoErrors();
        $this->assertSame('handled', $message->fresh()->status);
    }

    public function test_admin_actions_are_recorded_with_the_record_they_touched(): void
    {
        $property = $this->property();

        $this->patch(route('admin.properties.status', $property), ['status' => 'flagged']);

        $log = AuditLog::where('actor_id', $this->admin->id)->latest('id')->firstOrFail();
        $this->assertSame('admin', $log->actor_role);
        $this->assertSame('Updated', $log->action);
        $this->assertStringContainsString("admin properties status #{$property->id}", $log->description);
    }

    public function test_client_actions_are_recorded(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $this->actingAs($client)->post(route('client.account.feedback.store'), []);

        $this->assertDatabaseHas('audit_logs', ['actor_id' => $client->id, 'actor_role' => 'client']);
    }

    public function test_sign_in_and_sign_out_are_recorded(): void
    {
        $user = User::factory()->create(['role' => 'client', 'password' => 'password123', 'is_active' => true]);
        auth()->logout();

        $this->post(route('auth.login.post'), ['email' => $user->email, 'password' => 'password123']);
        $this->post(route('auth.logout'));

        $this->assertDatabaseHas('audit_logs', ['actor_id' => $user->id, 'action' => 'Signed in']);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $user->id, 'action' => 'Signed out']);
    }

    public function test_audit_page_lists_and_filters_real_logs(): void
    {
        $log = fn (string $role, string $action, string $description) => AuditLog::create(['actor_id' => $this->admin->id, 'actor_role' => $role, 'method' => 'POST', 'action' => $action, 'description' => $description, 'status_code' => 200]);

        $log('admin', 'Updated', 'admin one');
        $log('agent', 'Deleted', 'agent two');

        $this->get(route('admin.audit'))->assertSee('Admin one')->assertSee('Agent two');
        $this->get(route('admin.audit', ['role' => 'agent']))->assertSee('Agent two')->assertDontSee('Admin one');
        $this->get(route('admin.audit', ['action' => 'Updated']))->assertSee('Admin one')->assertDontSee('Agent two');
        $this->get(route('admin.audit', ['search' => 'two']))->assertSee('Agent two')->assertDontSee('Admin one');
    }

    public function test_backup_contains_every_table_and_stored_file(): void
    {
        $this->property(null, ['name' => 'Backed Up']);
        Storage::disk('local')->put('broker-compliance/1/id.pdf', 'private-id');
        Storage::disk('public')->put('commission-proof/p.jpg', 'proof');
        Storage::disk('supabase')->put('photo.jpg', 'house');
        \DB::table('password_reset_tokens')->insert(['email' => 'x@example.com', 'token' => 'secret-token', 'created_at' => now()]);

        $this->post(route('admin.backups.store'))->assertSessionHasNoErrors()->assertSessionHas('success');

        $name = app(BackupService::class)->all()->first()['name'];
        $zip = new ZipArchive;
        $this->assertTrue($zip->open(Storage::disk('local')->path("backups/{$name}")));

        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $this->assertSame([], $manifest['warnings']);
        $this->assertArrayHasKey('users', $manifest['tables']);
        $this->assertArrayNotHasKey('sessions', $manifest['tables']);
        $this->assertArrayNotHasKey('password_reset_tokens', $manifest['tables']);
        $this->assertSame(User::count(), $manifest['tables']['users']);

        $properties = json_decode($zip->getFromName('database/properties.json'), true);
        $this->assertContains('Backed Up', array_column($properties, 'name'));

        $this->assertSame('private-id', $zip->getFromName('files/private/broker-compliance/1/id.pdf'));
        $this->assertSame('proof', $zip->getFromName('files/public/commission-proof/p.jpg'));
        $this->assertSame('house', $zip->getFromName('files/supabase/photo.jpg'));
        $this->assertFalse($zip->getFromName('database/password_reset_tokens.json'));
        $zip->close();
    }

    public function test_backups_page_lists_a_saved_backup(): void
    {
        $name = $this->backup();

        $this->get(route('admin.backups'))->assertOk()->assertSee($name)->assertSee('KB');
    }

    public function test_database_sql_restores_into_an_empty_database(): void
    {
        // The agent has a lower id than the broker, so plain id order would break the link.
        $agent = User::factory()->create(['name' => "Agent O'Brien", 'role' => 'agent', 'is_active' => true]);
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => false]);
        $agent->update(['broker_id' => $broker->id]);

        $reservation = $this->reservation();
        $property = $this->property($agent, ['description' => "Line one\nIt's \"quoted\" \\ with a backslash; and a semicolon -- not a comment"]);
        Document::create(['client_id' => $reservation->client_id, 'name' => 'ID', 'type' => 'id', 'file_path' => 'a.pdf', 'status' => 'pending']);

        $zip = new ZipArchive;
        $zip->open(Storage::disk('local')->path('backups/'.$this->backup()));
        $sql = $zip->getFromName('database.sql');
        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $zip->close();

        config(['database.connections.restore' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        $this->artisan('migrate', ['--database' => 'restore'])->assertSuccessful();

        $restored = \DB::connection('restore');
        $restored->unprepared($sql);

        foreach ($manifest['tables'] as $table => $rows) {
            if ($table !== 'migrations') {
                $this->assertSame($rows, $restored->table($table)->count(), $table);
            }
        }

        $this->assertSame($broker->id, (int) $restored->table('users')->where('id', $agent->id)->value('broker_id'));
        $this->assertSame("Agent O'Brien", $restored->table('users')->where('id', $agent->id)->value('name'));
        $this->assertSame($property->description, $restored->table('properties')->where('id', $property->id)->value('description'));
        $this->assertSame(0, (int) $restored->table('users')->where('id', $broker->id)->value('is_active'));
    }

    public function test_second_backup_does_not_contain_the_first(): void
    {
        $this->backup();
        $second = $this->backup();

        $zip = new ZipArchive;
        $zip->open(Storage::disk('local')->path("backups/{$second}"));

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $this->assertStringNotContainsString('backups/', $zip->getNameIndex($i));
        }

        $zip->close();
    }

    public function test_download_and_delete_need_the_correct_password_and_are_logged(): void
    {
        $name = $this->backup();

        $this->from(route('admin.backups'))
            ->post(route('admin.backups.download', $name), ['password' => 'wrong'])
            ->assertSessionHasErrors('password');

        $this->post(route('admin.backups.download', $name), ['password' => 'admin-password'])
            ->assertOk()
            ->assertDownload($name);

        $this->assertDatabaseHas('audit_logs', ['actor_id' => $this->admin->id, 'route_name' => 'admin.backups.download', 'status_code' => 200]);

        $this->from(route('admin.backups'))
            ->delete(route('admin.backups.destroy', $name), ['password' => 'wrong'])
            ->assertSessionHasErrors('password');
        Storage::disk('local')->assertExists("backups/{$name}");

        $this->delete(route('admin.backups.destroy', $name), ['password' => 'admin-password']);
        Storage::disk('local')->assertMissing("backups/{$name}");
    }

    public function test_only_admins_can_reach_backups(): void
    {
        $name = $this->backup();
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true, 'password' => 'password123']);

        $this->actingAs($broker)->get(route('admin.backups'))->assertForbidden();
        $this->post(route('admin.backups.store'))->assertForbidden();
        $this->post(route('admin.backups.download', $name), ['password' => 'password123'])->assertForbidden();

        auth()->logout();
        $this->post(route('admin.backups.download', $name), ['password' => 'x'])->assertRedirect(route('auth.login'));
    }

    public function test_backup_names_cannot_reach_other_files(): void
    {
        $this->post('/admin/backups/..%2F.env/download', ['password' => 'admin-password'])->assertNotFound();
        $this->post('/admin/backups/nothing-here.zip/download', ['password' => 'admin-password'])->assertNotFound();
    }
}
