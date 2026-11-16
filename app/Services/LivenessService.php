<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Challenge-response liveness for face enrol / check-in / check-out.
 * The server picks a random pair of actions (blink + a head turn) and remembers it in the session;
 * the browser must report that exact pair as completed, and not instantly. A still photo cannot blink or turn.
 * (A determined attacker with a pre-recorded video of the right actions is a harder, separate problem.)
 */
class LivenessService
{
    private const KEY = 'liveness';
    private const MIN_SECONDS = 3;
    private const MAX_SECONDS = 600;

    /** @return string[] */
    public function issue(Request $request): array
    {
        $turn = random_int(0, 1) ? 'turn_left' : 'turn_right';
        $list = random_int(0, 1) ? ['blink', $turn] : [$turn, 'blink'];
        $request->session()->put(self::KEY, ['c' => $list, 't' => now()->timestamp]);

        return $list;
    }

    /** Returns null when the proof is valid, otherwise a user-facing error. Always consumes the challenge. */
    public function verify(Request $request, $teacher, string $type): ?string
    {
        $issued = $request->session()->pull(self::KEY);
        $proof = json_decode((string) $request->input('liveness'), true);

        $reason = null;
        if (! is_array($issued)) {
            $reason = 'liveness_no_challenge';
        } elseif (! is_array($proof) || ($proof['ok'] ?? false) !== true || ($proof['c'] ?? null) !== $issued['c']) {
            $reason = 'liveness_not_passed';
        } else {
            $age = now()->timestamp - (int) $issued['t'];
            if ($age < self::MIN_SECONDS) {
                $reason = 'liveness_too_fast';
            } elseif ($age > self::MAX_SECONDS) {
                $reason = 'liveness_expired';
            }
        }

        if ($reason === null) {
            return null;
        }

        if ($teacher) {
            DB::table('teacher_liveness_failures')->insert([
                'teacher_id' => (string) $teacher->id,
                'reason' => $reason,
                'failure_type' => $type,
                'session_id' => substr((string) $request->session()->getId(), 0, 40),
                'occurred_at' => now(),
            ]);
        }

        return 'Liveness check mukammal nahi hua (palak jhapakna aur sir ghumana zaroori hai). Page dobara khol kar scan karen.';
    }
}
