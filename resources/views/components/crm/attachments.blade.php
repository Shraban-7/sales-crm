@props(['subject', 'subjectType'])

<div class="crm-card p-5 space-y-3">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Attachments</h3>
        <span class="text-xs text-slate-400">{{ $subject->attachments->count() }}</span>
    </div>

    <div class="space-y-2">
        @forelse ($subject->attachments as $attachment)
            <div class="flex items-center justify-between gap-2 text-sm rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2">
                <div class="min-w-0">
                    <a href="{{ route('attachments.download', $attachment) }}" class="font-medium text-brand-600 hover:underline break-all">{{ $attachment->original_name }}</a>
                    <p class="text-xs text-slate-500">{{ number_format($attachment->size_bytes / 1024, 1) }} KB &middot; {{ $attachment->uploader?->name }} &middot; {{ $attachment->created_at?->diffForHumans() }}</p>
                </div>
                @can('delete', $attachment)
                    <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm('Remove this attachment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-800">Remove</button>
                    </form>
                @endcan
            </div>
        @empty
            <p class="text-xs text-slate-500">No attachments yet.</p>
        @endforelse
    </div>

    @can('update', $subject)
        <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="pt-3 border-t border-slate-200/80 space-y-2">
            @csrf
            <input type="hidden" name="attachable_type" value="{{ $subjectType }}">
            <input type="hidden" name="attachable_id" value="{{ $subject->id }}">
            <input type="file" name="file" required class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
            <p class="text-[11px] text-slate-500">PDF, Office, image, CSV, TXT, or ZIP. Max 10 MB.</p>
            <button type="submit" class="crm-btn-dark text-xs px-3 py-1.5">Upload File</button>
        </form>
    @endcan
</div>
