<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdAdmitCardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::forceCreate([
            'name' => 'Admin', 'email' => 'a@example.com', 'password' => bcrypt('x'),
            'role' => 'admin', 'is_active' => 1,
        ]);
    }

    private function student(): Student
    {
        Student::unguard();

        return Student::create([
            'id' => 's1', 'name' => 'Test Student', 'gr_number' => '412', 'class' => 'VII',
            'section' => 'A', 'dob' => '2012-03-05', 'passed_out' => false,
        ]);
    }

    public function test_student_id_and_admit_pdfs_render(): void
    {
        $s = $this->student();
        $this->actingAs($this->admin());
        foreach ([
            route('admin.id-cards.pdf', ['student' => $s->id], false),
            route('admin.admit-cards.pdf', ['student' => $s->id], false),
            route('admin.admit-cards.class-pdf', ['class' => 'VII'], false),
        ] as $url) {
            $r = $this->get($url)->assertOk();
            $this->assertSame('application/pdf', $r->headers->get('Content-Type'));
        }
    }

    public function test_admit_class_pdf_404_for_unknown_class(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('admin.admit-cards.class-pdf', ['class' => 'ZZ'], false))->assertNotFound();
    }

    public function test_admit_index_lists_class_buttons(): void
    {
        $this->student();
        $this->actingAs($this->admin());
        $this->get(route('admin.admit-cards.index', [], false))->assertOk()->assertSee('Class VII');
    }
}
