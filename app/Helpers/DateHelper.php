<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function parseDateRange(?string $dateRange): array
    {
        if (empty($dateRange)) {
            return [null, null];
        }

        if (strpos($dateRange, ' a ') !== false) {
            [$start, $end] = explode(' a ', $dateRange);
            $start = Carbon::parse(trim($start))->format('Y-m-d');
            $end   = Carbon::parse(trim($end))->format('Y-m-d');

            if ($start > $end) {
                [$start, $end] = [$end, $start];
            }
        } else {
            $start = Carbon::parse(trim($dateRange))->format('Y-m-d');
            $end   = $start;
        }

        return [$start, $end];
    }
}
