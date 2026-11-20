<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ config('app.name', 'Sale Fusion CRM') }}</title>
    <x-crm.favicons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 text-slate-900">
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">
    {{-- Brand & product showcase panel (desktop) --}}
    <aside class="hidden lg:flex lg:col-span-6 xl:col-span-7 relative overflow-hidden bg-slate-950 text-white p-12 xl:p-16 flex-col justify-between">
        {{-- Ambient layered backdrop --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950"></div>
        <div class="pointer-events-none absolute -top-28 -left-24 h-[28rem] w-[28rem] rounded-full bg-brand-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 right-0 h-[26rem] w-[26rem] rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
             style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-indigo-600 text-white shadow-lg shadow-brand-500/25 ring-1 ring-white/20">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-base font-bold tracking-tight text-white">{{ config('app.name', 'Sale Fusion CRM') }}</p>
                    <p class="text-xs text-slate-400">Revenue Operations Platform</p>
                </div>
            </div>

            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-slate-200 ring-1 ring-white/15 backdrop-blur-xs">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                Enterprise Ready
            </span>
        </div>

        <div class="relative z-10 max-w-xl space-y-8 my-auto py-10">
            <div class="space-y-4">
                <h2 class="text-4xl xl:text-[2.65rem] font-bold tracking-tight leading-[1.12] text-white">
                    Turn qualified leads into signed revenue in one workspace.
                </h2>
                <p class="text-base text-slate-300/90 leading-relaxed max-w-lg">
                    Keep your entire revenue team aligned from first enquiry to closed agreement with Kanban deal tracking, automated follow-ups, and versioned customer proposals.
                </p>
            </div>

            {{-- Stylized workspace preview card --}}
            <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-5 backdrop-blur-md shadow-2xl space-y-4">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-200">Pipeline Stage Flow</span>
                    <span class="rounded-full bg-emerald-500/15 px-2.5 py-0.5 font-medium text-emerald-300 ring-1 ring-emerald-400/20">Lead &rarr; Quote &rarr; Won</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-white/10 bg-slate-900/60 p-3 space-y-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                            <span class="text-[11px] font-medium text-slate-300">Qualified</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full w-4/5 rounded-full bg-blue-400"></div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-slate-900/60 p-3 space-y-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                            <span class="text-[11px] font-medium text-slate-300">Proposal Sent</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full w-3/5 rounded-full bg-amber-400"></div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-slate-900/60 p-3 space-y-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            <span class="text-[11px] font-medium text-slate-300">Closed Won</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full w-11/12 rounded-full bg-emerald-400"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl bg-white/[0.04] border border-white/10 p-4 space-y-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500/20 text-brand-100">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h4.5v13.5h-4.5V5.25ZM9.75 5.25h4.5v8.25h-4.5V5.25ZM15.75 5.25h4.5v10.5h-4.5V5.25Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-white">Visual Pipelines</p>
                    <p class="text-xs text-slate-400 leading-relaxed">Drag-and-drop stages with rotting deal alerts &amp; full history.</p>
                </div>

                <div class="rounded-2xl bg-white/[0.04] border border-white/10 p-4 space-y-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-white">Smart Quotations</p>
                    <p class="text-xs text-slate-400 leading-relaxed">Approval gates, PDF export, and signed client links.</p>
                </div>

                <div class="rounded-2xl bg-white/[0.04] border border-white/10 p-4 space-y-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/20 text-indigo-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-white">Team Security</p>
                    <p class="text-xs text-slate-400 leading-relaxed">Scoped RBAC, optional email 2FA, and audit trail logging.</p>
                </div>
            </div>
        </div>

        <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 border-t border-white/10 pt-5">
            <a href="{{ route('home') }}" class="font-medium text-slate-300 hover:text-white transition">
                &larr; Back to Landing Page
            </a>
            <a href="{{ route('leads.capture.show') }}" class="font-medium text-brand-100 hover:text-white transition">
                Public Enquiry Form &rarr;
            </a>
        </div>
    </aside>

    {{-- Sign-in form panel --}}
    <main class="lg:col-span-6 xl:col-span-5 relative flex flex-col justify-center px-5 py-10 sm:px-10 lg:px-14 bg-gradient-to-b from-white via-slate-50 to-slate-100/80">
        <div class="mx-auto w-full max-w-md space-y-6">
            {{-- Mobile brand header --}}
            <div class="flex items-center justify-between lg:hidden">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-base font-bold tracking-tight text-slate-900">{{ config('app.name', 'Sale Fusion CRM') }}</p>
                        <p class="text-xs text-slate-500">Revenue Operations</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-200/60 p-8 sm:p-9 space-y-6">
                <div class="space-y-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-brand-700 ring-1 ring-inset ring-brand-200/70">
                        <svg class="h-3.5 w-3.5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        Workspace Access
                    </span>
                    <h1 class="text-2xl sm:text-[1.75rem] font-bold tracking-tight text-slate-900">Welcome back</h1>
                    <p class="text-sm text-slate-500">Sign in with your account to access leads, pipelines, and quotations.</p>
                </div>

                @if (session('status'))
                    <div role="status" class="rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2.5 text-sm text-emerald-800 flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div role="alert" class="rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-sm text-rose-700 flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-sm text-rose-700 flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" data-login-form class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Work Email</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                value="{{ old('email', request('email')) }}"
                                required
                                autofocus
                                placeholder="name@company.com"
                                class="crm-input py-2.5 pl-10 @error('email') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1.5">Password</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                value="{{ old('password', request('email') ? 'password' : '') }}"
                                required
                                placeholder="Enter your password"
                                class="crm-input py-2.5 pl-10 pr-10"
                            >
                            <button
                                type="button"
                                data-toggle-password
                                aria-label="Show password"
                                aria-pressed="false"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition"
                            >
                                <svg data-eye-open class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg data-eye-closed class="h-4 w-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span>Keep me signed in</span>
                        </label>
                        <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                            <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            2FA Ready
                        </span>
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-md shadow-brand-600/20 hover:from-brand-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-brand-600/30 transition cursor-pointer"
                    >
                        <span>Sign In to Workspace</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>

                <div class="border-t border-slate-100 pt-4 flex items-center justify-between text-xs text-slate-500">
                    <span>Protected by rate limiting &amp; audit logs</span>
                    <a href="{{ route('leads.capture.show') }}" class="font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                        Talk to Sales &rarr;
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
