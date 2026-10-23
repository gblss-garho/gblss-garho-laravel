<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncDob extends Command
{
    protected $signature = 'sync:dob';
    protected $description = 'Fill missing student DOB from Supabase (only empty local DOBs)';

    public function handle()
    {
        $key = env('SUPABASE_SERVICE_KEY');
        $url = env('SUPABASE_URL');
        if (! $key || ! $url) {
            $this->error('SUPABASE_URL / SUPABASE_SERVICE_KEY missing in .env');
            return 1;
        }
        $r = Http::withHeaders(['apikey' => $key, 'Authorization' => 'Bearer ' . $key])
            ->get($url . '/rest/v1/students', ['select' => 'id,dob', 'limit' => 1000]);
        if (! $r->ok()) {
            $this->error('HTTP ' . $r->status());
            return 1;
        }
        $seen = 0;
        $n = 0;
        foreach ($r->json() as $row) {
            $seen++;
            if (empty($row['dob'])) {
                continue;
            }
            $n += DB::table('students')->where('id', $row['id'])
                ->where(fn ($q) => $q->whereNull('dob')->orWhere('dob', ''))
                ->update(['dob' => substr($row['dob'], 0, 10)]);
        }
        $this->info("rows $seen, updated $n");
        return 0;
    }
}
