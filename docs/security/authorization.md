# Authorization, RBAC & Data Isolation (IDOR Prevention) — Sale Fusion CRM

**Last Updated:** 2026-11-22
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/security/role-permission-matrix.md`, `docs/adr/ADR-003-authentication-and-authorization.md`

## 1. Dual-Layer Authorization Model

To guarantee that users can neither execute unauthorized actions nor access records belonging to other sales reps/teams (Insecure Direct Object Reference — IDOR), **Sale Fusion CRM** enforces a two-layer authorization model:

1. **Capability Layer ([Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v8/installation-laravel)):** Checks whether the user's assigned Role grants the permission slug (e.g., `deals.update.own` vs `deals.update.team`).
2. **Record Ownership & Team Layer (Laravel Policies + Eloquent Query Scopes):** Verifies whether the specific model instance's `owner_id` or `team_id` matches the authenticated user.

The canonical role identifiers and shared role groups are defined in `app/Enums/RoleId.php`. The seeded roles are Super Admin, Org Admin, Head of Sales, Sales Manager, Sales Rep, Sales Executive, Read-Only Analyst, and Viewer. Head of Sales inherits the Org Admin permission set; Sales Executive inherits Sales Rep permissions; Viewer inherits Read-Only Analyst permissions.

## 2. Record Visibility Query Scope (`HasVisibilityScope` Trait)

The CRM models use the `HasVisibilityScope` trait. Call `visibleTo($user)` explicitly on list, search, dashboard, report, export, and import duplicate queries; it is an opt-in local scope, not a global scope. Organization readers (including Head of Sales and Viewer) can read all rows. Sales Managers see their team and legacy unassigned rows; sales representatives see owned rows in their team and legacy unassigned rows. A user with no team is restricted to their own rows (or unassigned team rows for an unassigned manager).

## 3. Policy Implementation Pattern (`app/Policies/DealPolicy.php`)

`DealPolicy` and other scoped policies reuse `ResolvesTeamOwnership` for record ownership checks. Policies may add domain rules for state transitions; for example, converted leads cannot be edited and non-draft quotations cannot be changed.

## 4. Route-Level Role Guards (`app/Http/Middleware/EnsureRoleGroup.php`)

In addition to controller-level Policy checks, **Sale Fusion CRM** enforces dedicated route middleware guards at the HTTP routing boundary (`routes/web.php`) so that unauthorized role types are rejected before controller invocation:

| Role Group | Alias | Permitted Roles | Protected Endpoints |
| :--- | :--- | :--- | :--- |
| `super_admin` | `role.group:super_admin` | `Super Admin` | System super-administration, cross-tenant overrides |
| `organization_admin` | `role.group:organization_admin` | `Super Admin`, `Org Admin`, `Head of Sales` | `/settings/users`, `/settings/teams`, `/settings/stages`, `/settings/smtp`, `/settings/system`, `/settings/webhooks`, `/settings/audit-logs`, `/pipelines` schema mutations |
| `sales_manager` | `role.group:sales_manager` | Organization writers and `Sales Manager` | `/quotations/{quotation}/approve-discount`, `/imports/bulk`, `/targets` configuration |
| `sales_rep` | `role.group:sales_rep` | Organization writers, `Sales Manager`, `Sales Rep`, `Sales Executive` | Commercial mutation routes (`leads.*`, `companies.*`, `contacts.*`, `deals.*`, `tasks.*`, `quotations.*`, `proposals.*`, `mobile.*`) |
| `EnsureNotReadOnly` | `role.not_read_only` | Any non-read-only role | Rejects `Read-Only Analyst` and `Viewer` from all data-mutating routes (`POST`, `PUT`, `PATCH`, `DELETE`) with HTTP 403 Forbidden |
| `EnsureReadOnlyAnalyst`| `role.analyst` | All authenticated roles | Executive analytics dashboards and read-only reports |

