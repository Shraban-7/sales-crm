# Entity-Relationship Diagram (ERD) — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/database/database-design.md`, `docs/database/database-relationships.md`, `docs/database/migrations-and-indexes.md`

## 1. Core Commercial & Pipeline ERD

```mermaid
erDiagram
    TEAMS ||--o{ USERS : "employs"
    USERS ||--o{ LEADS : "owns"
    USERS ||--o{ COMPANIES : "owns"
    USERS ||--o{ CONTACTS : "owns"
    USERS ||--o{ DEALS : "owns"
    USERS ||--o{ QUOTATIONS : "prepares"

    COMPANIES ||--o{ COMPANIES : "parent_company_id"
    COMPANIES ||--o{ CONTACTS : "employs"
    COMPANIES ||--o{ DEALS : "has_opportunities"
    COMPANIES ||--o{ QUOTATIONS : "receives"

    CONTACTS ||--o{ DEALS : "primary_contact_id"
    DEALS }o--o{ CONTACTS : "deal_contact_pivot"

    LEADS |o--o| COMPANIES : "converted_company_id"
    LEADS |o--o| CONTACTS : "converted_contact_id"
    LEADS |o--o| DEALS : "converted_deal_id"

    PIPELINES ||--o{ PIPELINE_STAGES : "contains"
    PIPELINES ||--o{ DEALS : "tracks"
    PIPELINE_STAGES ||--o{ DEALS : "current_stage"
    DEALS ||--o{ DEAL_STAGE_HISTORIES : "logs_transitions"
    DEALS ||--o{ QUOTATIONS : "generates"
    QUOTATIONS ||--|{ QUOTATION_ITEMS : "line_items"

    TEAMS {
        bigint id PK
        string name
        string slug UK
        bigint leader_id FK
        decimal monthly_revenue_target
    }

    USERS {
        bigint id PK
        bigint team_id FK
        string name
        string email UK
        boolean is_active
    }

    LEADS {
        bigint id PK
        bigint team_id FK
        bigint owner_id FK
        string first_name
        string last_name
        string company_name
        string email
        string status
        smallint score
        bigint converted_deal_id FK
    }

    COMPANIES {
        bigint id PK
        bigint team_id FK
        bigint owner_id FK
        bigint parent_company_id FK
        string name
        string industry
        decimal annual_revenue
    }

    CONTACTS {
        bigint id PK
        bigint team_id FK
        bigint owner_id FK
        bigint company_id FK
        string first_name
        string last_name
        string email
        boolean is_primary_decision_maker
    }

    PIPELINES {
        bigint id PK
        bigint team_id FK
        string name
        boolean is_default
    }

    PIPELINE_STAGES {
        bigint id PK
        bigint pipeline_id FK
        string name
        smallint position
        decimal default_probability
        smallint rotting_days
        boolean is_won_stage
        boolean is_lost_stage
    }

    DEALS {
        bigint id PK
        bigint team_id FK
        bigint owner_id FK
        bigint pipeline_id FK
        bigint pipeline_stage_id FK
        bigint company_id FK
        bigint primary_contact_id FK
        string title
        decimal value
        string status
        date expected_close_date
    }

    QUOTATIONS {
        bigint id PK
        char ulid UK
        bigint deal_id FK
        bigint company_id FK
        string quote_number UK
        string status
        decimal subtotal
        decimal discount_total
        decimal tax_total
        decimal grand_total
        date valid_until
    }

    QUOTATION_ITEMS {
        bigint id PK
        bigint quotation_id FK
        smallint position
        string item_name
        decimal quantity
        decimal unit_price
        decimal discount_percent
        decimal tax_percent
        decimal line_total
    }
```

## 2. Polymorphic Execution & Governance ERD

```mermaid
erDiagram
    USERS ||--o{ ACTIVITIES : "logs"
    USERS ||--o{ TASKS : "assigned_to"
    USERS ||--o{ FOLLOW_UPS : "owns"
    USERS ||--o{ REMINDERS : "receives"
    USERS ||--o{ AUDIT_LOGS : "triggers"
    USERS ||--o{ IMPORT_EXPORT_JOBS : "initiates"

    TASKS ||--o{ REMINDERS : "remindable"
    FOLLOW_UPS ||--o{ REMINDERS : "remindable"
    FOLLOW_UPS |o--o| FOLLOW_UPS : "next_follow_up_id"

    ACTIVITIES {
        bigint id PK
        bigint team_id FK
        bigint user_id FK
        string subject_type
        bigint subject_id
        string type
        timestamp occurred_at
    }

    TASKS {
        bigint id PK
        bigint team_id FK
        bigint assigned_to_id FK
        string taskable_type
        bigint taskable_id
        string priority
        string status
        timestamp due_at
    }

    FOLLOW_UPS {
        bigint id PK
        bigint team_id FK
        bigint owner_id FK
        string subject_type
        bigint subject_id
        string status
        timestamp scheduled_at
        bigint next_follow_up_id FK
    }

    AUDIT_LOGS {
        bigint id PK
        bigint team_id FK
        bigint user_id FK
        string event
        string auditable_type
        bigint auditable_id
        json old_values
        json new_values
        timestamp created_at
    }
```
