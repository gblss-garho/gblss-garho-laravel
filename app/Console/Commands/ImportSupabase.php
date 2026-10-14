<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportSupabase extends Command
{
    protected $signature = 'import:supabase {--dry : only count, write nothing}';
    protected $description = 'Import students, teachers and classes from live Supabase';

    private function fetch(string $table): array
    {
        $url = rtrim((string) env('SUPABASE_URL'), '/');
        $key = (string) env('SUPABASE_SERVICE_KEY');
        $res = Http::withHeaders(['apikey' => $key, 'Authorization' => 'Bearer ' . $key])
            ->get($url . '/rest/v1/' . $table, ['select' => '*', 'limit' => 5000]);
        if (! $res->ok()) {
            $this->error($table . ' failed: HTTP ' . $res->status());
            return [];
        }
        return $res->json();
    }

    private function date($v): ?string
    {
        if (! $v) {
            return null;
        }
        try {
            return Carbon::parse($v)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function handle(): int
    {
        if (! env('SUPABASE_URL') || ! env('SUPABASE_SERVICE_KEY')) {
            $this->error('SUPABASE_URL / SUPABASE_SERVICE_KEY missing in .env');
            return 1;
        }
        $students = $this->fetch('students');
        $teachers = $this->fetch('teachers');
        $classes = $this->fetch('teacher_assigned_classes');
        $faces = collect($this->fetch('teacher_face_enrollment'))->keyBy('teacher_id');
        $this->info('students=' . count($students) . ' teachers=' . count($teachers) . ' classes=' . count($classes) . ' faces=' . count($faces));
        if ($this->option('dry')) {
            return 0;
        }
        $now = now();

        foreach ($students as $s) {
            DB::table('students')->updateOrInsert(['id' => $s['id']], [
                'gr_number' => $s['gr_number'],
                'name' => $s['name'],
                'father_name' => $s['father_name'],
                'dob' => $this->date($s['dob']),
                'class' => $s['class'] ?? '',
                'section' => $s['section'],
                'address' => $s['address'],
                'guardian_phone' => $s['guardian_phone'],
                'photo_url' => $s['photo_url'],
                'admission_date' => $this->date($s['admission_date']),
                'passed_out' => (int) ($s['passed_out'] ?? 0),
                'pass_out_year' => $s['pass_out_year'],
                'status' => $s['status'],
                'results_class_override' => $s['results_class_override'],
                'updated_at' => $now,
            ]);
        }

        foreach ($teachers as $t) {
            $uid = null;
            if (! empty($t['auth_email'])) {
                $email = strtolower(trim($t['auth_email']));
                $user = DB::table('users')->where('email', $email)->first();
                if ($user) {
                    $uid = $user->id;
                } else {
                    $uid = DB::table('users')->insertGetId([
                        'name' => $t['name'],
                        'email' => $email,
                        'role' => 'teacher',
                        'is_active' => 1,
                        'password' => Hash::make($t['portal_pin'] ?: Str::random(16)),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
            DB::table('teachers')->updateOrInsert(['id' => $t['id']], [
                'user_id' => $uid,
                'name' => $t['name'],
                'cnic' => $t['cnic'],
                'pid' => $t['pid'],
                'dob' => $this->date($t['dob']),
                'subject' => $t['subject'],
                'designation' => $t['designation'],
                'qualification' => $t['qualification'],
                'experience' => $t['experience'],
                'entry_in_service' => $this->date($t['entry_in_service']),
                'photo_url' => $t['photo_url'],
                'auth_email' => $t['auth_email'],
                'intro_line' => $t['intro_line'],
                'syllabus_plan' => $t['syllabus_plan'],
                'progress_notes' => $t['progress_notes'],
                'school_name' => $t['school_name'],
                'face_descriptor' => isset($faces[$t['id']]) ? json_encode($faces[$t['id']]['descriptor']) : null,
                'updated_at' => $now,
            ]);
        }

        foreach ($classes as $c) {
            DB::table('teacher_classes')->updateOrInsert(
                ['teacher_id' => $c['teacher_id'], 'class_name' => $c['class']],
                ['updated_at' => $now]
            );
        }

        $this->info('Import done.');
        return 0;
    }
}
