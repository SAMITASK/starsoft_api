<?php

namespace App\Utils;

use Carbon\Carbon;
use InvalidArgumentException;

class DateRangeParser
{
    private const SEPARATORS = [' a ', ' - ', ' to '];

    public function parse(string $dateRange): array
    {
        $dateRange = trim($dateRange);

        // Buscar separador
        $separator = $this->findSeparator($dateRange);

        if ($separator) {
            [$start, $end] = explode($separator, $dateRange, 2);
            return $this->formatDates(trim($start), trim($end));
        }

        // Una sola fecha
        $date = $this->parseDate($dateRange);
        return [$date, $date];
    }

    private function findSeparator(string $dateRange): ?string
    {
        foreach (self::SEPARATORS as $sep) {
            if (strpos($dateRange, $sep) !== false) {
                return $sep;
            }
        }
        return null;
    }

    private function formatDates(string $start, string $end): array
    {
        $startDate = $this->parseDate($start);
        $endDate = $this->parseDate($end);

        // Asegurar orden correcto
        if ($startDate > $endDate) {
            return [$endDate, $startDate];
        }

        return [$startDate, $endDate];
    }

    private function parseDate(string $date): string
    {
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Formato de fecha inválido: {$date}"
            );
        }
    }
}
?>
