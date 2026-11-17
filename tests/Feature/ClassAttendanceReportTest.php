<?php

namespace Tests\Feature;

use App\Models\AttendanceMark;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassAttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    private function seedClass(): void
    {
        Student::create(['id' => 's1', 'gr_number' => '1', 'name' => 'Ali Regular', 'class' => 'Five', 'passed_out' => 0]);
        Student::create(['id' => 's2', 'gr_number' => '2', 'name' => 'Bilal Absent', 'class' => 'Five', 'passed_out' => 0]);
        foreach (['2026-11-02', '2026-11-03', '2026-11-04'] as $i => $d) {
            $r = AttendanceRecord::create(['id' => 'r'.$i, 'date' => $d, 'class_name' => 'Five']);
            AttendanceMark::create(['attendance_record_id' => $r->id, 'student_id' => 's1', 'status' => 'P']);
            if ($i === 0) {
                AttendanceMark::create(['attendance_record_id' => $r->id, 'student_id' => 's2', 'status' => 'P']);
            }
        }
    }

    public function test_admin_sees_month_status_search_and_day_filter(): void
    {
        $this->seedClass();
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin);

        $this->get('/admin/class-attendance?class=Five&month=2026-11')
            ->assertOk()->assertSee('Ali Regular')->assertSee('Bilal Absent')->assertSee('Regular')->assertSee('Irregular');

        $this->get('/admin/class-attendance?class=Five&month=2026-11&q=Bilal')
            ->assertOk()->assertSee('Bilal Absent')->assertDontSee('Ali Regular');

        $this->get('/admin/class-attendance?class=Five&month=2026-11&date=2026-11-03&st=absent')
            ->assertOk()->assertSee('Bilal Absent')->assertDontSee('Ali Regular');

        $this->get('/admin/class-attendance?class=Five&mode=week&week=2026-11-03')->assertOk()->assertSee('Mon');
    }

    public function test_teacher_limited_to_own_classes(): void
    {
        $this->seedClass();
        $u = User::forceCreate(['name' => 'T', 'email' => 't@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        Teacher::forceCreate(['id' => 'tch0000003', 'user_id' => $u->id, 'name' => 'T', 'auth_email' => 't@example.test', 'assigned_class' => 'Six', 'created_at' => now()]);
        $this->actingAs($u);
        $this->get('/teacher/class-attendance?class=Five&month=2026-11')->assertOk()->assertDontSee('Ali Regular');
    }
}
