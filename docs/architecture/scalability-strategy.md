# Scalability & Performance Strategy — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/database/database-optimization.md`, `docs/debugging/performance-debugging.md`, `docs/deployment/queue-and-scheduler.md`

## 1. Scalability Targets

The architecture is designed to scale smoothly from a single-server deployment (supporting 10–50 sales reps and 100,000+ leads/contacts) to a horizontally scaled multi-node deployment (supporting 500+ concurrent users and 5,000,000+ activity/audit records).

## 2. Database & Query Scalability

1. **Composite Indexing by Access Path:** Every high-cardinality table (`leads`, `contacts`, `companies`, `deals`, `activities`, `tasks`, `follow_ups`, `audit_logs`) is indexed with `(team_id, owner_id, status)` or `(subject_type, subject_id, occurred_at)` so filtered queries never trigger full table scans. See `docs/database/migrations-and-indexes.md`.
2. **Strict Eager Loading & Column Selection:** Index views select only required columns and eager-load only needed relations (`->with(['owner:id,name', 'company:id,name', 'stage:id,name,color'])`).
3. **Pagination Strategy:**
   - Standard UI tables use indexed offset pagination (`paginate(25)`) capped at reasonable page depths.
   - Large exports, activity feeds, and background batch jobs use `cursorPaginate()` or `lazyById(1000)` to maintain $O(1)$ constant memory usage and index-seek performance.

## 3. Caching Strategy

| Cache Target | Cache Key Pattern | TTL / Invalidation Trigger |
| :--- | :--- | :--- |
| Spatie Role & Permission Map | `spatie.permission.cache` | 24 hours; auto-flushed when roles/permissions change |
| Pipeline & Stage Definitions | `crm:pipelines:team:{team_id}` | 12 hours; invalidated on pipeline/stage create/update/delete |
| Dashboard KPI Summary Cards | `crm:dashboard:kpis:user:{user_id}:period:{ym}` | 5 minutes (`Cache::remember`); invalidated on Deal Won/Lost |
| Active Team Member Directory | `crm:team:{team_id}:users` | 1 hour; invalidated on user update/deactivation |

## 4. Asynchronous Offloading & Horizontal Scaling Readiness

- **Stateless Web Tier:** Session state uses the `database` or `redis` driver (never local file sessions in multi-node production), and uploaded files use a configurable Flysystem disk (`private` local disk on single-node or S3-compatible object storage on multi-node).
- **Queue Partitioning:** Workers process three prioritized queues (`--queue=high,notifications,imports`) so a 50,000-row CSV import never delays real-time password resets or task reminders.
- **Read/Write Database Splitting:** [Laravel 12 Configuration](https://laravel.com/framework/docs/12.x/configuration) supports separate `read` and `write` MySQL hosts in `config/database.php` with `sticky => true` without changing application code.
