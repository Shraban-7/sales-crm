<x-layouts.app title="Users & Teams — Sale Fusion CRM" header="Users & Sales Teams">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @can('create', \App\Models\User::class)
            <form method="POST" action="{{ route('settings.users.store') }}" class="crm-card p-6 space-y-4">
                @csrf
                <h2 class="text-sm font-semibold text-slate-900">Create User</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input type="text" name="name" placeholder="Full Name *" required class="crm-input">
                    <input type="email" name="email" placeholder="Email *" required class="crm-input">
                    <select name="team_id" required class="crm-select">
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                    <select name="role" required class="crm-select">
                        <option value="Sales Rep">Sales Rep</option>
                        <option value="Sales Manager">Sales Manager</option>
                        <option value="Org Admin">Org Admin</option>
                        <option value="Read-Only Analyst">Read-Only Analyst</option>
                        @if (auth()->user()->hasRole('Super Admin'))
                            <option value="Super Admin">Super Admin</option>
                        @endif
                    </select>
                    <input type="password" name="password" placeholder="Password (12+ chars)" required class="crm-input">
                    <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="crm-input">
                </div>
                <button type="submit" class="crm-btn-primary">Create User</button>
            </form>
        @endcan

        @can('create', \App\Models\Team::class)
            <form method="POST" action="{{ route('settings.teams.store') }}" class="crm-card p-6 space-y-4 h-fit">
                @csrf
                <h2 class="text-sm font-semibold text-slate-900">Create Sales Team</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="text" name="name" placeholder="Team Name *" required class="crm-input">
                    <select name="leader_id" class="crm-select">
                        <option value="">No Leader</option>
                        @foreach ($activeUsers as $candidate)
                            <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" min="0" name="monthly_revenue_target" placeholder="Monthly Target *" value="0" required class="crm-input">
                </div>
                <button type="submit" class="crm-btn-dark">Create Team</button>
            </form>
        @endcan
    </div>

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead class="crm-thead">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Team</th>
                <th class="px-4 py-3">Role</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($users as $user)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $user->team?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700 font-medium">{{ $user->getRoleNames()->first() }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/80' : 'bg-rose-50 text-rose-700 ring-rose-200/80' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-3">
                        @can('update', $user)
                            <button type="button" onclick="document.getElementById('edit-user-{{ $user->id }}').showModal()" class="text-xs font-semibold text-brand-600 hover:underline">Edit</button>
                            <button type="button" onclick="document.getElementById('reassign-user-{{ $user->id }}').showModal()" class="text-xs font-semibold text-slate-700 hover:underline">Reassign</button>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}

    @foreach ($users as $user)
        @can('update', $user)
            <dialog id="edit-user-{{ $user->id }}" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-2xl backdrop:bg-slate-950/60">
                <form method="POST" action="{{ route('settings.users.update', $user) }}" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    <h3 class="text-base font-semibold text-slate-900">Edit {{ $user->name }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Name *</label>
                            <input type="text" name="name" value="{{ $user->name }}" required class="crm-input py-1.5">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Email *</label>
                            <input type="email" name="email" value="{{ $user->email }}" required class="crm-input py-1.5">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Team *</label>
                            <select name="team_id" required class="crm-select py-1.5">
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" @selected($user->team_id === $team->id)>{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Role *</label>
                            @php $currentRole = $user->getRoleNames()->first(); @endphp
                            <select name="role" required class="crm-select py-1.5">
                                @foreach (['Sales Rep', 'Sales Manager', 'Org Admin', 'Read-Only Analyst'] as $roleName)
                                    <option value="{{ $roleName }}" @selected($currentRole === $roleName)>{{ $roleName }}</option>
                                @endforeach
                                @if (auth()->user()->hasRole('Super Admin'))
                                    <option value="Super Admin" @selected($currentRole === 'Super Admin')>Super Admin</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                        <select name="is_active" class="crm-select py-1.5">
                            <option value="1" @selected($user->is_active)>Active</option>
                            <option value="0" @selected(! $user->is_active)>Inactive</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('edit-user-{{ $user->id }}').close()" class="crm-btn-secondary py-1.5">Cancel</button>
                        <button type="submit" class="crm-btn-dark py-1.5">Save</button>
                    </div>
                </form>
            </dialog>

            <dialog id="reassign-user-{{ $user->id }}" class="rounded-2xl border border-slate-200 p-6 max-w-md w-full m-auto shadow-2xl backdrop:bg-slate-950/60">
                <form method="POST" action="{{ route('settings.users.reassign', $user) }}" class="space-y-3.5">
                    @csrf
                    <h3 class="text-base font-semibold text-slate-900">Reassign Records from {{ $user->name }}</h3>
                    <p class="text-xs text-slate-500">Transfers open leads, open deals, contacts, companies, quotations, tasks, and follow-ups.</p>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Target Active User *</label>
                        <select name="to_user_id" required class="crm-select py-1.5">
                            @foreach ($activeUsers as $target)
                                @continue($target->id === $user->id)
                                <option value="{{ $target->id }}">{{ $target->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('reassign-user-{{ $user->id }}').close()" class="crm-btn-secondary py-1.5">Cancel</button>
                        <button type="submit" class="crm-btn-primary py-1.5">Reassign</button>
                    </div>
                </form>
            </dialog>
        @endcan
    @endforeach
</x-layouts.app>
