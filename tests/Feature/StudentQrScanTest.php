<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentQrScanTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function setup2(): array
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 09:30')); // Wednesday, school hours
        $u = User::forceCreate(['name' => 'T', 'email' => 't@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        Teacher::forceCreate(['id' => 'tch0000002', 'user_id' => $u->id, 'name' => 'T', 'auth_email' => 't@example.test', 'assigned_class' => 'Five', 'created_at' => now()]);
        $s = Student::create(['id' => 's1', 'gr_number' => '12', 'name' => 'Ali', 'class' => 'five ', 'passed_out' => 0, 'admission_date' => '2024-04-01']);

        return [$u, $s];
    }

    public function test_scan_by_qr_code_and_by_legacy_id_text(): void
    {
        [$u, $s] = $this->setup2();
        $this->actingAs($u);
        $code = $s->ensureQrCode();

        $this->postJson('/teacher/qr-scan', ['qr_code' => strtolower($code)])->assertOk()->assertJsonPath('status', 'marked');
        $this->postJson('/teacher/qr-scan', ['qr_code' => 'GBLSS-GARHO-2024-012'])->assertOk()->assertJsonPath('status', 'already_present');
    }
}
