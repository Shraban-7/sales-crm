<?php

declare(strict_types=1);

namespace App\Http\Controllers\Quotations;

use App\Actions\Quotations\AcceptQuotationAction;
use App\Actions\Quotations\CloneQuotationVersionAction;
use App\Enums\QuotationStatus;
use App\Exceptions\DomainRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotations\RejectQuotationRequest;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class QuotationWorkflowController extends Controller
{
    public function send(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('send', $quotation);

        if (! $quotation->isDraft()) {
            throw new DomainRuleException('Only draft quotations can be sent.', 409);
        }

        $quotation->update([
            'status' => QuotationStatus::Sent,
            'sent_at' => now(),
        ]);

        return back()->with('status', "Quotation {$quotation->quote_number} marked as Sent.");
    }

    public function accept(Request $request, Quotation $quotation, AcceptQuotationAction $acceptAction): RedirectResponse
    {
        $this->authorize('send', $quotation);

        $acceptAction->handle($quotation, $request->user());

        return back()->with('status', "Quotation {$quotation->quote_number} accepted and deal value synced.");
    }

    public function reject(RejectQuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('send', $quotation);

        $quotation->update([
            'status' => QuotationStatus::Rejected,
            'rejected_at' => now(),
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        return back()->with('status', "Quotation {$quotation->quote_number} marked as Rejected.");
    }

    public function cloneVersion(Request $request, Quotation $quotation, CloneQuotationVersionAction $cloneAction): RedirectResponse
    {
        $this->authorize('create', Quotation::class);
        $this->authorize('view', $quotation);

        $clone = $cloneAction->handle($quotation, $request->user());

        return redirect()->route('quotations.show', $clone)->with('status', "Cloned as {$clone->quote_number} (v{$clone->version}).");
    }

    public function print(Quotation $quotation): View
    {
        $this->authorize('view', $quotation);

        $quotation->load(['deal', 'company', 'contact', 'owner', 'items']);

        return view('pages.quotations.print', compact('quotation'));
    }
}
