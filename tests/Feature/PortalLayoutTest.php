<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::forceCreate([
            'name' => ucfirst($role) . ' Test',
            'email' => $role . '@example.test',
            'password' => bcrypt('secret-pass'),
            'role' => $role,
            'is_active' => 1,
        ]);
    }

    public function test_login_page_uses_school_design(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Staff Login')
            ->assertSee('site-head', false);
    }

    public function test_parents_corner_page_renders(): void
    {
        $this->get('/parent-lookup')
            ->assertOk()
            ->assertSee('Parents Corner')
            ->assertSee('gr_number', false);
    }

    public function test_teacher_pages_render_with_nav_and_logout(): void
    {
        $this->actingAs($this->user('teacher'));

        foreach (['/teacher/dashboard', '/teacher/attendance', '/teacher/face-enroll', '/teacher/qr-scan', '/teacher/absentees', '/teacher/homework', '/teacher/leave'] as $url) {
            $this->get($url)->assertOk()->assertSee('site-head', false)->assertSee('Logout');
        }

        $this->get('/teacher/dashboard')->assertSee('Chehra Enroll Karen')->assertSee('QR Attendance');
    }

    public function test_admin_dashboard_renders(): void
    {
        $this->actingAs($this->user('admin'));

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Admin Dashboard')
            ->assertSee('Security Logs');
    }

    public function test_admin_pages_use_portal_layout(): void
    {
        $this->actingAs($this->user('admin'));

        foreach (['admin.students.index', 'admin.teachers.index', 'admin.results.index', 'admin.results.history', 'admin.content.index',
                  'admin.settings.edit', 'admin.bookbank.index', 'admin.leave.index', 'admin.media.index', 'admin.security.index',
                  'admin.leaving-certificates.index', 'admin.id-cards.index', 'admin.admit-cards.index', 'admin.staff-id-cards.index'] as $name) {
            $this->get(route($name, [], false))->assertOk()->assertSee('site-head', false);
        }
    }
}
