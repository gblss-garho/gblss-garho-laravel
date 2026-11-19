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

    private function pdfHref(string $html, string $needle): string
    {
        $this->assertSame(1, preg_match('#href="([^"]*'.preg_quote($needle, '#').'[^"]*)"#', html_entity_decode($html), $m));

        return $m[1];
    }

    public function test_public_student_card_hides_dob_and_pdf_is_a_signed_get(): void
    {
        if (! extension_loaded("gd")) { $this->markTestSkipped("GD missing"); }
        $this->student();
        $html = $this->post('/id-cards', ['gr_number' => '77', 'dob' => '2012-01-02'])
            ->assertOk()->assertDontSee('Date of Birth')->assertDontSee('Jan 02, 2012')->getContent();
        $href = $this->pdfHref($html, '/id-cards/pdf/');
        $this->get($href)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get('/id-cards/pdf/s1')->assertForbidden();          // unsigned link refused
    }

    public function test_public_teacher_card_masks_pid_and_signed_pdf_works(): void
    {
        if (! extension_loaded("gd")) { $this->markTestSkipped("GD missing"); }
        Teacher::forceCreate(['id' => 'tchcard001', 'name' => 'Waris', 'pid' => '10829975', 'cnic' => '41234-1234567-1', 'dob' => '1990-05-05', 'designation' => 'JEST', 'created_at' => now()]);
        $html = $this->post('/id-cards/teacher', ['cnic_pid' => '10829975'])
            ->assertOk()->assertDontSee('10829975')->assertSee('*****975')->assertDontSee('May 05, 1990')->assertDontSee('Date of Birth')->getContent();
        $this->get($this->pdfHref($html, '/id-cards/teacher/pdf/'))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get('/id-cards/teacher/pdf/tchcard001')->assertForbidden();
    }

    public function test_admit_pdf_uses_signed_get(): void
    {
        if (! extension_loaded("gd")) { $this->markTestSkipped("GD missing"); }
        $this->student();
        $html = $this->post('/admit-card', ['gr_number' => '77', 'dob' => '2012-01-02'])->assertOk()->getContent();
        $this->get($this->pdfHref($html, '/admit-card/pdf/'))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get('/admit-card/pdf/s1')->assertForbidden();
    }

    public function test_admin_new_student_gets_short_id_and_gr_register_row(): void
    {
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin);
        \Illuminate\Support\Facades\DB::table('gr_register')->insert(['reg_no' => 10, 'name' => 'Old', 'created_at' => now(), 'updated_at' => now()]);

        $this->get('/admin/gr-register')->assertOk()->assertSee('Naya Dakhla')->assertSee('GR 11');
        $this->get('/admin/students/create?gr=11')->assertOk()->assertSee('value="11"', false);
        $this->post('/admin/students', ['gr_number' => '11', 'name' => 'Naya Bacha', 'class' => 'VI'])->assertRedirect();

        $s = Student::where('gr_number', '11')->first();
        $this->assertNotNull($s);
        $this->assertLessThanOrEqual(32, strlen($s->id));
        $this->assertDatabaseHas('gr_register', ['reg_no' => 11, 'name' => 'Naya Bacha', 'admission_class' => 'VI']);
        $this->post('/admin/students', ['gr_number' => '10', 'name' => 'Dup', 'class' => 'VI'])->assertSessionHasErrors('gr_number');
    }
}
