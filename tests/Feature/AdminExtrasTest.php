<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminExtrasTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
    }

    private function marks(): void
    {
        foreach ([['r1', '1', 'Ali', 80, 60], ['r2', '2', 'Sara', 90, 70]] as [$id, $gr, $n, $m, $e]) {
            Student::forceCreate(['id' => $id, 'gr_number' => $gr, 'name' => $n, 'class' => 'VI', 'passed_out' => 0]);
            StudentResult::create(['student_id' => $id, 'academic_year' => '2026-2027', 'class' => 'VI', 'exam_type' => 'annual', 'subject' => 'Math', 'score' => $m]);
            StudentResult::create(['student_id' => $id, 'academic_year' => '2026-2027', 'class' => 'VI', 'exam_type' => 'annual', 'subject' => 'English', 'score' => $e]);
        }
    }

    public function test_results_page_has_tabs_rank_subject_average_and_class_summary(): void
    {
        $this->marks();
        $this->actingAs($this->admin());
        $base = ['year' => '2026-2027', 'exam' => 'annual', 'class' => 'VI'];
        $this->get('/admin/results?'.http_build_query($base))->assertOk()->assertSee('Rank')->assertSee('Subject Averages')->assertSee('Archive');
        $this->get('/admin/results?'.http_build_query($base + ['tab' => 'subjects']))->assertOk()->assertSee('Math')->assertSee('85');   // (80+90)/2
        $this->get('/admin/results?'.http_build_query($base + ['tab' => 'classes']))->assertOk()->assertSee('Avg total')->assertSee('150');   // totals 140 and 160
    }

    public function test_archive_csv_and_reset_needs_typed_confirmation(): void
    {
        $this->marks();
        $this->actingAs($this->admin());
        $q = ['year' => '2026-2027', 'exam' => 'annual'];
        $this->get('/admin/results/archive?'.http_build_query($q))->assertOk()->assertDownload();
        $this->post('/admin/results/reset', $q + ['confirm' => 'yes'])->assertSessionHasErrors('confirm');
        $this->assertSame(4, StudentResult::count());
        $this->post('/admin/results/reset', $q + ['confirm' => 'RESET'])->assertRedirect();
        $this->assertSame(0, StudentResult::count());
    }

    public function test_sync_gr_register_adds_only_missing_rows(): void
    {
        $this->actingAs($this->admin());
        Student::forceCreate(['id' => 's1', 'gr_number' => '5', 'name' => 'Missing One', 'class' => 'VI', 'passed_out' => 0]);
        Student::forceCreate(['id' => 's2', 'gr_number' => '6', 'name' => 'Has Row', 'class' => 'VI', 'passed_out' => 0]);
        DB::table('gr_register')->insert(['reg_no' => 6, 'name' => 'Original Name', 'created_at' => now(), 'updated_at' => now()]);
        $this->post('/admin/gr-register/sync')->assertRedirect();
        $this->assertDatabaseHas('gr_register', ['reg_no' => 5, 'name' => 'Missing One']);
        $this->assertDatabaseHas('gr_register', ['reg_no' => 6, 'name' => 'Original Name']);
        $this->get('/admin/gr-register')->assertOk()->assertSee('Sync GR Register');
    }

    public function test_students_page_shows_donut_and_alumni_year_filter_works(): void
    {
        $this->actingAs($this->admin());
        Student::forceCreate(['id' => 'a1', 'gr_number' => '1', 'name' => 'Active Kid', 'class' => 'VI', 'passed_out' => 0]);
        Student::forceCreate(['id' => 'a2', 'gr_number' => '2', 'name' => 'Old Boy', 'class' => 'VIII', 'passed_out' => 1, 'pass_out_year' => '2024-2025']);
        Student::forceCreate(['id' => 'a3', 'gr_number' => '3', 'name' => 'Older Boy', 'class' => 'VIII', 'passed_out' => 1, 'pass_out_year' => '2023-2024']);
        $this->get('/admin/students')->assertOk()->assertSee('conic-gradient', false)->assertSee('Passed out');
        auth()->logout();
        $this->get('/alumni?year=2024-2025')->assertOk()->assertSee('Old Boy')->assertDontSee('Older Boy');
        $this->get('/alumni')->assertOk()->assertSee('Sab sessions');
    }

    public function test_session_fix_moves_results_to_last_session_and_previous_class(): void
    {
        $this->actingAs($this->admin());
        // Current session classes: VIII (were VII last session), VII (were VI), and a passed-out VIII student.
        foreach ([['p1', '1', 'Now VIII', 'VIII', 0], ['p2', '2', 'Now VII', 'VII', 0], ['p3', '3', 'Left VIII', 'VIII', 1]] as [$id, $gr, $n, $cls, $out]) {
            Student::forceCreate(['id' => $id, 'gr_number' => $gr, 'name' => $n, 'class' => $cls, 'passed_out' => $out]);
            StudentResult::create(['student_id' => $id, 'academic_year' => '2026-2027', 'class' => $cls, 'exam_type' => 'annual', 'subject' => 'Math', 'score' => 50]);
        }
        $this->get('/admin/results?year=2026-2027&exam=annual')->assertOk()->assertSee('Session / Class fix')->assertSee('VIII → VII');

        $this->post('/admin/results/relabel', ['from' => '2026-2027', 'to' => '2025-2026', 'confirm' => 'nope'])->assertSessionHasErrors('confirm');
        $this->post('/admin/results/relabel', ['from' => '2026-2027', 'to' => '2025-2026', 'confirm' => 'FIX'])->assertRedirect();

        $this->assertSame(0, StudentResult::where('academic_year', '2026-2027')->count());
        $this->assertSame('VII', StudentResult::where('student_id', 'p1')->value('class'));
        $this->assertSame('VI', StudentResult::where('student_id', 'p2')->value('class'));
        $this->assertSame('VIII', StudentResult::where('student_id', 'p3')->value('class'));   // passed out keeps VIII
        // Running it again onto a session that already has results is refused.
        $this->post('/admin/results/relabel', ['from' => '2025-2026', 'to' => '2025-2026', 'confirm' => 'FIX'])->assertSessionHasErrors('to');
    }

    public function test_parent_sees_results_published_for_the_class_the_exam_was_sat_in(): void
    {
        Student::forceCreate(['id' => 'q1', 'gr_number' => '9', 'name' => 'Now VIII', 'dob' => '2012-01-02', 'class' => 'VIII', 'passed_out' => 0]);
        StudentResult::create(['student_id' => 'q1', 'academic_year' => '2025-2026', 'class' => 'VII', 'exam_type' => 'annual', 'subject' => 'Math', 'score' => 251]);
        // Only "VIII" published -> the VII-sat result stays hidden.
        DB::table('result_publish_status')->insert(['id' => 'x1', 'class_name' => 'VIII', 'exam_type' => 'annual', 'status' => 'published', 'created_at' => now(), 'updated_at' => now()]);
        $this->post('/results', ['gr_number' => '9', 'dob' => '2012-01-02'])->assertOk()->assertDontSee('251');
        DB::table('result_publish_status')->insert(['id' => 'x2', 'class_name' => 'VII', 'exam_type' => 'annual', 'status' => 'published', 'created_at' => now(), 'updated_at' => now()]);
        $this->post('/results', ['gr_number' => '9', 'dob' => '2012-01-02'])->assertOk()->assertSee('251');
    }
}
