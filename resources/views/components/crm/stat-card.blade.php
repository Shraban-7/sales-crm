@props(['label', 'value' => null, 'subtext' => null, 'lines' => null])

<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $label }}</p>
    @if (! empty($lines))
        <div class="mt-2 space-y-0.5">
            @foreach ($lines as $line)
                <p class="text-lg font-semibold leading-tight text-slate-900">{{ $line }}</p>
            @endforeach
        </div>
    @else
        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $value }}</p>
    @endif
    @if ($subtext)
        <p class="mt-1 text-xs text-slate-500">{{ $subtext }}</p>
    @endif
</div>
