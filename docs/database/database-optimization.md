# Database Query Optimization Guide — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/database/migrations-and-indexes.md`, `docs/architecture/scalability-strategy.md`, `docs/debugging/performance-debugging.md`

## 1. Zero N+1 Query Enforcement

1. **Strict Lazy-Loading Prevention:** Enable `Model::preventLazyLoading(! app()->isProduction())` in `AppServiceProvider`. Any un-eager-loaded relation accessed inside a Blade loop immediately throws a `LazyLoadingViolationException` during development and automated tests.
2. **Constrained Eager Loading:** Load only the columns required for UI display:
   ```php
   Deal::query()
       ->visibleTo($user)
       ->select(['id', 'team_id', 'owner_id', 'company_id', 'pipeline_stage_id', 'title', 'value', 'currency', 'expected_close_date', 'stage_changed_at'])
       ->with([
           'owner:id,name',
           'company:id,name',
           'stage:id,name,color,rotting_days',
       ])
       ->where('pipeline_id', $pipeline->id)
       ->where('status', DealStatus::Open)
       ->get();
   ```

## 2. Aggregation & Dashboard Query Optimization

Never load Eloquent Model collections into PHP memory (`->get()->sum('value')`) to calculate totals or counts. Push all aggregations down to MySQL using indexed SQL queries:

```php
$stageMetrics = Deal::query()
    ->visibleTo($user)
    ->where('pipeline_id', $pipeline->id)
    ->where('status', DealStatus::Open)
    ->selectRaw('
        pipeline_stage_id,
        COUNT(*) as deal_count,
        COALESCE(SUM(value), 0) as total_value,
        COALESCE(SUM(value * (probability / 100)), 0) as weighted_value
    ')
    ->groupBy('pipeline_stage_id')
    ->get()
    ->keyBy('pipeline_stage_id');
```

## 3. Polymorphic Timeline & Search Optimization

- **Timeline Feeds:** Always query `activities` with both `subject_type` (short morph alias like `'deal'`) and `subject_id` so MySQL uses `idx_activities_morph_date` (`(subject_type, subject_id, occurred_at)`).
- **Prefix Search Over Wildcard Leading `%`:** For interactive search bars on `leads`, `contacts`, and `companies`, prefer prefix matching (`LIKE 'term%'`) on indexed columns (`email`, `phone`, `name`) or MySQL `FULLTEXT` indexes when searching multi-word notes, avoiding leading wildcard scans (`LIKE '%term%'`) on 100k+ row tables.
