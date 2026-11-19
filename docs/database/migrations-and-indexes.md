# Migrations Execution Order & Indexing Blueprint — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (19 Migrations + database/schema.sql Verified)`  
**Related Docs:** `docs/database/database-design.md`, `docs/database/database-optimization.md`, `docs/database/data-integrity.md`

## 1. Ordered Migration Execution Sequence

Because MySQL `InnoDB` foreign key constraints require referenced parent tables to exist before child tables are created, migrations are ordered in the following sequence (`database/migrations/`):

| Order | Migration Filename | Tables Created / Altered | Depends On |
| :--- | :--- | :--- | :--- |
| 01 | `0001_01_01_000000_create_users_table.php` | `teams`, `users`, `password_reset_tokens`, `sessions` | None (links `teams.leader_id` FK cleanly) |
| 02 | `0001_01_01_000001_create_cache_table.php` | `cache`, `cache_locks` | None |
| 03 | `0001_01_01_000002_create_jobs_table.php` | `jobs`, `job_batches`, `failed_jobs` | None |
| 04 | `0001_01_02_000001_create_permission_tables.php` | `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` | `users` |
| 05 | `0001_01_02_000002_create_companies_table.php` | `companies` | `teams`, `users` |
| 06 | `0001_01_02_000003_create_contacts_table.php` | `contacts` | `teams`, `users`, `companies` |
| 07 | `0001_01_02_000004_create_pipelines_and_stages_tables.php` | `pipelines`, `pipeline_stages` | `teams` |
| 08 | `0001_01_02_000005_create_deals_and_pivots_tables.php` | `deals`, `deal_contact`, `deal_stage_histories` | `teams`, `users`, `pipelines`, `pipeline_stages`, `companies`, `contacts` |
| 09 | `0001_01_02_000006_create_leads_table.php` | `leads` | `teams`, `users`, `companies`, `contacts`, `deals` |
| 10 | `0001_01_02_000007_create_activities_table.php` | `activities` | `teams`, `users` |
| 11 | `0001_01_02_000008_create_tasks_and_reminders_tables.php` | `tasks`, `reminders` | `teams`, `users` |
| 12 | `0001_01_02_000009_create_follow_ups_table.php` | `follow_ups` | `teams`, `users` |
| 13 | `0001_01_02_000010_create_quotations_and_items_tables.php` | `quotations`, `quotation_items` | `teams`, `users`, `deals`, `companies`, `contacts` |
| 14 | `0001_01_02_000011_create_attachments_table.php` | `attachments` | `teams`, `users` |
| 15 | `0001_01_02_000012_create_import_export_jobs_table.php` | `import_export_jobs` | `teams`, `users` |
| 16 | `0001_01_02_000013_create_audit_logs_table.php` | `audit_logs` | `teams`, `users` |
| 17 | `0001_01_02_000014_create_notifications_table.php` | `notifications` | `users` |
| 18 | `2026_11_18_000001_add_security_and_quotation_approval_columns.php` | `users` (`two_factor_*`), `quotations` (discount approval columns) | `users`, `quotations` |
| 19 | `2026_11_19_000001_create_webhooks_table.php` | `webhooks` | `teams`, `users` |

## 2. Composite Indexing Specification

Every index below is designed around the left-prefix rule to support team/owner RBAC scoping, Kanban rendering, duplicate detection, and dashboard aggregation:

| Table | Index Name | Columns | Query Supported |
| :--- | :--- | :--- | :--- |
| `leads` | `idx_leads_team_status_owner` | `(team_id, status, owner_id, created_at)` | Filtered lead index by status and rep |
| `leads` | `idx_leads_email` | `(email)` | Duplicate lead detection on capture/import |
| `leads` | `idx_leads_phone` | `(phone)` | Duplicate lead detection by phone |
| `contacts` | `idx_contacts_team_company` | `(team_id, company_id, last_name)` | Contacts directory & company detail page |
| `contacts` | `idx_contacts_email` | `(email)` | Email lookup & deduplication |
| `companies` | `idx_companies_team_owner_name` | `(team_id, owner_id, name)` | Scoped company search and sorting |
| `pipeline_stages` | `unq_pipeline_stage_pos` | `UNIQUE (pipeline_id, position)` | Deterministic Kanban column ordering |
| `deals` | `idx_deals_kanban` | `(pipeline_id, pipeline_stage_id, status, team_id)` | Kanban board loading & column totals |
| `deals` | `idx_deals_forecast` | `(team_id, status, expected_close_date, owner_id)` | Revenue forecasting & monthly close reports |
| `deal_stage_histories` | `idx_dsh_deal_created` | `(deal_id, created_at)` | Deal velocity & stage audit trail |
| `activities` | `idx_activities_morph_date` | `(subject_type, subject_id, occurred_at)` | Entity unified timeline feed |
| `tasks` | `idx_tasks_assignee_status_due` | `(assigned_to_id, status, due_at)` | Rep "My Due Tasks" widget |
| `tasks` | `idx_tasks_morph` | `(taskable_type, taskable_id)` | Entity-specific tasks list |
| `reminders` | `idx_reminders_due_unsent` | `(sent_at, remind_at)` | Minute-by-minute scheduler polling |
| `follow_ups` | `idx_followups_owner_status_sched` | `(owner_id, status, scheduled_at)` | Overdue & today's follow-ups queue |
| `quotations` | `idx_quotes_deal_version` | `(deal_id, version)` | Deal quote history & latest quote lookup |
| `audit_logs` | `idx_audit_morph_created` | `(auditable_type, auditable_id, created_at)` | Record-level security history |
| `audit_logs` | `idx_audit_team_event_date` | `(team_id, event, created_at)` | Compliance log filtering |
| `webhooks` | `idx_webhooks_team_active` | `(team_id, is_active)` | Active webhook fan-out by team scope |
| `webhooks` | `idx_webhooks_created_by` | `(created_by_id)` | Admin creator lookup |

## 3. Reference Migration Example (`deals` Table)

```php
Schema::create('deals', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
    $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
    $table->foreignId('pipeline_id')->constrained('pipelines')->restrictOnDelete();
    $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
    $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
    $table->foreignId('primary_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
    $table->string('title', 191);
    $table->decimal('value', 15, 2)->default(0.00);
    $table->char('currency', 3)->default('BDT');
    $table->decimal('probability', 5, 2)->default(10.00);
    $table->string('status', 30)->default('open');
    $table->date('expected_close_date')->nullable();
    $table->timestamp('closed_at')->nullable();
    $table->text('won_notes')->nullable();
    $table->string('lost_reason', 150)->nullable();
    $table->string('lost_competitor', 150)->nullable();
    $table->timestamp('stage_changed_at')->useCurrent();
    $table->timestamp('last_activity_at')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['pipeline_id', 'pipeline_stage_id', 'status', 'team_id'], 'idx_deals_kanban');
    $table->index(['team_id', 'status', 'expected_close_date', 'owner_id'], 'idx_deals_forecast');
});
```
