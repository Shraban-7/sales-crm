<?php

declare(strict_types=1);

namespace App\Http\Controllers\Quotations;

use App\Actions\Quotations\AcceptQuotationAction;
use App\Actions\Quotations\RejectQuotationAction;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Notifications\CrmAlertNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

final class PublicQuotationController extends Controller
{
    public function show(Request $request, Quotation $quotation): View
    {
        if ($quotation->status === QuotationStatus::Draft) {
            abort(404);
        }

        $quotation->load(['deal', 'company', 'contact', 'owner', 'items']);

        $expires = now()->addDays((int) config('crm.quotations.public_link_days', 30));

        return view('pages.quotations.public', [
            'quotation' => $quotation,
            'acceptUrl' => URL::temporarySignedRoute('quotations.public.accept', $expires, ['quotation' => $quotation->ulid]),
            'rejectUrl' => URL::temporarySignedRoute('quotations.public.reject', $expires, ['quotation' => $quotation->ulid]),
        ]);
    }

    public function accept(
        Request $request,
        Quotation $quotation,
        AcceptQuotationAction $acceptAction,
    ): RedirectResponse {
        $data = $request->validate([
            'acceptance_name' => ['required', 'string', 'max:150'],
            'acceptance_email' => ['nullable', 'email:rfc', 'max:191'],
        ]);

        $acceptAction->handle($quotation, null, $data['acceptance_name']);

        $this->notifyOwner(
            $quotation,
            'Quotation accepted',
            "{$data['acceptance_name']} accepted quotation {$quotation->quote_number} ({$quotation->currency} ".number_format((float) $quotation->grand_total, 2).').',
        );

        return back()->with('status', 'Thank you — the quotation has been accepted. Our team will be in touch shortly.');
    }

    public function reject(
        Request $request,
        Quotation $quotation,
        RejectQuotationAction $rejectAction,
    ): RedirectResponse {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $rejectAction->handle($quotation, $data['rejection_reason']);

        $this->notifyOwner(
            $quotation,
            'Quotation declined',
            "Quotation {$quotation->quote_number} was declined. Reason: {$data['rejection_reason']}",
        );

        return back()->with('status', 'Thank you — your response has been recorded.');
    }

    private function notifyOwner(Quotation $quotation, string $title, string $message): void
    {
        $owner = $quotation->owner;

        $owner?->notify(new CrmAlertNotification(
            category: 'quotations',
            title: $title,
            message: $message,
            actionUrl: route('quotations.show', $quotation),
        ));
    }
}
