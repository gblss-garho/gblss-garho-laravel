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
    /** Accepts a data: URI, an http(s) URL, or a path under /public; returns a small JPEG data URI (or null). */
    public static function small(?string $src, int $max = 220, bool $square = false): ?string
    {
        $src = trim((string) $src);
        if ($src === '' || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        // Remote / local-path photos (what the website shows) are fetched once, shrunk and cached for a day.
        if (! str_starts_with($src, 'data:')) {
            return Cache::remember('pdfphoto:'.md5($src.'|'.$max.'|'.(int) $square), 86400, fn () => self::shrink(self::load($src), $max, $square));
        }

        if (! preg_match('#^data:image/(?:jpe?g|png|webp|gif);base64,(.+)$#is', $src, $m)) {
            return null;
        }

        return self::shrink(base64_decode($m[1], true) ?: null, $max, $square);
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

    /** $square = centre-crop (biased towards the top, where faces are) to a square so it fills a photo frame exactly. */
    private static function shrink(?string $bin, int $max, bool $square = false): ?string
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
        if ($square) {
            $side = min($sw, $sh);
            $sx = (int) (($sw - $side) / 2);
            $sy = (int) round(($sh - $side) * 0.2);   // keep the head, trim the bottom
            $sw = $sh = $side;
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
