<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\SchoolScheduleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherAttendanceWeekTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): array
    {
        $u = User::forceCreate(['name' => 'T One', 'email' => 't1@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        $t = Teacher::forceCreate(['id' => 'tch0000001', 'user_id' => $u->id, 'name' => 'T One', 'auth_email' => 't1@example.test', 'created_at' => '2026-01-01 00:00:00']);

        return [$u, $t];
    }

    public function test_late_minutes_rule(): void
    {
        $s = new SchoolScheduleService();
        // winter Monday: on time until 09:00
        $this->assertSame(0, $s->lateMinutes(Carbon::parse('2026-11-09 08:59')));
        $this->assertSame(0, $s->lateMinutes(Carbon::parse('2026-11-09 09:00')));
        $this->assertSame(25, $s->lateMinutes(Carbon::parse('2026-11-09 09:25')));
        $this->assertTrue($s->isWithinCheckInPeriod(Carbon::parse('2026-11-09 09:25')));
        $this->assertFalse($s->isWithinCheckInPeriod(Carbon::parse('2026-11-09 13:50')));
        $this->assertFalse($s->isWithinCheckInPeriod(Carbon::parse('2026-11-14 09:00'))); // Saturday off
    }

    public function test_week_view_shows_present_late_leave_absent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-13 15:00')); // Friday afternoon
        [$u, $t] = $this->teacher();

        TeacherAttendance::create(['teacher_id' => $t->id, 'date' => '2026-11-09', 'check_in_at' => '2026-11-09 08:40:00', 'check_out_at' => '2026-11-09 13:50:00', 'late_minutes' => 0]);
        TeacherAttendance::create(['teacher_id' => $t->id, 'date' => '2026-11-10', 'check_in_at' => '2026-11-10 09:25:00', 'check_out_at' => '2026-11-10 13:55:00', 'late_minutes' => 25]);
        DB::table('leave_requests')->insert(['id' => 'lv0000000001', 'teacher_id' => $t->id, 'teacher_name' => 'T One', 'date' => '2026-11-11', 'reason' => 'Fever', 'status' => 'approved', 'created_at' => now(), 'updated_at' => now()]);

        $r = $this->actingAs($u)->get('/teacher/attendance')->assertOk();
        $r->assertSee('Late 25 min', false)->assertSee('Chhutti manzoor')->assertSee('Absent')->assertSee('Present');
        $r->assertSee('Hafta:', false)->assertSee('css/attendance.css', false);

        Carbon::setTestNow();
    }

    public function test_enroll_page_has_round_scanner(): void
    {
        [$u] = $this->teacher();
        $this->actingAs($u)->get('/teacher/face-enroll')->assertOk()
            ->assertSee('data-face-scan', false)->assertSee('fs-ticks', false);
    }

    public function test_asset_files_exist(): void
    {
        foreach (['js/face-scan.js', 'css/attendance.css'] as $f) {
            $this->assertFileExists(public_path($f));
        }
    }

    public function test_enrol_without_liveness_is_rejected_and_logged(): void
    {
        [$u, $t] = $this->teacher();
        $d = json_encode(array_fill(0, 128, 0.1));

        $this->actingAs($u)->get('/teacher/face-enroll')->assertOk()->assertSee('data-challenges="', false);
        $this->post('/teacher/face-enroll', ['descriptor_json' => $d])->assertSessionHasErrors('face');

        $this->assertNull($t->fresh()->face_descriptor);
        $this->assertSame(1, DB::table('teacher_liveness_failures')->where('failure_type', 'enroll')->count());
    }

    public function test_enrol_with_matching_liveness_proof_is_accepted(): void
    {
        [$u, $t] = $this->teacher();
        $d = json_encode(array_fill(0, 128, 0.1));

        $this->actingAs($u)->get('/teacher/face-enroll')->assertOk();
        $c = session('liveness')['c'];
        $this->assertCount(2, $c);
        $this->assertContains('blink', $c);

        $this->travel(5)->seconds();
        $this->post('/teacher/face-enroll', [
            'descriptor_json' => $d,
            'liveness' => json_encode(['c' => $c, 'ok' => true]),
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($t->fresh()->face_descriptor);

        // the challenge is single-use
        $this->post('/teacher/face-enroll', [
            'descriptor_json' => $d,
            'liveness' => json_encode(['c' => $c, 'ok' => true]),
        ])->assertSessionHasErrors('face');
    }
}
