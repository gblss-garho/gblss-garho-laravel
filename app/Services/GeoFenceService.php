<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Time In/Out is accepted only within a radius (default 200 m) of the school's saved location. */
class GeoFenceService
{
    private const MAX_ACCURACY_M = 150;

    /** @return array{lat: float, lng: float, radius: int}|null  null = school location not configured (no check) */
    public function config(): ?array
    {
        $s = DB::table('school_info')->where('id', 1)->first();
        if (! $s || ! is_numeric($s->school_lat ?? null) || ! is_numeric($s->school_lng ?? null)) {
            return null;
        }

        return [
            'lat' => (float) $s->school_lat,
            'lng' => (float) $s->school_lng,
            'radius' => max(20, (int) ($s->attendance_radius ?: 200)),
        ];
    }

    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $p1 = deg2rad($lat1);
        $p2 = deg2rad($lat2);
        $dp = $p2 - $p1;
        $dl = deg2rad($lng2 - $lng1);
        $a = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }

    /**
     * Strict location check. Attendance is refused when the school location is not set,
     * when the phone sends no usable GPS reading, or when the reading is outside the radius.
     *
     * @return array{error: ?string, reason: ?string, reading: array}
     */
    public function inspect(Request $request): array
    {
        $cfg = $this->config();
        if ($cfg === null) {
            return $this->deny('geo_school_not_set', 'اسڪول جي جڳهه (لوڪيشن) اڃا سيٽ ناهي، تنھنڪري حاضري لڳي نٿي سگهي. ايڊمن کي چئو ته Settings ۾ اسڪول جي لوڪيشن سيٽ ڪري.');
        }

        $lat = $request->input('lat');
        $lng = $request->input('lng');
        if (! is_numeric($lat) || ! is_numeric($lng) || abs((float) $lat) > 90 || abs((float) $lng) > 180) {
            return $this->deny('geo_missing', 'پنھنجي فون جي لوڪيشن (GPS) جي اجازت ڏيو ۽ ٻيهر ڪوشش ڪريو.');
        }

        $acc = $request->input('acc');
        if (! is_numeric($acc) || (float) $acc < 0 || (float) $acc > self::MAX_ACCURACY_M) {
            return $this->deny('geo_weak_gps', 'GPS جي درستگي ڪمزور آهي. کليل جاءِ تي اچي ٻيهر ڪوشش ڪريو.');
        }

        $d = (int) round(self::distanceMeters((float) $lat, (float) $lng, $cfg['lat'], $cfg['lng']));
        $reading = [
            'lat' => round((float) $lat, 7), 'lng' => round((float) $lng, 7),
            'acc' => (int) round((float) $acc), 'dist' => $d,
        ];
        if ($d > $cfg['radius']) {
            return $this->deny('geo_outside_'.$d.'m', 'اوهان هن وقت اسڪول جي جڳهه کان ٻاهر آهيو. حاضري رڳو اسڪول جي ' . $cfg['radius'] . ' ميٽرن اندر لڳي سگهي ٿي (اوهان اسڪول کان تقريبن ' . $d . ' ميٽر پري آهيو).', $reading);
        }

        return ['error' => null, 'reason' => null, 'reading' => $reading];
    }

    /** Sindhi error text, or null when the teacher may mark attendance. */
    public function check(Request $request): ?string
    {
        return $this->inspect($request)['error'];
    }

    private function deny(string $reason, string $message, array $reading = []): array
    {
        return ['error' => $message, 'reason' => $reason, 'reading' => $reading];
    }
}
