<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManifestTest extends TestCase
{
    public function test_manifest_valid_and_linked(): void
    {
        $j = json_decode(file_get_contents(public_path('manifest.json')), true);
        $this->assertSame('standalone', $j['display']);
        foreach ($j['icons'] as $i) {
            $this->assertFileExists(public_path(ltrim($i['src'], '/')));
        }
        $this->get('/')->assertSee('manifest.json', false)->assertSee('theme-color', false);
    }
}
