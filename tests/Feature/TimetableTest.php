<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
    }

    private function teacher(string $id, string $name, ?User $u = null): Teacher
    {
        return Teacher::forceCreate(['id' => $id, 'name' => $name, 'user_id' => $u?->id]);
    }

    public function test_admin_pages_open_and_assignment_saves(): void
    {
        $this->actingAs($this->admin());
        $t = $this->teacher('t1', 'Sir Ali');

        $this->get('/admin/teaching-assignments?class=V')->assertOk()->assertSee('Teaching Assignment');
        $this->post('/admin/teaching-assignments', ['class_name' => 'V', 'subject' => ' Mathematics ', 'teacher_id' => 't1'])
            ->assertRedirect();
        $this->assertDatabaseHas('teaching_assignments', ['class_name' => 'V', 'subject' => 'Mathematics', 'teacher_id' => 't1']);
        $this->get('/admin/timetable?class=V')->assertOk()->assertSee('Mathematics');
    }

    public function test_timetable_saves_five_days_seven_periods_and_times(): void
    {
        $this->actingAs($this->admin());
        $this->teacher('t1', 'Sir Ali');
        $a = TeachingAssignment::create(['class_name' => 'V', 'subject' => 'English', 'teacher_id' => 't1']);

        $this->post('/admin/timetable', [
            'class_name' => 'V',
            'cell' => [1 => [1 => $a->id, 7 => $a->id], 5 => [3 => $a->id]],
            'times' => [1 => ['starts_at' => '08:00', 'ends_at' => '08:40']],
        ])->assertRedirect();

        $this->assertSame(3, TimetableEntry::where('class_name', 'V')->count());
        $this->assertDatabaseHas('timetable_entries', ['class_name' => 'V', 'day' => 5, 'period' => 3, 'subject' => 'English', 'teacher_id' => 't1']);
        $this->assertDatabaseHas('timetable_periods', ['period' => 1, 'starts_at' => '08:00', 'ends_at' => '08:40']);
        // Saturday / Sunday / an 8th period do not exist in the grid.
        $this->get('/admin/timetable?class=V')->assertOk()->assertDontSee('Saturday')->assertDontSee('Sunday');
    }

    public function test_teacher_clash_is_blocked_with_a_reason(): void
    {
        $this->actingAs($this->admin());
        $this->teacher('t1', 'Sir Ali');
        $a5 = TeachingAssignment::create(['class_name' => 'V', 'subject' => 'English', 'teacher_id' => 't1']);
        $a6 = TeachingAssignment::create(['class_name' => 'VI', 'subject' => 'English', 'teacher_id' => 't1']);

        $this->post('/admin/timetable', ['class_name' => 'V', 'cell' => [2 => [4 => $a5->id]]])->assertRedirect();
        $this->post('/admin/timetable', ['class_name' => 'VI', 'cell' => [2 => [4 => $a6->id]]])
            ->assertSessionHasErrors('grid');
        $this->assertSame(0, TimetableEntry::where('class_name', 'VI')->count());
    }

    public function test_cell_from_another_class_assignment_is_rejected(): void
    {
        $this->actingAs($this->admin());
        $this->teacher('t1', 'Sir Ali');
        $other = TeachingAssignment::create(['class_name' => 'VI', 'subject' => 'Urdu', 'teacher_id' => 't1']);

        $this->post('/admin/timetable', ['class_name' => 'V', 'cell' => [1 => [1 => $other->id]]])->assertSessionHasErrors('grid');
        $this->assertSame(0, TimetableEntry::count());
    }

    public function test_changing_teacher_moves_timetable_cells_and_delete_clears_them(): void
    {
        $this->actingAs($this->admin());
        $this->teacher('t1', 'Sir Ali');
        $this->teacher('t2', 'Miss Sara');
        $a = TeachingAssignment::create(['class_name' => 'V', 'subject' => 'Science', 'teacher_id' => 't1']);
        TimetableEntry::create(['class_name' => 'V', 'day' => 1, 'period' => 2, 'subject' => 'Science', 'teacher_id' => 't1']);

        $this->post('/admin/teaching-assignments', ['class_name' => 'V', 'subject' => 'Science', 'teacher_id' => 't2']);
        $this->assertDatabaseHas('timetable_entries', ['class_name' => 'V', 'subject' => 'Science', 'teacher_id' => 't2']);

        $this->delete('/admin/teaching-assignments/'.$a->id);
        $this->assertSame(0, TimetableEntry::count());
    }

    public function test_teacher_sees_only_own_periods_and_public_page_is_open(): void
    {
        $u = User::forceCreate(['name' => 'T', 'email' => 't@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        $this->teacher('t1', 'Sir Ali', $u);
        $this->teacher('t2', 'Miss Sara');
        TimetableEntry::create(['class_name' => 'V', 'day' => 1, 'period' => 1, 'subject' => 'Maths', 'teacher_id' => 't1']);
        TimetableEntry::create(['class_name' => 'VI', 'day' => 1, 'period' => 2, 'subject' => 'Zoology', 'teacher_id' => 't2']);

        $this->get('/timetable?class=V')->assertOk()->assertSee('Maths')->assertSee('Sir Ali')->assertDontSee('Zoology');

        $this->actingAs($u)->get('/teacher/timetable')->assertOk()->assertSee('Maths')->assertSee('Class V')->assertDontSee('Zoology');
    }

    public function test_non_admin_cannot_edit(): void
    {
        $u = User::forceCreate(['name' => 'T', 'email' => 't@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        $this->actingAs($u)->get('/admin/timetable')->assertForbidden();
        $this->post('/admin/timetable', ['class_name' => 'V'])->assertForbidden();
    }
}
