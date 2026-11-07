<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_use_portal_layout(): void
    {
        foreach (['/', '/about', '/staff', '/toppers', '/gallery', '/textbooks', '/feedback'] as $url) {
            $this->get($url)->assertOk()->assertSee('class="site-head"', false)->assertSee('Toppers');
        }
    }

    public function test_feedback_has_star_rating(): void
    {
        $this->get('/feedback')->assertSee('class="stars"', false);
    }

    public function test_attendance_window_label_follows_settings(): void
    {
        $s = new \App\Services\SchoolScheduleService();
        $this->assertSame('8:00 AM – 8:30 AM', $s->checkInWindowLabel());
    }
}
