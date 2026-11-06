<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyImageGalleryTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('supabase');

        $this->agent = User::factory()->create([
            'role' => 'agent',
            'is_active' => true,
            'is_approved' => true,
        ]);
    }

    public function test_an_agent_can_create_a_property_with_ten_additional_images(): void
    {
        $this->actingAs($this->agent)
            ->post(route('agent.properties.store'), $this->propertyData(['images' => $this->photos(10)]))
            ->assertRedirect(route('agent.properties.index'))
            ->assertSessionHasNoErrors();

        $this->assertCount(10, Property::firstOrFail()->images);
        $this->assertCount(10, Storage::disk('supabase')->allFiles());
    }

    public function test_more_than_ten_additional_images_are_rejected(): void
    {
        $this->actingAs($this->agent)
            ->post(route('agent.properties.store'), $this->propertyData(['images' => $this->photos(11)]))
            ->assertSessionHasErrors('images');

        $this->assertSame(0, Property::count());
        $this->assertSame([], Storage::disk('supabase')->allFiles());
    }

    public function test_editing_without_photo_changes_keeps_the_gallery(): void
    {
        $property = $this->property(['https://example.com/one.jpg', 'https://example.com/two.jpg']);

        $this->actingAs($this->agent)
            ->put(route('agent.properties.update', $property), $this->propertyData())
            ->assertSessionHasNoErrors();

        $this->assertSame(['https://example.com/one.jpg', 'https://example.com/two.jpg'], $property->fresh()->images);
    }

    public function test_new_images_are_added_to_the_existing_gallery(): void
    {
        $property = $this->property(['https://example.com/one.jpg']);

        $this->actingAs($this->agent)
            ->put(route('agent.properties.update', $property), $this->propertyData(['images' => $this->photos(2)]))
            ->assertSessionHasNoErrors();

        $images = $property->fresh()->images;

        $this->assertCount(3, $images);
        $this->assertSame('https://example.com/one.jpg', $images[0]);
    }

    public function test_selected_images_can_be_removed(): void
    {
        $property = $this->property(['https://example.com/one.jpg', 'https://example.com/two.jpg']);

        $this->actingAs($this->agent)
            ->put(route('agent.properties.update', $property), $this->propertyData([
                'remove_images' => ['https://example.com/one.jpg'],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['https://example.com/two.jpg'], $property->fresh()->images);
    }

    public function test_the_limit_counts_images_already_in_the_gallery(): void
    {
        $existing = array_map(fn (int $number) => "https://example.com/{$number}.jpg", range(1, 9));
        $property = $this->property($existing);

        $this->actingAs($this->agent)
            ->put(route('agent.properties.update', $property), $this->propertyData(['images' => $this->photos(2)]))
            ->assertSessionHasErrors('images');

        $this->assertSame($existing, $property->fresh()->images);
    }

    public function test_removing_an_image_frees_a_slot_in_the_same_update(): void
    {
        $existing = array_map(fn (int $number) => "https://example.com/{$number}.jpg", range(1, 10));
        $property = $this->property($existing);

        $this->actingAs($this->agent)
            ->put(route('agent.properties.update', $property), $this->propertyData([
                'remove_images' => [$existing[0]],
                'images' => $this->photos(1),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertCount(10, $property->fresh()->images);
        $this->assertNotContains($existing[0], $property->fresh()->images);
    }

    public function test_the_gallery_shows_every_photo_without_stretching_them(): void
    {
        $property = $this->property(['https://example.com/one.jpg', 'https://example.com/two.jpg']);
        $property->update(['featured_image' => 'https://example.com/cover.jpg']);

        $this->get(route('client.property.show', $property->slug))
            ->assertOk()
            ->assertSeeInOrder([
                'https://example.com/cover.jpg',
                'https://example.com/one.jpg',
                'https://example.com/two.jpg',
            ])
            ->assertSee('1 / 3')
            ->assertSee('Next photo')
            ->assertSee('object-contain');
    }

    public function test_a_property_without_photos_does_not_show_a_stock_image(): void
    {
        $property = $this->property([]);

        $this->get(route('client.property.show', $property->slug))
            ->assertOk()
            ->assertSee('No photos yet')
            ->assertDontSee('unsplash')
            ->assertDontSee('Next photo');
    }

    public function test_the_listing_card_falls_back_to_the_first_gallery_image(): void
    {
        $this->property(['https://example.com/one.jpg']);

        $this->get(route('client.properties'))
            ->assertOk()
            ->assertSee('https://example.com/one.jpg');
    }

    private function property(array $images): Property
    {
        return Property::create([
            'broker_id' => $this->agent->id,
            'name' => 'Gallery House',
            'slug' => 'gallery-house',
            'type' => 'House and Lot',
            'status' => 'available',
            'images' => $images,
        ]);
    }

    private function propertyData(array $overrides = []): array
    {
        return [
            'name' => 'Gallery House',
            'type' => 'House and Lot',
            'status' => 'available',
            ...$overrides,
        ];
    }

    private function photos(int $count): array
    {
        // A real 1x1 PNG, so this works without the GD extension.
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');

        return array_map(
            fn (int $number) => UploadedFile::fake()->createWithContent("photo-{$number}.png", $png),
            range(1, $count)
        );
    }
}
