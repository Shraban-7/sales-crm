# Customer Follow-Ups & SLA Cadence Module (MOD-09)

**Last Updated:** 2026-11-17  
**Implementation Status:** `Proposed (0% Implemented — Greenfield Specification)`  
**Related Docs:** `PROJECT_STATUS.md`, `docs/database/database-design.md`, `docs/security/role-permission-matrix.md`

## 1. Business Objectives

Ensure zero prospect or deal drop-off by enforcing scheduled next-contact commitments, multi-channel follow-up cadences (`call`, `email`, `whatsapp`, `meeting`), outcome logging, and overdue SLA escalation.

## 2. Implementation Status Separation

### 2.1 Existing / Implemented Functionality
- None (`0% Implemented` — Greenfield repository).

### 2.2 Proposed Functionality
- Scheduled follow-up commitments linked polymorphically to Leads, Contacts, Companies, and Deals.
- Chained follow-up workflow (`Complete & Schedule Next Follow-Up` linking `next_follow_up_id`).
- Automatic `missed` status flagging and manager escalation when `scheduled_at` passes without completion.
- Guardrail warning when an active Open Deal has zero scheduled future follow-ups.

## 3. Functional Requirements & User Workflows

```mermaid
flowchart LR
    A["Follow-Up Scheduled (status: scheduled)"] --> B{"Completed Before scheduled_at?"}
    B -->|Yes| C["Outcome Logged (status: completed)"]
    C --> D["Chain Next Follow-Up (next_follow_up_id)"]
    B -->|No (Overdue > SLA)| E["Flagged Missed + Owner/Manager Alerted"]
    E --> F["Rescheduled or Completed with Notes"]
```

## 4. Permissions & Access Control

| Permission Slug | Authorized Roles |
| :--- | :--- |
| `follow_ups.view.own` | All Authenticated Roles |
| `follow_ups.view.team` | Sales Manager, Org Admin, Super Admin |
| `follow_ups.create` | Sales Rep, Sales Manager, Org Admin, Super Admin |
| `follow_ups.update.own` | Sales Rep, Sales Manager, Org Admin, Super Admin |
| `follow_ups.delete` | Sales Manager, Org Admin, Super Admin |

## 5. Business Rules

- Completing a follow-up requires a non-empty `outcome_notes` summary and automatically writes a corresponding touchpoint entry to the `activities` timeline.
- If a target Contact has `do_not_contact = true`, scheduling a new outbound follow-up is blocked with a validation error.
- Rescheduling a follow-up preserves audit history and updates `scheduled_at`.

## 6. Database Entities

- **Primary Schema:** `follow_ups` (`id`, `team_id`, `owner_id`, `subject_type`, `subject_id`, `channel`, `status`, `scheduled_at`, `completed_at`, `objective`, `outcome_notes`, `next_follow_up_id`, `timestamps`, `softDeletes`).
- See `docs/database/migrations-and-indexes.md` for composite indexes and foreign key constraints.

## 7. Eloquent Relationships

- `FollowUp::subject()` -> `MorphTo()` (`lead`, `contact`, `company`, `deal`)
- `FollowUp::owner()` -> `BelongsTo(User::class, 'owner_id')`
- `FollowUp::nextFollowUp()` -> `BelongsTo(FollowUp::class, 'next_follow_up_id')`
- `FollowUp::reminders()` -> `MorphMany(Reminder::class, 'remindable')`

## 8. Input Validation Rules (`FormRequest`)

```php
// app/Http/Requests/FollowUps/StoreFollowUpRequest.php
public function rules(): array
{
    return [
        'subject_type' => ['required', 'string', Rule::in(['lead', 'contact', 'company', 'deal'])],
        'subject_id' => ['required', 'integer'],
        'channel' => ['required', Rule::in(['call', 'email', 'whatsapp', 'meeting'])],
        'scheduled_at' => ['required', 'date', 'after:now'],
        'objective' => ['required', 'string', 'max:255'],
    ];
}
```

## 9. Edge Cases & Concurrency Handling

- Deal closed (`won` or `lost`) while future follow-ups are still scheduled: `MoveDealStageAction` prompts or automatically cancels pending sales follow-ups on closed-lost deals.
- Chaining follow-ups prevents orphaned deals by prompting the rep in the same modal.

## 10. Error Scenarios & HTTP Responses

- `422 Unprocessable Entity`: Scheduling a follow-up in the past or completing without `outcome_notes`.
- `403 Forbidden`: Modifying another rep's follow-up outside team hierarchy.

## 11. UI & Frontend Requirements (Blade + Tailwind CSS v4)

Daily Follow-Up Queue (`resources/views/pages/follow-ups/index.blade.php`) segmented into `Overdue (Red)`, `Today (Amber/Blue)`, and `Upcoming`, plus a `Complete & Schedule Next` modal.

## 12. Automated Test Scenarios (Pest / PHPUnit)

- `test('completing a follow-up requires outcome notes and creates an activity log')`
- `test('complete and schedule next links next_follow_up_id atomically')`
- `test('cannot schedule outbound follow-up for contact with do_not_contact enabled')`
