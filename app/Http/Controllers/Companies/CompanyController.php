<?php

declare(strict_types=1);

namespace App\Http\Controllers\Companies;

use App\Enums\DealStatus;
use App\Exceptions\DomainRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $search = trim((string) $request->query('search', ''));
        $companies = Company::query()
            ->with(['owner', 'parentCompany'])
            ->withCount(['contacts', 'deals'])
            ->visibleTo($request->user())
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate((int) config('crm.pagination.per_page', 20))
            ->withQueryString();

        $parentOptions = Company::query()
            ->visibleTo($request->user())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.companies.index', compact('companies', 'parentOptions', 'search'));
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $user = $request->user();
        $data = $request->validated();
        $contactData = [
            'first_name' => $data['contact_first_name'] ?? null,
            'last_name' => $data['contact_last_name'] ?? null,
            'email' => $data['contact_email'] ?? null,
            'phone' => $data['contact_phone'] ?? null,
        ];
        unset($data['contact_first_name'], $data['contact_last_name'], $data['contact_email'], $data['contact_phone']);

        $company = DB::transaction(function () use ($data, $contactData, $user): Company {
            $company = Company::query()->create(array_merge($data, [
                'team_id' => $user->team_id,
                'owner_id' => $user->id,
            ]));

            if ($contactData['first_name'] !== null) {
                $company->contacts()->create([
                    'team_id' => $user->team_id,
                    'owner_id' => $user->id,
                    ...$contactData,
                ]);
            }

            return $company;
        });

        return redirect()->route('companies.show', $company)->with('status', 'Company created.');
    }

    public function show(Request $request, Company $company): View
    {
        $this->authorize('view', $company);

        $company->load([
            'owner',
            'parentCompany',
            'subsidiaries',
            'contacts.owner',
            'deals.stage',
            'quotations',
            'activities.user',
            'tasks.assignee',
            'followUps.owner',
            'attachments.uploader',
        ]);

        $openPipeline = (float) $company->deals->where('status', DealStatus::Open)->sum('value');
        $wonRevenue = (float) $company->deals->where('status', DealStatus::Won)->sum('value');

        $parentOptions = Company::query()
            ->visibleTo($request->user())
            ->where('id', '!=', $company->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.companies.show', compact('company', 'openPipeline', 'wonRevenue', 'parentOptions'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $company->update($request->validated());

        return redirect()->route('companies.show', $company)->with('status', 'Company updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        if ($company->deals()->exists() || $company->quotations()->exists()) {
            throw new DomainRuleException('Cannot delete a company that has associated deals or quotations.', 409);
        }

        $company->delete();

        return redirect()->route('companies.index')->with('status', 'Company deleted.');
    }
}
