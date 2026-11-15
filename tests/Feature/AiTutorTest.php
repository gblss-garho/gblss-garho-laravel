<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiTutorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ai_tutor.api_key' => 'test-key', 'ai_tutor.daily_limit' => 2]);
        Student::forceCreate(['id' => 's1', 'gr_number' => '55', 'name' => 'Ali', 'father_name' => 'Aslam', 'dob' => '2012-01-02', 'class' => 'VII']);
    }

    private function ask(string $dob = '2012-01-02', string $msg = '2+2 kitne hote hain?')
    {
        return $this->postJson('/parent-lookup/ai-tutor', ['gr_number' => '55', 'dob' => $dob, 'message' => $msg]);
    }

    public function test_reply_is_saved_and_quota_counts_down(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => '2+2=4']]])]);
        $this->ask()->assertOk()->assertJson(['reply' => '2+2=4', 'remaining' => 1]);
        $this->assertDatabaseCount('ai_tutor_messages', 2);
        $this->ask()->assertOk()->assertJson(['remaining' => 0]);
        $this->ask()->assertStatus(429);
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => $r->hasHeader('x-api-key', 'test-key') && str_contains($r['system'], 'GBLSS AI Tutor'));
    }

    public function test_wrong_dob_is_rejected_and_no_api_call(): void
    {
        Http::fake();
        $this->ask('2010-05-05')->assertStatus(401);
        Http::assertNothingSent();
    }

    public function test_missing_key_and_api_failure_do_not_burn_quota(): void
    {
        config(['ai_tutor.api_key' => null]);
        $this->ask()->assertStatus(503);
        config(['ai_tutor.api_key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response(['error' => 'x'], 500)]);
        $this->ask()->assertStatus(502);
        $this->assertSame(0, (int) DB::table('ai_tutor_usage')->value('message_count'));
    }

    public function test_card_shows_on_verified_lookup_and_prune_deletes_old_chats(): void
    {
        $this->post('/parent-lookup', ['gr_number' => '55', 'dob' => '2012-01-02'])->assertOk()->assertSee('AI Tutor');
        DB::table('ai_tutor_messages')->insert([
            ['student_id' => 's1', 'role' => 'user', 'content' => 'old', 'created_at' => now()->subDays(31)],
            ['student_id' => 's1', 'role' => 'user', 'content' => 'new', 'created_at' => now()->subDays(2)],
        ]);
        $this->artisan('ai-tutor:prune')->assertSuccessful();
        $this->assertDatabaseMissing('ai_tutor_messages', ['content' => 'old']);
        $this->assertDatabaseHas('ai_tutor_messages', ['content' => 'new']);
    }
}
