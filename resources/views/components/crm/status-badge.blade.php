@props(['status'])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $colorClasses = match ($value) {
        'new', 'open', 'pending', 'scheduled' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'contacted', 'in_progress', 'processing', 'rescheduled' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'qualified', 'sent' => 'bg-cyan-50 text-cyan-700 ring-cyan-600/20',
        'converted', 'won', 'accepted', 'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'disqualified', 'lost', 'rejected', 'expired', 'missed', 'failed', 'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        default => 'bg-slate-50 text-slate-700 ring-slate-600/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {$colorClasses}"]) }}>
    {{ str($value)->replace('_', ' ')->title() }}
</span>
