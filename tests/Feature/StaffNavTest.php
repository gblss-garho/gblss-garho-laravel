<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_see_own_and_public_tabs(): void
    {
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x-pass-1'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()
            ->assertSee('GR Register')->assertSee('Book Bank')->assertSee('Alumni')->assertSee('Parents Corner');

        $teacher = User::forceCreate(['name' => 'T', 'email' => 't@example.test', 'password' => bcrypt('x-pass-1'), 'role' => 'teacher', 'is_active' => 1]);
        $this->actingAs($teacher)->get('/teacher/dashboard')->assertOk()
            ->assertSee('Chhutti')->assertSee('Leaving Cert.')->assertSee('Calendar');
    }
}
