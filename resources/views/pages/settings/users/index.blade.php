<x-layouts.app title="Users & Teams — Sales CRM" header="Users & Sales Teams">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @can('create', \App\Models\User::class)
            <form method="POST" action="{{ route('settings.users.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 space-y-3">
                @csrf
                <h2 class="text-sm font-semibold text-slate-900">Create User</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input type="text" name="name" placeholder="Full Name *" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input type="email" name="email" placeholder="Email *" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <select name="team_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                    <select name="role" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="Sales Rep">Sales Rep</option>
                        <option value="Sales Manager">Sales Manager</option>
                        <option value="Org Admin">Org Admin</option>
                        <option value="Read-Only Analyst">Read-Only Analyst</option>
                        @if (auth()->user()->hasRole('Super Admin'))
                            <option value="Super Admin">Super Admin</option>
                        @endif
                    </select>
                    <input type="password" name="password" placeholder="Password (12+ chars)" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Create User</button>
            </form>
        @endcan

        @can('create', \App\Models\Team::class)
            <form method="POST" action="{{ route('settings.teams.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 space-y-3 h-fit">
                @csrf
                <h2 class="text-sm font-semibold text-slate-900">Create Sales Team</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="text" name="name" placeholder="Team Name *" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <select name="leader_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">No Leader</option>
                        @foreach ($activeUsers as $candidate)
                            <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" min="0" name="monthly_revenue_target" placeholder="Monthly Target *" value="0" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Create Team</button>
            </form>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
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
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $user->team?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $user->getRoleNames()->first() }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded px-2 py-0.5 text-xs font-medium {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        @can('update', $user)
                            <button type="button" onclick="document.getElementById('edit-user-{{ $user->id }}').showModal()" class="text-xs font-medium text-brand-600 hover:underline">Edit</button>
                            <button type="button" onclick="document.getElementById('reassign-user-{{ $user->id }}').showModal()" class="text-xs font-medium text-slate-700 hover:underline">Reassign</button>
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
            <dialog id="edit-user-{{ $user->id }}" class="rounded-2xl border border-slate-200 p-6 max-w-lg w-full m-auto shadow-xl backdrop:bg-slate-900/50">
                <form method="POST" action="{{ route('settings.users.update', $user) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <h3 class="text-base font-semibold text-slate-900">Edit {{ $user->name }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Name *</label>
                            <input type="text" name="name" value="{{ $user->name }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Email *</label>
                            <input type="email" name="email" value="{{ $user->email }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Team *</label>
                            <select name="team_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" @selected($user->team_id === $team->id)>{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Role *</label>
                            @php $currentRole = $user->getRoleNames()->first(); @endphp
                            <select name="role" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
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
                        <label class="block text-xs font-medium text-slate-600">Status</label>
                        <select name="is_active" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            <option value="1" @selected($user->is_active)>Active</option>
                            <option value="0" @selected(! $user->is_active)>Inactive</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('edit-user-{{ $user->id }}').close()" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-semibold text-white">Save</button>
                    </div>
                </form>
            </dialog>

            <dialog id="reassign-user-{{ $user->id }}" class="rounded-2xl border border-slate-200 p-6 max-w-md w-full m-auto shadow-xl backdrop:bg-slate-900/50">
                <form method="POST" action="{{ route('settings.users.reassign', $user) }}" class="space-y-3">
                    @csrf
                    <h3 class="text-base font-semibold text-slate-900">Reassign Records from {{ $user->name }}</h3>
                    <p class="text-xs text-slate-500">Transfers open leads, open deals, contacts, companies, quotations, tasks, and follow-ups.</p>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Target Active User *</label>
                        <select name="to_user_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            @foreach ($activeUsers as $target)
                                @continue($target->id === $user->id)
                                <option value="{{ $target->id }}">{{ $target->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('reassign-user-{{ $user->id }}').close()" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-1.5 text-sm font-semibold text-white">Reassign</button>
                    </div>
                </form>
            </dialog>
        @endcan
    @endforeach
</x-layouts.app>
