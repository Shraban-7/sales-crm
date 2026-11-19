<x-layouts.app title="Webhooks — Sale Fusion CRM" header="Webhooks & Integrations">
    @if (session('generated_webhook_secret'))
        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 space-y-1.5">
            <p class="font-semibold">Copy your generated signing secret now — it will not be shown again:</p>
            <code class="block rounded-lg bg-white border border-amber-200 px-3 py-2 font-mono text-xs text-slate-900 break-all">{{ session('generated_webhook_secret') }}</code>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="crm-card p-6 space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Inbound Lead Capture Webhook</h2>
                    <p class="text-xs text-slate-500 mt-1">Accept HMAC-SHA256 signed leads from landing pages, Zapier, or external forms.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $inboundWebhookConfigured ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/80' : 'bg-amber-50 text-amber-700 ring-amber-200/80' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $inboundWebhookConfigured ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $inboundWebhookConfigured ? 'Configured' : 'Secret Not Set' }}
                </span>
            </div>

            <div class="space-y-2 text-xs">
                <p class="font-medium text-slate-600">Endpoint</p>
                <code class="block rounded-xl bg-slate-950 text-slate-100 px-3.5 py-2.5 font-mono break-all">POST {{ $inboundWebhookUrl }}</code>
            </div>

            <div class="space-y-1.5 text-xs text-slate-600">
                <p><span class="font-semibold text-slate-800">Required header:</span> <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">X-CRM-Signature</code> = <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">hash_hmac('sha256', rawBody, CRM_LEAD_WEBHOOK_SECRET)</code></p>
                <p>Configure <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">CRM_LEAD_WEBHOOK_SECRET</code> in your server environment file (<code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">.env</code>) to activate inbound webhook delivery.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.webhooks.store') }}" class="crm-card p-6 space-y-4">
            @csrf
            <h2 class="text-sm font-semibold text-slate-900">Add Outbound Webhook</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Endpoint Name *" required class="crm-input">
                <select name="team_id" class="crm-select">
                    <option value="">All Teams (Organization-Wide)</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected((string) old('team_id') === (string) $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
                <input type="url" name="url" value="{{ old('url') }}" placeholder="https://example.com/webhooks/crm *" required class="crm-input sm:col-span-2">
                <input type="text" name="secret" value="" placeholder="Signing Secret (optional, auto-generated if blank)" autocomplete="off" class="crm-input sm:col-span-2">
            </div>

            <div class="space-y-2">
                <p class="text-xs font-medium text-slate-600">Subscribed Events *</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($events as $event)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="events[]" value="{{ $event->value }}" @checked(in_array($event->value, old('events', []), true)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                            <span>{{ $event->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                    <span>Active</span>
                </label>
                <button type="submit" class="crm-btn-primary">Create Webhook</button>
            </div>
        </form>
    </div>

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
            <tr>
                <th class="px-4 py-3">Endpoint</th>
                <th class="px-4 py-3">Scope</th>
                <th class="px-4 py-3">Events</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Last Delivery</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($webhooks as $webhook)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-900">{{ $webhook->name }}</p>
                        <p class="text-xs font-mono text-slate-500 break-all">{{ $webhook->url }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $webhook->team?->name ?? 'All Teams' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach ($webhook->events ?? [] as $eventName)
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-mono text-slate-700">{{ $eventName }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $webhook->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/80' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $webhook->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $webhook->is_active ? 'Active' : 'Paused' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600">
                        @if ($webhook->last_triggered_at)
                            <span class="font-mono font-medium">HTTP {{ $webhook->last_status_code }}</span>
                            <span class="text-slate-400">&middot; {{ $webhook->last_triggered_at->diffForHumans() }}</span>
                        @else
                            <span class="text-slate-400">Never triggered</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                        <button type="button" onclick="document.getElementById('edit-webhook-{{ $webhook->id }}').showModal()" class="text-xs font-semibold text-brand-600 hover:underline">Edit</button>
                        <form method="POST" action="{{ route('settings.webhooks.destroy', $webhook) }}" class="inline" onsubmit="return confirm('Remove this webhook endpoint?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">No outbound webhooks configured yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $webhooks->links() }}

    @foreach ($webhooks as $webhook)
        <dialog id="edit-webhook-{{ $webhook->id }}" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-2xl backdrop:bg-slate-950/60">
            <form method="POST" action="{{ route('settings.webhooks.update', $webhook) }}" class="space-y-3.5">
                @csrf
                @method('PUT')
                <h3 class="text-base font-semibold text-slate-900">Edit Webhook: {{ $webhook->name }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Name *</label>
                        <input type="text" name="name" value="{{ $webhook->name }}" required class="crm-input py-1.5">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Scope</label>
                        <select name="team_id" class="crm-select py-1.5">
                            <option value="">All Teams</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected($webhook->team_id === $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Target URL *</label>
                        <input type="url" name="url" value="{{ $webhook->url }}" required class="crm-input py-1.5">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Rotate Signing Secret (leave blank to keep existing)</label>
                        <input type="text" name="secret" value="" autocomplete="off" placeholder="Min 16 characters" class="crm-input py-1.5">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <p class="text-xs font-medium text-slate-600">Subscribed Events *</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($events as $event)
                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="events[]" value="{{ $event->value }}" @checked(in_array($event->value, $webhook->events ?? [], true)) class="rounded border-slate-300 text-brand-600">
                                <span>{{ $event->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="is_active" class="crm-select py-1.5">
                        <option value="1" @selected($webhook->is_active)>Active</option>
                        <option value="0" @selected(! $webhook->is_active)>Paused</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('edit-webhook-{{ $webhook->id }}').close()" class="crm-btn-secondary py-1.5">Cancel</button>
                    <button type="submit" class="crm-btn-dark py-1.5">Save</button>
                </div>
            </form>
        </dialog>
    @endforeach
</x-layouts.app>
