@props(['subject', 'subjectType'])

<div class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
    <h3 class="text-sm font-semibold text-slate-900">Attachments</h3>

    <div class="space-y-2">
        @forelse ($subject->attachments as $attachment)
            <div class="flex items-center justify-between gap-2 text-sm border-b border-slate-100 pb-2">
                <div class="min-w-0">
                    <a href="{{ route('attachments.download', $attachment) }}" class="font-medium text-brand-600 hover:underline break-all">{{ $attachment->original_name }}</a>
                    <p class="text-xs text-slate-500">{{ number_format($attachment->size_bytes / 1024, 1) }} KB &middot; {{ $attachment->uploader?->name }} &middot; {{ $attachment->created_at?->diffForHumans() }}</p>
                </div>
                @can('delete', $attachment)
                    <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm('Remove this attachment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-rose-600">Remove</button>
                    </form>
                @endcan
            </div>
        @empty
            <p class="text-sm text-slate-500">No attachments yet.</p>
        @endforelse
    </div>

    @can('update', $subject)
        <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="pt-3 border-t border-slate-200 space-y-2">
            @csrf
            <input type="hidden" name="attachable_type" value="{{ $subjectType }}">
            <input type="hidden" name="attachable_id" value="{{ $subject->id }}">
            <input type="file" name="file" required class="block w-full text-xs text-slate-600">
            <p class="text-xs text-slate-500">PDF, Office, image, CSV, TXT, or ZIP. Maximum 10 MB.</p>
            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white">Upload</button>
        </form>
    @endcan
</div>
