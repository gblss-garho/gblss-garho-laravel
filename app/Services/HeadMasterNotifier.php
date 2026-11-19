<?php

namespace App\Services;

use App\Models\SchoolInfo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tells the Head Master (by WhatsApp) when a parent submits a leave application.
 * Honours WHATSAPP_DRY_RUN: while it is true (the default) nothing is sent, the message is only logged.
 * A failure here must never break the parent's submission, so everything is swallowed and logged.
 */
class HeadMasterNotifier
{
    public static function phone(): ?string
    {
        $raw = config('services.whatsapp.hm_phone') ?: (SchoolInfo::first()?->phone);
        $d = preg_replace('/\D+/', '', (string) $raw);
        if ($d === '') {
            return null;
        }
        if (str_starts_with($d, '0')) {
            return '92'.substr($d, 1);
        }

        return $d;
    }

    public static function leaveApplied(string $studentName, string $class, string $date, string $purpose): void
    {
        try {
            $to = self::phone();
            if (! $to) {
                return;
            }
            $params = [$studentName, trim(preg_replace('/^class\s+/i', '', $class)), $date, $purpose];
            $token = config('services.whatsapp.token');
            $phoneId = config('services.whatsapp.phone_id');
            if (config('services.whatsapp.dry_run') || ! $token || ! $phoneId) {
                Log::info('WA dry-run leave notice to Head Master', ['to' => $to, 'params' => $params]);

                return;
            }
            $res = Http::withToken($token)->timeout(15)->post("https://graph.facebook.com/v22.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'template',
                'template' => [
                    'name' => config('services.whatsapp.leave_template', 'student_leave_request'),
                    'language' => ['code' => config('services.whatsapp.template_lang', 'en_US')],
                    'components' => [['type' => 'body', 'parameters' => array_map(fn ($t) => ['type' => 'text', 'text' => (string) $t], $params)]],
                ],
            ]);
            if (! $res->successful()) {
                Log::warning('WA leave notice failed', ['body' => $res->body()]);
            }
        } catch (\Throwable $e) {
            Log::warning('WA leave notice error', ['e' => $e->getMessage()]);
        }
    }
}
