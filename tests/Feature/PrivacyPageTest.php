<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_page_is_public_and_footer_links_to_it(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Privacy &amp; Data', false)->assertSee('General Register');
        $this->get('/')->assertSee('/privacy', false);
    }
}
