<?php

declare(strict_types=1);

namespace App\Http\Controllers\Quotations;

use App\Actions\Quotations\CalculateQuotationTotalsAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Exceptions\DomainRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotations\StoreQuotationRequest;
use App\Http\Requests\Quotations\UpdateQuotationRequest;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class QuotationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quotation::class);

        $quotations = Quotation::query()
            ->with(['deal', 'company', 'owner'])
            ->visibleTo($request->user())
            ->latest()
            ->paginate((int) config('crm.pagination.per_page', 20));

        return view('pages.quotations.index', compact('quotations'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Quotation::class);

        $deals = Deal::query()->with('company')->visibleTo($request->user())->orderBy('title')->get();
        $contacts = Contact::query()->visibleTo($request->user())->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $selectedDealId = $request->query('deal_id');

        return view('pages.quotations.form', compact('deals', 'contacts', 'selectedDealId'));
    }

    public function store(StoreQuotationRequest $request, CreateQuotationAction $createAction): RedirectResponse
    {
        $this->authorize('create', Quotation::class);

        $deal = Deal::query()->findOrFail((int) $request->validated('deal_id'));
        $this->authorize('update', $deal);

        $quotation = $createAction->handle($request->validated(), $request->user());

        return redirect()->route('quotations.show', $quotation)->with('status', "Quotation {$quotation->quote_number} created.");
    }

    public function show(Request $request, Quotation $quotation): View
    {
        $this->authorize('view', $quotation);

        $quotation->load(['deal', 'company', 'contact', 'owner', 'items', 'discountApprover']);
        $contacts = Contact::query()->visibleTo($request->user())->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('pages.quotations.show', compact('quotation', 'contacts'));
    }

    public function update(
        UpdateQuotationRequest $request,
        Quotation $quotation,
        CalculateQuotationTotalsAction $calculateTotals,
    ): RedirectResponse {
        if (! $quotation->isDraft()) {
            throw new DomainRuleException('Only draft quotations can be edited.', 409);
        }

        $this->authorize('update', $quotation);

        $data = $request->validated();
        $computed = $calculateTotals->handle($data['items']);

        DB::transaction(function () use ($quotation, $data, $computed): void {
            $quotation->update([
                'contact_id' => $data['contact_id'] ?? $quotation->contact_id,
                'currency' => $data['currency'],
                'subtotal' => $computed['subtotal'],
                'discount_total' => $computed['discount_total'],
                'tax_total' => $computed['tax_total'],
                'grand_total' => $computed['grand_total'],
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'],
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'discount_approved_by_id' => null,
                'discount_approved_at' => null,
            ]);

            $quotation->items()->delete();
            $quotation->items()->createMany($computed['items']);
        });

        return redirect()->route('quotations.show', $quotation)->with('status', 'Quotation updated.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorize('delete', $quotation);

        $quotation->delete();

        return redirect()->route('quotations.index')->with('status', 'Quotation deleted.');
    }
}
