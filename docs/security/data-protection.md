# Data Protection, Privacy & PII Governance — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/security/security-policy.md`, `docs/modules/audit-logs.md`, `docs/deployment/backup-and-recovery.md`

## 1. Classification of Sensitive CRM Data

| Data Tier | CRM Examples | Protection Requirement |
| :--- | :--- | :--- |
| **Tier 1: Credentials & Secrets** | `users.password`, `remember_token`, session payload, `.env` keys | Argon2id/Bcrypt hashing; `APP_KEY` AES-256-CBC encryption; never logged or serialized (`$hidden`) |
| **Tier 2: Customer PII** | Contact/Lead `email`, `phone`, `mobile`, billing/shipping addresses | Scoped access via `VisibleToUserScope`; TLS 1.2/1.3 in transit; audit logged on bulk export |
| **Tier 3: Commercial & Financial** | `deals.value`, `quotations.grand_total`, discount rates, win/loss notes | Team/Owner policy enforcement; immutable quotation snapshots once sent |

## 2. Eloquent Model Serialization Guards (`$hidden`)

Every `User` model must explicitly hide authentication secrets from JSON/array serialization:
```php
protected $hidden = [
    'password',
    'remember_token',
];
```

## 3. Audit Log Redaction Rules

The `AuditLoggerService` maintains a hard-coded denylist of sensitive fields (`password`, `remember_token`, `secret`, `token`, `api_key`) that are stripped before writing `old_values` and `new_values` JSON payloads into `audit_logs`.

## 4. Communication Consent (`do_not_contact`) & Data Retention

- When a Contact or Lead requests opt-out, setting `do_not_contact = true` immediately suppresses automated follow-up emails and displays a warning badge on the entity profile.
- Soft-deleted records (`deleted_at IS NOT NULL`) are excluded from standard queries and can be permanently purged (`forceDelete`) by a scheduled compliance command after the configured retention period (default `730 days`).
