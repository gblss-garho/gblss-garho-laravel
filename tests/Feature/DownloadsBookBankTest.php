<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadsBookBankTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): User
    {
        return User::forceCreate(['name' => $role, 'email' => $email, 'password' => bcrypt('x'), 'role' => $role, 'is_active' => 1]);
    }

    private function teacher(string $classes): User
    {
        $u = $this->user('teacher', 't@example.test');
        Teacher::forceCreate(['id' => 'tchbook001', 'user_id' => $u->id, 'name' => 'T', 'auth_email' => 't@example.test', 'assigned_class' => $classes, 'created_at' => now()]);

        return $u;
    }

    public function test_admin_uploads_staff_and_public_files_and_access_rules(): void
    {
        Storage::fake('local');
        $this->actingAs($this->user('admin', 'a@example.test'));
        $this->post('/downloads', ['title' => 'Winter Vacation', 'category' => 'Notification', 'audience' => 'public', 'file' => UploadedFile::fake()->create('n.pdf', 50, 'application/pdf')])->assertRedirect();
        $this->post('/downloads', ['title' => 'Staff Only Letter', 'category' => 'Letter', 'audience' => 'staff', 'file' => UploadedFile::fake()->create('l.pdf', 50, 'application/pdf')])->assertRedirect();
        $this->post('/downloads', ['title' => 'Bad', 'category' => 'Letter', 'audience' => 'staff', 'file' => UploadedFile::fake()->create('x.exe', 5)])->assertSessionHasErrors('file');

        $public = DB::table('downloads')->where('audience', 'public')->first();
        $staff = DB::table('downloads')->where('audience', 'staff')->first();
        $this->get('/downloads')->assertOk()->assertSee('Winter Vacation')->assertSee('Staff Only Letter');

        auth()->logout();
        $this->get('/downloads')->assertOk()->assertSee('Winter Vacation')->assertDontSee('Staff Only Letter');
        $this->get('/downloads/'.$public->id.'/file')->assertOk();
        $this->get('/downloads/'.$staff->id.'/file')->assertStatus(403);
    }

    public function test_teacher_book_bank_issue_return_and_class_limits(): void
    {
        Student::create(['id' => 's1', 'gr_number' => '5', 'name' => 'Ali', 'class' => 'Class VI', 'passed_out' => 0]);
        Student::create(['id' => 's2', 'gr_number' => '6', 'name' => 'Other', 'class' => 'Class VII', 'passed_out' => 0]);
        DB::table('bookbank_catalog')->insert(['id' => 'b1', 'class' => 'VI', 'subject' => 'English', 'book_title' => 'English VI', 'available_qty' => 3, 'issued_qty' => 0, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->teacher('Class VI'));
        $this->get('/teacher/bookbank?view=catalog')->assertOk()->assertSee('Book Bank')->assertSee('English VI');

        $this->post('/teacher/bookbank/issue', ['catalog_id' => 'b1', 'student_id' => 's2'])->assertSessionHasErrors('gr_number');
        $this->post('/teacher/bookbank/issue', ['catalog_id' => 'b1', 'student_id' => 's1', 'class' => 'Class VI'])->assertRedirect();
        $this->assertSame(2, (int) DB::table('bookbank_catalog')->where('id', 'b1')->value('available_qty'));

        $issue = DB::table('bookbank_issues')->first();
        $this->get('/teacher/bookbank?class=Class+VI')->assertOk()->assertSee('Ali')->assertSee('Books Distributed');
        $this->post('/teacher/bookbank/issues/'.$issue->id.'/return', ['condition' => 'Fair'])->assertRedirect();
        $this->assertSame(3, (int) DB::table('bookbank_catalog')->where('id', 'b1')->value('available_qty'));

        // teacher cannot touch the catalog
        $this->post('/admin/bookbank/catalog', ['class' => 'VI', 'subject' => 'x', 'book_title' => 'y', 'available_qty' => 1])->assertStatus(403);
    }

    public function test_book_bank_matrix_distribute_and_collect(): void
    {
        Student::create(['id' => 's1', 'gr_number' => '5', 'name' => 'Ali', 'class' => 'Class VI', 'passed_out' => 0]);
        foreach ([['b1', 'English', 'English VI'], ['b2', 'Urdu', 'Urdu VI']] as [$id, $sub, $title]) {
            DB::table('bookbank_catalog')->insert(['id' => $id, 'class' => 'VI', 'subject' => $sub, 'book_title' => $title, 'available_qty' => 2, 'issued_qty' => 0, 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->actingAs($this->teacher('Class VI'));

        $this->post('/teacher/bookbank/matrix', ['student_id' => 's1', 'dist' => ['English', 'Urdu']])->assertRedirect();
        $this->assertSame(1, (int) DB::table('bookbank_catalog')->where('id', 'b1')->value('available_qty'));
        $this->assertSame(2, DB::table('bookbank_issues')->where('status', 'Issued')->count());

        $this->post('/teacher/bookbank/matrix', ['student_id' => 's1', 'dist' => ['English', 'Urdu'], 'coll' => ['English']])->assertRedirect();
        $this->assertSame(2, (int) DB::table('bookbank_catalog')->where('id', 'b1')->value('available_qty'));
        $this->assertSame('Returned', DB::table('bookbank_issues')->where('book_title', 'English VI')->value('status'));
        $this->get('/teacher/bookbank?class=Class+VI')->assertOk()->assertSee('Ali')->assertSee('✓', false);

        $this->post('/teacher/bookbank/matrix', ['student_id' => 's1'])->assertRedirect();
        $this->assertSame(0, DB::table('bookbank_issues')->count());
        $this->assertSame(2, (int) DB::table('bookbank_catalog')->where('id', 'b2')->value('available_qty'));
    }

    public function test_qr_scan_page_has_round_scanner(): void
    {
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-11-18 09:30'));
        $this->actingAs($this->teacher('Class VI'));
        $this->get('/teacher/qr-scan')->assertOk()->assertSee('fs-qr', false)->assertSee('اسڪين مڪمل ٿي وئي');
        \Carbon\Carbon::setTestNow();
    }
}
