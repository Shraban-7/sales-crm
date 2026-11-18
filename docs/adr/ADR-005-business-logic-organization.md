# ADR-005: Domain Services, Invokable Actions & Immutable DTOs

**Date:** 2026-11-17  
**Status:** `Accepted (Implemented)`  
**Deciders:** Principal Software Architect, Senior Laravel Engineer  
**Related Docs:** `docs/architecture/backend-architecture.md`, `docs/architecture/design-patterns.md`

## 1. Context

In large CRM applications, placing validation, database transactions, state transitions, and side effects inside Controllers or Eloquent Models leads to untestable, tightly coupled code.

## 2. Proposed Decision

Organize all business logic using a 3-part domain structure:
1. **Immutable DTOs (`app/Data/<Domain>/*Data.php`):** Strongly typed PHP 8.3 `readonly` classes constructed from `FormRequest::validated()`.
2. **Single-Responsibility Actions (`app/Actions/<Domain>/*Action.php`):** Handle atomic write operations (`ConvertLeadToDealAction`, `MoveDealStageAction`, `CalculateQuotationTotalsAction`) inside `DB::transaction()`.
3. **Domain Services (`app/Services/<Domain>/*Service.php`):** Coordinate broader domain workflows, multi-widget analytics queries, and chunked CSV import/export processing.

## 3. Consequences

- **Positive:** Actions and Services can be invoked identically from HTTP Controllers, Artisan CLI commands, Queue Jobs, and fast Unit/Feature tests.
- **Negative / Mitigation:** Slightly more files per feature—kept navigable via strict module sub-namespaces (`App\Actions\Leads\*`).
