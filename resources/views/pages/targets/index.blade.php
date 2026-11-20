<x-layouts.app title="Targets & Commission — Sale Fusion CRM" header="Revenue Targets & Commission Tracking">
    <div class="space-y-6">
        <!-- Target Setting & List -->
        <div class="crm-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Monthly Revenue Targets ({{ $selectedMonth }}/{{ $selectedYear }})</h2>
                    <p class="text-xs text-slate-500">Track and assign quota goals for company, teams, and individual reps.</p>
                </div>
            </div>

            <!-- Set Target Form -->
            <form method="POST" action="{{ route('targets.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                @csrf
                <input type="hidden" name="period_year" value="{{ $selectedYear }}">
                <input type="hidden" name="period_month" value="{{ $selectedMonth }}">

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Target Type</label>
                    <select name="type" required class="crm-select text-xs">
                        <option value="company">Company</option>
                        <option value="team">Team</option>
                        <option value="individual">Individual</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Team</label>
                    <select name="team_id" class="crm-select text-xs">
                        <option value="">None</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">User</label>
                    <select name="user_id" class="crm-select text-xs">
                        <option value="">None</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Target Amount</label>
                    <input type="number" step="0.01" name="target_amount" required placeholder="50000.00" class="crm-input text-xs">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Currency</label>
                    <input type="text" name="currency" value="USD" required maxlength="3" class="crm-input text-xs uppercase">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="crm-btn-primary w-full text-xs">Set Target</button>
                </div>
            </form>

            <!-- Targets Table -->
            <div class="divide-y divide-slate-100">
                @forelse ($targets as $target)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex rounded px-2 py-0.5 font-bold uppercase
                                @if($target->type === 'company') bg-purple-100 text-purple-800
                                @elseif($target->type === 'team') bg-blue-100 text-blue-800
                                @else bg-emerald-100 text-emerald-800 @endif">
                                {{ $target->type }}
                            </span>
                            <span class="font-semibold text-slate-900">
                                {{ $target->type === 'team' ? $target->team?->name : ($target->type === 'individual' ? $target->user?->name : 'Company-wide') }}
                            </span>
                        </div>
                        <span class="font-bold text-slate-900">
                            {{ $target->currency }} {{ number_format((float) $target->target_amount, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-3 text-center">No revenue targets established for this month.</p>
                @endforelse
            </div>
        </div>

        <!-- Commission Tracking -->
        <div class="crm-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-900">Commission Tracking on Won Deals</h2>

            <!-- Record Commission Form -->
            <form method="POST" action="{{ route('targets.commissions.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Won Deal *</label>
                    <select name="deal_id" required class="crm-select text-xs">
                        <option value="">Select Won Deal</option>
                        @foreach ($deals as $deal)
                            <option value="{{ $deal->id }}">{{ $deal->title }} ({{ $deal->currency }} {{ number_format((float) $deal->value, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sales Rep *</label>
                    <select name="user_id" required class="crm-select text-xs">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Deal Value *</label>
                    <input type="number" step="0.01" name="deal_value" required placeholder="0.00" class="crm-input text-xs">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Commission ($) *</label>
                    <input type="number" step="0.01" name="amount" required placeholder="0.00" class="crm-input text-xs">
                </div>

                <div class="flex items-end">
                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="crm-btn-primary w-full text-xs">Record Commission</button>
                </div>
            </form>

            <!-- Commissions Table -->
            <div class="divide-y divide-slate-100 overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-slate-400 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-2">Deal</th>
                            <th class="py-2">Rep</th>
                            <th class="py-2">Deal Value</th>
                            <th class="py-2">Commission</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($commissions as $comm)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 font-semibold text-slate-900">{{ $comm->deal?->title }}</td>
                                <td class="py-2.5 text-slate-600">{{ $comm->user?->name }}</td>
                                <td class="py-2.5 text-slate-700">${{ number_format((float) $comm->deal_value, 2) }}</td>
                                <td class="py-2.5 font-bold text-emerald-600">${{ number_format((float) $comm->amount, 2) }}</td>
                                <td class="py-2.5">
                                    <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold uppercase
                                        {{ $comm->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $comm->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">No commissions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
