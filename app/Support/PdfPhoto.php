<?php

namespace App\Support;

/**
 * Shrinks a photo (data URI) before it goes into a DomPDF page.
 * Phone photos can be 10+ megapixels; DomPDF decodes them in full and
 * runs out of memory, which makes the web server return a bare error page.
 */
class PdfPhoto
{
    public static function small(?string $src, int $max = 220): ?string
    {
        $src = (string) $src;
        if (! preg_match('#^data:image/(?:jpe?g|png);base64,(.+)$#is', $src, $m) || ! function_exists('imagecreatefromstring')) {
            return null;
        }
        $bin = base64_decode($m[1], true);
        if ($bin === false) {
            return null;
        }
        $info = @getimagesizefromstring($bin);
        if (! $info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 60000000) {
            return null;
        }
        $ratio = min(1, $max / max($info[0], $info[1]));
        if ($ratio === 1 && strlen($bin) < 150000) {
            return $src;
        }
        @ini_set('memory_limit', '512M');
        $im = @imagecreatefromstring($bin);
        unset($bin);
        if (! $im) {
            return null;
        }
        $w = max(1, (int) round($info[0] * $ratio));
        $h = max(1, (int) round($info[1] * $ratio));
        $out = imagecreatetruecolor($w, $h);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        imagecopyresampled($out, $im, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);
        imagedestroy($im);
        ob_start();
        imagejpeg($out, null, 80);
        $jpg = ob_get_clean();
        imagedestroy($out);

        return 'data:image/jpeg;base64,'.base64_encode($jpg);
    }
}
