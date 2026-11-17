<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\GeoFenceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeoFenceTest extends TestCase
{
    use RefreshDatabase;

    private const LAT = 25.2000;
    private const LNG = 68.2000;

    private function teacher(): array
    {
        $u = User::forceCreate(['name' => 'T One', 'email' => 'g1@example.test', 'password' => bcrypt('x'), 'role' => 'teacher', 'is_active' => 1]);
        $t = Teacher::forceCreate(['id' => 'tchgeo0001', 'user_id' => $u->id, 'name' => 'T One', 'auth_email' => 'g1@example.test', 'created_at' => '2026-01-01 00:00:00']);
        $t->face_descriptor = json_encode(array_fill(0, 128, 0.1));
        $t->save();

        return [$u, $t];
    }

    private function school(?float $lat = self::LAT, ?float $lng = self::LNG): void
    {
        DB::table('school_info')->updateOrInsert(['id' => 1], ['name' => 'GBLSS Garho', 'school_lat' => $lat, 'school_lng' => $lng, 'attendance_radius' => 200]);
    }

    public function test_distance_helper(): void
    {
        $this->assertEqualsWithDelta(200, GeoFenceService::distanceMeters(self::LAT, self::LNG, self::LAT + 0.0018, self::LNG), 3);
        $this->assertLessThan(1, GeoFenceService::distanceMeters(self::LAT, self::LNG, self::LAT, self::LNG));
    }

    public function test_checkin_outside_radius_is_blocked_with_sindhi_message(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 08:40'));
        [$u, $t] = $this->teacher();
        $this->school();

        $r = $this->actingAs($u)->post('/teacher/attendance/checkin', ['lat' => self::LAT + 0.01, 'lng' => self::LNG, 'acc' => 20]);
        $r->assertSessionHasErrors('geo');
        $this->assertStringContainsString('اسڪول جي جڳهه کان ٻاهر', session('errors')->first('geo'));
        $this->assertSame(0, TeacherAttendance::count());

        $this->post('/teacher/attendance/checkin', [])->assertSessionHasErrors('geo'); // no location sent
        $this->post('/teacher/attendance/checkin', ['lat' => self::LAT, 'lng' => self::LNG, 'acc' => 400])->assertSessionHasErrors('geo'); // weak GPS

        Carbon::setTestNow();
    }

    public function test_inside_radius_passes_geo_step_and_unconfigured_school_skips_it(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 08:40'));
        [$u] = $this->teacher();

        // not configured -> geo step is skipped (the request then stops later at liveness, not at geo)
        $this->school(null, null);
        $this->actingAs($u)->post('/teacher/attendance/checkin', [])->assertSessionDoesntHaveErrors('geo');

        // configured and inside 200 m -> geo ok as well
        $this->school();
        $this->post('/teacher/attendance/checkin', ['lat' => self::LAT + 0.0005, 'lng' => self::LNG, 'acc' => 15])->assertSessionDoesntHaveErrors('geo');

        Carbon::setTestNow();
    }

    public function test_scanner_gets_geo_attributes_and_admin_settings_has_button(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 08:40'));
        [$u] = $this->teacher();
        $this->school();
        $this->actingAs($u)->get('/teacher/attendance')->assertOk()->assertSee('data-geo-lat', false)->assertSee('data-geo-radius="200"', false);
        Carbon::setTestNow();

        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('x'), 'role' => 'admin', 'is_active' => 1]);
        $this->actingAs($admin)->get('/admin/settings')->assertOk()->assertSee('useMyLoc', false);
    }
}
