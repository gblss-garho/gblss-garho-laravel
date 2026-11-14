<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent(): void
    {
        $r = $this->get('/');
        $r->assertOk();
        $r->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_hsts_only_over_https(): void
    {
        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security');
    }

    public function test_login_locks_after_five_bad_attempts(): void
    {
        User::forceCreate([
            'name' => 'Admin Test', 'email' => 'lock@example.test', 'password' => bcrypt('right-pass'),
            'role' => 'admin', 'is_active' => 1,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'lock@example.test', 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }

        // Even the correct password is refused while locked.
        $this->post('/login', ['email' => 'lock@example.test', 'password' => 'right-pass'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_good_login_still_works_and_clears_counter(): void
    {
        User::forceCreate([
            'name' => 'Admin Test', 'email' => 'ok@example.test', 'password' => bcrypt('right-pass'),
            'role' => 'admin', 'is_active' => 1,
        ]);

        $this->post('/login', ['email' => 'ok@example.test', 'password' => 'wrong']);
        $this->post('/login', ['email' => 'ok@example.test', 'password' => 'right-pass'])
            ->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }
}
