# ADR-002: Relational MySQL 8.0+ Schema with Polymorphic Engagement Hubs

**Date:** 2026-11-17  
**Status:** `Accepted (Implemented)`  
**Deciders:** Database Architect, Principal Software Architect  
**Related Docs:** `docs/database/database-design.md`, `docs/database/database-relationships.md`, `docs/database/erd.md`

## 1. Context

CRM entities (`Lead`, `Contact`, `Company`, `Deal`, `Quotation`) all require chronological activities, tasks, follow-ups, file attachments, and audit trails, while commercial entities require strict foreign-key referential integrity and exact decimal currency arithmetic.

## 2. Options Considered

1. **Separate Activity/Task Tables per Entity (`lead_tasks`, `deal_tasks`, `contact_tasks`):** Creates 15+ duplicate tables and complicates unified "My Tasks" and "Today's Follow-Ups" views.
2. **Hybrid Relational Core + Enforced Polymorphic Engagement Tables on MySQL 8.0+ (`InnoDB`):** Uses strict foreign keys for commercial entities (`companies`, `contacts`, `deals`, `quotations`, `quotation_items`) and indexed polymorphic relations (`Relation::enforceMorphMap`) for `activities`, `tasks`, `follow_ups`, `attachments`, and `audit_logs`.

## 3. Proposed Decision

Adopt **MySQL 8.0+ (`InnoDB`)** with:
- Strict foreign keys (`RESTRICT` on parent commercial tables, `CASCADE` on child value-object line items).
- `DECIMAL(15, 2)` for all monetary fields.
- Polymorphic tables backed by `Relation::enforceMorphMap()` and composite `(subject_type, subject_id, ...)` indexes.

## 4. Consequences

- **Positive:** Unified cross-entity task/follow-up queues, single timeline component (`<x-crm.timeline-feed>`), exact financial precision.
- **Negative / Mitigation:** Polymorphic columns cannot use native SQL foreign keys; mitigated via application-level `MorphMap` enforcement and soft-delete cleanup observers.
