# ADR-003: Stateful Web Session Auth + Spatie RBAC + Eloquent Ownership Scopes

**Date:** 2026-11-17  
**Status:** `Accepted (Implemented)`  
**Deciders:** Security Engineer, Principal Software Architect  
**Related Docs:** `docs/security/authentication.md`, `docs/security/authorization.md`, `docs/security/role-permission-matrix.md`

## 1. Context

Sale Fusion CRM requires role-based capabilities across 5 roles (`Super Admin`, `Org Admin`, `Sales Manager`, `Sales Rep`, `Read-Only Analyst`) combined with strict record-level isolation (`Own` vs `Team` vs `All`) so sales reps cannot view or mutate competitors' deals within the organization.

## 2. Proposed Decision

1. **Authentication:** Use Laravel's stateful `web` session guard (`SESSION_DRIVER=database`, encrypted cookies, CSRF protection, login rate limiting, and `EnsureUserIsActive` middleware).
2. **Role & Permission Management:** Use **[Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v8/installation-laravel)** for role and permission assignment.
3. **Row-Level Ownership & Team Isolation:** Combine Laravel Model Policies (`DealPolicy`, `LeadPolicy`, etc.) for single-record checks with a reusable `HasVisibilityScope` (`scopeVisibleTo(Builder $query, User $user)`) trait for all list, search, report, and export queries.

## 3. Consequences

- **Positive:** Eliminates IDOR vulnerabilities across both detail endpoints and list/export queries; cached permission checks incur near-zero overhead.
- **Negative / Mitigation:** Developers must remember to apply `->visibleTo($user)` on custom queries—enforced by `docs/testing/authorization-testing.md`.
