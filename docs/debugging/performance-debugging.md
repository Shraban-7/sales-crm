# Performance Debugging & Profiling Guide — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/database/database-optimization.md`, `docs/architecture/scalability-strategy.md`

## 1. Slow Query Detection in `AppServiceProvider`

Register a database query duration threshold listener in `AppServiceProvider::boot()` to automatically log queries exceeding `500ms`:

```php
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

DB::whenQueryingForLongerThan(500, function ($connection, QueryExecuted $event): void {
    Log::warning('Slow database query detected', [
        'sql' => $event->sql,
        'time_ms' => $event->time,
        'connection' => $connection->getName(),
    ]);
});
```

## 2. Analyzing Query Plans with MySQL `EXPLAIN`

When optimizing a report or filtered list query:
1. Run `EXPLAIN FORMAT=JSON SELECT ...` against MySQL.
2. Verify `type` is `ref`, `eq_ref`, or `range` (never `ALL` full table scan on large tables).
3. Verify `key` matches one of the composite indexes defined in `docs/database/migrations-and-indexes.md`.
4. Eliminate `Using filesort` and `Using temporary` on high-frequency Kanban and timeline queries by aligning `ORDER BY` columns with index suffixes.
