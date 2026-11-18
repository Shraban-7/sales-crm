# Sales CRM — Product & Code Review

**Review date:** 18 November 2026
**Repository:** `D:\projects\php_projects\sales-crm`
**Revision reviewed:** `cafa2db` (`add pipeline kanaban`)
**Review lens:** Sales, lead, and marketing CRM product management — not only code quality.
**Status:** Read-only review. No application source files were changed. This report is the only new file.

---

## 1. Executive verdict

**Strong engineering foundation, incomplete CRM product.**

The codebase is well above the quality of a typical Laravel MVP. It has a coherent domain model, typed enums, invokable actions, policies, form requests, server-side quotation totals, immutable audit logging, CSV export hardening, and an atomic lead-conversion flow.

As an operating sales CRM, however, the product is still a **pipeline and quotation tracker with manual data entry**, not a system that can capture, route, nurture, and convert demand on its own. The lead-to-cash loop is only partly closed, reporting semantics are misleading, and several capabilities exist in the backend with no usable interface.

### Readiness scorecard

| Area | Rating | Summary |
|---|---|---|
| Architecture and code structure | Strong | Consistent actions/enums/policies, clear boundaries |
| Authentication and authorization | Adequate | Solid basics, team scoping, but one role/list mismatch |
| Lead management | Weak | Manual capture only, hardcoded source, limited editing, no routing |
| Deal pipeline | Adequate | Good stage history; no deal editing, weak lost-reason capture |
| Quotations | Weak | No delivery, no customer acceptance, no expiry, no approval gate |
| Reporting and analytics | Weak | Wrong date semantics, mixed-currency totals, missing filters/panels |
| Communications and reminders | Missing | No email/SMS integration, no automated reminders |
| Mobile / field usability | Missing | No mobile navigation at all |
| Data quality and admin operations | Weak | Import and reassignment defects, backend-only admin routes |
| Testing and CI | Weak | 15 tests, no CI, Pint fails on 11 files |

### Best-fit use today

- Portfolio, demo, or internal pilot with disciplined manual entry.
- A small team that already has deals and only needs a shared pipeline.

### Not ready for

- Inbound lead capture, assignment, and follow-up at volume.
- Sending quotations and recording customer acceptance.
- Management reporting used for forecasting or compensation.
- Field sales work on phones or tablets.

---

## 2. Review method

- Logged into the live app as `manager@salescrm.test` and walked through dashboard, leads, kanban, reports, quotations, tasks, follow-ups, users, and pipeline configuration.
- Read routes, controllers, actions, policies, form requests, models, migrations, seeders, Blade views, jobs, and tests.
- Ran the test suite and the Pint style check.
- Cross-checked the UI against the backend to identify capabilities that exist in code but not in the product.

---

## 3. Severity model

| Level | Meaning |
|---|---|
| **P0** | Blocks real-world use or makes a core workflow misleading/ineffective |
| **P1** | Causes incorrect data, broken permissions, or significant operational friction |
| **P2** | Quality, maintainability, documentation, and polish issues |

---

## 4. Findings summary

| ID | Sev. | Area | Finding |
|---|---|---|---|
| P0-01 | P0 | Leads | Lead capture is manual only; source is hardcoded; no routing or duplicate detection |
| P0-02 | P0 | Leads | Lead detail only edits status; disqualified leads can be converted |
| P0-03 | P0 | Quotations | "Send" does not deliver anything; no PDF/email/public acceptance; expiry never applied |
| P0-04 | P0 | Communications | No email integration, sales notifications, or automated reminders |
| P0-05 | P0 | Mobile | Sidebar is hidden below `md` with no replacement; app unusable on phones |
| P1-01 | P1 | Reports | Date filter uses `deals.created_at`, so won revenue and pipeline are wrong |
| P1-02 | P1 | Reports | Multi-currency deal values are summed without conversion |
| P1-03 | P1 | Reports | Lead sources computed but not shown; no owner/team filters; escaped `&amp;` in header |
| P1-04 | P1 | Dashboard | Pending Tasks KPI is capped at 6 |
| P1-05 | P1 | Deals | Lost reason auto-filled with placeholder; no deal edit screen |
| P1-06 | P1 | Permissions | Read-Only Analyst sees all tasks but gets 403 on record view |
| P1-07 | P1 | Admin | User reassignment leaves quotations/activities inconsistent and ignores target team |
| P1-08 | P1 | Import | CSV sanitizer corrupts phone numbers; duplicate strategy only covers leads |
| P1-09 | P1 | Attachments | Attachments are modelled and migrated but have no routes or UI |
| P1-10 | P1 | Admin UI | User update/reassign and pipeline-stage management routes are unreachable |
| P1-11 | P1 | Quotations | Discount approval threshold and permission are unused |
| P2-01 | P2 | Testing | Only 15 tests; no CI or static analysis |
| P2-02 | P2 | Style | Pint fails on 11 files |
| P2-03 | P2 | Docs | README says 0% implemented; `PROJECT_STATUS.md` says 100% |
| P2-04 | P2 | Security | No 2FA or sales-notification system; security posture plateaus early |

---

## 5. P0 — Release blockers

### P0-01 — Lead capture is manual, mislabeled, and unrouted

**Evidence**

- Quick-add hardcodes the source: `<input type="hidden" name="source" value="website">` — `resources/views/pages/leads/index.blade.php:23`.
- There is no public lead form, API intake, webhook, or email-to-lead path.
- No assignment/routing rules exist. Imported and manually created leads are owned by the actor (`ProcessCsvImportJob::importLead` sets `owner_id = $job->user_id`).
- Duplicate detection exists only in CSV import, and only for leads by email within a team (`ProcessCsvImportJob:181-198`). Manual creation has no dedupe.
- `LeadSource` has multiple cases, but the capture UI only ever produces `website` or `import`.

**Why it matters**

Marketing cannot feed the CRM. Every inbound lead must be typed by a rep, every lead is falsely attributed to "website," and managers cannot distribute work. Source-based reporting and attribution are meaningless from day one.

**Recommended fix**

1. Add a source selector (and owner/team selector where permitted) to quick-add.
2. Add a rate-limited public capture endpoint with honeypot/spam protection, plus an authenticated API/webhook intake.
3. Implement round-robin or territory-based assignment rules.
4. Add duplicate detection and merge for manual creation and imports.
5. Backfill/replace the hardcoded `website` default with an explicit choice.

---

### P0-02 — Lead editing and conversion rules are incomplete

**Evidence**

- The lead detail page only posts `status` plus hidden copies of `first_name`, `email`, `phone`, and `source` — `resources/views/pages/leads/show.blade.php:37-54`. Last name, company, job title, website, score, and estimated value cannot be edited.
- The convert button is rendered for any user with the `convert` permission (`show.blade.php:63`), and `ConvertLeadToDealAction` only rejects leads already marked `converted` — `app/Actions/Leads/ConvertLeadToDealAction.php:34-35`. A disqualified lead can therefore be converted.
- The update request supports rich fields (`UpdateLeadRequest`), but the UI exposes only status.

**Why it matters**

Reps cannot correct or enrich lead data, which is the daily work of lead management. Converting a disqualified lead creates pipeline records that contradict the qualification decision, polluting conversion metrics and forecasts.

**Recommended fix**

1. Add a full lead edit form (inline or dedicated page).
2. Block conversion unless the lead is in an allowed status (e.g. `new`, `contacted`, `qualified`), and surface why in the UI.
3. Reuse `UpdateLeadRequest` validation for the full form.

---

### P0-03 — Quotations are tracked, not delivered

**Evidence**

- `QuotationWorkflowController::send` only sets `status = Sent` and `sent_at` — `app/Http/Controllers/Quotations/QuotationWorkflowController.php:20-34`. No email, PDF attachment, or customer link.
- A print view exists (`resources/views/pages/quotations/print.blade.php`), but the rep must print/save it and deliver it outside the CRM.
- `accept` is an internal POST that any authorized staff member can trigger immediately after creation. `AcceptQuotationAction` permits both `Draft` and `Sent` (`app/Actions/Quotations/AcceptQuotationAction.php:26`), so an unsent quote can be marked accepted.
- `QuotationStatus::Expired` is declared (`app/Enums/QuotationStatus.php:13`) but never set anywhere in `app/`. There is no scheduled command to expire stale quotes.
- No public, signed accept/reject page exists. Customer consent is unverifiable.

**Why it matters**

"Sent" and "Accepted" are assertions made by staff, not auditable customer events. Quotes can be accepted without ever reaching the customer, and stale quotes remain acceptable indefinitely. This undermines the core revenue workflow and any quote-conversion reporting.

**Recommended fix**

1. Generate a PDF (or signed, expiring public URL) for each quotation.
2. Email the quote from the CRM with delivery tracking.
3. Add a signed public accept/reject page; record IP/timestamp and acceptance evidence.
4. Enforce transitions: `draft → sent → accepted/rejected/expired`; only `sent` can be accepted by the customer.
5. Add a scheduled expiry command using `valid_until`, and display expiry state in the UI.

---

### P0-04 — No communication or reminder layer

**Evidence**

- No mail/SMS/WhatsApp integration, no email logging, no inbound email parsing.
- Activities are logged manually through `LogActivityAction`; there is no automatic capture.
- No scheduled reminder notifications for tasks or follow-ups. `CrmAlertNotification` is used only for import completion (`ProcessCsvImportJob:145`).
- Tasks and follow-ups are passive lists; nothing reaches the user when something is due.

**Why it matters**

A CRM that does not communicate is a database that reps must remember to update. Follow-up discipline — the single biggest driver of lead conversion — is left entirely to human memory.

**Recommended fix**

1. Add transactional email for quotes and follow-ups.
2. Log sent/received emails against the lead/deal timeline.
3. Add scheduled notifications for due tasks and follow-ups (in-app first, email second).
4. Consider inbound email parsing or a provider integration (Postmark, SES, Mailgun) with webhook activity logging.

---

### P0-05 — The app is unusable on mobile

**Evidence**

- The sidebar is `hidden md:flex` with no hamburger or drawer replacement — `resources/views/components/layouts/app.blade.php:12`.
- No mobile navigation component or responsive menu exists.

**Why it matters**

Field sales is phone-first. Without navigation, reps cannot use the CRM between meetings, which is where most CRM updates happen.

**Recommended fix**

Add a mobile header with a drawer/hamburger menu and verify core flows (leads, deals, tasks, quote view) at 375px and 768px.

---

## 6. P1 — Significant defects and friction

### P1-01 — Reporting date semantics are wrong

**Evidence**

`AnalyticsService` filters open pipeline, won revenue, and the leaderboard by `deals.created_at` — `app/Services/Reports/AnalyticsService.php:44`.

**Impact**

- A deal created 1 September and won 5 October is excluded from October's won revenue and included in September's — even though it was not won then.
- "Open pipeline" only includes deals created inside the selected range, so it shrinks artificially for short ranges.
- Period-over-period comparisons and forecasts are unreliable.

**Fix**

- Use a `closed_at` timestamp for won/lost revenue and conversion metrics.
- Use `expected_close_date` for pipeline/forecast views.
- Keep `created_at` only for "new deals created" metrics, and label it explicitly.

---

### P1-02 — Multi-currency totals are meaningless

**Evidence**

Deals store a `currency` (`database/migrations/0001_01_02_000005_create_deals_and_pivots_tables.php:23`), but analytics uses raw `SUM(deals.value)` for pipeline, weighted forecast, won revenue, and the leaderboard (`AnalyticsService:58-109`). Quotations also carry their own currency.

**Impact**

BDT, USD, and any other currency are added as if they were the same unit. Pipeline and revenue KPIs, plus the leaderboard, are numerically invalid for any organization using more than one currency.

**Fix**

- Either normalize to a base currency using stored exchange rates, or
- Group and display totals per currency, and never present a single blended number.
- Show the currency/date basis next to each KPI.

---

### P1-03 — Reports UI omits available data and has a rendering bug

**Evidence**

- `lead_sources` is computed at `AnalyticsService:124` but never rendered in `resources/views/pages/reports/index.blade.php`.
- No owner or team filter is present, despite role-based teams existing throughout the app.
- The page header contains a literal double-escaped ampersand: `<x-layouts.app ... header="Reports &amp; Executive Analytics">` — `reports/index.blade.php:1`.

**Impact**

Managers cannot break down performance by source, owner, or team, and the most marketing-relevant dimension is invisible. The header renders as "Reports &amp; Executive Analytics."

**Fix**

Render the lead-source breakdown, add owner/team filters, and use a plain `&` in the Blade attribute.

---

### P1-04 — Dashboard "Pending Tasks" KPI is capped at six

**Evidence**

`DashboardController` fetches tasks with `->limit(6)` (`app/Http/Controllers/DashboardController.php:29-35`), and the KPI renders `$dueTasks->count()` (`resources/views/pages/dashboard.blade.php:6`).

**Impact**

A manager with 40 overdue tasks sees "6." The number is structurally incapable of exceeding the preview limit. The `upcomingFollowUps` query is also fetched but only the task list is used for the KPI.

**Fix**

Run a separate `count()` query for each KPI, independent of the limited preview collections.

---

### P1-05 — Deal closing captures placeholder data; deals cannot be edited

**Evidence**

- The Closed Lost action submits a hidden reason: `<input type="hidden" name="lost_reason" value="Marked lost from deal workspace">` — `resources/views/pages/deals/show.blade.php:14`.
- There is no deal edit form in `deals/show.blade.php`, even though `DealController::update` and `UpdateDealRequest` support title, value, currency, probability, and expected close date.

**Impact**

Lost-reason analytics becomes a single placeholder value. Reps cannot correct deal value, close date, probability, or ownership after creation, so pipeline data drifts and forecasts degrade.

**Fix**

- Require a structured lost reason (select + optional note) in a confirmation dialog.
- Add a deal edit form wired to the existing update endpoint.
- Add an audit event for manual value/date changes.

---

### P1-06 — Read-Only Analyst list/detail permission mismatch

**Evidence**

- `HasVisibilityScope::scopeVisibleTo` returns all records for `Read-Only Analyst` (`app/Models/Concerns/HasVisibilityScope.php:14-16`).
- `TaskPolicy::view` delegates to `canViewScoped`, which grants analyst access only with `tasks.view.all` (`app/Policies/Concerns/ResolvesTeamOwnership.php:14`).
- The Analyst role is seeded with `tasks.view.own` but not `tasks.view.all` (`database/seeders/RolePermissionSeeder.php:166-175`).

**Impact**

Analysts see every task in the list but receive 403 when opening one they do not own. The same mismatch pattern can affect other modules that use `visibleTo` plus per-record policies.

**Fix**

Align the scope and policies: either grant analysts the relevant `view.all` permissions, or restrict list queries to the records the policy permits. Add a feature test for each role × module pair.

---

### P1-07 — User reassignment leaves records inconsistent

**Evidence**

`ReassignUserRecordsAction` updates only:
- open leads,
- open deals,
- pending/in-progress tasks,
- scheduled follow-ups.

It does not move quotations, activities, attachments, or other related records, and it sets `team_id` to the target user's team without validating that the move is allowed (`app/Actions/Users/ReassignUserRecordsAction.php:28-46`).

**Impact**

After reassignment, quotations and activity history for moved deals can remain owned by the old user/team. Visibility rules then hide the history from the new owner, and team-level reporting splits one deal's records across two teams. Cross-team reassignment can silently leak records into another team.

**Fix**

1. Include quotations and activities (and any other owned records) in the transaction, or explicitly reassign by parent deal.
2. Restrict target users to the actor's organization/team unless the actor is an Org/Super Admin.
3. Write audit entries per affected entity or a detailed summary.
4. Add a test that reassigns a user with a deal + quote + activity and asserts visibility for both old and new owners.

---

### P1-08 — CSV import corrupts phone numbers and ignores duplicates outside leads

**Evidence**

- Every CSV cell is passed through `CsvSanitizer::sanitizeCell()` before validation — `app/Jobs/ProcessCsvImportJob.php:100-103`. That sanitizer prefixes values starting with `=`, `+`, `-`, `@` to prevent spreadsheet formula execution, which is correct for export but wrong for inbound data. A phone number `+8801700000000` becomes `'+8801700000000`.
- The duplicate strategy is used only in `importLead` (`ProcessCsvImportJob:181-198`). `importContact` and `importCompany` always create new records.

**Impact**

Imported phone numbers are unusable for dialing or integrations. Re-importing contacts or companies creates unbounded duplicates.

**Fix**

- Do not apply export sanitization to inbound values. Sanitize on export only; for import, validate and normalize per field (phone via a phone library, URLs via URL validation).
- Implement duplicate detection for contacts (email/phone) and companies (name/domain), honoring the chosen strategy.
- Add import tests covering `+` phone numbers and duplicate rows.

---

### P1-09 — Attachments exist only in the database

**Evidence**

- `app/Models/Attachment.php` and `database/migrations/0001_01_02_000011_create_attachments_table.php` exist.
- No attachment routes are registered in `routes/web.php`, and no upload/download UI exists.

**Impact**

Dead domain model increases maintenance cost and misleads developers/users into expecting file support. Sales teams cannot attach proposals, contracts, or proof of delivery.

**Fix**

Either implement upload/download with authorization, virus/type/size validation, and private storage, or remove the model and migration until it is in scope.

---

### P1-10 — Admin capabilities are backend-only

**Evidence**

- `PUT /settings/users/{user}` and `POST /settings/users/{user}/reassign` are registered (`routes/web.php:102-103`), but `resources/views/pages/settings/users/index.blade.php` only renders a create-user form. There is no edit, role-change, deactivate, or reassign UI.
- Pipeline stage `store`, `reorder`, and `destroy` routes exist (`routes/web.php:51-53`), but the pipelines index only creates pipelines and lists stages; there is no stage management UI.

**Impact**

An administrator cannot operate user lifecycle or pipeline configuration from the product. Offboarding and territory changes require developer/database intervention, which is a serious operational risk.

**Fix**

Add user edit/deactivate/reassign controls and pipeline-stage management (add, rename, reorder, delete with deal-count guard).

---

### P1-11 — Discount approval is configured but unenforced

**Evidence**

- `config/crm.php:12` defines `discount_approval_threshold`.
- `quotations.approve_discount` is seeded (`RolePermissionSeeder:96`) and documented, but no application code reads either the config value or the permission.

**Impact**

Any rep can grant an arbitrarily large discount without approval. The permission matrix promises a control that does not exist.

**Fix**

Enforce the threshold in quotation creation/update: require `approve_discount` when discount exceeds the configured percentage, record the approver, and audit the event.

---

## 7. P2 — Quality and maintainability

### P2-01 — Thin test coverage and no CI

- `php artisan test --compact` passes: **15 tests, 58 assertions**.
- Coverage is broad in domain but shallow relative to the surface: no tests for reporting date/currency semantics, reassignment integrity, quote expiry, import phone handling, or admin routes.
- No CI workflow (`.github/workflows` absent) and no static analysis (PHPStan/Larastan) configured.

**Fix:** add CI running tests + Pint, then raise coverage around authorization and money/reporting logic first.

### P2-02 — Pint fails on 11 files

`vendor\bin\pint --test` exits 1. Examples:

- `app/Services/Reports/AnalyticsService.php` — unused imports
- `app/Http/Controllers/Settings/UserController.php` — concat spacing, unary/not spacing
- `app/Notifications/CrmAlertNotification.php` — multiple style issues
- `tests/Feature/ReportsAndImportExportTest.php` — import ordering/strict types

**Fix:** run `vendor\bin\pint` and enforce it in CI.

### P2-03 — Documentation contradicts itself

- `readme.md` describes the project as 0% implemented.
- `PROJECT_STATUS.md` describes it as 100% implemented.

Both cannot be true and neither matches the actual state. Update both to a single, honest feature matrix, or archive one.

### P2-04 — Security/operational maturity gaps

- No two-factor authentication for admin accounts; the audit logger only redacts 2FA fields, it does not implement them.
- No sales notification system beyond import completion.
- No rate limiting specific to a public surface (because there is no public surface yet); add it with lead intake.

---

## 8. What is working well

These should be preserved while fixing the gaps:

- **Consistent architecture:** typed enums, invokable actions, form requests, policies, and domain exceptions used consistently.
- **Atomic lead conversion:** row lock, duplicate-conversion guard, conversion traceability (`converted_*` columns).
- **Deal stage history:** stage transitions recorded with actor and timestamps, enabling real pipeline analytics later.
- **Lost-reason enforcement:** the domain requires a reason even though the UI currently supplies a placeholder.
- **Server-side quotation totals:** line items and totals are not trusted from the client.
- **Quotation version cloning:** useful for revisions and negotiation history.
- **Audit logging with redaction:** sensitive values are not written to audit payloads.
- **CSV export hardening:** formula injection is handled correctly on the outbound path.
- **Auth basics:** login throttling, active-user enforcement, team-based scoping, per-record policies.
- **Schema discipline:** foreign keys, indexes, and sensible column types.
- **UI consistency:** the Blade components produce a coherent visual language.

---

## 9. Recommended remediation order

### Phase 1 — Close the revenue loop (highest ROI)

1. Quote PDF + email delivery + signed public accept/reject page.
2. Enforce `draft → sent → accepted/rejected/expired`; add scheduled expiry.
3. Enforce discount approval threshold and permission.
4. Fix deal closing: structured lost reason, deal edit form.

**Outcome:** quotes reach customers, acceptance is auditable, and deal data stays correct.

### Phase 2 — Build real lead intake

1. Source selector + owner/team assignment on quick-add.
2. Public capture endpoint, API/webhook, spam protection.
3. Duplicate detection and merge for leads/contacts/companies.
4. Full lead edit; block conversion of disqualified leads.
5. Basic assignment rules (round-robin or territory).

**Outcome:** marketing can feed the CRM and managers can route work.

### Phase 3 — Make reporting trustworthy

1. Add `closed_at`; use `expected_close_date` for pipeline/forecast.
2. Normalize or group by currency.
3. Add owner/team filters; render lead-source breakdown.
4. Fix the dashboard KPI counts and the `&amp;` header.
5. Add tests for date ranges, currencies, and role-based visibility.

**Outcome:** managers can make decisions from the numbers.

### Phase 4 — Field readiness and operations

1. Mobile navigation and responsive verification.
2. Email/reminder notifications for tasks and follow-ups.
3. Transactional user reassignment covering quotations/activities.
4. Admin UI for user lifecycle and pipeline stages.
5. Fix import phone handling and non-lead dedupe; ship or remove attachments.

### Phase 5 — Engineering hygiene

1. CI: tests + Pint + static analysis.
2. Expand feature tests around authorization, money, and reporting.
3. Reconcile README and `PROJECT_STATUS.md`.
4. Add 2FA for privileged roles.

---

## 10. Verification appendix

| Check | Command | Result |
|---|---|---|
| Test suite | `php artisan test --compact` | Passed — 15 tests, 58 assertions |
| Code style | `vendor\bin\pint --test` | Failed — 11 files |
| Working tree | `git status --porcelain` | Clean before this report |

### Key files inspected

- `app/Services/Reports/AnalyticsService.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/Quotations/QuotationWorkflowController.php`
- `app/Actions/Quotations/AcceptQuotationAction.php`
- `app/Actions/Leads/ConvertLeadToDealAction.php`
- `app/Actions/Users/ReassignUserRecordsAction.php`
- `app/Jobs/ProcessCsvImportJob.php`
- `app/Support/CsvSanitizer.php`
- `app/Models/Concerns/HasVisibilityScope.php`
- `app/Policies/Concerns/ResolvesTeamOwnership.php`
- `app/Policies/TaskPolicy.php`
- `database/seeders/RolePermissionSeeder.php`
- `config/crm.php`
- `routes/web.php`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/pages/dashboard.blade.php`
- `resources/views/pages/leads/index.blade.php`
- `resources/views/pages/leads/show.blade.php`
- `resources/views/pages/deals/show.blade.php`
- `resources/views/pages/reports/index.blade.php`
- `resources/views/pages/settings/users/index.blade.php`
- `resources/views/pages/pipelines/index.blade.php`

---

## 11. Limitations

- The walkthrough used the Sales Manager role; other roles were assessed from seeders, policies, and code rather than a full UI session each.
- No load, penetration, or accessibility audit was performed.
- Findings reflect revision `cafa2db`; later changes may resolve some items.

---

**Bottom line:** the engineering is good enough to build on. The fastest path from "impressive demo" to "daily-use CRM" is Phase 1 plus Phase 3 — deliver quotes customers can actually receive, and make the numbers managers see true.
