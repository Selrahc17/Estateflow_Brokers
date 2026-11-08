<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Browsers only offer "Install EstateFlow" when the manifest and its icons are real.
 * The icons were once empty files, which silently blocked installing.
 */
class InstallableAppTest extends TestCase
{
    use RefreshDatabase;

    private function manifest(): array
    {
        return json_decode(file_get_contents(public_path('manifest.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_the_manifest_describes_an_installable_app(): void
    {
        $manifest = $this->manifest();

        $this->assertSame('EstateFlow', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertNotEmpty($manifest['start_url']);
        $this->assertContains('any', array_column($manifest['icons'], 'purpose'));
        $this->assertContains('maskable', array_column($manifest['icons'], 'purpose'));
    }

    public function test_every_icon_is_a_real_png_of_the_stated_size(): void
    {
        foreach ($this->manifest()['icons'] as $icon) {
            $path = public_path(ltrim($icon['src'], '/'));
            $info = @getimagesize($path);

            $this->assertNotFalse($info, "{$icon['src']} is not a readable image");
            $this->assertSame('image/png', $info['mime']);
            $this->assertSame($icon['sizes'], "{$info[0]}x{$info[1]}");
        }

        foreach (['192x192', '512x512'] as $size) {
            $this->assertContains($size, array_column(array_filter($this->manifest()['icons'], fn ($icon) => $icon['purpose'] === 'any'), 'sizes'));
        }
    }

    public function test_the_favicon_and_home_screen_icon_are_not_empty(): void
    {
        $this->assertGreaterThan(1000, filesize(public_path('favicon.ico')));
        $this->assertSame([180, 180], array_slice(getimagesize(public_path('icons/apple-touch-icon.png')), 0, 2));
    }

    public function test_pages_link_the_manifest_and_icons(): void
    {
        foreach ([route('auth.login'), route('client.properties')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('<link rel="manifest" href="/manifest.json">', false)
                ->assertSee('<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">', false)
                ->assertSee("navigator.serviceWorker.register('/sw.js')", false);
        }
    }

    public function test_the_offline_worker_never_stores_a_page_someone_viewed(): void
    {
        $worker = file_get_contents(public_path('sw.js'));

        $this->assertStringNotContainsString("const PRECACHE = ['/',", $worker);
        $this->assertStringContainsString("Never cache HTML pages", $worker);
        $this->get('/offline')->assertOk();
    }
}
