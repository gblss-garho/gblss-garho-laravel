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

    public function test_admin_student_lists_are_in_gr_order_not_alphabetical(): void
    {
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin);
        foreach ([['g1', '2', 'Zubair'], ['g2', '10', 'Ali'], ['g3', '1', 'Maria']] as [$id, $gr, $n]) {
            Student::forceCreate(['id' => $id, 'gr_number' => $gr, 'name' => $n, 'class' => 'VII', 'passed_out' => 0]);
        }
        $this->get('/admin/students')->assertOk()->assertSeeInOrder(['Maria', 'Zubair', 'Ali']);   // GR 1, 2, 10
        $this->get('/admin/id-cards')->assertOk()->assertSeeInOrder(['Maria', 'Zubair', 'Ali']);
    }

    public function test_passed_out_students_get_no_id_or_admit_card_and_are_not_listed(): void
    {
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->student();
        Student::forceCreate(['id' => 's2', 'gr_number' => '230', 'name' => 'Ahsan Left', 'dob' => '2011-03-04', 'class' => 'VIII', 'passed_out' => 1]);

        $this->actingAs($admin)->get('/admin/id-cards')->assertOk()->assertSee('Ali Khan')->assertDontSee('Ahsan Left');
        $this->post('/id-cards', ['gr_number' => '230', 'dob' => '2011-03-04'])->assertOk()->assertSee('enrolled nahi')->assertDontSee('AHSAN LEFT');
        $this->post('/admit-card', ['gr_number' => '230', 'dob' => '2011-03-04'])->assertOk()->assertSee('enrolled nahi')->assertDontSee('Ahsan Left');
    }

    public function test_preview_shows_sindhi_motto_in_sindhi_font_class(): void
    {
        $this->student();
        \Illuminate\Support\Facades\DB::table('school_info')->insert(['name' => 'GBLSS Garho', 'motto' => \App\Support\CardData::SINDHI_MOTTO]);
        $this->post('/id-cards', ['gr_number' => '77', 'dob' => '2012-01-02'])->assertOk()->assertSee('class="pc-sd"', false);
        $this->assertFileExists(public_path('images/motto-sd.png'));
    }

    public function test_admit_and_id_card_lists_show_all_active_students_not_just_50(): void
    {
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        for ($i = 1; $i <= 60; $i++) {
            Student::forceCreate(['id' => 'm'.$i, 'gr_number' => (string) $i, 'name' => 'Pupil'.$i.'x', 'class' => 'VI', 'passed_out' => 0]);
        }
        $this->actingAs($admin);
        $this->get('/admin/admit-cards')->assertOk()->assertSee('Pupil60x');
        $this->get('/admin/id-cards')->assertOk()->assertSee('Pupil60x');
    }

    public function test_home_tiles_redirect_by_role_and_guests_go_to_login_then_back(): void
    {
        $this->get('/go/bookbank')->assertRedirect('/login');
        $this->assertSame('/go/bookbank', session('url.intended'));
        $this->get('/go/nothing')->assertNotFound();
        $teacher = User::forceCreate(['name' => 'T', 'email' => 't@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        $this->actingAs($teacher)->get('/go/bookbank')->assertRedirect(route('teacher.bookbank.index'));
        $this->get('/go/attendance')->assertRedirect(route('teacher.attendance.index'));
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin)->get('/go/register')->assertRedirect(route('admin.students.index'));
        $this->get('/')->assertOk()->assertSee('/go/attendance', false)->assertSee('/go/bookbank', false);
    }

    public function test_admin_can_set_student_photo_by_link_and_by_upload(): void
    {
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin);
        $this->post('/admin/students', ['gr_number' => '21', 'name' => 'Link Bacha', 'class' => 'VI', 'photo_link' => 'https://example.com/p.jpg'])->assertSessionHasNoErrors();
        $s = Student::where('gr_number', '21')->first();
        $this->assertSame('https://example.com/p.jpg', $s->photo_url);

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('p.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        $base = ['gr_number' => '21', 'name' => 'Link Bacha', 'class' => 'VI', 'status_choice' => 'active'];
        $this->put('/admin/students/'.$s->id, $base + ['photo_file' => $file])->assertSessionHasNoErrors();
        $s->refresh();
        $this->assertStringStartsWith('/uploads/students/'.$s->id.'.', $s->photo_url);
        @unlink(public_path(strtok(ltrim($s->photo_url, '/'), '?')));

        $this->put('/admin/students/'.$s->id, $base + ['remove_photo' => 1])->assertSessionHasNoErrors();
        $this->assertNull($s->fresh()->photo_url);
        $this->get('/admin/students/'.$s->id.'/edit')->assertOk()->assertSee('photo_file', false);
    }
}
