<?php

declare(strict_types=1);

namespace App\Support;

final class SecuritySecret
{
    private static ?string $adminPassword = null;

    private static ?string $demoPassword = null;

    /**
     * Generate a cryptographically secure hex secret of the given byte length.
     */
    public static function generate(int $bytes = 24): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Resolve the main administrator password.
     */
    public static function resolveAdminPassword(): string
    {
        $configured = config('crm.admin_password');

        return filled($configured) ? (string) $configured : (self::$adminPassword ??= self::generate(8));
    }

    /**
     * Resolve the demo account password.
     */
    public static function resolveDemoPassword(): string
    {
        $configured = config('crm.demo_password');

        return filled($configured) ? (string) $configured : (self::$demoPassword ??= self::generate(8));
    }
}
