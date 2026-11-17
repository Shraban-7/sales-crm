<x-layouts.app title="Users &amp; Teams — Sales CRM" header="Users &amp; Sales Teams">
    @can('create', \App\Models\User::class)
        <form method="POST" action="{{ route('settings.users.store') }}" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-6 gap-3">
            @csrf
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
            </select>
            <input type="password" name="password" placeholder="Password (12+ chars)" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="md:col-span-6 justify-self-end rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Create User</button>
        </form>
    @endcan

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Team</th>
                <th class="px-4 py-3">Role</th>
                <th class="px-4 py-3">Status</th>
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
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</x-layouts.app>
