<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Shrinks a photo (data URI) before it goes into a DomPDF page.
 * Phone photos can be 10+ megapixels; DomPDF decodes them in full and
 * runs out of memory, which makes the web server return a bare error page.
 */
class PdfPhoto
{
    /** ID-card photo frame (inner size, px): passport style, width : height. */
    public const FRAME_W = 91;
    public const FRAME_H = 106;

    /** Accepts a data: URI, an http(s) URL, or a path under /public; returns a small JPEG data URI (or null). */
    /** $square: true = head-and-shoulders square crop; $portrait: 3:4 portrait crop (ID card photo frames). */
    public static function small(?string $src, int $max = 220, bool $square = false, bool $portrait = false): ?string
    {
        $src = trim((string) $src);
        $shape = $portrait ? 2 : ($square ? 1 : 0);
        if ($src === '' || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        // Remote / local-path photos (what the website shows) are fetched once, shrunk and cached for a day.
        if (! str_starts_with($src, 'data:')) {
            return Cache::remember('pdfphoto:v3:'.md5($src.'|'.$max.'|'.$shape), 86400, fn () => self::shrink(self::load($src), $max, $shape));
        }

        if (! preg_match('#^data:image/(?:jpe?g|png|webp|gif);base64,(.+)$#is', $src, $m)) {
            return null;
        }

        return self::shrink(base64_decode($m[1], true) ?: null, $max, $shape);
    }

    /** Saves an uploaded photo under public/uploads/students and returns the relative URL stored in photo_url. */
    public static function saveUpload(\Illuminate\Http\UploadedFile $file, string $id): string
    {
        $dir = public_path('uploads/students');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $name = preg_replace('/[^A-Za-z0-9_-]/', '', $id);
        $bin = (string) @file_get_contents($file->getRealPath());
        $jpg = function_exists('imagecreatefromstring') ? self::shrink($bin, 600, false) : null;
        if ($jpg !== null) {
            file_put_contents("{$dir}/{$name}.jpg", base64_decode(substr($jpg, strlen('data:image/jpeg;base64,'))));
            $ext = 'jpg';
        } else {   // no GD: keep the original file as it is
            $ext = in_array(strtolower($file->extension()), ['jpg', 'jpeg', 'png', 'webp'], true) ? strtolower($file->extension()) : 'jpg';
            file_put_contents("{$dir}/{$name}.{$ext}", $bin);
        }

        return "/uploads/students/{$name}.{$ext}?v=".time();
    }

    /**
     * Square crop [x, y, side] that frames the head and shoulders.
     * Plain-background photos are measured (so a small full-length figure is zoomed in, while a tight
     * face photo is left almost as it is); busy or edge-to-edge photos only get a gentle top-biased trim.
     */
    private static function headCrop($im, int $w, int $h, bool $passport = false): array
    {
        $full = min($w, $h);
        $gentle = function () use ($w, $h, $full, $passport) {
            if ($passport) {   // already a head-and-shoulders photo: keep its full width
                return [0, 0, $w, null];
            }
            $side = (int) round($full * 0.92);

            return [(int) (($w - $side) / 2), (int) round(($h - $side) * 0.12), $side, null];
        };

        $tw = 64;
        $th = max(8, (int) round($h * $tw / $w));
        $t = imagecreatetruecolor($tw, $th);
        imagecopyresampled($t, $im, 0, 0, 0, 0, $tw, $th, $w, $h);
        $px = fn ($x, $y) => [(imagecolorat($t, $x, $y) >> 16) & 255, (imagecolorat($t, $x, $y) >> 8) & 255, imagecolorat($t, $x, $y) & 255];

        // Background colour = average of the top row and the upper side edges.
        $sum = [0, 0, 0];
        $n = 0;
        for ($x = 0; $x < $tw; $x++) {
            foreach ([0, 1] as $y) {
                foreach ($px($x, $y) as $i => $v) { $sum[$i] += $v; }
                $n++;
            }
        }
        for ($y = 0; $y < (int) ($th * 0.4); $y++) {
            foreach ([0, $tw - 1] as $x) {
                foreach ($px($x, $y) as $i => $v) { $sum[$i] += $v; }
                $n++;
            }
        }
        $bg = array_map(fn ($v) => $v / $n, $sum);
        $fg = [];
        $fgCount = 0;
        for ($y = 0; $y < $th; $y++) {
            for ($x = 0; $x < $tw; $x++) {
                $c = $px($x, $y);
                $d = max(abs($c[0] - $bg[0]), abs($c[1] - $bg[1]), abs($c[2] - $bg[2]));
                $fg[$y][$x] = $d > 40;
                $fgCount += $d > 40 ? 1 : 0;
            }
        }
        if ($fgCount / ($tw * $th) > 0.8) {   // photo fills the whole frame (or busy background)
            imagedestroy($t);

            return $gentle();
        }
        $rowCount = fn ($y) => count(array_filter($fg[$y]));
        $y0 = null;
        $y1 = null;
        for ($y = 0; $y < $th; $y++) {
            if ($rowCount($y) >= max(2, (int) ($tw * 0.04))) {
                $y0 ??= $y;
                $y1 = $y;
            }
        }
        if ($y0 === null) {
            imagedestroy($t);

            return $gentle();
        }
        // Width of the subject over its upper part only (head + shoulders; arms hanging lower are ignored).
        $yTop = $y0 + max(1, (int) round(($y1 - $y0) * ($passport ? 0.6 : 0.45)));
        $x0 = null;
        $x1 = null;
        for ($x = 0; $x < $tw; $x++) {
            $c = 0;
            for ($y = $y0; $y <= $yTop; $y++) { $c += $fg[$y][$x] ? 1 : 0; }
            if ($c >= 2) {
                $x0 ??= $x;
                $x1 = $x;
            }
        }
        imagedestroy($t);
        if ($x0 === null) {
            return $gentle();
        }
        $bw = ($x1 - $x0 + 1) * $w / $tw;
        if ($bw >= 0.85 * $w && ($y1 - $y0 + 1) >= 0.85 * $th) {   // subject already fills the frame
            return $gentle();
        }
        // Passport style: shoulders reach the frame edges (a little tighter than the square crop used on admit slips).
        if ($passport) {
            // Wide enough for the shoulders, and tall enough to show roughly the top 60% of the figure (head to chest).
            $subjH = ($y1 - $y0 + 1) * $h / $th;
            $side = (int) round(min($w, max(0.35 * $w, 1.08 * $bw, 0.6 * $subjH * self::FRAME_W / self::FRAME_H)));
        } else {
            $side = (int) round(min($full, max(0.5 * $full, 1.35 * $bw)));
        }
        $cx = (($x0 + $x1 + 1) / 2) * $w / $tw;
        $subjectTop = $y0 * $h / $th;
        $top = $subjectTop - 0.06 * $side;
        $sx = (int) max(0, min($w - $side, round($cx - $side / 2)));
        $sy = (int) max(0, min($h - $side, round($top)));

        return [$sx, $sy, $side, $subjectTop];
    }

    /** Raw bytes for a URL or public-path photo, or null. Only http(s) to public hosts is allowed. */
    private static function load(string $src): ?string
    {
        if (str_starts_with($src, '//')) {
            $src = 'https:'.$src;
        }
        if (preg_match('#^https?://#i', $src)) {
            $src = str_replace(' ', '%20', $src);
            $host = parse_url($src, PHP_URL_HOST);
            $ip = $host ? gethostbyname($host) : null;
            if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return null;
            }
            try {
                $r = Http::timeout(8)->withHeaders(['Accept' => 'image/*'])->get($src);
            } catch (\Throwable $e) {
                \Log::warning('PdfPhoto fetch failed: '.$e->getMessage(), ['src' => $src]);

                return null;
            }
            if (! $r->successful()) {
                \Log::warning('PdfPhoto HTTP '.$r->status(), ['src' => $src]);
            }

            return $r->successful() && strlen($r->body()) < 12_000_000 ? $r->body() : null;
        }

        $rel = ltrim(parse_url($src, PHP_URL_PATH) ?: '', '/');
        $file = realpath(public_path($rel));
        if ($rel === '' || ! $file || ! str_starts_with($file, realpath(public_path()) ?: '#') || ! is_file($file)) {
            return null;
        }

        return @file_get_contents($file) ?: null;
    }

    /** $square = close-up crop (centred, biased towards the top where faces are) to a square so it fills a photo frame. */
    private static function shrink(?string $bin, int $max, int $shape = 0): ?string
    {
        if (! $bin) {
            return null;
        }
        $info = @getimagesizefromstring($bin);
        if (! $info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 60000000) {
            return null;
        }
        @ini_set('memory_limit', '512M');
        $im = @imagecreatefromstring($bin);
        unset($bin);
        if (! $im) {
            return null;
        }
        $sx = 0;
        $sy = 0;
        $sw = $info[0];
        $sh = $info[1];
        if ($shape > 0) {
            [$sx, $sy, $side, $subjectTop] = self::headCrop($im, $sw, $sh, $shape === 2);
            $cw = $ch = $side;
            if ($shape === 2) {   // passport portrait (FRAME_W : FRAME_H): never stretched
                $ratio = self::FRAME_H / self::FRAME_W;
                $ch = (int) round($side * $ratio);
                if ($ch > $sh) {
                    $ch = $sh;
                    $cw = (int) round($sh / $ratio);
                }
                $sx = (int) max(0, min($sw - $cw, round($sx + $side / 2 - $cw / 2)));
                // Small space above the head; plain photos are placed by the measured head, others keep their top.
                $sy = (int) max(0, min($sh - $ch, $subjectTop !== null ? $subjectTop - 0.12 * $ch : ($sh - $ch) * 0.15));
            }
            $sw = $cw;
            $sh = $ch;
        }
        $ratio = min(1, $max / max($sw, $sh));
        $w = max(1, (int) round($sw * $ratio));
        $h = max(1, (int) round($sh * $ratio));
        $out = imagecreatetruecolor($w, $h);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        imagecopyresampled($out, $im, 0, 0, $sx, $sy, $w, $h, $sw, $sh);
        imagedestroy($im);
        ob_start();
        imagejpeg($out, null, 80);
        $jpg = ob_get_clean();
        imagedestroy($out);

        return 'data:image/jpeg;base64,'.base64_encode($jpg);
    }
}
