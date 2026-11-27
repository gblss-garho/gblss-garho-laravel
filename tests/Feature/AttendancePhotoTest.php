<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendancePhotoTest extends TestCase
{
    use RefreshDatabase;

    private const LAT = 25.2000;
    private const LNG = 68.2000;

    /** A tiny but structurally valid 320x240 JPEG (no GD needed). */
    private function jpeg(): string
    {
        $b = "\xFF\xD8"
            ."\xFF\xE0".pack('n', 16)."JFIF\0\x01\x01\x00".pack('nn', 1, 1)."\x00\x00"
            ."\xFF\xFE".pack('n', 2002).str_repeat('x', 2000)
            ."\xFF\xC0".pack('n', 17)."\x08".pack('nn', 240, 320)."\x03\x01\x22\x00\x02\x11\x01\x03\x11\x01"
            ."\xFF\xD9";

        return 'data:image/jpeg;base64,'.base64_encode($b);
    }

    private function setupTeacher(): array
    {
        DB::table('school_info')->updateOrInsert(['id' => 1], ['name' => 'GBLSS Garho', 'school_lat' => self::LAT, 'school_lng' => self::LNG, 'attendance_radius' => 200]);
        $u = User::forceCreate(['name' => 'T One', 'email' => 'p1@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        $t = Teacher::forceCreate(['id' => 'tchpho0001', 'user_id' => $u->id, 'name' => 'T One', 'auth_email' => 'p1@example.test', 'created_at' => '2026-01-01 00:00:00']);
        $t->face_descriptor = json_encode(array_fill(0, 128, 0.1));
        $t->save();
        $this->mock(\App\Services\LivenessService::class, fn ($m) => $m->shouldReceive('verify')->andReturn(null));
        $this->mock(\App\Services\FaceRecognitionService::class, fn ($m) => $m->shouldReceive('isMatch')->andReturn(true));

        return [$u, $t];
    }

    private function body(array $extra = []): array
    {
        return array_merge(['lat' => self::LAT + 0.0005, 'lng' => self::LNG, 'acc' => 15, 'descriptor_json' => json_encode(array_fill(0, 128, 0.1))], $extra);
    }

    public function test_checkin_without_photo_is_refused_and_logged(): void
    {
        Storage::fake('local');
        Carbon::setTestNow(Carbon::parse('2026-11-17 08:40'));
        [$u, $t] = $this->setupTeacher();

        $this->actingAs($u)->post('/teacher/attendance/checkin', $this->body())->assertSessionHasErrors('attendance');
        $this->post('/teacher/attendance/checkin', $this->body(['snapshot' => 'data:image/jpeg;base64,AAAA']))->assertSessionHasErrors('attendance'); // not a real picture
        $this->post('/teacher/attendance/checkin', $this->body(['snapshot' => 'hello']))->assertSessionHasErrors('attendance');
        $this->assertSame(0, TeacherAttendance::count());
        $this->assertSame(3, DB::table('teacher_liveness_failures')->where('reason', 'photo_missing')->count());
        Carbon::setTestNow();
    }

    public function test_checkin_with_photo_is_saved_and_only_admin_can_see_it(): void
    {
        Storage::fake('local');
        Carbon::setTestNow(Carbon::parse('2026-11-17 08:40'));
        [$u, $t] = $this->setupTeacher();

        $this->actingAs($u)->post('/teacher/attendance/checkin', $this->body(['snapshot' => $this->jpeg()]))->assertSessionDoesntHaveErrors();
        $rec = TeacherAttendance::first();
        $this->assertNotNull($rec, 'check-in was not saved');
        $this->assertNotNull($rec->check_in_photo);
        Storage::disk('local')->assertExists($rec->check_in_photo);

        $url = '/admin/staff-attendance/photo/'.$rec->id.'/in';
        $this->actingAs($u)->get($url)->assertForbidden();

        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin)->get($url)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->get('/admin/staff-attendance/photo/'.$rec->id.'/out')->assertNotFound();
        $this->get('/admin/staff-attendance?from=2026-11-17&to=2026-11-17')->assertOk()->assertSee($url, false);
        Carbon::setTestNow();
    }

    public function test_prune_command_removes_old_photos_only(): void
    {
        Storage::fake('local');
        [$u, $t] = $this->setupTeacher();
        Storage::disk('local')->put('attendance-photos/old.jpg', 'x');
        Storage::disk('local')->put('attendance-photos/new.jpg', 'x');
        $old = new TeacherAttendance(['teacher_id' => $t->id, 'date' => now()->subDays(200)->toDateString()]);
        $old->check_in_at = now()->subDays(200);
        $old->check_in_photo = 'attendance-photos/old.jpg';
        $old->save();
        $new = new TeacherAttendance(['teacher_id' => $t->id, 'date' => now()->toDateString()]);
        $new->check_in_at = now();
        $new->check_in_photo = 'attendance-photos/new.jpg';
        $new->save();

        $this->artisan('attendance:prune-photos', ['--days' => 90])->assertSuccessful();

        Storage::disk('local')->assertMissing('attendance-photos/old.jpg');
        Storage::disk('local')->assertExists('attendance-photos/new.jpg');
        $this->assertNull($old->fresh()->check_in_photo);
        $this->assertNotNull($new->fresh()->check_in_photo);
    }
}
