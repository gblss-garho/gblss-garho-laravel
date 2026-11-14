<?php

namespace Tests\Feature;

use Tests\TestCase;

class CssScopeTest extends TestCase
{
    /** Shared components (.hero-lk, .lk-card, .pane ...) must not live inside a mobile-only @media block. */
    public function test_shared_component_css_is_global_and_braces_balance(): void
    {
        $html = file_get_contents(resource_path('views/layouts/portal.blade.php'));
        $start = strpos($html, '<style>');
        $css = substr($html, $start, strpos($html, '</style>', $start) - $start);

        $this->assertSame(substr_count($css, '{'), substr_count($css, '}'), 'unbalanced CSS braces');

        foreach (['.hero-lk {', '.lk-card {', '.pane {', '.tb-grid {', '.fb-item {'] as $sel) {
            $pos = strpos($css, $sel);
            $this->assertNotFalse($pos, "$sel missing");
            $before = substr($css, 0, $pos);
            $this->assertSame(0, substr_count($before, '{') - substr_count($before, '}'), "$sel is nested inside another block");
        }
    }
}
