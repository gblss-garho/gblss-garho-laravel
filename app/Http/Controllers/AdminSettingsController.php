<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    private array $fields = [
        'name', 'urdu_name', 'motto', 'address', 'phone', 'email', 'semis_code',
        'established_year', 'total_classes', 'current_exam_title', 'current_exam_year',
        'school_lat', 'school_lng', 'attendance_radius',
        'time_in_window_start', 'time_in_window_end',
        'time_out_window_start', 'time_out_window_end', 'weekly_off_days',
    ];

    public function edit()
    {
        $s = DB::table('school_info')->where('id', 1)->first();
        return view('admin.settings', ['s' => $s, 'fields' => $this->fields]);
    }

    public function update(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:150',
            'time_in_window_start' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'time_in_window_end' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'time_out_window_start' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'time_out_window_end' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'weekly_off_days' => ['required', 'regex:/^[0-6](,[0-6])*$/'],
        ]);
        $data = [];
        foreach ($this->fields as $f) {
            $data[$f] = $r->input($f);
        }
        $data['updated_at'] = now();
        if (DB::table('school_info')->where('id', 1)->exists()) {
            DB::table('school_info')->where('id', 1)->update($data);
        } else {
            DB::table('school_info')->insert($data + ['id' => 1, 'created_at' => now()]);
        }
        return redirect()->route('admin.settings.edit')->with('ok', 'Settings save ho gayin.');
    }
}
