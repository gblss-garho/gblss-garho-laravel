<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GrRegisterTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::forceCreate([
            'name' => 'Admin Test', 'email' => 'admin@example.test', 'password' => bcrypt('secret-pass'),
            'role' => 'admin', 'is_active' => 1,
        ]);
    }

    public function test_register_lists_old_and_current_students_from_gr_one(): void
    {
        DB::table('gr_register')->insert([
            ['reg_no' => 1, 'name' => 'Old Student One', 'father_name' => 'Old Father', 'removal_cause' => 'to join elsewhere', 'created_at' => now(), 'updated_at' => now()],
            ['reg_no' => 2, 'name' => 'Old Student Two', 'father_name' => 'Old Father 2', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($this->admin());
        $this->get('/admin/gr-register')
            ->assertOk()
            ->assertSee('General Register')
            ->assertSee('Old Student One')
            ->assertSee('Old Student Two');
        $this->get('/admin/dashboard')->assertSee('General Register (GR)');
    }

    public function test_register_requires_admin_login(): void
    {
        $this->get('/admin/gr-register')->assertRedirect();
    }
}
