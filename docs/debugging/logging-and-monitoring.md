# Logging & Observability Standards — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/modules/audit-logs.md`, `docs/debugging/error-handling.md`

## 1. Application Logs vs. Business Audit Logs

- **Application Logs (`storage/logs/laravel-YYYY-MM-DD.log`):** Operational diagnostics, exceptions, slow query warnings (`> 500ms`), and queue job failures managed via Laravel's `daily` log channel (`LOG_CHANNEL=daily`, `LOG_DAYS=30`).
- **Security & Compliance Audit Logs (`audit_logs` MySQL table):** Structured, queryable record of user-initiated CRM mutations (`LeadConverted`, `DealStageMoved`, `QuotationAccepted`, `RoleChanged`, `DataExported`).

## 2. Structured Log Context Middleware (`AddLogContextMiddleware`)

Every incoming request attaches non-sensitive correlation metadata via `Log::withContext()`:

```php
Log::withContext([
    'request_id' => (string) Str::uuid(),
    'user_id' => $request->user()?->id,
    'team_id' => $request->user()?->team_id,
    'route' => $request->route()?->getName(),
]);
```

## 3. Strict PII & Secret Redaction in Logs

Never pass raw `$request->all()`, plaintext passwords, session cookies, or full customer CSV rows into `Log::info()` or `Log::error()`. Log only entity IDs (`lead_id`, `deal_id`, `quotation_id`) and status codes.
