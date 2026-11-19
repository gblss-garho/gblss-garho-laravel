<?php

namespace Tests\Feature;

use App\Models\Homework;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    private function student(): Student
    {
        return Student::forceCreate(['id' => 's1', 'gr_number' => '55', 'name' => 'Ali', 'father_name' => 'Aslam', 'dob' => '2012-01-02', 'class' => 'VII']);
    }

    public function test_header_shows_attendance_and_progress_and_homework_filters(): void
    {
        $s = $this->student();
        DB::table('attendance_records')->insert([['id' => 'r1', 'date' => '2026-11-10', 'class_name' => 'VII'], ['id' => 'r2', 'date' => '2026-11-11', 'class_name' => 'VII']]);
        DB::table('attendance_marks')->insert(['attendance_record_id' => 'r1', 'student_id' => $s->id, 'status' => 'P']);
        Homework::create(['id' => 'h1', 'date' => now()->toDateString(), 'text' => 'Do exercise 1', 'subject' => 'english', 'class_name' => 'VII', 'status' => 'published']);
        Homework::create(['id' => 'h2', 'date' => now()->subDays(10)->toDateString(), 'text' => 'Old work', 'subject' => 'math', 'class_name' => 'VII', 'status' => 'published']);

        $this->post('/parent-lookup', ['gr_number' => '55', 'dob' => '2012-01-02'])->assertOk()
            ->assertSee('50%')->assertSee('1 / 2 din')->assertSee("Today's Homework", false)->assertSee('Do exercise 1')->assertSee('Old work')
            ->assertSee('Previous Homework')->assertSee('Leave / Absence Application');
    }

    public function test_leave_application_with_attachment_and_staff_review(): void
    {
        Storage::fake('local');
        $s = $this->student();
        DB::table('attendance_records')->insert(['id' => 'r9', 'date' => now()->toDateString(), 'class_name' => 'VII']);

        $this->post('/parent-lookup/leave', ['gr_number' => '55', 'dob' => '2012-01-02', 'leave_date' => now()->toDateString(), 'purpose' => 'Sick Leave', 'reason' => 'Bukhar hai', 'attachment' => UploadedFile::fake()->create('slip.pdf', 100, 'application/pdf')])
            ->assertOk()->assertSee('bhej di gayi');
        $row = DB::table('student_leave_applications')->first();
        $this->assertSame('pending', $row->status);
        Storage::disk('local')->assertExists($row->attachment_path);

        // wrong DOB / bad file are refused
        $this->post('/parent-lookup/leave', ['gr_number' => '55', 'dob' => '2010-01-01', 'leave_date' => now()->toDateString(), 'purpose' => 'Sick Leave', 'reason' => 'x'])->assertOk()->assertSee('نه ملي');
        $this->post('/parent-lookup/leave', ['gr_number' => '55', 'dob' => '2012-01-02', 'leave_date' => now()->toDateString(), 'purpose' => 'Sick Leave', 'reason' => 'x', 'attachment' => UploadedFile::fake()->create('a.exe', 10)])->assertOk()->assertSee('Attachment');
        $this->assertSame(1, DB::table('student_leave_applications')->count());

        $this->assertGuest();
        $this->get('/admin/student-leave')->assertRedirect();

        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin)->get('/admin/student-leave')->assertOk()->assertSee('Bukhar hai');
        $this->get('/admin/student-leave/'.$row->id.'/file')->assertOk();
        $this->put('/admin/student-leave/'.$row->id, ['status' => 'approved'])->assertRedirect();
        $this->assertSame('approved', DB::table('student_leave_applications')->value('status'));
        $this->assertSame('L', DB::table('attendance_marks')->where('student_id', $s->id)->value('status'));
    }
}
