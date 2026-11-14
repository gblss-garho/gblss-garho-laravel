<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : Days of backups to keep (older db-*.sql.gz files are removed)} {--bin=mysqldump}';

    protected $description = 'Dump the MySQL database to storage/app/backups/db-YYYY-MM-DD_HHMM.sql.gz';

    public function handle(): int
    {
        $conn = config('database.default');
        if ($conn !== 'mysql') {
            $this->error("db:backup supports MySQL only (current connection: {$conn}).");

            return self::FAILURE;
        }

        $cfg = config('database.connections.mysql');
        $dir = storage_path('app/backups');
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            $this->error('Cannot create '.$dir);

            return self::FAILURE;
        }

        // Password goes in a private temp file, never on the command line.
        $cnf = $dir.'/.my-'.bin2hex(random_bytes(4)).'.cnf';
        $lines = ["[client]", 'user="'.addcslashes((string) $cfg['username'], '"\\').'"', 'password="'.addcslashes((string) $cfg['password'], '"\\').'"', 'host="'.$cfg['host'].'"', 'port='.$cfg['port']];
        file_put_contents($cnf, implode("\n", $lines)."\n");
        chmod($cnf, 0600);

        $file = $dir.'/db-'.now('Asia/Karachi')->format('Y-m-d_Hi').'.sql.gz';
        $gz = gzopen($file, 'wb9');
        $err = '';

        try {
            $p = new Process([
                $this->option('bin'),
                '--defaults-extra-file='.$cnf,
                '--single-transaction', '--no-tablespaces', '--default-character-set=utf8mb4',
                $cfg['database'],
            ]);
            $p->setTimeout(300);
            $p->run(function ($type, $buf) use ($gz, &$err) {
                if ($type === Process::OUT) {
                    gzwrite($gz, $buf);
                } else {
                    $err .= $buf;
                }
            });
            $ok = $p->isSuccessful();
        } finally {
            gzclose($gz);
            @unlink($cnf);
        }

        if (! $ok || filesize($file) < 200) {
            @unlink($file);
            $this->error('Backup failed: '.trim($err));

            return self::FAILURE;
        }

        chmod($file, 0640);
        $this->info('Backup saved: '.basename($file).' ('.round(filesize($file) / 1024).' KB)');

        $cutoff = now()->subDays(max(1, (int) $this->option('keep')))->getTimestamp();
        foreach (glob($dir.'/db-*.sql.gz') ?: [] as $old) {
            if ($old !== $file && filemtime($old) < $cutoff) {
                @unlink($old);
                $this->line('Removed old backup: '.basename($old));
            }
        }

        return self::SUCCESS;
    }
}
