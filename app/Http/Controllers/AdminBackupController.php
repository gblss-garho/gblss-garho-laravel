<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

/** One-tap database backup: runs db:backup and hands the newest file to the admin. */
class AdminBackupController extends Controller
{
    public function download()
    {
        $code = Artisan::call('db:backup');
        $files = glob(storage_path('app/backups/db-*.sql.gz')) ?: [];
        rsort($files);
        if ($code !== 0 || ! $files) {
            return back()->with('err', 'Backup nahi ban saka: '.trim(Artisan::output()));
        }

        return response()->download($files[0]);
    }
}
