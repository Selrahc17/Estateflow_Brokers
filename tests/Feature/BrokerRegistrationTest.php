<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class BrokerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function brokerPayload(array $overrides = []): array
    {
        return array_merge([
            'role' => 'broker',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.com',
            'phone' => '09171234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'privacy_consent' => '1',
            'license_number' => '0012345',
            'prc_id_expires_at' => now()->addYear()->toDateString(),
            'dhsud_registration_number' => 'DHSUD-2026-001',
            'dhsud_registration_expires_at' => now()->addYear()->toDateString(),
            'tin' => '123-456-789-000',
            'prc_id_file' => UploadedFile::fake()->create('prc-id.pdf', 100, 'application/pdf'),
        ], $overrides);
    }

    private function pendingBroker(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => false,
            'phone' => '09171234567',
            'license_number' => '0012345',
            'prc_id_expires_at' => now()->addYear(),
            'dhsud_registration_number' => 'DHSUD-2026-001',
            'dhsud_registration_expires_at' => now()->addYear(),
            'tin' => '123-456-789-000',
        ], $overrides));
    }

    private function addDocuments(User $broker, string $status): void
    {
        foreach (array_keys(Document::BROKER_COMPLIANCE_TYPES) as $type) {
            Document::create([
                'broker_id' => $broker->id,
                'name' => $type,
                'type' => $type,
                'file_path' => "broker-compliance/{$broker->id}/{$type}.pdf",
                'status' => $status,
            ]);
        }
    }

    public function test_broker_can_register_and_is_sent_to_the_application_page(): void
    {
        $this->post(route('auth.register.post'), $this->brokerPayload())
            ->assertRedirect(route('broker.application.show'));

        $broker = User::where('email', 'maria@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($broker);
        $this->assertSame('broker', $broker->role);
        $this->assertFalse((bool) $broker->is_approved);
        $this->assertSame('0012345', $broker->license_number);
        $this->assertSame('123-456-789-000', $broker->tin);
        $this->assertNotNull($broker->privacy_consented_at);

        $document = $broker->complianceDocuments()->firstOrFail();
        $this->assertSame('prc_id', $document->type);
        $this->assertSame('pending', $document->status);
        Storage::disk('local')->assertExists($document->file_path);

        $this->get(route('broker.application.show'))->assertOk()->assertSee('0 of 5 documents verified');
    }

    public function test_register_page_offers_client_and_broker_but_not_agent(): void
    {
        $this->get(route('auth.register'))
            ->assertOk()
            ->assertSee('Broker credentials')
            ->assertSee('Data Privacy Act of 2012')
            ->assertSee('Show password')
            ->assertDontSee("role = 'agent'", false)
            ->assertDontSee('check your email to verify');
    }

    public function test_login_page_renders_the_show_password_control(): void
    {
        $this->get(route('auth.login'))->assertOk()->assertSee('Show password')->assertDontSee('togglePassword');
    }

    public function test_login_and_register_share_the_sign_in_switch(): void
    {
        foreach (['auth.login', 'auth.register'] as $page) {
            $this->get(route($page))
                ->assertOk()
                ->assertSee('href="'.route('auth.login').'"', false)
                ->assertSee('href="'.route('auth.register').'"', false)
                ->assertSee('Your Real Estate');
        }
    }

    private function registerPageAfterError(string $field, array $oldInput): \Illuminate\Testing\TestResponse
    {
        return $this->withSession([
            'errors' => (new ViewErrorBag)->put('default', new MessageBag([$field => ['Invalid.']])),
            '_old_input' => $oldInput,
        ])->get(route('auth.register'));
    }

    public function test_register_form_reopens_the_step_that_has_the_error(): void
    {
        $this->registerPageAfterError('tin', ['role' => 'broker', 'first_name' => 'Maria'])
            ->assertSee('step: 2', false)
            ->assertSee('value="Maria"', false);

        $this->registerPageAfterError('email', ['role' => 'broker', 'tin' => '123-456-789'])
            ->assertSee('step: 1', false)
            ->assertSee('value="123-456-789"', false);
    }

    public function test_broker_registration_requires_credentials_and_prc_id(): void
    {
        $this->from(route('auth.register'))
            ->post(route('auth.register.post'), [
                'role' => 'broker',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'privacy_consent' => '1',
            ])
            ->assertSessionHasErrors([
                'phone', 'license_number', 'prc_id_expires_at', 'dhsud_registration_number',
                'dhsud_registration_expires_at', 'tin', 'prc_id_file',
            ]);

        $this->assertDatabaseMissing('users', ['email' => 'maria@example.com']);
    }

    public function test_broker_registration_rejects_expired_credentials_and_bad_files(): void
    {
        $this->from(route('auth.register'))
            ->post(route('auth.register.post'), $this->brokerPayload([
                'prc_id_expires_at' => now()->subDay()->toDateString(),
                'tin' => 'not-a-tin',
                'prc_id_file' => UploadedFile::fake()->create('prc-id.exe', 10, 'application/octet-stream'),
            ]))
            ->assertSessionHasErrors(['prc_id_expires_at', 'tin', 'prc_id_file']);
    }

    public function test_agents_cannot_register_themselves(): void
    {
        $this->from(route('auth.register'))
            ->post(route('auth.register.post'), $this->brokerPayload(['role' => 'agent']))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'maria@example.com']);
    }

    public function test_everyone_must_consent_to_data_privacy(): void
    {
        $this->from(route('auth.register'))
            ->post(route('auth.register.post'), [
                'role' => 'client',
                'first_name' => 'Juan',
                'last_name' => 'Cruz',
                'email' => 'juan@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('privacy_consent');

        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
    }

    public function test_client_registration_records_consent_and_is_active_immediately(): void
    {
        $this->post(route('auth.register.post'), [
            'role' => 'client',
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'privacy_consent' => '1',
        ])->assertRedirect(route('client.properties'));

        $client = User::where('email', 'juan@example.com')->firstOrFail();

        $this->assertTrue((bool) $client->is_approved);
        $this->assertNotNull($client->privacy_consented_at);
        $this->assertDatabaseHas('clients', ['user_id' => $client->id]);
    }

    public function test_pending_broker_can_sign_in_but_only_reach_the_application_page(): void
    {
        $broker = $this->pendingBroker(['password' => 'password123']);

        $this->post(route('auth.login.post'), ['email' => $broker->email, 'password' => 'password123'])
            ->assertRedirect(route('broker.application.show'));

        $this->get(route('broker.dashboard'))->assertRedirect(route('broker.application.show'));
        $this->get(route('broker.application.show'))->assertOk();
    }

    public function test_approved_broker_is_sent_away_from_the_application_page(): void
    {
        $broker = $this->pendingBroker(['is_approved' => true]);

        $this->actingAs($broker)
            ->get(route('broker.application.show'))
            ->assertRedirect(route('broker.dashboard'));
    }

    public function test_other_roles_cannot_open_the_application_page(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $this->actingAs($client)->get(route('broker.application.show'))->assertForbidden();
    }

    public function test_broker_upload_goes_to_private_storage_and_replaces_a_rejected_copy(): void
    {
        $broker = $this->pendingBroker();
        $this->actingAs($broker)->post(route('broker.application.documents.store'), [
            'type' => 'bond',
            'file' => UploadedFile::fake()->create('bond.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $first = $broker->complianceDocuments()->where('type', 'bond')->firstOrFail();
        $firstPath = $first->file_path;
        Storage::disk('local')->assertExists($firstPath);
        $first->update(['status' => 'rejected', 'notes' => 'Blurry copy']);

        $this->post(route('broker.application.documents.store'), [
            'type' => 'bond',
            'file' => UploadedFile::fake()->create('bond-2.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $broker->complianceDocuments()->where('type', 'bond')->count());
        $replacement = $first->fresh();
        $this->assertSame('pending', $replacement->status);
        $this->assertNull($replacement->notes);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($replacement->file_path);
    }

    public function test_verified_document_cannot_be_replaced(): void
    {
        $broker = $this->pendingBroker();
        $this->addDocuments($broker, 'verified');
        $path = $broker->complianceDocuments()->where('type', 'bond')->value('file_path');

        $this->actingAs($broker)->post(route('broker.application.documents.store'), [
            'type' => 'bond',
            'file' => UploadedFile::fake()->create('bond.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('file');

        $this->assertSame($path, $broker->complianceDocuments()->where('type', 'bond')->value('file_path'));
    }

    public function test_broker_cannot_upload_an_unknown_type_or_open_another_brokers_file(): void
    {
        $broker = $this->pendingBroker();
        $other = $this->pendingBroker(['license_number' => '999']);
        $this->addDocuments($other, 'pending');
        $otherDocument = $other->complianceDocuments()->firstOrFail();
        Storage::disk('local')->put($otherDocument->file_path, 'private');

        $this->actingAs($broker)->post(route('broker.application.documents.store'), [
            'type' => 'contract',
            'file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('type');

        $this->get(route('broker.application.documents.download', $otherDocument))->assertNotFound();
    }

    public function test_admins_are_notified_when_all_documents_are_uploaded(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $broker = $this->pendingBroker();
        $this->actingAs($broker);

        foreach (array_keys(Document::BROKER_COMPLIANCE_TYPES) as $type) {
            $this->assertSame(0, AppNotification::where('user_id', $admin->id)->count());

            $this->post(route('broker.application.documents.store'), [
                'type' => $type,
                'file' => UploadedFile::fake()->create("{$type}.pdf", 10, 'application/pdf'),
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(1, AppNotification::where('user_id', $admin->id)->count());
    }

    public function test_admin_cannot_approve_until_every_document_is_verified(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $broker = $this->pendingBroker();
        $this->addDocuments($broker, 'pending');

        $this->actingAs($admin)
            ->from(route('admin.brokers.show', $broker))
            ->patch(route('admin.brokers.approve', $broker))
            ->assertSessionHasErrors('approval');

        $this->assertFalse((bool) $broker->fresh()->is_approved);

        $broker->complianceDocuments()->update(['status' => 'verified']);

        $this->patch(route('admin.brokers.approve', $broker))->assertSessionHasNoErrors();
        $this->assertTrue((bool) $broker->fresh()->is_approved);
    }

    public function test_admin_cannot_approve_expired_credentials(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $broker = $this->pendingBroker(['prc_id_expires_at' => now()->subDay()]);
        $this->addDocuments($broker, 'verified');

        $this->actingAs($admin)
            ->from(route('admin.brokers.show', $broker))
            ->patch(route('admin.brokers.approve', $broker))
            ->assertSessionHasErrors('approval');

        $this->assertFalse((bool) $broker->fresh()->is_approved);
    }

    public function test_admin_can_review_and_download_broker_documents(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $broker = $this->pendingBroker();
        $this->addDocuments($broker, 'pending');
        $document = $broker->complianceDocuments()->firstOrFail();
        Storage::disk('local')->put($document->file_path, 'private');

        $this->actingAs($admin)
            ->get(route('admin.brokers.show', $broker))
            ->assertOk()
            ->assertSee('Licensing and Compliance')
            ->assertSee($broker->tin);

        $this->get(route('admin.documents.download', $document))->assertOk();
    }

    public function test_pages_for_guests_do_not_expose_the_private_file(): void
    {
        $broker = $this->pendingBroker();
        $this->addDocuments($broker, 'pending');
        $document = $broker->complianceDocuments()->firstOrFail();

        $this->get(route('admin.documents.download', $document))->assertRedirect(route('auth.login'));
        $this->get(route('broker.application.documents.download', $document))->assertRedirect(route('auth.login'));
    }
}
