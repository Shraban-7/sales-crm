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
        ConvertLeadToDealAction $convertAction,
    ): RedirectResponse {
        $this->authorize('convert', $lead);

        $deal = $convertAction->handle(
            lead: $lead,
            data: LeadConversionData::fromRequest($request),
            actor: $request->user(),
        );

        return redirect()
            ->route('deals.show', $deal)
            ->with('status', __('Lead successfully converted to deal :title.', ['title' => $deal->title]));
    }
}
