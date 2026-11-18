# Role-Permission Matrix — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/security/authorization.md`, `docs/modules/users-teams-and-permissions.md`

## 1. Canonical CRM Roles

1. **Super Admin (`super_admin`):** Full system configuration, role management, all-team record access, audit log access. Bypass granted via `Gate::before` except on immutable domain rules (e.g., cannot edit an already-converted lead or mutate an audit log).
2. **Org Admin (`org_admin`):** Manages users, teams, pipelines, stages, imports/exports, and all commercial records across teams; cannot modify `Super Admin` accounts.
3. **Sales Manager (`sales_manager`):** Full view, edit, reassignment, discount approval, and reporting visibility across all records within their assigned `team_id`.
4. **Sales Rep (`sales_rep`):** Creates and manages their own (`owner_id === $user->id`) Leads, Contacts, Companies, Deals, Activities, Tasks, Follow-Ups, and Draft Quotations.
5. **Read-Only Analyst (`read_only_analyst`):** Read-only access to commercial records, pipelines, and executive analytics reports; zero create/update/delete capabilities.

## 2. Granular Permission Matrix

Legend: `All` = Organization-wide | `Team` = Within `user->team_id` | `Own` = Where `owner_id === user->id` | `—` = Denied (`403`)

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
