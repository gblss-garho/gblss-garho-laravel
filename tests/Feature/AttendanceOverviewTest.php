<?php

namespace Tests\Feature;

use App\Models\AttendanceMark;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\AttendanceOverview;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function seedWeek(): User
    {
        Carbon::setTestNow(Carbon::parse('2026-11-12 15:00'));   // Thursday afternoon
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);

        foreach ([['s1', '1', 'Ali'], ['s2', '2', 'Sara']] as [$id, $gr, $n]) {
            Student::forceCreate(['id' => $id, 'gr_number' => $gr, 'name' => $n, 'class' => 'VI', 'passed_out' => 0]);
        }
        foreach (['2026-11-09' => ['P', 'P'], '2026-11-10' => ['P', 'A']] as $d => [$a, $b]) {
            $r = AttendanceRecord::create(['id' => 'r'.$d, 'date' => $d, 'class_name' => 'VI']);
            AttendanceMark::create(['attendance_record_id' => $r->id, 'student_id' => 's1', 'status' => $a]);
            AttendanceMark::create(['attendance_record_id' => $r->id, 'student_id' => 's2', 'status' => $b]);
        }

        $u = User::forceCreate(['name' => 'T One', 'email' => 't1@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        $t = Teacher::forceCreate(['id' => 'tch0000001', 'user_id' => $u->id, 'name' => 'T One', 'auth_email' => 't1@example.test', 'created_at' => '2026-01-01 00:00:00']);
        TeacherAttendance::create(['teacher_id' => $t->id, 'date' => '2026-11-09', 'check_in_at' => '2026-11-09 08:40:00', 'check_out_at' => '2026-11-09 13:50:00', 'late_minutes' => 0]);
        TeacherAttendance::create(['teacher_id' => $t->id, 'date' => '2026-11-10', 'check_in_at' => '2026-11-10 09:25:00', 'check_out_at' => '2026-11-10 13:55:00', 'late_minutes' => 25]);
        DB::table('leave_requests')->insert(['id' => 'lv0000000001', 'teacher_id' => $t->id, 'teacher_name' => 'T One', 'date' => '2026-11-11', 'reason' => 'Fever', 'status' => 'approved', 'created_at' => now(), 'updated_at' => now()]);

        return $admin;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_service_totals_for_students_and_staff(): void
    {
        $this->seedWeek();
        $o = app(AttendanceOverview::class);
        $from = Carbon::parse('2026-11-09');
        $to = Carbon::parse('2026-11-15');

        $st = $o->students($from, $to, 'week');
        $this->assertSame(3, $st['total']['p']);
        $this->assertSame(1, $st['total']['a']);
        $this->assertSame(75, $st['total']['pct']);

        $sf = $o->staff($from, $to, Carbon::now(), 'week');
        $this->assertSame(2, $sf['sum']['present']);   // Mon on time + Tue late
        $this->assertSame(1, $sf['sum']['late']);
        $this->assertSame(1, $sf['sum']['leave']);
        $this->assertSame(1, $sf['sum']['absent']);    // Thursday, today after hours, no check-in

        $month = $o->staff(Carbon::parse('2026-11-01'), Carbon::parse('2026-11-30'), Carbon::now(), 'month');
        $this->assertSame(2, $month['sum']['present']);   // whole month, same teacher
    }

    public function test_overview_page_shows_students_and_staff_for_week_and_month(): void
    {
        $admin = $this->seedWeek();
        $this->actingAs($admin);

        $this->get('/admin/attendance-overview?mode=week&week=2026-11-10')->assertOk()
            ->assertSee('Attendance Overview')->assertSee('VI')->assertSee('75%');
        $this->get('/admin/attendance-overview?mode=week&week=2026-11-10&tab=staff')->assertOk()
            ->assertSee('T One')->assertSee('Late 25 min', false);
        $this->get('/admin/attendance-overview?mode=month&month=2026-11&tab=staff')->assertOk()->assertSee('T One');
        $this->get('/admin/attendance-overview?mode=month&month=bad&week=nonsense')->assertOk();
    }

    public function test_dashboard_has_todays_card_and_link_and_only_admin_can_open_overview(): void
    {
        $admin = $this->seedWeek();
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()
            ->assertSee('Aaj ki Hazri')->assertSee('Poora record')->assertSee('/admin/attendance-overview', false);

        $teacher = User::where('role', 'teacher')->first();
        $this->actingAs($teacher)->get('/admin/attendance-overview')->assertForbidden();
    }
}
