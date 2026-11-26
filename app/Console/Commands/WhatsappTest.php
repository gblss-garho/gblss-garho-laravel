<?php

namespace App\Console\Commands;

use App\Services\HeadMasterNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/** Sends the leave-notice template once, right now, and prints exactly what Meta answered (no secrets are printed). */
class WhatsappTest extends Command
{
    protected $signature = 'whatsapp:test {phone? : e.g. 923001234567 (default: WHATSAPP_HM_PHONE / school phone)}';

    protected $description = 'Check WhatsApp settings and send one test leave notice';

    public function handle(): int
    {
        $c = config('services.whatsapp');
        $to = $this->argument('phone') ? preg_replace('/\D+/', '', (string) $this->argument('phone')) : HeadMasterNotifier::phone();
        $this->line('dry_run      : '.($c['dry_run'] ? 'TRUE (nothing is sent)' : 'false'));
        $this->line('token set    : '.(! empty($c['token']) ? 'yes ('.strlen((string) $c['token']).' chars)' : 'NO'));
        $this->line('phone_id set : '.(! empty($c['phone_id']) ? 'yes (ends '.substr((string) $c['phone_id'], -4).')' : 'NO'));
        $this->line('template     : '.($c['leave_template'] ?? '').' / '.($c['leave_template_lang'] ?? ''));
        $this->line('send to      : '.($to ? '...'.substr($to, -4).' ('.strlen($to).' digits)' : 'NONE'));
        if (! $to || empty($c['token']) || empty($c['phone_id'])) {
            $this->error('Settings incomplete.');

            return self::FAILURE;
        }
        if ($c['dry_run']) {
            $this->warn('WHATSAPP_DRY_RUN is true in the config the app is using. Fix .env and run config:clear.');

            return self::FAILURE;
        }
        $res = Http::withToken($c['token'])->timeout(20)->post("https://graph.facebook.com/v22.0/{$c['phone_id']}/messages", [
            'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'template',
            'template' => [
                'name' => $c['leave_template'] ?? 'student_leave_request',
                'language' => ['code' => $c['leave_template_lang'] ?? 'en'],
                'components' => [['type' => 'body', 'parameters' => array_map(fn ($t) => ['type' => 'text', 'text' => $t], ['Test Student', 'VI', date('d-m-Y'), 'Test message'])]],
            ],
        ]);
        $this->line('HTTP status  : '.$res->status());
        $this->line(mb_substr($res->body(), 0, 700));

        return $res->successful() ? self::SUCCESS : self::FAILURE;
    }
}
