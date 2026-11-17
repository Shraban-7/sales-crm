<?php

declare(strict_types=1);

namespace App\Support;

final class CsvSanitizer
{
    public static function sanitizeCell(mixed $value): string
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '') {
            return '';
        }

        if (preg_match('/^[=+\-@\t\r]/', $string) === 1) {
            return "'" . $string;
        }

        return $string;
    }
}
