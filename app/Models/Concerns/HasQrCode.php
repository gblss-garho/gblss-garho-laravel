<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasQrCode
{
    protected static function bootHasQrCode(): void
    {
        static::creating(function ($model) {
            if (empty($model->qr_code)) {
                $model->qr_code = static::generateUniqueQrCode();
            }
        });
    }

    public static function generateUniqueQrCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (static::where('qr_code', $code)->exists());

        return $code;
    }

    /** Make sure the record has a persisted qr_code (old imported rows may lack one). */
    public function ensureQrCode(): string
    {
        if (empty($this->qr_code)) {
            $this->forceFill(['qr_code' => static::generateUniqueQrCode()])->saveQuietly();
        }

        return $this->qr_code;
    }
}
