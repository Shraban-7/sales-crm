# Data Integrity, Transactions & Concurrency — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/database/database-design.md`, `docs/database/migrations-and-indexes.md`

## 1. Foreign Key Delete & Cascade Policy

To prevent accidental cascade wiping of commercial records when a user, team, or pipeline stage is modified, foreign keys follow strict rules:

| Parent Entity | Child Entity | `ON DELETE` Rule | Rationale |
| :--- | :--- | :--- | :--- |
| `teams` | `users`, `leads`, `companies`, `contacts`, `deals`, `quotations` | `RESTRICT` | A team cannot be hard-deleted while commercial records belong to it |
| `users` (`owner_id`) | `leads`, `companies`, `contacts`, `deals`, `quotations` | `RESTRICT` | Deactivate departed sales reps (`is_active = false`) and reassign records; never hard-delete users with owned deals |
| `companies` | `deals`, `quotations` | `RESTRICT` | Commercial history must remain intact; soft-delete company only when no active open deals exist |
| `contacts` | `deals.primary_contact_id` | `SET NULL` | If a contact leaves, the deal remains open on the company |
| `quotations` | `quotation_items` | `CASCADE` | Line items are value objects owned exclusively by their parent quotation |
| `deals` | `deal_stage_histories`, `deal_contact` | `CASCADE` | Stage history and contact pivot belong strictly to the parent deal |

## 2. Mandatory Transactional Boundaries (`DB::transaction`)

The following operations **must** execute inside `DB::transaction()` with pessimistic locking (`->lockForUpdate()`):

1. **Lead Conversion (`ConvertLeadToDealAction`):** Locks the `leads` row (`SELECT ... FOR UPDATE`), verifies `status !== 'converted'`, creates/links `Company`, `Contact`, and `Deal`, updates `leads.converted_*` columns, and logs the conversion event. Prevents double-click duplicate deal creation.
2. **Deal Stage Movement (`MoveDealStageAction`):** Locks the `deals` row, calculates exact seconds spent in the previous stage, updates `pipeline_stage_id`, `probability`, and `stage_changed_at`, inserts `deal_stage_histories`, and records an audit entry.
3. **Quotation Numbering & Line-Item Recalculation (`SaveQuotationAction`):** Locks the quotation row, deletes/re-syncs `quotation_items`, recalculates `subtotal`, `discount_total`, `tax_total`, and `grand_total` using `bcmath` / integer-cents precision, and updates the parent `quotations` record atomically.

## 3. Monetary Arithmetic Integrity

- Never perform currency addition, multiplication, or percentage discounts using native PHP `float` arithmetic without rounding at each boundary.
- Standard formula per `quotation_items` row:
  - `gross = round(quantity * unit_price, 2)`
  - `discount_amount = round(gross * (discount_percent / 100), 2)`
  - `taxable_amount = gross - discount_amount`
  - `tax_amount = round(taxable_amount * (tax_percent / 100), 2)`
  - `line_total = taxable_amount + tax_amount`
