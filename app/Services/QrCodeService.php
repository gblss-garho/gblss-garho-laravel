<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    public function svg(string $text, int $size = 200, int $margin = 2): string
    {
        $r = new ImageRenderer(new RendererStyle($size, $margin), new SvgImageBackEnd());
        return (new Writer($r))->writeString($text);
    }

    public function dataUri(string $text, int $size = 200, int $margin = 2): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg($text, $size, $margin));
    }
}
