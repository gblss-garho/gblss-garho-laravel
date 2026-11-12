<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportGrRegister extends Command
{
    protected $signature = 'register:import {--csv= : optional GR register CSV (kept OUT of git) with removal details} {--dry : only count, write nothing}';
    protected $description = 'Fill the General Register (GR 1..latest) from live Supabase and/or a CSV. Re-running is safe.';

    private function date($v): ?string
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $v, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        return preg_match('/^\d{4}-\d{2}-\d{2}/', $v) ? substr($v, 0, 10) : null;
    }

    public function handle()
    {
        $rows = [];

        $key = env('SUPABASE_SERVICE_KEY');
        $url = env('SUPABASE_URL');
        if ($key && $url) {
            $r = Http::withHeaders(['apikey' => $key, 'Authorization' => 'Bearer ' . $key])
                ->get($url . '/rest/v1/students', ['select' => '*', 'limit' => 2000]);
            if (! $r->ok()) {
                $this->error('Supabase HTTP ' . $r->status());
                return 1;
            }
            foreach ($r->json() as $s) {
                $n = (int) ($s['gr_number'] ?? 0);
                if ($n < 1) {
                    continue;
                }
                $rows[$n] = [
                    'name' => $s['name'] ?? '',
                    'father_name' => $s['father_name'] ?? null,
                    'dob' => $this->date($s['dob'] ?? null),
                    'address' => $s['address'] ?? null,
                    'admission_date' => $this->date($s['admission_date'] ?? null),
                    'removal_class' => ! empty($s['passed_out']) ? ($s['class'] ?? null) : null,
                    'status' => $s['status'] ?? (empty($s['passed_out']) ? 'active' : null),
                ];
            }
            $this->info('Supabase rows: ' . count($rows));
        } elseif (! $this->option('csv')) {
            $this->error('SUPABASE_URL / SUPABASE_SERVICE_KEY missing in .env — give --csv=path instead.');
            return 1;
        }

        if ($path = $this->option('csv')) {
            if (! is_file($path)) {
                $this->error("CSV not found: $path");
                return 1;
            }
            $fh = fopen($path, 'r');
            $head = fgetcsv($fh);
            $head[0] = ltrim((string) $head[0], "\xEF\xBB\xBF");
            $map = [
                'Reg No' => 'reg', 'Name' => 'name', "Father's Name" => 'father_name', 'Religion' => 'religion',
                'Surname' => 'surname', 'Date of Birth' => 'dob', 'Address' => 'address',
                'Last Institution Attended' => 'last_institution', 'Date of Admission' => 'admission_date',
                'Class in which admitted' => 'admission_class', 'Date of Removal' => 'removal_date',
                'Class at time of removal' => 'removal_class', 'Cause of removal' => 'removal_cause', 'Remarks' => 'remarks',
            ];
            $c = 0;
            while (($line = fgetcsv($fh)) !== false) {
                $rec = [];
                foreach ($head as $i => $h) {
                    if (isset($map[trim($h)])) {
                        $rec[$map[trim($h)]] = trim((string) ($line[$i] ?? ''));
                    }
                }
                $n = (int) ($rec['reg'] ?? 0);
                if ($n < 1) {
                    continue;
                }
                unset($rec['reg']);
                foreach (['dob', 'admission_date', 'removal_date'] as $d) {
                    if (isset($rec[$d])) {
                        $rec[$d] = $this->date($rec[$d]);
                    }
                }
                foreach ($rec as $k => $v) {
                    if ($v === '' || $v === null) {
                        unset($rec[$k]);
                    }
                }
                $rows[$n] = array_merge($rows[$n] ?? [], $rec);
                $c++;
            }
            fclose($fh);
            $this->info("CSV rows: $c");
        }

        ksort($rows);
        $this->info('Total register rows: ' . count($rows) . ' (GR ' . array_key_first($rows) . ' to ' . array_key_last($rows) . ')');
        if ($this->option('dry')) {
            return 0;
        }

        $now = now();
        foreach ($rows as $n => $v) {
            $v = array_filter($v, fn ($x) => $x !== null && $x !== '');
            $v['name'] = $v['name'] ?? ('GR ' . $n);
            $exists = DB::table('gr_register')->where('reg_no', $n)->exists();
            if ($exists) {
                DB::table('gr_register')->where('reg_no', $n)->update($v + ['updated_at' => $now]);
            } else {
                DB::table('gr_register')->insert($v + ['reg_no' => $n, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
        $this->info('Done.');
        return 0;
    }
}
