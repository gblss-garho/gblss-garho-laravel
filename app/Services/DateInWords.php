<?php

namespace App\Services;

use Carbon\CarbonInterface;

class DateInWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    private const ORDINALS = [
        '', 'First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth',
        'Tenth', 'Eleventh', 'Twelfth', 'Thirteenth', 'Fourteenth', 'Fifteenth', 'Sixteenth',
        'Seventeenth', 'Eighteenth', 'Nineteenth',
    ];

    public function format(CarbonInterface $date): string
    {
        return $date->format('F') . ' ' . $this->dayOrdinal($date->day) . ', ' . $this->year($date->year);
    }

    private function dayOrdinal(int $day): string
    {
        if ($day < 20) {
            return self::ORDINALS[$day];
        }
        if ($day === 20) {
            return 'Twentieth';
        }
        if ($day < 30) {
            return 'Twenty-' . self::ORDINALS[$day - 20];
        }
        if ($day === 30) {
            return 'Thirtieth';
        }

        return 'Thirty-' . self::ORDINALS[$day - 30];
    }

    private function year(int $year): string
    {
        $parts = [];
        $thousands = intdiv($year, 1000);
        $hundreds = intdiv($year % 1000, 100);
        $rest = $year % 100;

        if ($thousands) {
            $parts[] = self::ONES[$thousands] . ' Thousand';
        }
        if ($hundreds) {
            $parts[] = self::ONES[$hundreds] . ' Hundred';
        }
        if ($rest) {
            $parts[] = $this->belowHundred($rest);
        }

        return implode(' ', $parts);
    }

    private function belowHundred(int $n): string
    {
        if ($n < 20) {
            return self::ONES[$n];
        }

        return self::TENS[intdiv($n, 10)] . ($n % 10 ? '-' . self::ONES[$n % 10] : '');
    }
}
