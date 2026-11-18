<?php

declare(strict_types=1);

namespace App\Support;

final class PhoneNumber
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $hasPlus = str_starts_with($trimmed, '+');
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if ($digits === '') {
            return null;
        }

        return $hasPlus ? '+'.$digits : $digits;
    }

    public static function comparable(?string $value): ?string
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return null;
        }

        return ltrim($normalized, '+');
    }
}
