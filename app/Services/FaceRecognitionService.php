<?php

namespace App\Services;

/**
 * Compares 128-length face-api.js descriptors server-side.
 * A lower distance means a closer match. face-api.js's own docs
 * recommend 0.6 as the standard match threshold.
 */
class FaceRecognitionService
{
    public const MATCH_THRESHOLD = 0.6;

    public function euclideanDistance(array $a, array $b): float
    {
        if (count($a) !== count($b) || count($a) === 0) {
            return INF;
        }

        $sum = 0.0;
        foreach ($a as $i => $value) {
            $diff = $value - $b[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    public function isMatch(array $enrolled, array $captured, float $threshold = self::MATCH_THRESHOLD): bool
    {
        return $this->euclideanDistance($enrolled, $captured) <= $threshold;
    }
}
