<?php

declare(strict_types=1);

return [
    'currency' => env('CRM_DEFAULT_CURRENCY', 'BDT'),
    'country' => env('CRM_DEFAULT_COUNTRY', 'BD'),
    'pagination' => [
        'per_page' => (int) env('CRM_PER_PAGE', 20),
    ],
    'quotations' => [
        'discount_approval_threshold' => (float) env('CRM_QUOTE_DISCOUNT_THRESHOLD', 15.00),
        'number_prefix' => env('CRM_QUOTE_PREFIX', 'QT'),
    ],
    'audit' => [
        'retention_days' => (int) env('CRM_AUDIT_RETENTION_DAYS', 365),
    ],
    'reports' => [
        'cache_ttl_seconds' => (int) env('CRM_REPORT_CACHE_TTL', 300),
        'max_date_range_days' => 366,
    ],
];
