<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    /** Text fields saved as-is (the school timing windows now follow the Sindh season rule, see SchoolScheduleService). */
    private array $fields = [
        'name', 'urdu_name', 'school_photo_url', 'motto', 'about_text',
        'established_year', 'semis_code', 'address', 'map_location', 'phone', 'email', 'total_classes',
        'school_lat', 'school_lng', 'attendance_radius',
        'principal_name', 'principal_photo_url', 'principal_message',
        'teo_name', 'teo_photo_url', 'teo_message',
        'deo_name', 'deo_photo_url', 'deo_message',
        'current_exam_title', 'current_exam_year',
    ];

    public function edit()
    {
        $s = DB::table('school_info')->where('id', 1)->first();
        $off = array_map('intval', array_filter(explode(',', (string) ($s->weekly_off_days ?? '0,6')), fn ($x) => trim($x) !== ''));

        return view('admin.settings', ['s' => $s, 'off' => $off]);
    }

    public function update(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:150',
            'urdu_name' => 'nullable|string|max:200',
            'motto' => 'nullable|string|max:300',
            'about_text' => 'nullable|string|max:5000',
            'school_photo_url' => 'nullable|string|max:500',
            'principal_photo_url' => 'nullable|string|max:500',
            'teo_photo_url' => 'nullable|string|max:500',
            'deo_photo_url' => 'nullable|string|max:500',
            'principal_message' => 'nullable|string|max:3000',
            'teo_message' => 'nullable|string|max:3000',
            'deo_message' => 'nullable|string|max:3000',
            'established_year' => 'nullable|digits:4',
            'total_classes' => 'nullable|string|max:60',
            'school_lat' => 'nullable|numeric|between:-90,90',
            'school_lng' => 'nullable|numeric|between:-180,180',
            'attendance_radius' => 'nullable|integer|min:20|max:5000',
            'email' => 'nullable|email|max:150',
            'off' => 'required|array|min:1',
            'off.*' => 'integer|between:0,6',
        ], [
            'off.required' => 'Kam az kam ek haftawar chhutti ka din chunein.',
            'off.min' => 'Kam az kam ek haftawar chhutti ka din chunein.',
        ]);

        $data = [];
        foreach ($this->fields as $f) {
            $v = $r->input($f);
            $data[$f] = ($v === null || trim((string) $v) === '') ? null : trim((string) $v);
        }
        $data['weekly_off_days'] = implode(',', collect($r->input('off', []))->map(fn ($d) => (int) $d)->unique()->sort()->values()->all());
        $data['updated_at'] = now();

        if (DB::table('school_info')->where('id', 1)->exists()) {
            DB::table('school_info')->where('id', 1)->update($data);
        } else {
            DB::table('school_info')->insert($data + ['id' => 1, 'created_at' => now()]);
        }

        return redirect()->route('admin.settings.edit')->with('ok', 'Settings save ho gayin.');
    }
}
