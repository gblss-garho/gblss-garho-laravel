<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ElibraryKidsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::forceCreate(['name' => 'U', 'email' => $role.'@example.test', 'password' => bcrypt('x-pass-1'), 'role' => $role, 'is_active' => 1]);
    }

    public function test_pages_render_and_data_file_is_valid(): void
    {
        $this->get('/e-library')->assertOk()->assertSee('Story')->assertSee('Song');
        $this->get('/kids-corner')->assertOk()->assertSee('Dictionary')->assertSee('Math Puzzle');
        $j = json_decode(file_get_contents(public_path('js/kids-data.json')), true);
        $this->assertGreaterThan(100, count($j['dict']));
        $this->assertCount(20, $j['quiz']);
    }

    public function test_public_cannot_add_but_staff_can_add_edit_delete(): void
    {
        $this->post('/e-library', ['type' => 'story', 'title' => 'X'])->assertRedirect();
        $this->assertDatabaseCount('funtime_items', 0);

        $this->actingAs($this->user('teacher'));
        $this->post('/e-library', ['type' => 'story', 'title' => 'Billi', 'content' => 'Ek thi billi'])->assertRedirect('/e-library?type=story');
        $id = DB::table('funtime_items')->value('id');
        $this->get('/e-library?type=story')->assertOk()->assertSee('Billi')->assertSee('Edit');
        $this->post('/e-library', ['id' => $id, 'type' => 'story', 'title' => 'Billi 2'])->assertRedirect();
        $this->assertDatabaseHas('funtime_items', ['id' => $id, 'title' => 'Billi 2']);
        $this->post('/e-library', ['type' => 'video', 'title' => 'V', 'media_url' => 'javascript:alert(1)'])->assertSessionHasErrors('media_url');
        $this->delete('/e-library/'.$id)->assertRedirect();
        $this->assertDatabaseCount('funtime_items', 0);
    }

    public function test_youtube_embed_only_for_youtube(): void
    {
        $f = \App\Http\Controllers\ElibraryController::class;
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $f::youtubeEmbed('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertNull($f::youtubeEmbed('https://evil.example/x'));
    }
}
