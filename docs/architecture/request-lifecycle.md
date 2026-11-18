# Request & Job Lifecycle — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/architecture/system-architecture.md`, `docs/security/authentication.md`, `docs/security/authorization.md`

## 1. Synchronous Web / AJAX Request Lifecycle

Every incoming HTTP request passes through a deterministic security, validation, authorization, and domain execution pipeline on [Laravel 12](https://laravel.com/framework/docs/12.x/configuration).

```mermaid
sequenceDiagram
    autonumber
    actor User as Sales Rep / Manager
    participant MW as Middleware Stack
    participant FR as FormRequest
    participant Pol as Model Policy
    participant Ctrl as Controller
    participant Act as Domain Action / Service
    participant DB as MySQL 8.0 (InnoDB)
    participant Evt as Event Dispatcher / Audit

    User->>MW: HTTP POST /deals/{deal}/move-stage (CSRF + Session Cookie)
    MW->>MW: Verify CSRF, Auth Session, Active User, Rate Limit, Team Context
    MW->>FR: MoveDealStageRequest::authorize() & rules()
    FR-->>User: 422 Unprocessable Entity (if validation fails)
    FR->>Ctrl: Validated DTO / Request
    Ctrl->>Pol: $this->authorize('changeStage', $deal)
    Pol-->>User: 403 Forbidden (if not owner/team manager)
    Ctrl->>Act: MoveDealStageAction::handle($deal, $newStage, $user)
    Act->>DB: BEGIN TRANSACTION + SELECT FOR UPDATE
    Act->>DB: UPDATE deals SET pipeline_stage_id = ?, probability = ?
    Act->>DB: INSERT INTO deal_stage_histories (...)
    Act->>Evt: Dispatch DealStageMoved Event (writes audit_logs)
    Act->>DB: COMMIT TRANSACTION
    Act-->>Ctrl: Updated Deal Model
    Ctrl-->>User: 200 JSON / 302 Redirect with Flash Message
```

## 2. Middleware Execution Order (`bootstrap/app.php`)

1. `TrustProxies` & `SecurityHeadersMiddleware` (`X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Content-Security-Policy`).
2. `EncryptCookies` & `StartSession`.
3. `ValidateCsrfToken`.
4. `Authenticate` (`auth` guard) & `EnsureUserIsActive` (immediately logs out deactivated users).
5. `ResolveTeamContext` (binds authenticated user's active `team_id` and role permissions for request duration).
6. `ThrottleRequests` (rate-limits login, exports, and API-like AJAX endpoints).
7. `SubstituteBindings` (resolves route-model bindings).

## 3. Asynchronous Queue & Scheduler Lifecycle

Heavy or time-delayed operations never block the HTTP response thread:
- **Queued Jobs (`database` or `redis` driver):**
  - `ProcessCsvImportJob` — Parses uploaded CSV/XLSX chunks (500 rows/chunk), validates rows, inserts records, and generates an error summary file.
  - `GenerateExportFileJob` — Streams filtered CRM records to a private CSV/XLSX file and notifies the requester when ready.
  - `SendNotificationMailJob` — Delivers transactional emails (quote sent, task assigned, deal won).
- **Scheduled Commands (`routes/console.php`):**
  - `crm:dispatch-due-reminders` (runs every minute) — Scans `reminders` and `follow_ups` due within the current window and dispatches notifications.
  - `crm:flag-rotting-deals` (runs hourly) — Identifies open deals exceeding their stage's `rotting_days` threshold without activity and notifies owners.
