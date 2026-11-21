<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class ConflictEventDateParser
{
    private const MONTHS = [
        'jan' => 1,
        'january' => 1,
        'janvier' => 1,
        'janv' => 1,
        'feb' => 2,
        'february' => 2,
        'fevrier' => 2,
        'fevr' => 2,
        'mar' => 3,
        'march' => 3,
        'mars' => 3,
        'apr' => 4,
        'april' => 4,
        'avril' => 4,
        'avr' => 4,
        'may' => 5,
        'mai' => 5,
        'jun' => 6,
        'june' => 6,
        'juin' => 6,
        'jul' => 7,
        'july' => 7,
        'juillet' => 7,
        'juil' => 7,
        'aug' => 8,
        'august' => 8,
        'aout' => 8,
        'août' => 8,
        'sep' => 9,
        'sept' => 9,
        'september' => 9,
        'septembre' => 9,
        'oct' => 10,
        'october' => 10,
        'octobre' => 10,
        'nov' => 11,
        'november' => 11,
        'novembre' => 11,
        'dec' => 12,
        'december' => 12,
        'decembre' => 12,
        'décembre' => 12,
    ];

    public static function parse(?string $value, ?int $eventYear = null): ?CarbonImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $date = strtolower(Str::ascii(trim($value)));
        $date = preg_replace('/[.,]/', '', $date);

        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/D', $date, $matches)) {
            return self::makeDate((int) $matches[1], (int) $matches[2], (int) $matches[3], $eventYear);
        }

        if (preg_match('/^(\d{1,2})[-\/\s]+([a-z]+)[-\/\s]+(\d{2,4})$/D', $date, $matches)) {
            $month = self::MONTHS[$matches[2]] ?? null;

            if ($month === null) {
                return null;
            }

            $year = (int) $matches[3];

            return self::makeDate($year < 100 ? self::expandYear($year, $eventYear) : $year, $month, (int) $matches[1], $eventYear);
        }

        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{2,4})$/D', $date, $matches)) {
            $year = (int) $matches[3];

            return self::makeDate(
                $year < 100 ? self::expandYear($year, $eventYear) : $year,
                (int) $matches[2],
                (int) $matches[1],
                $eventYear
            );
        }

        return null;
    }

    private static function expandYear(int $year, ?int $eventYear): int
    {
        if ($eventYear !== null && $eventYear % 100 === $year) {
            return $eventYear;
        }

        return 2000 + $year;
    }

    private static function makeDate(int $year, int $month, int $day, ?int $eventYear): ?CarbonImmutable
    {
        if (($eventYear !== null && $year !== $eventYear) || !checkdate($month, $day, $year)) {
            return null;
        }

        return CarbonImmutable::create($year, $month, $day)->startOfDay();
    }
}
