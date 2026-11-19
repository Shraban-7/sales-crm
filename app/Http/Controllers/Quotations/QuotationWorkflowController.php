<?php

declare(strict_types=1);

namespace App\Http\Controllers\Quotations;

use App\Actions\Activities\LogActivityAction;
use App\Actions\Quotations\AcceptQuotationAction;
use App\Actions\Quotations\CloneQuotationVersionAction;
use App\Actions\Quotations\RejectQuotationAction;
use App\Enums\ActivityDirection;
use App\Enums\ActivityType;
use App\Enums\QuotationStatus;
use App\Enums\WebhookEvent;
use App\Exceptions\DomainRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotations\RejectQuotationRequest;
use App\Mail\QuotationDeliveryMail;
use App\Models\Quotation;
use App\Services\Audit\AuditLoggerService;
use App\Services\Webhooks\WebhookDispatcherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

final class QuotationWorkflowController extends Controller
{
    public function send(
        Request $request,
        Quotation $quotation,
        AuditLoggerService $auditLogger,
        LogActivityAction $logActivity,
        WebhookDispatcherService $webhooks,
    ): RedirectResponse {
        $this->authorize('send', $quotation);

        if (! $quotation->isDraft()) {
            throw new DomainRuleException('Only draft quotations can be sent.', 409);
        }

        if ($quotation->requiresDiscountApproval() && $quotation->discount_approved_at === null) {
            throw new DomainRuleException(
                'This quotation discount exceeds '.config('crm.quotations.discount_approval_threshold', 15).'% and needs manager approval before it can be sent.',
                409,
            );
        }

        $quotation->load(['deal', 'company', 'contact', 'owner', 'items']);

        $recipient = $quotation->contact?->email;

        if ($recipient === null || $recipient === '') {
            throw new DomainRuleException('Add an email address to the quotation contact before sending.', 409);
        }

        $publicUrl = URL::temporarySignedRoute(
            'quotations.public.show',
            now()->addDays((int) config('crm.quotations.public_link_days', 30)),
            ['quotation' => $quotation->ulid],
        );

        $quotation->update([
            'status' => QuotationStatus::Sent,
            'sent_at' => now(),
        ]);

        Mail::to($recipient)->queue(new QuotationDeliveryMail(
            $quotation->fresh(['deal', 'company', 'contact', 'owner', 'items']),
            $publicUrl,
        ));

        if ($quotation->deal !== null) {
            $logActivity->handle($quotation->deal, $request->user(), [
                'type' => ActivityType::Email->value,
                'direction' => ActivityDirection::Outbound->value,
                'outcome' => 'Sent',
                'summary' => "Sent quotation {$quotation->quote_number} to {$recipient}",
                'body' => "Total: {$quotation->currency} ".number_format((float) $quotation->grand_total, 2),
                'occurred_at' => now(),
            ]);
        }

        $auditLogger->log(
            event: 'sent',
            auditable: $quotation,
            actor: $request->user(),
            oldValues: ['status' => QuotationStatus::Draft->value],
            newValues: ['status' => QuotationStatus::Sent->value, 'recipient' => $recipient],
            teamId: $quotation->team_id,
        );

        $webhooks->dispatch(
            WebhookEvent::QuotationSent,
            [
                'quotation_id' => $quotation->id,
                'quote_number' => $quotation->quote_number,
                'deal_id' => $quotation->deal_id,
                'grand_total' => (float) $quotation->grand_total,
                'currency' => $quotation->currency,
            ],
            $quotation->team_id,
        );

        return back()->with('status', "Quotation {$quotation->quote_number} sent to {$recipient}.");
    }

    public function accept(Request $request, Quotation $quotation, AcceptQuotationAction $acceptAction): RedirectResponse
    {
        $this->authorize('send', $quotation);

        $acceptAction->handle($quotation, $request->user());

        return back()->with('status', "Quotation {$quotation->quote_number} accepted and deal value synced.");
    }

    public function reject(
        RejectQuotationRequest $request,
        Quotation $quotation,
        RejectQuotationAction $rejectAction,
    ): RedirectResponse {
        $this->authorize('send', $quotation);

        $rejectAction->handle($quotation, (string) $request->validated('rejection_reason'), $request->user());

        return back()->with('status', "Quotation {$quotation->quote_number} marked as Rejected.");
    }

    public function approveDiscount(
        Request $request,
        Quotation $quotation,
        AuditLoggerService $auditLogger,
    ): RedirectResponse {
        $this->authorize('approveDiscount', $quotation);

        if (! $quotation->isDraft()) {
            throw new DomainRuleException('Only draft quotations can be approved.', 409);
        }

        if (! $quotation->requiresDiscountApproval()) {
            throw new DomainRuleException('This quotation does not require discount approval.', 409);
        }

        $quotation->update([
            'discount_approved_by_id' => $request->user()->id,
            'discount_approved_at' => now(),
        ]);

        $auditLogger->log(
            event: 'discount_approved',
            auditable: $quotation,
            actor: $request->user(),
            newValues: [
                'discount_percent' => $quotation->discountPercent(),
                'discount_approved_by_id' => $request->user()->id,
            ],
            teamId: $quotation->team_id,
        );

        return back()->with('status', 'Discount approved. The quotation can now be sent.');
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
