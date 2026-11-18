@props(['status'])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $styles = match ($value) {
        'new', 'open', 'pending', 'scheduled' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'bg-blue-500'],
        'contacted', 'in_progress', 'processing', 'rescheduled', 'high' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'bg-amber-500'],
        'qualified', 'sent', 'medium' => ['bg-cyan-50 text-cyan-700 ring-cyan-600/20', 'bg-cyan-500'],
        'converted', 'won', 'accepted', 'completed', 'enabled' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
        'disqualified', 'lost', 'rejected', 'expired', 'missed', 'failed', 'cancelled', 'urgent' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'bg-rose-500'],
        default => ['bg-slate-100 text-slate-700 ring-slate-600/20', 'bg-slate-400'],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {$styles[0]}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $styles[1] }}" aria-hidden="true"></span>
    {{ str($value)->replace('_', ' ')->title() }}
</span>
