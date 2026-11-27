<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FestiveThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_sindhi_culture_banner_shows_in_the_festival_window(): void
    {
        Carbon::setTestNow('2026-12-03 09:00:00');
        $this->get('/')->assertOk()
            ->assertSee('سنڌي ثقافت جو ڏينهن مبارڪ', false)
            ->assertSee('Mohenjo-daro')
            ->assertSee('Makli Necropolis')
            ->assertSee('Ranikot Fort');
    }

    public function test_banner_is_hidden_outside_the_window_but_preview_works(): void
    {
        Carbon::setTestNow('2026-12-20 09:00:00');
        $this->get('/')->assertOk()->assertDontSee('Happy Sindhi Culture Day');
        $this->get('/?festive=sindhi')->assertOk()->assertSee('Happy Sindhi Culture Day');

        Carbon::setTestNow('2026-12-03 09:00:00');
        $this->get('/?festive=off')->assertOk()->assertDontSee('Happy Sindhi Culture Day');
    }

    public function test_effect_assets_are_linked_and_exist(): void
    {
        $this->get('/')->assertSee('css/home-fx.css', false)->assertSee('js/home-fx.js', false);
        $this->assertFileExists(public_path('css/home-fx.css'));
        $this->assertFileExists(public_path('js/home-fx.js'));
    }
}
