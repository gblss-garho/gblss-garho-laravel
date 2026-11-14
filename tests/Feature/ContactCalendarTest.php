<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContactCalendarTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x-pass-1'), 'role' => 'admin', 'is_active' => 1]);
    }

    public function test_contact_page_and_message_submit(): void
    {
        $this->get('/contact')->assertOk()->assertSee('Send us a message');
        $this->post('/contact', ['name' => 'Ali', 'phone' => '0300-1234567', 'subject' => 'Fee question', 'message' => 'Fee kab jama karwani hai?'])
            ->assertRedirect('/contact');
        $this->assertDatabaseHas('contact_messages', ['name' => 'Ali', 'subject' => 'Fee question']);
    }

    public function test_contact_validation_and_honeypot(): void
    {
        $this->post('/contact', ['name' => '', 'phone' => '', 'subject' => 'Nope', 'message' => ''])->assertSessionHasErrors(['name', 'phone', 'subject', 'message']);
        $this->post('/contact', ['name' => 'Bot', 'phone' => '1', 'subject' => 'Other', 'message' => 'spam', 'website' => 'http://spam'])->assertRedirect('/contact');
        $this->assertDatabaseMissing('contact_messages', ['name' => 'Bot']);
    }

    public function test_admin_inbox_is_admin_only_and_can_delete(): void
    {
        DB::table('contact_messages')->insert(['id' => 'm1', 'name' => 'Sara', 'phone' => '1', 'subject' => 'Other', 'message' => 'Hello', 'created_at' => now(), 'updated_at' => now()]);
        $this->get('/admin/contact')->assertRedirect();
        $this->actingAs($this->admin());
        $this->get('/admin/contact')->assertOk()->assertSee('Sara');
        $this->delete('/admin/contact/m1')->assertRedirect('/admin/contact');
        $this->assertDatabaseMissing('contact_messages', ['id' => 'm1']);
    }

    public function test_calendar_lists_events(): void
    {
        DB::table('calendar_events')->insert([
            ['id' => 'e1', 'date' => now()->addDays(5)->toDateString(), 'type' => 'holiday', 'title' => 'Future Holiday', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'e2', 'date' => now()->subDays(5)->toDateString(), 'type' => 'celebration', 'title' => 'Old Celebration', 'created_at' => now(), 'updated_at' => now()],
        ]);
        $this->get('/calendar')->assertOk()->assertSee('Future Holiday')->assertSee('Old Celebration')->assertSee('Celebration Day');
    }

    public function test_nav_links_present(): void
    {
        $this->get('/')->assertSee('/contact', false)->assertSee('/calendar', false);
    }
}
