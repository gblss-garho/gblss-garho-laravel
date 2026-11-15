<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneAiTutorChats extends Command
{
    protected $signature = 'ai-tutor:prune';
    protected $description = 'Delete AI Tutor chats (and old usage rows) past the retention period';

    public function handle(): int
    {
        $days = (int) config('ai_tutor.retention_days');
        $msgs = DB::table('ai_tutor_messages')->where('created_at', '<', now()->subDays($days))->delete();
        DB::table('ai_tutor_usage')->where('usage_date', '<', now()->subDays(7)->toDateString())->delete();
        $this->info("Deleted {$msgs} AI Tutor message(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
