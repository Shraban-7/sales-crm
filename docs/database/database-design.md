# Database Design Specification — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (18 Ordered Migrations)`  
**Related Docs:** `docs/database/erd.md`, `docs/database/database-relationships.md`, `docs/database/migrations-and-indexes.md`, `docs/database/data-integrity.md`

## 1. Schema Standard & Relational Basis

This document specifies the **Canonical Relational Schema** for MySQL 8.0+ (`InnoDB`, character set `utf8mb4`, collation `utf8mb4_unicode_ci`) implemented using Laravel Eloquent ORM across `18` migrations in `database/migrations/`.

## 2. Global Schema Conventions

| Convention | Standard | Rationale |
| :--- | :--- | :--- |
| **Table Names** | Plural `snake_case` (`leads`, `pipeline_stages`, `quotation_items`) | Matches Laravel Eloquent pluralization conventions |
| **Primary Keys** | `id` (`BIGINT UNSIGNED AUTO_INCREMENT`) | High capacity, fast sequential InnoDB clustered indexing |
| **Public/External Identifiers** | Optional `ulid` (`CHAR(26) UNIQUE`) on externally shared entities (`quotations`) | Prevents sequential enumeration on public/signed quote views |
| **Foreign Keys** | Singular `<entity>_id` (`BIGINT UNSIGNED`) with explicit FK constraint | Enforces relational referential integrity at the database engine level |
| **Monetary Columns** | `DECIMAL(15, 2)` (default `0.00`) + `currency` (`CHAR(3)`, default `'BDT'`) | Eliminates IEEE 754 floating-point rounding errors |
| **Percentages / Rates** | `DECIMAL(5, 2)` (e.g., `tax_rate`, `discount_rate`, `probability`) | Supports exact fractional percentages (`0.00` to `100.00`) |
| **Timestamps** | `created_at` and `updated_at` (`TIMESTAMP NULL`) in UTC | Consistent timezone normalization across users |
| **Soft Deletes** | `deleted_at` (`TIMESTAMP NULL`) on core commercial records | Prevents accidental permanent loss of revenue/customer history |

## 3. Complete Domain Table Catalog (18 Core Entities + System Tables)

| # | Table Name | Domain Module | Primary Responsibility | Soft Deletes |
| :--- | :--- | :--- | :--- | :--- |
| 1 | `teams` | Users & Teams | Sales teams / departments for data scoping and targets | Yes |
| 2 | `users` | Users & Teams | Authenticated CRM users, sales reps, managers, admins | Yes |
| 3 | `roles`, `permissions`, `model_has_*`, `role_has_permissions` | Users & Teams | [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v8/installation-laravel) RBAC tables | No |
| 4 | `companies` | Companies | B2B client accounts, parent-child hierarchy, billing/shipping info | Yes |
| 5 | `contacts` | Contacts | Individual decision-makers and stakeholders linked to companies | Yes |
| 6 | `leads` | Leads | Inbound/outbound prospects prior to qualification and conversion | Yes |
| 7 | `pipelines` | Sales Pipeline | Named sales pipelines (e.g., Enterprise Sales, SMB Inbound) | Yes |
| 8 | `pipeline_stages` | Sales Pipeline | Ordered stages within a pipeline with default win probability & rotting days | Yes |
| 9 | `deals` | Deals & Opportunities | Qualified revenue opportunities moving through pipeline stages | Yes |
| 10 | `deal_contact` | Deals & Opportunities | Pivot linking multiple stakeholder contacts with roles to a deal | No |
| 11 | `deal_stage_histories` | Deals & Opportunities | Immutable log of every stage transition and duration in stage | No |
| 12 | `activities` | Activities & Comms | Polymorphic timeline logs (`call`, `email`, `meeting`, `note`) | Yes |
| 13 | `tasks` | Tasks & Reminders | Polymorphic actionable to-do items with priority, assignee, and due date | Yes |
| 14 | `reminders` | Tasks & Reminders | Scheduled alert triggers linked to tasks or follow-ups | No |
| 15 | `follow_ups` | Customer Follow-Ups | Scheduled next-contact commitments with SLA tracking and outcome notes | Yes |
| 16 | `quotations` | Quotations & Proposals | Formal numbered price quotes linked to a Deal, Company, and Contact | Yes |
| 17 | `quotation_items` | Quotations & Proposals | Ordered line items (description, qty, unit price, discount, tax, total) | No |
| 18 | `import_export_jobs` | Import & Export | Tracking metadata, status, row counts, and error files for CSV/XLSX jobs | No |
| 19 | `audit_logs` | Audit Logs | Immutable compliance trail of create/update/delete/security events | No |
| 20 | `attachments` | Shared / Security | Polymorphic private file metadata linked to Deals, Quotations, Activities | Yes |
| 21 | `notifications` | Notifications | Native Laravel database notifications table (`uuid` PK) | No |

## 4. Core Table Column Specifications

### 4.1 `teams` & `users`
- **`teams`**: `id`, `name` (`VARCHAR(120)`), `slug` (`VARCHAR(120) UNIQUE`), `leader_id` (`FK -> users.id NULLABLE`), `monthly_revenue_target` (`DECIMAL(15,2)`), `is_active` (`BOOLEAN`), `timestamps`, `softDeletes`.
- **`users`**: `id`, `team_id` (`FK -> teams.id NULLABLE`), `name` (`VARCHAR(150)`), `email` (`VARCHAR(191) UNIQUE`), `phone` (`VARCHAR(30) NULLABLE`), `job_title` (`VARCHAR(100) NULLABLE`), `password` (`VARCHAR(255)`), `is_active` (`BOOLEAN DEFAULT 1`), `last_login_at` (`TIMESTAMP NULL`), `last_login_ip` (`VARCHAR(45) NULL`), `notification_preferences` (`JSON NULL`), `remember_token`, `timestamps`, `softDeletes`.

### 4.2 `companies` & `contacts`
- **`companies`**: `id`, `team_id` (`FK -> teams.id`), `owner_id` (`FK -> users.id`), `parent_company_id` (`FK -> companies.id NULLABLE`), `name` (`VARCHAR(191)`), `registration_number` (`VARCHAR(80) NULL`), `industry` (`VARCHAR(100) NULL`), `company_size` (`VARCHAR(50) NULL`), `annual_revenue` (`DECIMAL(15,2) NULL`), `website` (`VARCHAR(255) NULL`), `phone` (`VARCHAR(30) NULL`), `billing_address` (`TEXT NULL`), `shipping_address` (`TEXT NULL`), `city` (`VARCHAR(100) NULL`), `country` (`CHAR(2) DEFAULT 'BD'`), `timestamps`, `softDeletes`.
- **`contacts`**: `id`, `team_id` (`FK -> teams.id`), `owner_id` (`FK -> users.id`), `company_id` (`FK -> companies.id NULLABLE`), `first_name` (`VARCHAR(100)`), `last_name` (`VARCHAR(100)`), `email` (`VARCHAR(191) NULL`), `phone` (`VARCHAR(30) NULL`), `mobile` (`VARCHAR(30) NULL`), `job_title` (`VARCHAR(120) NULL`), `department` (`VARCHAR(100) NULL`), `is_primary_decision_maker` (`BOOLEAN DEFAULT 0`), `do_not_contact` (`BOOLEAN DEFAULT 0`), `lead_source` (`VARCHAR(80) NULL`), `timestamps`, `softDeletes`.

### 4.3 `leads`
- **`leads`**: `id`, `team_id` (`FK -> teams.id`), `owner_id` (`FK -> users.id`), `first_name` (`VARCHAR(100)`), `last_name` (`VARCHAR(100) NULL`), `company_name` (`VARCHAR(191) NULL`), `job_title` (`VARCHAR(120) NULL`), `email` (`VARCHAR(191) NULL`), `phone` (`VARCHAR(30) NULL`), `website` (`VARCHAR(255) NULL`), `source` (`VARCHAR(60)` — enum: `website`, `referral`, `cold_call`, `linkedin`, `event`, `import`), `status` (`VARCHAR(40)` — enum: `new`, `contacted`, `qualified`, `disqualified`, `converted`), `score` (`SMALLINT UNSIGNED DEFAULT 0`), `estimated_value` (`DECIMAL(15,2) NULL`), `disqualified_reason` (`VARCHAR(255) NULL`), `converted_at` (`TIMESTAMP NULL`), `converted_by_id` (`FK -> users.id NULL`), `converted_company_id` (`FK -> companies.id NULL`), `converted_contact_id` (`FK -> contacts.id NULL`), `converted_deal_id` (`FK -> deals.id NULL`), `last_contacted_at` (`TIMESTAMP NULL`), `timestamps`, `softDeletes`.

### 4.4 `pipelines`, `pipeline_stages`, `deals`, `deal_stage_histories`
- **`pipelines`**: `id`, `team_id` (`FK -> teams.id NULLABLE`), `name` (`VARCHAR(120)`), `is_default` (`BOOLEAN DEFAULT 0`), `is_active` (`BOOLEAN DEFAULT 1`), `timestamps`, `softDeletes`.
- **`pipeline_stages`**: `id`, `pipeline_id` (`FK -> pipelines.id`), `name` (`VARCHAR(100)`), `slug` (`VARCHAR(100)`), `position` (`SMALLINT UNSIGNED`), `default_probability` (`DECIMAL(5,2)`), `rotting_days` (`SMALLINT UNSIGNED DEFAULT 14`), `is_won_stage` (`BOOLEAN DEFAULT 0`), `is_lost_stage` (`BOOLEAN DEFAULT 0`), `color` (`VARCHAR(20) DEFAULT 'slate'`), `timestamps`, `softDeletes`.
- **`deals`**: `id`, `team_id` (`FK -> teams.id`), `owner_id` (`FK -> users.id`), `pipeline_id` (`FK -> pipelines.id`), `pipeline_stage_id` (`FK -> pipeline_stages.id`), `company_id` (`FK -> companies.id`), `primary_contact_id` (`FK -> contacts.id NULLABLE`), `title` (`VARCHAR(191)`), `value` (`DECIMAL(15,2) DEFAULT 0.00`), `currency` (`CHAR(3) DEFAULT 'BDT'`), `probability` (`DECIMAL(5,2) DEFAULT 10.00`), `status` (`VARCHAR(30)` — enum: `open`, `won`, `lost`), `expected_close_date` (`DATE NULL`), `closed_at` (`TIMESTAMP NULL`), `won_notes` (`TEXT NULL`), `lost_reason` (`VARCHAR(150) NULL`), `lost_competitor` (`VARCHAR(150) NULL`), `stage_changed_at` (`TIMESTAMP`), `last_activity_at` (`TIMESTAMP NULL`), `timestamps`, `softDeletes`.
- **`deal_stage_histories`**: `id`, `deal_id` (`FK -> deals.id`), `from_stage_id` (`FK -> pipeline_stages.id NULLABLE`), `to_stage_id` (`FK -> pipeline_stages.id`), `Moved_by_id` (`FK -> users.id`), `duration_in_previous_stage_seconds` (`INT UNSIGNED DEFAULT 0`), `notes` (`VARCHAR(500) NULL`), `created_at` (`TIMESTAMP`).

### 4.5 `activities`, `tasks`, `reminders`, `follow_ups`
- **`activities`**: `id`, `team_id` (`FK -> teams.id`), `user_id` (`FK -> users.id`), `subject_type` (`VARCHAR(100)`), `subject_id` (`BIGINT UNSIGNED`), `type` (`VARCHAR(30)` — enum: `call`, `email`, `meeting`, `note`, `system`), `direction` (`VARCHAR(20) NULL` — `inbound`, `outbound`), `outcome` (`VARCHAR(60) NULL`), `summary` (`VARCHAR(255)`), `body` (`TEXT NULL`), `duration_minutes` (`SMALLINT UNSIGNED NULL`), `occurred_at` (`TIMESTAMP`), `timestamps`, `softDeletes`.
- **`tasks`**: `id`, `team_id` (`FK -> teams.id`), `assigned_to_id` (`FK -> users.id`), `created_by_id` (`FK -> users.id`), `taskable_type` (`VARCHAR(100)`), `taskable_id` (`BIGINT UNSIGNED`), `title` (`VARCHAR(191)`), `description` (`TEXT NULL`), `priority` (`VARCHAR(20)` — enum: `low`, `medium`, `high`, `urgent`), `status` (`VARCHAR(30)` — enum: `pending`, `in_progress`, `completed`, `cancelled`), `due_at` (`TIMESTAMP`), `completed_at` (`TIMESTAMP NULL`), `timestamps`, `softDeletes`.
- **`reminders`**: `id`, `user_id` (`FK -> users.id`), `remindable_type` (`VARCHAR(100)`), `remindable_id` (`BIGINT UNSIGNED`), `remind_at` (`TIMESTAMP`), `channel` (`VARCHAR(30) DEFAULT 'database'`), `sent_at` (`TIMESTAMP NULL`), `timestamps`.
- **`follow_ups`**: `id`, `team_id` (`FK -> teams.id`), `owner_id` (`FK -> users.id`), `subject_type` (`VARCHAR(100)`), `subject_id` (`BIGINT UNSIGNED`), `channel` (`VARCHAR(30)` — `call`, `email`, `whatsapp`, `meeting`), `status` (`VARCHAR(30)` — `scheduled`, `completed`, `missed`, `rescheduled`, `cancelled`), `scheduled_at` (`TIMESTAMP`), `completed_at` (`TIMESTAMP NULL`), `objective` (`VARCHAR(255)`), `outcome_notes` (`TEXT NULL`), `next_follow_up_id` (`FK -> follow_ups.id NULLABLE`), `timestamps`, `softDeletes`.

### 4.6 `quotations` & `quotation_items`
- **`quotations`**: `id`, `ulid` (`CHAR(26) UNIQUE`), `team_id` (`FK -> teams.id`), `owner_id` (`FK -> users.id`), `deal_id` (`FK -> deals.id`), `company_id` (`FK -> companies.id`), `contact_id` (`FK -> contacts.id NULLABLE`), `quote_number` (`VARCHAR(50) UNIQUE`), `version` (`SMALLINT UNSIGNED DEFAULT 1`), `status` (`VARCHAR(30)` — enum: `draft`, `sent`, `accepted`, `rejected`, `expired`), `currency` (`CHAR(3) DEFAULT 'BDT'`), `subtotal` (`DECIMAL(15,2) DEFAULT 0.00`), `discount_total` (`DECIMAL(15,2) DEFAULT 0.00`), `tax_total` (`DECIMAL(15,2) DEFAULT 0.00`), `grand_total` (`DECIMAL(15,2) DEFAULT 0.00`), `issue_date` (`DATE`), `valid_until` (`DATE`), `terms_and_conditions` (`TEXT NULL`), `customer_notes` (`TEXT NULL`), `sent_at` (`TIMESTAMP NULL`), `accepted_at` (`TIMESTAMP NULL`), `rejected_at` (`TIMESTAMP NULL`), `rejection_reason` (`VARCHAR(255) NULL`), `timestamps`, `softDeletes`.
- **`quotation_items`**: `id`, `quotation_id` (`FK -> quotations.id CASCADE`), `position` (`SMALLINT UNSIGNED DEFAULT 1`), `item_name` (`VARCHAR(191)`), `description` (`TEXT NULL`), `quantity` (`DECIMAL(10,2) DEFAULT 1.00`), `unit` (`VARCHAR(30) DEFAULT 'unit'`), `unit_price` (`DECIMAL(15,2)`), `discount_percent` (`DECIMAL(5,2) DEFAULT 0.00`), `tax_percent` (`DECIMAL(5,2) DEFAULT 0.00`), `line_total` (`DECIMAL(15,2)`), `timestamps`.

### 4.7 `import_export_jobs`, `attachments`, `audit_logs`
- **`import_export_jobs`**: `id`, `team_id` (`FK -> teams.id`), `user_id` (`FK -> users.id`), `type` (`VARCHAR(20)` — `import`, `export`), `module` (`VARCHAR(40)` — `leads`, `contacts`, `companies`, `deals`), `status` (`VARCHAR(30)` — `pending`, `processing`, `completed`, `failed`), `file_disk` (`VARCHAR(30) DEFAULT 'private'`), `source_file_path` (`VARCHAR(255) NULL`), `result_file_path` (`VARCHAR(255) NULL`), `total_rows` (`INT UNSIGNED DEFAULT 0`), `processed_rows` (`INT UNSIGNED DEFAULT 0`), `failed_rows` (`INT UNSIGNED DEFAULT 0`), `error_summary` (`JSON NULL`), `started_at` (`TIMESTAMP NULL`), `completed_at` (`TIMESTAMP NULL`), `timestamps`.
- **`attachments`**: `id`, `team_id` (`FK -> teams.id`), `uploaded_by_id` (`FK -> users.id`), `attachable_type` (`VARCHAR(100)`), `attachable_id` (`BIGINT UNSIGNED`), `original_name` (`VARCHAR(255)`), `storage_disk` (`VARCHAR(30) DEFAULT 'private'`), `storage_path` (`VARCHAR(255)`), `mime_type` (`VARCHAR(100)`), `size_bytes` (`BIGINT UNSIGNED`), `sha256_checksum` (`CHAR(64)`), `timestamps`, `softDeletes`.
- **`audit_logs`**: `id`, `team_id` (`FK -> teams.id NULLABLE`), `user_id` (`FK -> users.id NULLABLE`), `event` (`VARCHAR(60)` — `created`, `updated`, `deleted`, `restored`, `converted`, `stage_moved`, `exported`, `login_failed`), `auditable_type` (`VARCHAR(100)`), `auditable_id` (`BIGINT UNSIGNED NULLABLE`), `old_values` (`JSON NULL`), `new_values` (`JSON NULL`), `ip_address` (`VARCHAR(45) NULL`), `user_agent` (`VARCHAR(500) NULL`), `created_at` (`TIMESTAMP`).
