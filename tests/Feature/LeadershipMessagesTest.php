<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeadershipMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_minister_and_secretary_messages_show_when_set(): void
    {
        DB::table('school_info')->updateOrInsert(['id' => 1], [
            'name' => 'GBLSS Garho',
            'minister_name' => 'Test Minister', 'minister_message' => 'Minister test message',
            'secretary_name' => 'Test Secretary', 'secretary_message' => 'Secretary test message',
        ]);
        $this->get('/')->assertOk()->assertSee('Minister test message')->assertSee('Secretary test message');
        $this->get('/about')->assertOk()->assertSee('Test Secretary');
    }
}
