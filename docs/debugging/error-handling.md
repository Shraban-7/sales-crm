# Exception & Error Handling Architecture — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/architecture/backend-architecture.md`, `docs/debugging/logging-and-monitoring.md`

## 1. Custom Domain Exception Hierarchy (`app/Exceptions/`)

| Exception Class | HTTP Status | When Thrown |
| :--- | :---: | :--- |
| `DomainRuleException` | `409 Conflict` / `422` | Business invariant violated (e.g., converting an already converted lead, editing a sent quotation, deleting a company with open deals) |
| `ImmutableRecordException` | `403 Forbidden` / `500` | Attempt to update or delete an `AuditLog` or `system` activity |
| `TenantIsolationException` | `403 Forbidden` | Cross-team foreign key reference detected at service layer |

## 2. Centralized Exception Rendering (`bootstrap/app.php`)

In [Laravel 12](https://laravel.com/framework/docs/12.x/configuration), exception rendering and reporting are configured in `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (DomainRuleException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'DOMAIN_RULE_VIOLATION',
            ], 409);
        }

        return back()->withInput()->with('error', $e->getMessage());
    });
})
```

## 3. Custom Branded Error Views (`resources/views/errors/`)

Provide clean, Tailwind CSS v4-styled error pages that never leak stack traces, SQL queries, or file paths:
- `resources/views/errors/403.blade.php` — Access Denied (with link back to Dashboard)
- `resources/views/errors/404.blade.php` — Record Not Found
- `resources/views/errors/419.blade.php` — Session Expired (with button to reload and sign in)
- `resources/views/errors/500.blade.php` — Unexpected Server Error (displays correlation `request_id` for support)
