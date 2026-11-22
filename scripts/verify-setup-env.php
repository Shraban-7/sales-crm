<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$root = dirname(__DIR__);
Dotenv::createImmutable($root)->safeLoad();

$required = [
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'CRM_ADMIN_PASSWORD',
    'CRM_DEMO_PASSWORD',
];

$missing = array_values(array_filter($required, static function (string $key): bool {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    return ! is_string($value) || trim($value) === '' || str_starts_with(trim($value), 'replace-with-');
}));

if ($missing !== []) {
    fwrite(STDERR, "Configure these values in .env before setup: ".implode(', ', $missing).PHP_EOL);
    fwrite(STDERR, "Create the MySQL database first; the setup command does not create databases.".PHP_EOL);
    exit(1);
}
