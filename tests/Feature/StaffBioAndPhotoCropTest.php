<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Support\PdfPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffBioAndPhotoCropTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_qr_opens_school_and_teacher_biodata_without_private_fields(): void
    {
        $t = Teacher::forceCreate(['id' => 'tchbio0001', 'name' => 'Waris Ali', 'pid' => '10829975', 'cnic' => '41234-1234567-1', 'dob' => '1990-05-05',
            'designation' => 'JEST', 'qualification' => 'MA B.Ed', 'subject' => 'Math', 'created_at' => now()]);
        $code = $t->ensureQrCode();

        $this->get('/staff/id/'.$code)->assertOk()->assertSee('Waris Ali')->assertSee('JEST')->assertSee('MA B.Ed')->assertSee('404030082')
            ->assertSee('*****975')->assertDontSee('10829975')->assertDontSee('41234')->assertDontSee('1990');
        $this->get('/staff/id/'.strtolower($code))->assertOk()->assertSee('Waris Ali');
        $this->get('/staff/id/ZZZZZZZZZZ')->assertNotFound();
    }

    public function test_staff_card_qr_encodes_the_biodata_url_not_the_raw_attendance_code(): void
    {
        $t = Teacher::forceCreate(['id' => 'tchbio0002', 'name' => 'Waris', 'pid' => '10829975', 'cnic' => '41234-1234567-1', 'designation' => 'JEST', 'created_at' => now()]);
        $this->assertStringStartsWith('data:image/', \App\Support\CardData::staffQr($t));
        $this->post('/id-cards/teacher', ['cnic_pid' => '10829975'])->assertOk()->assertSee('Scan for school &amp; staff biodata', false);
    }

    public function test_head_crop_zooms_a_small_figure_but_leaves_a_tight_face_photo_alone(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension not installed here.');
        }
        $crop = new \ReflectionMethod(PdfPhoto::class, 'headCrop');
        $crop->setAccessible(true);

        // Plain white photo, small head-and-shoulders figure in the middle.
        $a = imagecreatetruecolor(600, 800);
        imagefill($a, 0, 0, imagecolorallocate($a, 255, 255, 255));
        imagefilledellipse($a, 300, 330, 120, 150, imagecolorallocate($a, 200, 150, 120));
        imagefilledrectangle($a, 180, 400, 420, 800, imagecolorallocate($a, 20, 20, 20));
        [$x, $y, $side] = $crop->invoke(null, $a, 600, 800);
        $this->assertLessThan(420, $side);                 // zoomed in
        $this->assertLessThanOrEqual(255, $y);              // starts above the head (top at y=255)
        $this->assertGreaterThanOrEqual(180, $y + $side);   // still includes the whole face
        $this->assertLessThan(300, $x);
        $this->assertGreaterThan(300, $x + $side);

        // Face fills the frame on a busy background: only a gentle trim.
        $b = imagecreatetruecolor(600, 800);
        imagefill($b, 0, 0, imagecolorallocate($b, 120, 90, 70));
        mt_srand(3);
        for ($i = 0; $i < 300; $i++) {
            imagefilledellipse($b, mt_rand(0, 600), mt_rand(0, 800), mt_rand(20, 80), mt_rand(20, 80), imagecolorallocate($b, mt_rand(60, 200), mt_rand(40, 160), mt_rand(30, 140)));
        }
        imagefilledellipse($b, 300, 380, 480, 620, imagecolorallocate($b, 210, 160, 130));
        [, , $side2] = $crop->invoke(null, $b, 600, 800);
        $this->assertGreaterThan(500, $side2);
    }
}
