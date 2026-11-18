# ADR-001: Modular Monolith Application Architecture on Laravel 12

**Date:** 2026-11-17  
**Status:** `Accepted (Implemented)`  
**Deciders:** Principal Software Architect, Project Owner  
**Related Docs:** `docs/architecture/system-architecture.md`, `docs/architecture/application-structure.md`

## 1. Context

The **Sales CRM** project requires a maintainable, high-velocity architecture supporting 14 interconnected CRM domains (Leads, Contacts, Companies, Pipelines, Deals, Tasks, Activities, Follow-Ups, Quotations, Reports, RBAC, Notifications, Imports/Exports, Audit Logs) developed primarily via Google Gemini Antigravity IDE.

## 2. Options Considered

1. **Distributed Microservices:** High operational overhead, distributed transaction complexity for atomic Lead -> Company/Contact/Deal conversion, unnecessary network latency.
2. **Decoupled SPA API + Separate Frontend Repo:** Doubles deployment, validation, and authorization maintenance surface.
3. **Laravel 12 Modular Monolith with Server-Rendered Blade + Progressive Vanilla JS:** Single deployable unit with strict internal domain boundaries (`app/Actions`, `app/Services`, `app/Policies`, `app/Data`).

## 3. Proposed Decision

Adopt a **Modular Monolith on [Laravel 12](https://laravel.com/framework/docs/12.x/configuration)**. All CRM domain modules share a single repository and MySQL 8.0+ database, allowing multi-entity mutations (such as Lead Conversion and Quotation Acceptance) to execute inside ACID `DB::transaction()` boundaries while keeping domain logic isolated in typed Services and Actions.

## 4. Consequences

- **Positive:** Fast local and AI-assisted development in Antigravity IDE; ACID transactions across CRM modules; simple deployment and testing.
- **Negative / Mitigation:** Requires strict discipline so controllers do not bypass domain services—enforced via and code review prompts.
