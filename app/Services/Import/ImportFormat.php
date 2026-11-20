<?php

declare(strict_types=1);

namespace App\Services\Import;

final class ImportFormat
{
    /**
     * @return array<string, list<string>>
     */
    public static function expectedHeaders(): array
    {
        return [
            'leads' => ['first_name', 'last_name', 'company_name', 'email', 'phone', 'job_title', 'source', 'estimated_value'],
            'contacts' => ['first_name', 'last_name', 'company_name', 'email', 'phone', 'job_title'],
            'companies' => ['name', 'industry', 'website', 'phone', 'city', 'country'],
        ];
    }

    /**
     * @param  list<string>  $headers
     */
    public static function validate(string $module, array $headers): bool
    {
        $expected = self::expectedHeaders()[$module] ?? null;
        if ($expected === null) {
            return false;
        }

        $normalized = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

        return in_array('first_name', $normalized, true) || in_array('name', $normalized, true);
    }
}
