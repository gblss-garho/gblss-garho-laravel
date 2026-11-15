<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/** Parents Corner "AI Tutor": study-help chat for a student verified by GR number + date of birth. */
class AiTutorController extends PublicLookupController
{
    public function chat(Request $request): JsonResponse
    {
        $cfg = config('ai_tutor');
        if (! $cfg['api_key']) {
            return response()->json(['error' => 'AI Tutor abhi configure nahi hua — school admin ko ANTHROPIC_API_KEY set karni hogi.'], 503);
        }

        $data = $request->validate([
            'gr_number' => ['required', 'string', 'max:50'],
            'dob' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:'.$cfg['max_message_chars']],
        ]);
        $message = trim($data['message']);
        $gr = trim($data['gr_number']);

        // 1. Same identity check as the rest of Parents Corner; only FAILED tries count towards the lock.
        $dob = $this->parseDob($data['dob']);
        $fails = DB::table('lookup_attempts')->where('scope', 'student')->where('identifier', $gr)
            ->where('created_at', '>', now()->subMinutes(15)->toDateTimeString())->count();
        if ($fails >= 8) {
            return response()->json(['error' => 'Bohat zyada koshishein. 15 minute baad dobara try karein.'], 429);
        }
        $student = $dob ? Student::where('gr_number', $gr)->whereDate('dob', $dob)->first() : null;
        if (! $student) {
            DB::table('lookup_attempts')->insert(['scope' => 'student', 'identifier' => $gr, 'created_at' => now()->toDateTimeString()]);

            return response()->json(['error' => 'GR number ya date of birth match nahi hui.'], 401);
        }

        // 2. Daily quota, reserved atomically before the (paid) API call.
        $today = now()->toDateString();
        DB::table('ai_tutor_usage')->insertOrIgnore(['student_id' => $student->id, 'usage_date' => $today, 'message_count' => 0]);
        $reserved = DB::table('ai_tutor_usage')->where('student_id', $student->id)->where('usage_date', $today)
            ->where('message_count', '<', $cfg['daily_limit'])->increment('message_count');
        if (! $reserved) {
            return response()->json(['error' => "Aaj ke {$cfg['daily_limit']} sawal poore ho gaye — kal dobara koshish karein."], 429);
        }
        $refund = fn () => DB::table('ai_tutor_usage')->where('student_id', $student->id)->where('usage_date', $today)->decrement('message_count');

        // 3. Small window of recent history for context.
        $history = DB::table('ai_tutor_messages')->where('student_id', $student->id)
            ->orderByDesc('id')->limit($cfg['history_messages'])->get(['role', 'content'])->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])->values()->all();
        // The API needs the conversation to start with a user turn.
        while ($history && $history[0]['role'] !== 'user') {
            array_shift($history);
        }
        $messages = array_merge($history, [['role' => 'user', 'content' => $message]]);

        // 4. Ask Claude (key stays on the server).
        try {
            $resp = Http::withHeaders(['x-api-key' => $cfg['api_key'], 'anthropic-version' => '2023-06-01'])
                ->timeout(45)->acceptJson()
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $cfg['model'],
                    'max_tokens' => $cfg['max_tokens'],
                    'system' => $cfg['system_prompt'],
                    'messages' => $messages,
                ]);
        } catch (\Throwable $e) {
            $refund();
            report($e);

            return response()->json(['error' => 'AI Tutor se connect nahi ho saka. Thodi der baad koshish karein.'], 502);
        }
        if (! $resp->successful()) {
            $refund();
            \Log::warning('AI Tutor API error', ['status' => $resp->status()]);

            return response()->json(['error' => 'AI Tutor abhi jawab nahi de saka. Thodi der baad koshish karein.'], 502);
        }
        $reply = trim(collect($resp->json('content', []))->where('type', 'text')->pluck('text')->implode("\n"));
        if ($reply === '') {
            $reply = 'Maazrat, jawab nahi ban saka. Sawal dobara likh kar poochein.';
        }

        // 5. Keep both turns (reviewable by the school; auto-deleted after the retention period).
        DB::table('ai_tutor_messages')->insert([
            ['student_id' => $student->id, 'role' => 'user', 'content' => $message, 'created_at' => now()],
            ['student_id' => $student->id, 'role' => 'assistant', 'content' => $reply, 'created_at' => now()],
        ]);
        $used = (int) DB::table('ai_tutor_usage')->where('student_id', $student->id)->where('usage_date', $today)->value('message_count');

        return response()->json(['reply' => $reply, 'remaining' => max(0, $cfg['daily_limit'] - $used)]);
    }
}
