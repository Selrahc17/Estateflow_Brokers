<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyDeveloperTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('supabase');
        config(['filesystems.disks.supabase.endpoint' => 'https://project.supabase.co/storage/v1/s3', 'filesystems.disks.supabase.bucket' => 'properties']);
        $this->agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
    }

    private function listing(array $overrides = []): array
    {
        return array_merge(['name' => 'Vista Homes', 'type' => 'House and Lot', 'status' => 'available'], $overrides);
    }

    public function test_an_agent_can_add_the_developer_name_and_logo(): void
    {
        $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->listing([
            'developer' => 'Sample Land Inc.',
            'developer_logo' => UploadedFile::fake()->create('logo.png', 20, 'image/png'),
        ]))->assertSessionHasNoErrors();

        $property = Property::firstOrFail();
        $this->assertSame('Sample Land Inc.', $property->developer);
        $this->assertStringStartsWith('https://project.supabase.co/storage/v1/object/public/properties/developer-logos/', $property->developer_logo);
        Storage::disk('supabase')->assertExists(str_replace('https://project.supabase.co/storage/v1/object/public/properties/', '', $property->developer_logo));
    }

    public function test_the_developer_is_optional(): void
    {
        $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->listing())->assertSessionHasNoErrors();

        $property = Property::firstOrFail();
        $this->assertNull($property->developer);
        $this->assertNull($property->developer_logo);
        $this->get(route('agent.properties.show', $property))->assertSee('Individually owned');
    }

    public function test_only_safe_image_types_are_accepted_as_logos(): void
    {
        foreach (['logo.svg' => 'image/svg+xml', 'logo.pdf' => 'application/pdf'] as $name => $mime) {
            $this->actingAs($this->agent)->post(route('agent.properties.store'), $this->listing([
                'developer' => 'Sample Land Inc.',
                'developer_logo' => UploadedFile::fake()->create($name, 20, $mime),
            ]))->assertSessionHasErrors('developer_logo');
        }

        $this->post(route('agent.properties.store'), $this->listing([
            'developer_logo' => UploadedFile::fake()->create('huge.png', 3000, 'image/png'),
        ]))->assertSessionHasErrors('developer_logo');

        $this->assertSame(0, Property::count());
    }

    public function test_buyers_see_the_developer_on_the_listing(): void
    {
        $property = Property::create(['broker_id' => $this->agent->id, 'name' => 'Vista Homes', 'slug' => 'vista-homes', 'type' => 'House and Lot', 'status' => 'available', 'developer' => 'Sample Land Inc.', 'developer_logo' => 'https://cdn.example.com/logo.png']);

        $this->get(route('client.property.show', $property->slug))
            ->assertOk()
            ->assertSee('Sample Land Inc.')
            ->assertSee('https://cdn.example.com/logo.png', false);
        $this->get(route('client.properties'))->assertSee('by Sample Land Inc.');
    }

    public function test_the_logo_can_be_replaced_or_removed_and_kept_otherwise(): void
    {
        $property = Property::create(['broker_id' => $this->agent->id, 'name' => 'Vista Homes', 'slug' => 'vista-homes', 'type' => 'House and Lot', 'status' => 'available', 'developer' => 'Old Name', 'developer_logo' => 'https://cdn.example.com/old.png']);
        $update = fn (array $extra) => $this->actingAs($this->agent)->put(route('agent.properties.update', $property), $this->listing($extra))->assertSessionHasNoErrors();

        $update(['developer' => 'New Name']);
        $this->assertSame('https://cdn.example.com/old.png', $property->fresh()->developer_logo);
        $this->assertSame('New Name', $property->fresh()->developer);

        $update(['developer_logo' => UploadedFile::fake()->create('new.webp', 20, 'image/webp')]);
        $this->assertStringContainsString('developer-logos/', $property->fresh()->developer_logo);

        $update(['remove_developer_logo' => '1']);
        $this->assertNull($property->fresh()->developer_logo);
    }

    public function test_the_edit_form_shows_the_current_developer(): void
    {
        $property = Property::create(['broker_id' => $this->agent->id, 'name' => 'Vista Homes', 'slug' => 'vista-homes', 'type' => 'House and Lot', 'status' => 'available', 'developer' => 'Sample Land Inc.', 'developer_logo' => 'https://cdn.example.com/logo.png']);

        $this->actingAs($this->agent)->get(route('agent.properties.edit', $property))
            ->assertSee('value="Sample Land Inc."', false)
            ->assertSee('Remove current logo');
        $this->get(route('agent.properties.create'))->assertSee('Developer Logo')->assertDontSee('Remove current logo');
    }
}
