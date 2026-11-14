<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/**
 * Restoring a whole backup zip: records and uploaded files, from the website or,
 * after a total loss, from the terminal.
 */
class BackupArchiveRestoreTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        Storage::fake('supabase');

        $this->admin = User::factory()->create(['name' => 'Admin One', 'email' => 'admin@example.com', 'role' => 'admin', 'is_active' => true, 'is_approved' => true, 'password' => 'admin-password']);
        Property::create(['broker_id' => $this->admin->id, 'name' => 'Vista Homes', 'slug' => 'vista-homes', 'type' => 'Lot Only', 'status' => 'available']);
        Storage::disk('local')->put('documents/7/id.pdf', 'client id');
        Storage::disk('local')->put('commission-proof/receipt.jpg', 'receipt');
        Storage::disk('public')->put('client-avatars/a.png', 'avatar');
        Storage::disk('supabase')->put('photo-1.jpg', 'property photo');
    }

    /** A backup zip copied out of storage, like the file an admin downloads. */
    private function downloadedZip(): string
    {
        $name = app(BackupService::class)->create();
        $copy = tempnam(sys_get_temp_dir(), 'ef').'.zip';
        copy(Storage::disk('local')->path(app(BackupService::class)->path($name)), $copy);

        return $copy;
    }

    private function wipeFiles(): void
    {
        foreach (['local' => ['documents', 'commission-proof'], 'public' => ['client-avatars']] as $disk => $folders) {
            foreach ($folders as $folder) {
                Storage::disk($disk)->deleteDirectory($folder);
            }
        }
        Storage::disk('supabase')->delete('photo-1.jpg');
    }

    private function assertEverythingIsBack(): void
    {
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('properties', ['name' => 'Vista Homes']);
        $this->assertSame('client id', Storage::disk('local')->get('documents/7/id.pdf'));
        $this->assertSame('receipt', Storage::disk('local')->get('commission-proof/receipt.jpg'));
        $this->assertSame('avatar', Storage::disk('public')->get('client-avatars/a.png'));
        $this->assertSame('property photo', Storage::disk('supabase')->get('photo-1.jpg'));
    }

    public function test_the_website_imports_a_zip_with_records_and_files(): void
    {
        $zip = $this->downloadedZip();
        Property::query()->forceDelete();
        $this->wipeFiles();

        $this->actingAs($this->admin)->post(route('admin.backups.import'), [
            'backup_file' => new UploadedFile($zip, 'estateflow-backup.zip', 'application/zip', null, true),
            'confirm_replace' => '1',
            'password' => 'admin-password',
        ])->assertRedirect(route('auth.login'))->assertSessionHas('success', fn ($message) => str_contains($message, '4 uploaded files were put back'));

        $this->assertEverythingIsBack();
    }

    public function test_after_a_total_loss_the_terminal_command_brings_everything_back(): void
    {
        $zip = $this->downloadedZip();

        // Everything gone: every table dropped and every uploaded file deleted.
        Schema::disableForeignKeyConstraints();
        foreach (Schema::getTableListing() as $table) {
            Schema::drop($table);
        }
        Schema::enableForeignKeyConstraints();
        $this->wipeFiles();
        $this->assertFalse(Schema::hasTable('users'));

        $this->artisan('backup:restore', ['file' => $zip, '--force' => true])
            ->expectsOutputToContain('The database has no tables. Creating them...')
            ->expectsOutputToContain('Uploaded files put back: 4')
            ->assertExitCode(0);

        $this->assertEverythingIsBack();
        $this->post(route('auth.login.post'), ['email' => 'admin@example.com', 'password' => 'admin-password']);
        $this->assertAuthenticated();
    }

    public function test_the_command_asks_before_replacing_existing_data(): void
    {
        $zip = $this->downloadedZip();

        $this->artisan('backup:restore', ['file' => $zip])
            ->expectsConfirmation('This replaces ALL current records with the ones in the backup. Continue?', 'no')
            ->expectsOutput('Nothing was changed.')
            ->assertExitCode(1);
    }

    public function test_a_zip_with_a_swapped_file_is_refused_before_anything_changes(): void
    {
        $zip = $this->downloadedZip();
        $archive = new ZipArchive;
        $archive->open($zip);
        $archive->addFromString('files/private/documents/7/id.pdf', 'forged id');
        $archive->close();
        Property::create(['broker_id' => $this->admin->id, 'name' => 'Added Later', 'slug' => 'added-later', 'type' => 'Lot Only', 'status' => 'available']);

        $this->artisan('backup:restore', ['file' => $zip, '--force' => true])
            ->expectsOutputToContain('was changed after it was made')
            ->assertExitCode(1);

        $this->assertDatabaseHas('properties', ['name' => 'Added Later']);
        $this->assertSame('client id', Storage::disk('local')->get('documents/7/id.pdf'));
    }

    public function test_a_signed_zip_cannot_write_outside_estateflow_storage(): void
    {
        $zip = $this->downloadedZip();
        $archive = new ZipArchive;
        $archive->open($zip);
        $manifest = json_decode($archive->getFromName('manifest.json'), true);
        $manifest['file_hashes']['files/private/../../../.env'] = hash('sha256', 'APP_KEY=stolen');
        $archive->addFromString('files/private/../../../.env', 'APP_KEY=stolen');
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $archive->addFromString('manifest.json', $json);
        $archive->addFromString('manifest.sig', hash_hmac('sha256', $json, (string) config('app.key')));
        $archive->close();

        $this->artisan('backup:restore', ['file' => $zip, '--force' => true])
            ->expectsOutputToContain("outside EstateFlow's storage")
            ->assertExitCode(1);
    }

    public function test_a_zip_made_before_signing_is_refused(): void
    {
        $zip = $this->downloadedZip();
        $archive = new ZipArchive;
        $archive->open($zip);
        $archive->deleteName('manifest.sig');
        $archive->close();

        $this->artisan('backup:restore', ['file' => $zip, '--force' => true])
            ->expectsOutputToContain('made before importing was supported')
            ->assertExitCode(1);
    }

    public function test_the_page_explains_zip_restore_and_the_recovery_command(): void
    {
        $this->actingAs($this->admin)->get(route('admin.backups'))
            ->assertSee('Backup file (.zip or .sql, up to 200 MB)')
            ->assertSee('php artisan backup:restore', false);
    }
}
