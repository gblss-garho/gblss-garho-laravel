<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncStudents extends Command
{
    protected $signature = 'sync:students {--dry : only count, write nothing}';
    protected $description = 'Sync students from live Supabase (students table only)';

    private function d($v)
    {
        return empty($v) ? null : substr($v, 0, 10);
    }

    public function handle()
    {
        $key = env('SUPABASE_SERVICE_KEY');
        $url = env('SUPABASE_URL');
        if (! $key || ! $url) {
            $this->error('SUPABASE_URL / SUPABASE_SERVICE_KEY missing in .env');
            return 1;
        }
        $r = Http::withHeaders(['apikey' => $key, 'Authorization' => 'Bearer ' . $key])
            ->get($url . '/rest/v1/students', ['select' => '*', 'limit' => 2000]);
        if (! $r->ok()) {
            $this->error('HTTP ' . $r->status());
            return 1;
        }
        $dry = (bool) $this->option('dry');
        $now = now();
        $ins = 0;
        $upd = 0;
        $seen = 0;
        $cols = [];
        $newActive = 0;
        foreach ($r->json() as $s) {
            $seen++;
            $vals = [
                'gr_number' => $s['gr_number'],
                'name' => $s['name'],
                'father_name' => $s['father_name'] ?? null,
                'class' => $s['class'] ?? '',
                'section' => $s['section'] ?? null,
                'address' => $s['address'] ?? null,
                'photo_url' => $s['photo_url'] ?? null,
                'admission_date' => $this->d($s['admission_date'] ?? null),
                'passed_out' => (int) ($s['passed_out'] ?? 0),
                'pass_out_year' => $s['pass_out_year'] ?? null,
                'status' => $s['status'] ?? null,
                'results_class_override' => $s['results_class_override'] ?? null,
            ];
            $dob = $this->d($s['dob'] ?? null);
            $phone = $s['guardian_phone'] ?? null;
            $cur = DB::table('students')->where('id', $s['id'])->first();
            if (! $cur) {
                if ((int) ($s['passed_out'] ?? 0)) {
                    $cols['_skipped_old'] = ($cols['_skipped_old'] ?? 0) + 1;
                    continue;
                }
                $ins++;
                if (! (int) ($s['passed_out'] ?? 0)) {
                    $newActive++;
                }
                if (! $dry) {
                    DB::table('students')->insert(array_merge(['id' => $s['id']], $vals, [
                        'dob' => $dob, 'guardian_phone' => $phone,
                        'created_at' => $now, 'updated_at' => $now,
                    ]));
                }
                continue;
            }
            if ($dob) {
                $vals['dob'] = $dob;
            }
            if ($phone && empty($cur->guardian_phone)) {
                $vals['guardian_phone'] = $phone;
            }
            $diff = false;
            foreach ($vals as $k => $v) {
                if ((string) $cur->$k !== (string) $v) {
                    $diff = true;
                    $cols[$k] = ($cols[$k] ?? 0) + 1;
                }
            }
            if ($diff) {
                $upd++;
                if (! $dry) {
                    DB::table('students')->where('id', $s['id'])->update($vals + ['updated_at' => $now]);
                }
            }
        }
        $this->info(($dry ? '[DRY] ' : '') . "rows $seen, new $ins, changed $upd");
        $this->line("new active: $newActive; differing columns: " . json_encode($cols));
        return 0;
    }
}
