<x-layouts.app title="Proposals — Sale Fusion CRM" header="Proposals & Formal Offers">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('proposals.create') }}" class="crm-btn-primary text-xs">
                    + New Proposal
                </a>
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                <a href="{{ route('proposals.index') }}" class="px-3 py-1.5 rounded-lg font-medium {{ empty($currentStatus) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">All</a>
                <a href="{{ route('proposals.index', ['status' => 'draft']) }}" class="px-3 py-1.5 rounded-lg font-medium {{ $currentStatus === 'draft' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Draft</a>
                <a href="{{ route('proposals.index', ['status' => 'sent']) }}" class="px-3 py-1.5 rounded-lg font-medium {{ $currentStatus === 'sent' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Sent</a>
                <a href="{{ route('proposals.index', ['status' => 'accepted']) }}" class="px-3 py-1.5 rounded-lg font-medium {{ $currentStatus === 'accepted' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Accepted</a>
            </div>
        </div>

        <div class="crm-card divide-y divide-slate-100 overflow-hidden">
            @forelse ($proposals as $proposal)
                <div class="p-4 flex flex-wrap items-center justify-between gap-4 hover:bg-slate-50/70 transition">
                    <div class="min-w-0 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-brand-600">{{ $proposal->proposal_number }}</span>
                            <a href="{{ route('proposals.show', $proposal) }}" class="text-sm font-semibold text-slate-900 hover:text-brand-600 transition">
                                {{ $proposal->title }}
                            </a>
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium uppercase
                                @if($proposal->status === 'accepted') bg-emerald-100 text-emerald-800
                                @elseif($proposal->status === 'sent') bg-blue-100 text-blue-800
                                @elseif($proposal->status === 'rejected') bg-rose-100 text-rose-800
                                @else bg-slate-100 text-slate-800 @endif">
                                {{ $proposal->status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">
                            Client: <strong class="text-slate-700">{{ $proposal->company?->name ?? $proposal->contact?->name ?? 'None' }}</strong> &middot;
                            Owner: <span class="text-slate-700">{{ $proposal->owner?->name }}</span> &middot;
                            Valid Until: <span class="text-slate-700">{{ $proposal->valid_until?->format('M d, Y') ?? 'No expiry' }}</span>
                            @if ($proposal->documents->count() > 0)
                                &middot; 📎 {{ $proposal->documents->count() }} file(s)
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="text-sm font-bold text-slate-900">
                            {{ $proposal->currency }} {{ number_format((float) $proposal->amount, 2) }}
                        </span>
                        <a href="{{ route('proposals.show', $proposal) }}" class="crm-btn-secondary text-xs">
                            View Proposal
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-500 text-sm">
                    No proposals found. Click "+ New Proposal" to create one.
                </div>
            @endforelse
        </div>

        <div>
            {{ $proposals->links() }}
        </div>
    </div>
</x-layouts.app>
