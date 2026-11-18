@props(['label', 'value' => null, 'subtext' => null, 'lines' => null, 'accent' => 'brand'])

@php
    $accentRing = match ($accent) {
        'emerald' => 'border-t-emerald-500',
        'amber' => 'border-t-amber-500',
        'rose' => 'border-t-rose-500',
        'indigo' => 'border-t-indigo-500',
        default => 'border-t-brand-600',
    };
@endphp

<div class="bg-white rounded-2xl border border-slate-200/80 border-t-2 {{ $accentRing }} p-5 shadow-xs transition hover:shadow-sm">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
    @if (! empty($lines))
        <div class="mt-2 space-y-0.5 tabular-nums">
            @foreach ($lines as $line)
                <p class="text-xl font-bold leading-tight text-slate-900">{{ $line }}</p>
            @endforeach
        </div>
    @else
        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 tabular-nums">{{ $value }}</p>
    @endif
    @if ($subtext)
        <p class="mt-1.5 text-xs text-slate-500">{{ $subtext }}</p>
    @endif
</div>
