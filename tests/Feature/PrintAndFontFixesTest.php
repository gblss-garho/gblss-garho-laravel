<?php

namespace Tests\Feature;

use App\Support\PdfPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrintAndFontFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_bank_register_prints_on_a4_landscape_without_clipping(): void
    {
        $css = file_get_contents(public_path('css/bookbank.css'));
        $this->assertStringContainsString('size: A4 landscape', $css);
        $this->assertStringContainsString('table-layout:fixed', $css);
        $this->assertMatchesRegularExpression('/\.bb-scroll[^}]*overflow:visible/', $css);
    }

    public function test_urdu_story_uses_an_urdu_font_not_the_sindhi_one(): void
    {
        DB::table('funtime_items')->insert(['id' => 'st00000001', 'type' => 'story', 'title' => 'وقت کی پابندی',
            'content' => 'احمد آٹھویں جماعت کا طالب علم تھا۔ وہ ذہین تھا لیکن بہت سست تھا۔', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('funtime_items')->insert(['id' => 'st00000002', 'type' => 'story', 'title' => 'Plain English story',
            'content' => 'Once upon a time.', 'created_at' => now(), 'updated_at' => now()]);

        $html = $this->get('/e-library?type=story')->assertOk()->getContent();
        $this->assertStringContainsString('Noto+Nastaliq+Urdu', $html);
        $this->assertSame(2, preg_match_all('/class="ur"/', $html));   // Urdu title + Urdu text only
    }

    public function test_id_card_photo_spare_space_is_plain_white(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension not installed here.');
        }
        $im = imagecreatetruecolor(600, 800);
        imagefill($im, 0, 0, imagecolorallocate($im, 120, 130, 150));   // coloured backdrop
        imagefilledellipse($im, 300, 130, 200, 260, imagecolorallocate($im, 200, 150, 120));   // head touching the top edge
        imagefilledrectangle($im, 60, 300, 540, 799, imagecolorallocate($im, 20, 20, 20));
        ob_start();
        imagepng($im);
        $uri = 'data:image/png;base64,'.base64_encode(ob_get_clean());
        $res = PdfPhoto::small($uri, 340, false, true);
        $out = imagecreatefromstring(base64_decode(substr($res, strpos($res, ',') + 1)));
        $c = imagecolorat($out, (int) (imagesx($out) / 2), 1);
        $this->assertGreaterThan(245, ($c >> 16) & 255);
        $this->assertGreaterThan(245, $c & 255);
    }
}
