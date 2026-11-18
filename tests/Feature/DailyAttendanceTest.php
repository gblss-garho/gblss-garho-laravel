<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DailyAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        $u = User::forceCreate(['name' => 'T', 'email' => 't@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        Teacher::forceCreate(['id' => 'tchdaily01', 'user_id' => $u->id, 'name' => 'T', 'auth_email' => 't@example.test', 'assigned_class' => 'Class VI', 'created_at' => now()]);

        return $u;
    }

    public function test_teacher_marks_attendance_by_hand(): void
    {
        Student::create(['id' => 's1', 'gr_number' => '5', 'name' => 'Ali', 'class' => 'Class VI', 'passed_out' => 0]);
        Student::create(['id' => 's2', 'gr_number' => '6', 'name' => 'Bilal', 'class' => 'Class VI', 'passed_out' => 0]);
        Student::create(['id' => 's3', 'gr_number' => '7', 'name' => 'Other', 'class' => 'Class VII', 'passed_out' => 0]);
        $today = now()->toDateString();
        $this->actingAs($this->teacher());

        $this->get('/teacher/daily-attendance')->assertOk()->assertSee('Daily Attendance')->assertSee('Ali')->assertDontSee('Other');

        $this->postJson('/teacher/daily-attendance', ['class' => 'Class VI', 'date' => $today, 'student_id' => 's1', 'status' => 'P'])
            ->assertOk()->assertJson(['present' => 1, 'total' => 2, 'unmarked' => 1, 'percent' => 50]);
        $this->postJson('/teacher/daily-attendance', ['class' => 'Class VI', 'date' => $today, 'student_id' => 's2', 'status' => 'A'])
            ->assertOk()->assertJson(['absent' => 1, 'unmarked' => 0]);
        $this->postJson('/teacher/daily-attendance', ['class' => 'Class VI', 'date' => $today, 'student_id' => 's2', 'status' => 'L'])
            ->assertOk()->assertJson(['absent' => 0, 'leave' => 1]);
        $this->postJson('/teacher/daily-attendance', ['class' => 'Class VI', 'date' => $today, 'student_id' => 's2', 'status' => 'clear'])
            ->assertOk()->assertJson(['leave' => 0, 'unmarked' => 1]);
        $this->postJson('/teacher/daily-attendance', ['class' => 'Class VI', 'date' => $today, 'status' => 'all_present'])
            ->assertOk()->assertJson(['present' => 2, 'percent' => 100]);
        $this->assertSame(1, DB::table('attendance_records')->count());

        // other class / future date refused
        $this->postJson('/teacher/daily-attendance', ['class' => 'Class VII', 'date' => $today, 'student_id' => 's3', 'status' => 'P'])->assertStatus(403);
        $this->postJson('/teacher/daily-attendance', ['class' => 'Class VI', 'date' => now()->addDay()->toDateString(), 'student_id' => 's1', 'status' => 'P'])->assertStatus(422);
    }
}
