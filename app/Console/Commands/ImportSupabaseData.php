<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ImportSupabaseData extends Command
{
    protected $signature = 'import:supabase-data {--dry : only count, write nothing}';
    protected $description = 'Import remaining live Supabase data (attendance, results, notices, etc.)';

    private array $colCache = [];

    private function fetch(string $table): array
    {
        $key = env('SUPABASE_SERVICE_KEY');
        $rows = [];
        $off = 0;
        do {
            $r = Http::withHeaders(['apikey' => $key, 'Authorization' => 'Bearer ' . $key])
                ->get(env('SUPABASE_URL') . '/rest/v1/' . $table, [
                    'select' => '*', 'order' => 'id', 'limit' => 1000, 'offset' => $off,
                ]);
            if (! $r->ok()) {
                throw new \RuntimeException($table . ': HTTP ' . $r->status());
            }
            $chunk = $r->json();
            $rows = array_merge($rows, $chunk);
            $off += 1000;
        } while (count($chunk) === 1000);
        return $rows;
    }

    private function d($v): ?string
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

    private function dt($v): ?string
    {
        if (! $v) {
            return null;
        }
        try {
            return Carbon::parse($v)->setTimezone(config('app.timezone'))->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function put(string $t, array $key, array $d): void
    {
        $c = $this->colCache[$t] ??= Schema::getColumnListing($t);
        $now = now()->toDateTimeString();
        foreach (['created_at', 'updated_at'] as $x) {
            if (in_array($x, $c) && ! array_key_exists($x, $d)) {
                $d[$x] = $now;
            }
        }
        $d = array_intersect_key($d, array_flip($c));
        DB::table($t)->updateOrInsert($key, $d);
    }

    private function runSet(string $label, array $rows, callable $fn): void
    {
        $ok = 0;
        $skip = 0;
        foreach ($rows as $r) {
            try {
                if ($fn($r) === false) {
                    $skip++;
                } else {
                    $ok++;
                }
            } catch (\Throwable $e) {
                $skip++;
                if ($skip <= 3) {
                    $this->warn($label . ' skip: ' . substr($e->getMessage(), 0, 120));
                }
            }
        }
        $this->info(str_pad($label, 24) . 'ok=' . $ok . ' skipped=' . $skip);
    }

    public function handle(): int
    {
        if (! env('SUPABASE_URL') || ! env('SUPABASE_SERVICE_KEY')) {
            $this->error('SUPABASE_URL / SUPABASE_SERVICE_KEY missing in .env');
            return 1;
        }
        $tables = ['attendance_records', 'attendance_marks', 'teacher_attendance', 'student_results',
            'notices', 'calendar_events', 'homework', 'feedback', 'gallery', 'leave_requests',
            'textbooks', 'leaving_certificates', 'school_info'];
        $data = [];
        foreach ($tables as $t) {
            $data[$t] = $this->fetch($t);
            $this->line(str_pad($t, 24) . count($data[$t]));
        }
        if ($this->option('dry')) {
            return 0;
        }

        $students = DB::table('students')->pluck('class', 'id')->all();
        $grMap = DB::table('students')->pluck('id', 'gr_number')->all();
        $teachers = DB::table('teachers')->pluck('id')->flip()->all();
        $year = ($data['school_info'][0]['current_exam_year'] ?? null) ?: date('Y');

        $this->runSet('school_info', $data['school_info'], function ($r) {
            $this->put('school_info', ['id' => 1], [
                'name' => $r['name'] ?? 'GBLSS Garho',
                'motto' => $r['motto'] ?? null,
                'address' => $r['address'] ?? null,
                'phone' => $r['phone'] ?? null,
                'email' => $r['email'] ?? null,
                'semis_code' => $r['emis_code'] ?? null,
                'about_text' => $r['about_text'] ?? null,
                'school_lat' => $r['school_lat'] ?? null,
                'school_lng' => $r['school_lng'] ?? null,
                'map_location' => $r['map_location'] ?? null,
                'deo_name' => $r['deo_name'] ?? null,
                'deo_message' => $r['deo_message'] ?? null,
                'deo_photo_url' => $r['deo_photo_url'] ?? null,
                'teo_name' => $r['teo_name'] ?? null,
                'teo_message' => $r['teo_message'] ?? null,
                'teo_photo_url' => $r['teo_photo_url'] ?? null,
                'current_exam_year' => $r['current_exam_year'] ?? null,
                'shifts' => isset($r['shifts']) ? json_encode($r['shifts']) : null,
            ]);
        });

        $recIds = [];
        $this->runSet('attendance_records', $data['attendance_records'], function ($r) use (&$recIds) {
            $this->put('attendance_records', ['id' => $r['id']], [
                'date' => $this->d($r['date']), 'class_name' => $r['class_name'],
            ]);
            $recIds[$r['id']] = true;
        });

        $this->runSet('attendance_marks', $data['attendance_marks'], function ($r) use ($students, $recIds) {
            $s = strtoupper(substr((string) $r['status'], 0, 1));
            if (! isset($recIds[$r['attendance_id']]) || ! isset($students[$r['student_id']]) || ! in_array($s, ['P', 'A', 'L'])) {
                return false;
            }
            $this->put('attendance_marks', [
                'attendance_record_id' => $r['attendance_id'], 'student_id' => $r['student_id'],
            ], ['status' => $s, 'marked_via' => 'manual']);
        });

        $this->runSet('teacher_attendance', $data['teacher_attendance'], function ($r) use ($teachers) {
            if (! isset($teachers[$r['teacher_id']])) {
                return false;
            }
            $this->put('teacher_attendance', [
                'teacher_id' => $r['teacher_id'], 'date' => $this->d($r['date']),
            ], [
                'check_in_at' => $this->dt($r['time_in'] ?? null),
                'check_out_at' => $this->dt($r['time_out'] ?? null),
                'check_in_method' => $r['time_in_method'] ?? null,
                'check_out_method' => $r['time_out_method'] ?? null,
            ]);
        });

        $this->runSet('student_results', $data['student_results'], function ($r) use ($students, $year) {
            if (! isset($students[$r['student_id']])) {
                return false;
            }
            $this->put('student_results', [
                'student_id' => $r['student_id'], 'academic_year' => $year,
                'exam_type' => $r['exam_type'], 'subject' => $r['subject'],
            ], ['class' => $students[$r['student_id']], 'score' => (int) $r['mark']]);
        });

        $this->runSet('notices', $data['notices'], function ($r) {
            $this->put('notices', ['id' => $r['id']], [
                'date' => $this->dt($r['date']) ?? now()->toDateTimeString(),
                'text' => $r['text'], 'type' => $r['type'] ?: 'info',
            ]);
        });

        $this->runSet('calendar_events', $data['calendar_events'], function ($r) {
            $this->put('calendar_events', ['id' => $r['id']], [
                'date' => $this->d($r['date']), 'title' => $r['title'], 'type' => $r['type'] ?: 'event',
            ]);
        });

        $this->runSet('homework', $data['homework'], function ($r) {
            $path = null;
            $u = $r['attachment_data_url'] ?? null;
            if ($u && preg_match('#^data:([^;,]*)(;base64)?,(.*)$#s', $u, $m)) {
                $bin = $m[2] ? base64_decode($m[3]) : rawurldecode($m[3]);
                $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $r['attachment_name'] ?: 'file');
                $path = 'homework/' . $r['id'] . '_' . $name;
                Storage::disk('public')->put($path, $bin);
            }
            $this->put('homework', ['id' => $r['id']], [
                'date' => $this->d($r['date']), 'text' => $r['text'], 'status' => $r['status'] ?: 'published',
                'subject' => $r['subject'], 'class_name' => $r['class_name'], 'teacher_name' => $r['teacher_name'],
                'published_at' => $this->dt($r['published_at'] ?? null),
                'attachment_path' => $path, 'attachment_name' => $r['attachment_name'] ?? null,
                'attachment_type' => $r['attachment_type'] ?? null,
                'created_at' => $this->dt($r['created_at'] ?? null) ?? now()->toDateTimeString(),
            ]);
        });

        $this->runSet('feedback', $data['feedback'], function ($r) {
            $this->put('feedback', ['id' => $r['id']], [
                'name' => $r['name'] ?: 'Anonymous', 'rating' => $r['rating'], 'message' => $r['message'],
                'created_at' => $this->dt($r['created_at'] ?? null) ?? now()->toDateTimeString(),
            ]);
        });

        $this->runSet('gallery', $data['gallery'], function ($r) {
            $this->put('gallery', ['id' => $r['id']], ['url' => $r['url'], 'caption' => $r['caption'] ?? null]);
        });

        $this->runSet('textbooks', $data['textbooks'], function ($r) {
            $this->put('textbooks', ['id' => $r['id']], [
                'class_name' => $r['class_name'], 'subject' => $r['subject'] ?: 'All',
                'book_name' => $r['book_name'], 'book_url' => $r['book_url'],
            ]);
        });

        $this->runSet('leave_requests', $data['leave_requests'], function ($r) use ($teachers) {
            $this->put('leave_requests', ['id' => $r['id']], [
                'teacher_id' => isset($teachers[$r['teacher_id'] ?? '']) ? $r['teacher_id'] : null,
                'teacher_name' => $r['teacher_name'] ?: ($r['student_name'] ?? null),
                'date' => $this->d($r['date']) ?? now()->toDateString(),
                'reason' => $r['reason'] ?: ($r['purpose'] ?? '-'),
                'submitted_at' => $this->dt($r['submitted_at'] ?? null),
                'status' => $r['status'] ?: 'pending',
            ]);
        });

        $serial = (int) DB::table('leaving_certificates')->max('serial_no');
        $lcs = $data['leaving_certificates'];
        usort($lcs, fn ($a, $b) => strcmp((string) $a['created_at'], (string) $b['created_at']));
        $this->runSet('leaving_certificates', $lcs, function ($r) use ($grMap, &$serial) {
            $exists = DB::table('leaving_certificates')->where('id', $r['id'])->first();
            $sn = $exists ? $exists->serial_no : ++$serial;
            $this->put('leaving_certificates', ['id' => $r['id']], [
                'student_id' => $grMap[$r['gr_number']] ?? null,
                'gr_number' => $r['gr_number'], 'name' => $r['name'], 'father_name' => $r['father_name'],
                'dob' => $this->d($r['dob']), 'dob_words' => $r['dob_words'], 'caste' => $r['caste'],
                'religion' => $r['religion'], 'place_of_birth' => $r['place_of_birth'],
                'admission_date' => $this->d($r['admission_date']), 'admitted_class' => $r['admitted_class'],
                'passed_class' => $r['passed_class'], 'year' => $r['year'], 'progress' => $r['progress'],
                'conduct' => $r['conduct'], 'dues' => $r['dues'], 'reason' => $r['reason'],
                'remarks' => $r['remarks'], 'last_school' => $r['last_school'],
                'leaving_date' => $this->d($r['leaving_date']), 'issue_date' => $this->d($r['issue_date']),
                'serial_no' => $sn,
                'created_at' => $this->dt($r['created_at'] ?? null) ?? now()->toDateTimeString(),
            ]);
        });

        $this->info('Import done.');
        return 0;
    }
}
