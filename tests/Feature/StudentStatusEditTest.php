<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentStatusEditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::forceCreate([
            'name' => 'Admin Test', 'email' => 'admin@example.test', 'password' => bcrypt('secret-pass'),
            'role' => 'admin', 'is_active' => 1,
        ]);
    }

    private function student(): Student
    {
        return Student::create([
            'id' => 'stu-1', 'gr_number' => '7', 'name' => 'Ali', 'father_name' => 'Khan', 'class' => 'Five', 'passed_out' => 0,
        ]);
    }

    public function test_gr_edit_marks_lc_issued_and_back_to_active(): void
    {
        $s = $this->student();
        $this->actingAs($this->admin());
        $this->get('/admin/gr-register/7/edit')->assertOk()->assertSee('LC Issued');

        $this->put('/admin/gr-register/7', [
            'status_choice' => 'lc', 'removal_date' => '2026-11-17', 'removal_class' => 'Five', 'removal_cause' => 'Shifted',
        ])->assertRedirect();

        $s->refresh();
        $this->assertSame(1, (int) $s->passed_out);
        $this->assertSame('LC Issued', $s->status);
        $row = DB::table('gr_register')->where('reg_no', 7)->first();
        $this->assertSame('LC Issued', $row->status);
        $this->assertSame('Shifted', $row->removal_cause);
        $this->get('/admin/students')->assertDontSee('Ali');

        $this->put('/admin/gr-register/7', ['status_choice' => 'active'])->assertRedirect();
        $s->refresh();
        $this->assertSame(0, (int) $s->passed_out);
        $this->assertNull(DB::table('gr_register')->where('reg_no', 7)->first()->removal_date);
    }

    public function test_student_form_can_set_left_status(): void
    {
        $s = $this->student();
        $this->actingAs($this->admin());
        $this->put('/admin/students/'.$s->id, [
            'gr_number' => '7', 'name' => 'Ali', 'class' => 'Five', 'status_choice' => 'left', 'removal_date' => '2026-11-01',
        ])->assertRedirect();
        $this->assertSame('Left', $s->fresh()->status);
        $this->assertSame(1, (int) $s->fresh()->passed_out);
    }

    public function test_old_register_only_row_can_be_edited(): void
    {
        DB::table('gr_register')->insert(['reg_no' => 3, 'name' => 'Old', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($this->admin());
        $this->put('/admin/gr-register/3', ['status_choice' => 'passed', 'removal_date' => '2020-03-01'])->assertRedirect();
        $this->assertSame('Passed out', DB::table('gr_register')->where('reg_no', 3)->value('status'));
    }
}
