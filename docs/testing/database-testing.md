# Database Integrity, Migration & Constraint Testing — Sales CRM

**Last Updated:** 2026-11-17  
**Implementation Status:** `Proposed (Greenfield Blueprint)`  
**Related Docs:** `docs/database/data-integrity.md`, `docs/database/migrations-and-indexes.md`

## 1. Database Testing Objectives

Database tests validate:
1. **Foreign Key Referential Integrity:** `RESTRICT` prevents deleting companies with deals or users with owned records; `CASCADE` cleans up `quotation_items` when a draft quotation is force-deleted.
2. **Soft Delete Preservation:** Soft-deleted `leads`, `deals`, and `companies` remain in the table with `deleted_at` populated and can be restored by admins.
3. **Zero Lazy Loading (`N+1` Prevention):** Running index and dashboard routes with `Model::preventLazyLoading()` enabled throws an immediate failure if any relation is not eager-loaded.
4. **Immutable Audit Logs:** Attempting to call `update()` or `delete()` on `AuditLog` throws `ImmutableRecordException`.

## 2. Foreign Key & Immutability Test Examples (`Proposed`)

```php
<?php

declare(strict_types=1);

use App\Exceptions\ImmutableRecordException;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Deal;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents hard deleting a company that has associated deals', function (): void {
    $company = Company::factory()->create();
    Deal::factory()->for($company)->create(['team_id' => $company->team_id]);

    expect(fn () => $company->forceDelete())->toThrow(QueryException::class);
});

it('blocks mutation or deletion of audit_logs records', function (): void {
    $log = AuditLog::factory()->create(['event' => 'created']);

    expect(fn () => $log->update(['event' => 'tampered']))
        ->toThrow(ImmutableRecordException::class);

    expect(fn () => $log->delete())
        ->toThrow(ImmutableRecordException::class);
});
```
