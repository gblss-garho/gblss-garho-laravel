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

    public function test_about_shows_shift_and_classes(): void
    {
        \App\Models\SchoolInfo::create(['name' => 'GBLSS Garho', 'shifts' => 'Morning', 'total_classes' => 'I to VIII']);
        $this->get('/about')->assertOk()->assertSee('Shift:')->assertSee('Morning')->assertSee('Classes Offered:');
    }

    public function test_home_has_old_site_layout_pieces(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('WHY CHOOSE US')
            ->assertSee('id="moreSheet"', false)
            ->assertSee('class="m-nav"', false)
            ->assertSee('Designed &amp; Developed by Sir Waris Ghafoor', false);
    }

    public function test_feedback_has_star_rating(): void
    {
        $this->get('/feedback')->assertSee('class="stars"', false);
    }

    public function test_attendance_window_label_follows_settings(): void
    {
        $s = new \App\Services\SchoolScheduleService();
        $C = fn ($d) => \Carbon\Carbon::parse($d);
        $this->assertSame('8:30 AM – 9:00 AM', $s->checkInWindowLabel($C('2026-11-09 08:45')));
        $this->assertSame('1:40 PM – 2:00 PM', $s->checkOutWindowLabel($C('2026-11-09 13:50')));
        $this->assertSame('8:00 AM – 8:30 AM', $s->checkInWindowLabel($C('2026-06-08 08:15')));
        $this->assertSame('1:10 PM – 1:30 PM', $s->checkOutWindowLabel($C('2026-06-08 13:20')));
        $this->assertSame('12:10 PM – 12:30 PM', $s->checkOutWindowLabel($C('2026-11-13 12:15')));
        $this->assertSame('11:40 AM – 12:00 PM', $s->checkOutWindowLabel($C('2026-06-12 11:50')));
        $this->assertTrue($s->isWithinCheckInWindow($C('2026-11-09 08:45')));
        $this->assertFalse($s->isWithinCheckInWindow($C('2026-11-09 08:10')));
    }

    public function test_sindhi_font_and_text_present(): void
    {
        $this->get('/feedback')->assertSee('CustomSindhi', false)->assertSee('پنهنجو فيڊبيڪ ڏيو');
        $this->assertFileExists(public_path('fonts/CustomSindhi.ttf'));
    }
}
