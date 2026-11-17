<?php

namespace App\Console\Commands;

use App\Models\Teacher;
use App\Support\PdfPhoto;
use Illuminate\Console\Command;

class CheckStaffPhotos extends Command
{
    protected $signature = 'photos:check';

    protected $description = 'Show which teachers have a photo that the ID card can load';

    public function handle(): int
    {
        $rows = Teacher::orderBy('name')->get()->map(function (Teacher $t) {
            $src = trim((string) $t->photo_url);
            $kind = $src === '' ? 'none' : (str_starts_with($src, 'data:') ? 'data-uri' : (preg_match('#^(https?:)?//#i', $src) ? 'url' : 'path'));
            $ok = $src !== '' && PdfPhoto::small($src) !== null;

            return [$t->name, $kind, $kind === 'url' || $kind === 'path' ? mb_substr($src, 0, 70) : '', $ok ? 'OK' : ($src === '' ? 'no photo' : 'FAILED')];
        });
        $this->table(['Teacher', 'Type', 'Value', 'Card photo'], $rows->all());

        return self::SUCCESS;
    }
}
