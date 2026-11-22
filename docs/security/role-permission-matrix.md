# Role-Permission Matrix — Sale Fusion CRM

**Last Updated:** 2026-11-22
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/security/authorization.md`, `docs/modules/users-teams-and-permissions.md`

## 1. Canonical CRM Roles

1. **Super Admin:** Full system configuration and cross-organization access, with domain rules still enforced.
2. **Org Admin:** Organization-wide CRM administration; cannot modify Super Admin accounts.
3. **Head of Sales:** Inherits the Org Admin permission set and organization-wide CRM access.
4. **Sales Manager:** Team-scoped CRM management, reassignment, discount approval, and reporting.
5. **Sales Rep:** Manages records they own within the assigned team.
6. **Sales Executive:** Inherits the Sales Rep permission set.
7. **Read-Only Analyst:** Read-only access to organization-wide commercial records and reports.
8. **Viewer:** Inherits the Read-Only Analyst permission set.

These role identifiers are defined by `app/Enums/RoleId.php`; the permission inheritance is applied by `RolePermissionSeeder`.

## 2. Granular Permission Matrix

Legend: `All` = Organization-wide | `Team` = Within `user->team_id` | `Own` = Where `owner_id === user->id` | `—` = Denied (`403`)

The table below shows the base roles. Head of Sales inherits Org Admin permissions, Sales Executive inherits Sales Rep permissions, and Viewer inherits Read-Only Analyst permissions.

| Module & Action | Permission Slug | Super Admin | Org Admin | Sales Manager | Sales Rep | Read-Only Analyst |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: |
| **Leads: View** | `leads.view.*` | All | All | Team | Own | All |
| **Leads: Create** | `leads.create` | Yes | Yes | Yes | Yes | — |
| **Leads: Update** | `leads.update.*` | All | All | Team | Own | — |
| **Leads: Convert** | `leads.convert` | All | All | Team | Own | — |
| **Leads: Delete** | `leads.delete` | All | All | Team | — | — |
| **Contacts: View / Edit** | `contacts.*` | All | All | Team | Own | View All |
| **Contacts: Delete** | `contacts.delete` | All | All | Team | — | — |
| **Companies: View / Edit** | `companies.*` | All | All | Team | Own | View All |
| **Companies: Delete** | `companies.delete` | All | All | Team | — | — |
| **Pipelines: Configure** | `pipelines.manage` | All | All | — | — | — |
| **Deals: View** | `deals.view.*` | All | All | Team | Own | All |
| **Deals: Create / Edit / Move** | `deals.update.*` | All | All | Team | Own | — |
| **Deals: Reassign Owner** | `deals.reassign` | All | All | Team | — | — |
| **Deals: Delete** | `deals.delete` | All | All | Team | — | — |
| **Activities & Follow-Ups** | `activities.*` / `follow_ups.*` | All | All | Team | Own | View All |
| **Tasks: Assign Others** | `tasks.assign.team` | All | All | Team | — (Self Only) | — |
| **Quotations: Create / Send** | `quotations.create` / `send` | All | All | Team | Own | — |
| **Quotations: Approve High Discount** | `quotations.approve_discount` | All | All | Team | — | — |
| **Reports & Forecasts** | `reports.view.*` | All | All | Team | Own | All |
| **Bulk CSV Import** | `imports.create` | All | All | Team | — | — |
| **Bulk CSV Export** | `exports.create.*` | All | All | Team | — | All |
| **Users & Teams Admin** | `users.manage` / `teams.manage` | All | All | — | — | — |
| **Audit Logs Explorer** | `audit_logs.view.*` | All | All | — | — | — |
