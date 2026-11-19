<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardPreviewTest extends TestCase
{
    use RefreshDatabase;

    private function student(): Student
    {
        return Student::forceCreate(['id' => 's1', 'gr_number' => '77', 'name' => 'Ali Khan', 'father_name' => 'Aslam', 'dob' => '2012-01-02', 'class' => 'VII', 'section' => 'Green']);
    }

    public function test_student_id_card_preview_then_pdf_form(): void
    {
        $this->student();
        $this->get('/id-cards')->assertOk()->assertSee('Teacher ID Card')->assertSee('Student ID Card');
        $this->post('/id-cards', ['gr_number' => '77', 'dob' => '2012-01-02'])
            ->assertOk()->assertSee('ALI KHAN')->assertSee('TERMS &amp; CONDITIONS', false)->assertSee('Print / Save as PDF', false)->assertSee('/id-cards/pdf', false);
        $this->post('/id-cards', ['gr_number' => '77', 'dob' => '2010-01-01'])->assertOk()->assertSee('نه ملي')->assertDontSee('ALI KHAN');
    }

    public function test_teacher_card_lookup_by_pid_or_cnic_and_hides_cnic(): void
    {
        Teacher::forceCreate(['id' => 'tchcard001', 'name' => 'Waris', 'pid' => '10829975', 'cnic' => '41234-1234567-1', 'designation' => 'JEST', 'created_at' => now()]);
        $this->post('/id-cards/teacher', ['cnic_pid' => '10829975'])->assertOk()->assertSee('WARIS')->assertSee('JEST')->assertDontSee('41234');
        $this->post('/id-cards/teacher', ['cnic_pid' => '4123412345671'])->assertOk()->assertSee('WARIS');
        $this->post('/id-cards/teacher', ['cnic_pid' => '999'])->assertOk()->assertSee('nahi mila')->assertDontSee('WARIS');
    }

    public function test_admit_card_preview_and_admin_class_grid(): void
    {
        $this->student();
        $this->post('/admit-card', ['gr_number' => '77', 'dob' => '2012-01-02'])->assertOk()->assertSee('ADMIT CARD')->assertSee('Ali Khan')->assertSee('/admit-card/pdf', false);

        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin);
        $this->get('/admin/admit-cards?class=VII')->assertOk()->assertSee('Print All (1)', false)->assertSee('Ali Khan');
    }
}
