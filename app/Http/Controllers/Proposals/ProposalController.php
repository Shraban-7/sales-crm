<?php

declare(strict_types=1);

namespace App\Http\Controllers\Proposals;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProposalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->query('status');

        $proposals = Proposal::query()
            ->with(['deal', 'company', 'contact', 'owner', 'documents'])
            ->visibleTo($user)
            ->when(is_string($status) && $status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.proposals.index', [
            'proposals' => $proposals,
            'currentStatus' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('pages.proposals.create', [
            'proposalNumber' => Proposal::generateProposalNumber(),
            'deals' => Deal::query()->visibleTo($user)->where('status', 'open')->get(),
            'companies' => Company::query()->visibleTo($user)->get(),
            'contacts' => Contact::query()->visibleTo($user)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'deal_id' => ['nullable', 'integer', 'exists:deals,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $user = $request->user();

        $proposal = Proposal::query()->create([
            'team_id' => $user->team_id,
            'owner_id' => $user->id,
            'deal_id' => $data['deal_id'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'proposal_number' => Proposal::generateProposalNumber(),
            'title' => $data['title'],
            'amount' => $data['amount'],
            'currency' => strtoupper((string) $data['currency']),
            'status' => 'draft',
            'valid_until' => $data['valid_until'] ?? now()->addDays(30),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('proposals.show', $proposal)->with('status', 'Proposal created successfully.');
    }

    public function show(Proposal $proposal): View
    {
        $this->authorize('view', $proposal);

        $proposal->load(['deal', 'company', 'contact', 'owner', 'documents.uploader']);

        return view('pages.proposals.show', [
            'proposal' => $proposal,
        ]);
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        $this->authorize('delete', $proposal);

        $proposal->delete();

        return redirect()->route('proposals.index')->with('status', 'Proposal deleted.');
    }
}
