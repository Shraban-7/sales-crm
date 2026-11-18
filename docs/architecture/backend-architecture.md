# Backend Architecture — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/architecture/system-architecture.md`, `docs/architecture/design-patterns.md`, `docs/adr/ADR-005-business-logic-organization.md`

## 1. Backend Design Philosophy

The backend is built on [Laravel 12](https://laravel.com/framework/docs/12.x/configuration) and PHP 8.3+ using strict typing (`declare(strict_types=1);`). To prevent "fat controllers" and "god models," business logic is partitioned into:
- **FormRequests** for input validation and preliminary gate checks.
- **Policies** for fine-grained RBAC and team/ownership authorization.
- **Actions** for single, reusable atomic operations.
- **Services** for orchestrating complex domain workflows, transactions, and side effects.
- **Eloquent Models** strictly for schema relationships, attribute casting, and query scopes.

## 2. Component Responsibilities & Code Patterns

### 2.1 Thin Resource & Action Controllers
Controllers must never contain `DB::beginTransaction()`, complex math, or multi-model creation logic.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Leads;

use App\Actions\Leads\ConvertLeadToDealAction;
use App\Data\Leads\LeadConversionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leads\ConvertLeadRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

final class ConvertLeadController extends Controller
{
    public function __invoke(
        ConvertLeadRequest $request,
        Lead $lead,
        ConvertLeadToDealAction $convertAction
    ): RedirectResponse {
        $this->authorize('convert', $lead);

        $deal = $convertAction->handle(
            lead: $lead,
            data: LeadConversionData::fromRequest($request),
            actor: $request->user()
        );

        return redirect()
            ->route('deals.show', $deal)
            ->with('status', __('Lead successfully converted to deal :title.', ['title' => $deal->title]));
    }
}
```

### 2.2 Invokable Domain Actions
Each Action class performs one well-defined domain mutation inside a database transaction:

```php
<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Data\Leads\LeadConversionData;
use App\Enums\LeadStatus;
use App\Events\LeadConverted;
use App\Exceptions\DomainRuleException;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class ConvertLeadToDealAction
{
    public function handle(Lead $lead, LeadConversionData $data, User $actor): Deal
    {
        return DB::transaction(function () use ($lead, $data, $actor): Deal {
            $lockedLead = Lead::query()->lockForUpdate()->findOrFail($lead->id);

            if ($lockedLead->status === LeadStatus::Converted) {
                throw new DomainRuleException('This lead has already been converted.');
            }

            $company = Company::query()->firstOrCreate(
                ['name' => $data->companyName, 'team_id' => $lockedLead->team_id],
                ['owner_id' => $lockedLead->owner_id, 'website' => $lockedLead->website]
            );

            $contact = Contact::query()->create([
                'team_id' => $lockedLead->team_id,
                'owner_id' => $lockedLead->owner_id,
                'company_id' => $company->id,
                'first_name' => $lockedLead->first_name,
                'last_name' => $lockedLead->last_name,
                'email' => $lockedLead->email,
                'phone' => $lockedLead->phone,
            ]);

            $deal = Deal::query()->create([
                'team_id' => $lockedLead->team_id,
                'owner_id' => $lockedLead->owner_id,
                'company_id' => $company->id,
                'primary_contact_id' => $contact->id,
                'pipeline_id' => $data->pipelineId,
                'pipeline_stage_id' => $data->pipelineStageId,
                'title' => $data->dealTitle,
                'value' => $data->expectedValue,
                'currency' => $data->currency,
                'expected_close_date' => $data->expectedCloseDate,
            ]);

            $lockedLead->update([
                'status' => LeadStatus::Converted,
                'converted_at' => now(),
                'converted_by_id' => $actor->id,
                'converted_company_id' => $company->id,
                'converted_contact_id' => $contact->id,
                'converted_deal_id' => $deal->id,
            ]);

            event(new LeadConverted($lockedLead, $deal, $actor));

            return $deal;
        });
    }
}
```

### 2.3 Eloquent Strictness in `AppServiceProvider`
To eliminate silent bugs in development and testing, `AppServiceProvider::boot()` must configure:

```php
Model::shouldBeStrict(! $this->app->isProduction());
// Enforces:
// 1. Model::preventLazyLoading() -> catches N+1 queries immediately
// 2. Model::preventSilentlyDiscardingAttributes() -> catches unfillable mass-assignment keys
// 3. Model::preventAccessingMissingAttributes() -> catches typos on unselected columns
```
