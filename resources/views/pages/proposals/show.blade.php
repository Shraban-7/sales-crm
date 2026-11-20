<x-layouts.app title="{{ $proposal->proposal_number }} — Sale Fusion CRM" header="Proposal {{ $proposal->proposal_number }}">
    <div class="space-y-6 max-w-4xl">
        <div class="flex items-center justify-between">
            <a href="{{ route('proposals.index') }}" class="text-xs text-brand-600 hover:underline">&larr; Back to proposals</a>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded px-2.5 py-1 text-xs font-semibold uppercase
                    @if($proposal->status === 'accepted') bg-emerald-100 text-emerald-800
                    @elseif($proposal->status === 'sent') bg-blue-100 text-blue-800
                    @elseif($proposal->status === 'rejected') bg-rose-100 text-rose-800
                    @else bg-slate-100 text-slate-800 @endif">
                    {{ $proposal->status }}
                </span>
            </div>
        </div>

        <div class="crm-card p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $proposal->title }}</h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Owner: <strong class="text-slate-700">{{ $proposal->owner?->name }}</strong> &middot;
                        Created: {{ $proposal->created_at->format('M d, Y') }} &middot;
                        Valid until: {{ $proposal->valid_until?->format('M d, Y') ?? 'Indefinite' }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 uppercase font-bold">Total Amount</p>
                    <p class="text-2xl font-black text-brand-700">{{ $proposal->currency }} {{ number_format((float) $proposal->amount, 2) }}</p>
                </div>
            </div>

            @if ($proposal->notes)
                <div class="space-y-1">
                    <h3 class="text-xs font-semibold text-slate-700 uppercase">Scope &amp; Deliverables</h3>
                    <div class="text-sm text-slate-700 whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100">
                        {{ $proposal->notes }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Proposal Documents (Secure Private Storage) -->
        <div class="crm-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-900">Uploaded Proposal Documents (Private &amp; Access-Controlled)</h2>

            <form method="POST" action="{{ route('proposals.documents.store', $proposal) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="file" required class="crm-input text-xs">
                <button type="submit" class="crm-btn-primary text-xs">Upload Document</button>
            </form>

            <div class="divide-y divide-slate-100 pt-2">
                @forelse ($proposal->documents as $doc)
                    <div class="py-3 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span>📄</span>
                            <span class="font-medium text-slate-800">{{ $doc->original_name }}</span>
                            <span class="text-slate-400">({{ number_format($doc->size_bytes / 1024, 1) }} KB)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('proposals.documents.download', $doc) }}" class="text-brand-600 hover:underline">Download</a>
                            <form method="POST" action="{{ route('proposals.documents.destroy', $doc) }}" onsubmit="return confirm('Remove file?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-3">No documents attached to this proposal yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
