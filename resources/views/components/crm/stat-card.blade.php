@props(['label', 'value', 'subtext' => null])

<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $value }}</p>
    @if ($subtext)
        <p class="mt-1 text-xs text-slate-500">{{ $subtext }}</p>
    @endif
</div>
