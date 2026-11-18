# Master Test Case Catalog — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/testing/testing-strategy.md`, `docs/modules/`

## 1. End-to-End Test Case Matrix Across Modules

| Test ID | Module | Scenario Description | Input / Precondition | Expected Outcome |
| :--- | :--- | :--- | :--- | :--- |
| `TC-AUTH-01` | Auth | Brute-force login rate limiting | 6 failed login POSTs in 60s | 6th request returns `429 Too Many Requests` + `audit_logs` entry |
| `TC-AUTH-02` | Auth | Deactivated user session termination | Active session user set to `is_active = false` | Next GET request logs user out and returns `403 Forbidden` |
| `TC-LEAD-01` | Leads | Lead creation requires email or phone | POST `/leads` with both `email` and `phone` null | Returns `422` validation error on `email` and `phone` |
| `TC-LEAD-02` | Leads | Atomic lead conversion happy path | Qualified lead converted by owner | Creates `Company`, `Contact`, `Deal`; marks lead `converted`; writes `audit_logs` |
| `TC-LEAD-03` | Leads | Double conversion prevention | POST `/leads/{id}/convert` on already converted lead | Rejected with `409` / domain error; no duplicate Deal created |
| `TC-CONT-01` | Contacts | Do-Not-Contact enforcement | Contact has `do_not_contact = true` | Scheduling outbound email follow-up is rejected with `422` |
| `TC-COMP-01` | Companies | Circular parent hierarchy prevention | Company A sets `parent_company_id = B` where B's parent is A | Rejected with `422 Unprocessable Entity` |
| `TC-PIPE-01` | Pipeline | Atomic stage reordering | Admin reorders stages `[1,2,3]` to `[3,1,2]` | Positions updated in transaction without unique constraint error |
| `TC-DEAL-01` | Deals | Stage transition dwell time calculation | Deal created 2 hours ago moves from Stage 1 to Stage 2 | `deal_stage_histories.duration_in_previous_stage_seconds` is `~7200` |
| `TC-DEAL-02` | Deals | Marking deal lost requires reason | PATCH `/deals/{id}` with `status=lost` and empty `lost_reason` | Rejected with `422`; deal remains `open` |
| `TC-ACT-01` | Activities | First call transitions new lead | Call logged on Lead with `status=new` | Lead status auto-updates to `contacted` and `last_contacted_at` set |
| `TC-TASK-01` | Tasks | Reminder idempotency | `crm:dispatch-due-reminders` run twice in same minute | Notification sent once; `reminders.sent_at` populated on first run |
| `TC-FLW-01` | Follow-Ups | Complete and chain next follow-up | Rep completes follow-up #1 and schedules #2 | Follow-up #1 `next_follow_up_id` points to #2; activity logged |
| `TC-QUOT-01` | Quotations | Server-side decimal math integrity | 2 line items with fractional qty, discount %, and tax % | `subtotal`, `discount_total`, `tax_total`, `grand_total` match exact cent math |
| `TC-QUOT-02` | Quotations | Sent quotation immutability | Attempt to edit line items of a `sent` quotation | Rejected with `409`; requires cloning a new version (`version + 1`) |
| `TC-RPT-01` | Reports | Weighted forecast calculation | 2 open deals (`100k @ 50%`, `200k @ 25%`) | Total pipeline = `300,000.00`; Weighted forecast = `100,000.00` |
| `TC-IMP-01` | Import/Export | CSV formula injection sanitization | Lead name `=1+1` exported to CSV | Cell output is escaped as `'=1+1` |
| `TC-AUD-01` | Audit Logs | Audit log immutability | Call `$auditLog->delete()` or `$auditLog->update()` | Throws `ImmutableRecordException` |
