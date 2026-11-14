<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Downloading a backup as .sql and importing it back. Importing replaces every
 * record, so these tests also prove that bad files change nothing.
 */
class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        Storage::fake('supabase');

        $this->admin = User::factory()->create(['name' => 'Admin One', 'role' => 'admin', 'is_active' => true, 'is_approved' => true, 'password' => 'admin-password']);
        $this->actingAs($this->admin);
    }

    private function backupSql(): array
    {
        $name = app(BackupService::class)->create();
        $response = $this->post(route('admin.backups.download-sql', $name), ['password' => 'admin-password']);
        $response->assertOk();

        return [$name, $response->streamedContent()];
    }

    private function import(string $sql, array $overrides = [])
    {
        return $this->post(route('admin.backups.import'), array_merge([
            'backup_file' => UploadedFile::fake()->createWithContent('estateflow-backup.sql', $sql),
            'confirm_replace' => '1',
            'password' => 'admin-password',
        ], $overrides));
    }

    private function listing(string $name): Property
    {
        return Property::create(['broker_id' => $this->admin->id, 'name' => $name, 'slug' => str($name)->slug(), 'type' => 'Lot Only', 'status' => 'available']);
    }

    public function test_a_backup_downloads_as_a_signed_sql_file(): void
    {
        $this->listing('Vista Homes');
        [$name, $sql] = $this->backupSql();

        $this->assertStringStartsWith('-- EstateFlow database backup', $sql);
        $this->assertStringContainsString('INSERT INTO "properties"', $sql);
        $this->assertStringContainsString("'Vista Homes'", $sql);
        $this->assertMatchesRegularExpression('/^-- EstateFlow-Signature: [0-9a-f]{64}$/m', $sql);

        $this->post(route('admin.backups.download-sql', $name), ['password' => 'admin-password'])
            ->assertHeader('content-disposition', 'attachment; filename='.str_replace('.zip', '.sql', $name));
    }

    public function test_the_sql_download_needs_the_admin_password(): void
    {
        $name = app(BackupService::class)->create();

        $this->post(route('admin.backups.download-sql', $name), ['password' => 'wrong'])->assertSessionHasErrors('password');
    }

    public function test_importing_puts_every_record_back_the_way_it_was(): void
    {
        $kept = $this->listing('Vista Homes');
        $removedLater = $this->listing('Green Fields');
        [, $sql] = $this->backupSql();

        $removedLater->forceDelete();
        $kept->update(['name' => 'Renamed After Backup']);
        $this->listing('Added After Backup');
        User::factory()->create(['email' => 'new@example.com']);

        $this->import($sql)->assertRedirect(route('auth.login'))->assertSessionHas('success');
        $this->assertGuest();

        $this->assertSame(['Green Fields', 'Vista Homes'], Property::orderBy('name')->pluck('name')->all());
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
        $this->assertSame($kept->id, Property::where('name', 'Vista Homes')->value('id'));
    }

    public function test_a_safety_backup_is_made_before_importing(): void
    {
        [$original, $sql] = $this->backupSql();
        $this->listing('Added After Backup');

        $this->import($sql)->assertSessionHas('success');

        $backups = app(BackupService::class)->all()->pluck('name');
        $this->assertCount(2, $backups);
        $safety = $backups->reject(fn ($name) => $name === $original)->first();
        $this->assertStringContainsString($safety, session('success'));
    }

    public function test_an_edited_file_is_refused_and_nothing_changes(): void
    {
        $this->listing('Vista Homes');
        [, $sql] = $this->backupSql();
        $this->listing('Added After Backup');

        $tampered = str_replace("'Vista Homes'", "'Hacked Homes'", $sql);
        $this->import($tampered)->assertSessionHasErrors('backup');

        $this->assertDatabaseHas('properties', ['name' => 'Added After Backup']);
        $this->assertDatabaseMissing('properties', ['name' => 'Hacked Homes']);
        $this->assertCount(1, app(BackupService::class)->all(), 'The safety backup is removed when the file is refused');
    }

    public function test_an_ordinary_sql_file_is_refused(): void
    {
        $this->listing('Vista Homes');

        $this->import("DROP TABLE properties;\n")->assertSessionHasErrors('backup');
        $this->import("-- EstateFlow database backup\nDELETE FROM properties;\n")->assertSessionHasErrors('backup');

        $this->assertDatabaseHas('properties', ['name' => 'Vista Homes']);
    }

    public function test_a_signed_file_that_fails_to_load_changes_nothing(): void
    {
        $this->listing('Vista Homes');
        [, $sql] = $this->backupSql();

        // Correctly signed, but one row points at a column that does not exist.
        $body = substr($sql, 0, strrpos(rtrim($sql, "\n"), "\n") + 1);
        $broken = str_replace("\nCOMMIT;\n", "\nINSERT INTO \"properties\" (\"no_such_column\") VALUES (1);\nCOMMIT;\n", $body);
        $broken .= BackupService::SIGNATURE_PREFIX.hash_hmac('sha256', $broken, (string) config('app.key'))."\n";

        $this->listing('Added After Backup');
        $this->import($broken)->assertSessionHasErrors('backup');

        $this->assertDatabaseHas('properties', ['name' => 'Added After Backup']);
        $this->assertDatabaseHas('properties', ['name' => 'Vista Homes']);
    }

    public function test_import_needs_the_password_the_confirmation_and_a_sql_file(): void
    {
        [, $sql] = $this->backupSql();

        $this->import($sql, ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->import($sql, ['confirm_replace' => null])->assertSessionHasErrors('confirm_replace');
        $this->import($sql, ['backup_file' => UploadedFile::fake()->create('backup.txt', 10)])->assertSessionHasErrors('backup_file');
        $this->assertCount(1, app(BackupService::class)->all());
    }

    public function test_an_admin_missing_from_the_imported_data_is_signed_out(): void
    {
        [, $sql] = $this->backupSql();
        $newAdmin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true, 'password' => 'admin-password']);

        $this->actingAs($newAdmin)->import($sql)->assertRedirect(route('auth.login'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $newAdmin->id]);
    }

    public function test_only_admins_can_import_or_download_sql(): void
    {
        $name = app(BackupService::class)->create();
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true, 'password' => 'password123']);

        $this->actingAs($broker)->post(route('admin.backups.download-sql', $name), ['password' => 'password123'])->assertForbidden();
        $this->post(route('admin.backups.import'), ['password' => 'password123', 'confirm_replace' => '1'])->assertForbidden();
    }

    public function test_the_backups_page_offers_sql_download_and_restore(): void
    {
        app(BackupService::class)->create();

        $this->get(route('admin.backups'))
            ->assertOk()
            ->assertSee('Restore from a backup')
            ->assertSee('Download .sql')
            ->assertSee(route('admin.backups.import'), false);
    }
}
