# Design Patterns & Anti-Patterns — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/architecture/backend-architecture.md`, `docs/adr/ADR-005-business-logic-organization.md`

## 1. Approved Architectural Patterns

| Pattern | Where Used in Sale Fusion CRM | Purpose & Benefit |
| :--- | :--- | :--- |
| **Action Pattern (Command)** | `app/Actions/<Domain>/*Action.php` | Encapsulates a single atomic business mutation (`ConvertLeadToDealAction`, `MoveDealStageAction`, `ApproveQuotationAction`) callable from Web Controllers, CLI Commands, or Queue Jobs. |
| **Domain Service Pattern** | `app/Services/<Domain>/*Service.php` | Coordinates broader domain calculations or multi-query read/write workflows (`PipelineForecastService`, `CsvImportService`). |
| **Data Transfer Object (DTO)** | `app/Data/<Domain>/*Data.php` | Passes validated, strongly typed data (`readonly class`) from `FormRequest` or CSV rows into Services and Actions instead of untyped associative arrays. |
| **Policy & Gate Pattern** | `app/Policies/*Policy.php` | Centralizes RBAC permission checks combined with ownership (`$deal->owner_id === $user->id`) and team membership (`$deal->team_id === $user->team_id`). |
| **State Machine / Enum Transitions** | `app/Enums/*Status.php` | Defines valid state transitions (e.g., `QuotationStatus::canTransitionTo(QuotationStatus $next): bool`) so invalid status jumps are rejected at the domain level. |
| **Polymorphic Relation Pattern** | `activities`, `tasks`, `notes`, `attachments`, `audit_logs` | Allows a single unified table to attach records to `Lead`, `Contact`, `Company`, `Deal`, or `Quotation` models via `morphTo()`. |
| **Domain Event & Listener Pattern** | `app/Events/` & `app/Listeners/` | Decouples core database transactions from secondary side effects (sending email notifications, recalculating dashboard cache, writing audit logs). |

## 2. State Transition Pattern Example (`app/Enums/QuotationStatus.php`)

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => in_array($target, [self::Sent], true),
            self::Sent => in_array($target, [self::Accepted, self::Rejected, self::Expired], true),
            self::Accepted, self::Rejected, self::Expired => false,
        };
    }
}
```

## 3. Strictly Forbidden Anti-Patterns

1. **Fat Controllers:** Placing 100+ lines of validation, Eloquent queries, financial calculations, and mail dispatching directly inside controller methods.
2. **Generic Repository Layer Wrapping Eloquent:** Creating boilerplate `LeadRepositoryInterface` / `EloquentLeadRepository` classes that merely proxy `find()`, `create()`, and `paginate()` without adding domain value. Use Eloquent Query Scopes and Domain Actions instead.
3. **Business Logic Inside Model Boot/Saving Hooks:** Hiding external side effects (like sending emails or mutating related deals) inside `Model::booted()` hooks where they cannot be easily bypassed in seeders or unit tests. Use explicit Domain Events or Actions.
4. **Unscoped Queries (`Model::all()` / `Model::find($id)`):** Querying CRM records without applying `->visibleTo($request->user())` or `$this->authorize()`.
