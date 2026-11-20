<x-layouts.app title="Profile Settings — Sale Fusion CRM" header="My Profile & Preferences">
    <div class="max-w-2xl space-y-6">
        <form method="POST" action="{{ route('settings.profile.update') }}" class="crm-card p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-base font-semibold text-slate-900">Personal Account Information</h2>
                <p class="text-xs text-slate-500">Update your contact information, title, and timezone.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="crm-input">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Work Email</label>
                <input type="email" value="{{ $user->email }}" disabled class="crm-input bg-slate-100 cursor-not-allowed">
                <p class="text-[11px] text-slate-400 mt-0.5">Email can only be changed by an organization administrator.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+1 234 567 890" class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Job Title</label>
                    <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" placeholder="Enterprise Account Executive" class="crm-input">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Preferred Timezone</label>
                <input type="text" name="timezone" value="{{ old('timezone', $user->timezone ?? config('app.timezone', 'UTC')) }}" placeholder="Asia/Dhaka, America/New_York..." class="crm-input">
            </div>

            <hr class="border-slate-100">

            <div>
                <h3 class="text-sm font-semibold text-slate-900">Change Password</h3>
                <p class="text-xs text-slate-500">Leave blank if you do not wish to change your password.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Current Password</label>
                <input type="password" name="current_password" class="crm-input">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">New Password</label>
                    <input type="password" name="password" class="crm-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="crm-input">
                </div>
            </div>

            <div class="pt-3 flex justify-end">
                <button type="submit" class="crm-btn-primary">Update Profile Preferences</button>
            </div>
        </form>
    </div>
</x-layouts.app>
