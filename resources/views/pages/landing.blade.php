<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sale Fusion CRM') }} — Enterprise Sales CRM &amp; Pipeline Engine</title>
    <meta name="description" content="Production-ready, self-hostable B2B Sales CRM. Drag-and-drop Kanban pipelines, automated follow-up SLAs, smart versioned proposals, and offline mobile PWA event lead capture.">
    <x-crm.favicons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-950 text-slate-100 font-sans selection:bg-brand-500 selection:text-white overflow-x-hidden">

    {{-- Ambient Background Glows --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-[38rem] h-[38rem] rounded-full bg-brand-600/15 blur-[120px]"></div>
        <div class="absolute top-1/4 -right-40 w-[42rem] h-[42rem] rounded-full bg-indigo-600/15 blur-[140px]"></div>
        <div class="absolute top-2/3 left-1/3 w-[36rem] h-[36rem] rounded-full bg-emerald-600/10 blur-[130px]"></div>
        <div class="absolute inset-0 opacity-[0.035]"
             style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 48px 48px;"></div>
    </div>

    {{-- Main Container --}}
    <div class="relative z-10 flex flex-col min-h-screen">

        {{-- Header / Navigation Bar --}}
        <header class="sticky top-0 z-50 backdrop-blur-md bg-slate-950/80 border-b border-white/10 transition">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                {{-- Brand Identity --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 via-brand-600 to-indigo-600 text-white shadow-lg shadow-brand-500/25 ring-1 ring-white/20 group-hover:scale-105 transition">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold tracking-tight text-white">{{ config('app.name', 'Sale Fusion CRM') }}</span>
                            <span class="rounded-full bg-brand-500/20 px-2 py-0.5 text-[10px] font-semibold text-brand-300 ring-1 ring-brand-500/30">v1.0.1</span>
                        </div>
                        <p class="text-xs text-slate-400">Enterprise Revenue Engine</p>
                    </div>
                </a>

                {{-- Desktop Navigation Links --}}
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                    <a href="#features" class="hover:text-white transition">Features</a>
                    <a href="#pipeline-demo" class="hover:text-white transition">Kanban &amp; Deals</a>
                    <a href="#mobile-pwa" class="hover:text-white transition">Mobile PWA</a>
                    <a href="#demo-accounts" class="hover:text-white transition">Live Demo Roles</a>
                    <a href="#architecture" class="hover:text-white transition">Architecture</a>
                </nav>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('leads.capture.show') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-300 hover:text-white transition">
                        Talk to Sales &rarr;
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-md shadow-brand-600/30 hover:from-brand-500 hover:to-indigo-500 transition">
                            <span>Open Dashboard</span>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center rounded-xl border border-white/15 bg-white/5 px-3.5 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10 hover:text-white transition">
                            Sign In
                        </a>
                        <a href="#demo-accounts" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-md shadow-brand-600/30 hover:from-brand-500 hover:to-indigo-500 transition">
                            <span>Explore Demo</span>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Hero Section --}}
        <section class="relative pt-12 pb-24 md:pt-20 md:pb-32 overflow-hidden">
            {{-- Radial spotlight beam --}}
            <div class="pointer-events-none absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[45rem] overflow-hidden -z-10">
                <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[55rem] h-[30rem] bg-gradient-to-b from-brand-500/25 via-indigo-600/15 to-transparent blur-[120px] rounded-full"></div>
                <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-brand-500/60 to-transparent"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8 sm:space-y-10">
                {{-- Release Pill --}}
                <div class="inline-flex items-center gap-2.5 rounded-full border border-brand-500/30 bg-brand-500/10 px-4 py-1.5 text-xs font-medium text-slate-200 backdrop-blur-md shadow-lg shadow-brand-500/10 hover:border-brand-500/50 transition">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-brand-300 font-semibold tracking-wide">Enterprise Release v1.0.1</span>
                    <span class="text-slate-500">&middot;</span>
                    <span class="text-slate-300">Self-Hostable B2B SaaS &middot; Zero Per-Seat Fees</span>
                </div>

                {{-- Headline --}}
                <div class="max-w-4xl mx-auto space-y-6">
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white leading-[1.1]">
                        Turn Inbound Leads into
                        <span class="relative inline-flex flex-col items-center justify-center mx-1 sm:mx-2 align-baseline">
                            <span id="hero-rotating-word" class="text-emerald-400 font-extrabold drop-shadow-[0_0_25px_rgba(52,211,153,0.45)] transition-all duration-300 transform inline-block">
                                Signed Revenue
                            </span>
                            <span class="h-1.5 sm:h-2 w-full bg-emerald-400 rounded-full shadow-lg shadow-emerald-400/50 animate-pulse mt-0.5"></span>
                        </span>
                        in One Workspace.
                    </h1>
                    <p class="max-w-2xl mx-auto text-base sm:text-lg lg:text-xl text-slate-200 leading-relaxed font-normal">
                        Ditch restrictive per-seat subscription models. Sale Fusion CRM gives your sales organization interactive Kanban deal pipelines, automated SLA follow-ups, versioned PDF proposals, and offline mobile business card capture.
                    </p>
                </div>

                {{-- Primary Action Buttons & Terminal Snippet --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-1">
                    <a href="#demo-accounts" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-brand-600 via-brand-500 to-indigo-600 px-7 py-3.5 text-sm sm:text-base font-semibold text-white shadow-xl shadow-brand-500/30 hover:shadow-brand-500/50 hover:scale-[1.02] transition">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                        </svg>
                        <span>Launch Live Demo (6 Roles)</span>
                    </a>

                    <a href="{{ route('leads.capture.show') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/[0.05] px-6 py-3.5 text-sm sm:text-base font-semibold text-slate-200 hover:bg-white/10 hover:text-white transition">
                        <span>Web-to-Lead Capture Form</span>
                        <span class="text-xs text-brand-300">&rarr;</span>
                    </a>
                </div>

                {{-- Fast 1-Click Terminal Command Pill --}}
                <div class="flex items-center justify-center pt-1">
                    <div class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-slate-900/80 px-3.5 py-1.5 text-xs font-mono text-slate-300 shadow-inner backdrop-blur-md">
                        <span class="text-brand-400 select-none">$</span>
                        <span class="select-all">php artisan db:seed --class=DemoCrmSeeder</span>
                        <span class="text-[10px] text-emerald-400 font-sans font-semibold bg-emerald-500/15 px-2 py-0.5 rounded-md border border-emerald-500/20">Seeded in 3s</span>
                    </div>
                </div>

                {{-- Fast 4-Pillar Stat Badges --}}
                <div class="pt-4 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-center backdrop-blur-xs hover:border-white/20 transition">
                        <p class="text-2xl sm:text-3xl font-extrabold text-white">14</p>
                        <p class="text-xs text-slate-400 mt-1 font-medium">Production CRM Modules</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-center backdrop-blur-xs hover:border-white/20 transition">
                        <p class="text-2xl sm:text-3xl font-extrabold text-emerald-400">$0</p>
                        <p class="text-xs text-slate-400 mt-1 font-medium">Recurring Per-Seat Fees</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-center backdrop-blur-xs hover:border-white/20 transition">
                        <p class="text-2xl sm:text-3xl font-extrabold text-brand-400">100%</p>
                        <p class="text-xs text-slate-400 mt-1 font-medium">Self-Hosted Data Ownership</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-center backdrop-blur-xs hover:border-white/20 transition">
                        <p class="text-2xl sm:text-3xl font-extrabold text-indigo-400">Offline</p>
                        <p class="text-xs text-slate-400 mt-1 font-medium">PWA Mobile Event Lead Capture</p>
                    </div>
                </div>

                {{-- Interactive Multi-Screen Showcase Window & Mobile Mockup --}}
                <div class="relative max-w-6xl mx-auto pt-8">
                    {{-- Interactive Screen Switcher Tabs (Top) --}}
                    <div class="flex items-center justify-center gap-2 pb-4 overflow-x-auto no-scrollbar">
                        <button
                            type="button"
                            data-hero-tab
                            data-hero-img="{{ asset('images/screenshots/10-pipelines-kanban.png') }}"
                            data-hero-url="demo.salefusioncrm.com/pipelines/kanban"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-brand-500/50 bg-white/15 text-xs font-semibold text-white shadow-xs transition"
                        >
                            <svg class="h-3.5 w-3.5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-13.5 18v-2.25Z" /></svg>
                            <span>Kanban Pipeline</span>
                        </button>

                        <button
                            type="button"
                            data-hero-tab
                            data-hero-img="{{ asset('images/screenshots/03-dashboard.png') }}"
                            data-hero-url="demo.salefusioncrm.com/dashboard"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-transparent text-xs font-semibold text-slate-400 hover:text-white hover:bg-white/5 transition"
                        >
                            <svg class="h-3.5 w-3.5 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                            <span>Executive Dashboard</span>
                        </button>

                        <button
                            type="button"
                            data-hero-tab
                            data-hero-img="{{ asset('images/screenshots/16-quotations-index.png') }}"
                            data-hero-url="demo.salefusioncrm.com/quotations"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-transparent text-xs font-semibold text-slate-400 hover:text-white hover:bg-white/5 transition"
                        >
                            <svg class="h-3.5 w-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                            <span>Smart Quotations</span>
                        </button>

                        <button
                            type="button"
                            data-hero-tab
                            data-hero-img="{{ asset('images/screenshots/21-reports.png') }}"
                            data-hero-url="demo.salefusioncrm.com/reports"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-transparent text-xs font-semibold text-slate-400 hover:text-white hover:bg-white/5 transition"
                        >
                            <svg class="h-3.5 w-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>
                            <span>Revenue Reports</span>
                        </button>
                    </div>

                    {{-- Ambient Backing Glow --}}
                    <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-brand-600/30 via-indigo-600/30 to-emerald-600/25 blur-3xl opacity-80 pointer-events-none"></div>

                    {{-- Main Desktop Browser Frame --}}
                    <div class="relative rounded-2xl sm:rounded-3xl border border-white/15 bg-slate-900/90 shadow-2xl backdrop-blur-xl overflow-hidden ring-1 ring-white/10 text-left">
                        {{-- Window Header Bar --}}
                        <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b border-white/10 bg-slate-950/70">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-rose-500/80 shadow-xs"></span>
                                <span class="h-3 w-3 rounded-full bg-amber-500/80 shadow-xs"></span>
                                <span class="h-3 w-3 rounded-full bg-emerald-500/80 shadow-xs"></span>
                            </div>

                            <div class="flex items-center gap-2 rounded-xl bg-white/[0.06] px-3.5 sm:px-6 py-1.5 text-xs text-slate-300 ring-1 ring-white/10 font-mono">
                                <svg class="h-3.5 w-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                <span class="text-slate-500 hidden sm:inline">https://</span>
                                <span id="hero-mockup-url" class="font-medium text-white">demo.salefusioncrm.com/pipelines/kanban</span>
                            </div>

                            <div class="flex items-center gap-2 text-xs text-slate-400 font-sans">
                                <span class="hidden sm:inline-flex items-center gap-1.5 text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full ring-1 ring-emerald-500/20 font-medium">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Live Connected
                                </span>
                            </div>
                        </div>

                        {{-- Screenshot Display Container --}}
                        <div class="relative aspect-16/10 sm:aspect-16/9 overflow-hidden bg-slate-950">
                            <img
                                id="hero-mockup-img"
                                src="{{ asset('images/screenshots/10-pipelines-kanban.png') }}"
                                alt="Sale Fusion CRM Drag-and-Drop Pipeline Kanban Board"
                                class="w-full h-full object-cover object-top transition duration-500"
                                loading="eager"
                            >

                            {{-- Floating Live Widget 1: Deal Won (Bottom Left) --}}
                            <div class="absolute bottom-6 left-6 hidden sm:flex items-center gap-3.5 rounded-2xl border border-white/15 bg-slate-900/95 p-4 shadow-2xl backdrop-blur-md ring-1 ring-white/10 transform hover:scale-105 transition">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/20">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                        <p class="text-xs font-semibold text-slate-200">Deal Closed Won!</p>
                                    </div>
                                    <p class="text-sm font-bold text-emerald-400">BDT 2,400,000.00</p>
                                    <p class="text-[11px] text-slate-400">Apex Textiles BD &middot; Nadia Karim</p>
                                </div>
                            </div>

                            {{-- Floating Live Widget 2: Manager Approval Gate (Top Right) --}}
                            <div class="absolute top-6 right-6 hidden sm:flex items-center gap-3 rounded-2xl border border-white/15 bg-slate-900/95 p-3.5 shadow-2xl backdrop-blur-md ring-1 ring-white/10 transform hover:scale-105 transition">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/20 text-amber-300">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs font-semibold text-white">Quotation Approval Required</p>
                                    <p class="text-[11px] text-amber-300">Discount &gt; 15% threshold &middot; Tariq Rahman</p>
                                </div>
                            </div>

                            {{-- Floating Overlapping Smartphone Mockup (Bottom Right of Showcase!) --}}
                            <div class="absolute -bottom-8 -right-4 hidden lg:block w-52 z-30 transform hover:-translate-y-2 transition duration-300">
                                <div class="relative rounded-[2rem] border-[6px] border-slate-800 bg-slate-950 p-1.5 shadow-2xl ring-2 ring-white/20">
                                    {{-- Speaker notch --}}
                                    <div class="absolute top-2.5 left-1/2 -translate-x-1/2 w-16 h-3 rounded-full bg-slate-900 flex items-center justify-center z-40">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-950"></span>
                                    </div>

                                    {{-- Mobile screen --}}
                                    <div class="rounded-[1.6rem] overflow-hidden aspect-9/18 bg-slate-900 relative">
                                        <img
                                            src="{{ asset('images/screenshots/28-mobile-dashboard.png') }}"
                                            alt="Sale Fusion CRM Mobile View"
                                            class="w-full h-full object-cover object-top"
                                            loading="eager"
                                        >

                                        {{-- Overlay badge --}}
                                        <div class="absolute bottom-2 inset-x-2 rounded-lg bg-slate-950/90 border border-white/10 p-1.5 backdrop-blur-md flex items-center justify-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span class="text-[9px] font-bold text-white uppercase tracking-wider">Mobile PWA</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 1-Click Demo Accounts / Marketplace Testing Suite --}}
        <section id="demo-accounts" class="py-20 bg-slate-900/60 border-y border-white/10 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-indigo-300 ring-1 ring-indigo-500/30">
                        Interactive Reviewer Credentials
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">
                        Test Drive Every Role in Seconds.
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Experience how permissions, metrics, and deal pipelines adapt dynamically depending on whether you sign in as an executive, sales manager, or quota-carrying rep.
                    </p>
                </div>

                {{-- Cards Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Role 1: Super Admin --}}
                    <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6 hover:border-brand-500/40 hover:bg-white/[0.06] transition flex flex-col justify-between space-y-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-500/15 px-2.5 py-0.5 text-xs font-semibold text-purple-300 ring-1 ring-purple-500/30">
                                    Global Super Admin
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Full Access</span>
                            </div>
                            <h3 class="text-lg font-bold text-white">Demo Super Administrator</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Full administrative control: manage users, create custom pipeline stages, configure SMTP, customize site settings, and inspect immutable audit logs.
                            </p>
                            <div class="rounded-xl bg-slate-950/60 border border-white/10 p-3 space-y-1 font-mono text-xs">
                                <p class="text-slate-400">Email: <span class="text-brand-300 select-all">admin@demo.yourproduct.com</span></p>
                                <p class="text-slate-400">Password: <span class="text-slate-200">password</span> <span class="text-[10px] text-slate-500">(or Demo!2026)</span></p>
                            </div>
                        </div>
                        <a href="{{ route('login') }}?email=admin@demo.yourproduct.com" class="inline-flex items-center justify-center gap-2 rounded-xl bg-purple-600 hover:bg-purple-500 py-2.5 text-xs font-semibold text-white shadow transition">
                            <span>Sign In as Super Admin &rarr;</span>
                        </a>
                    </div>

                    {{-- Role 2: Org Admin --}}
                    <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6 hover:border-brand-500/40 hover:bg-white/[0.06] transition flex flex-col justify-between space-y-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/15 px-2.5 py-0.5 text-xs font-semibold text-blue-300 ring-1 ring-blue-500/30">
                                    VP Revenue Operations
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Cross-Team</span>
                            </div>
                            <h3 class="text-lg font-bold text-white">Org Admin</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Cross-team executive view: evaluate sales velocity, bulk import and export CSV records, review user performance, and reassign records atomically.
                            </p>
                            <div class="rounded-xl bg-slate-950/60 border border-white/10 p-3 space-y-1 font-mono text-xs">
                                <p class="text-slate-400">Email: <span class="text-brand-300 select-all">admin@salescrm.test</span></p>
                                <p class="text-slate-400">Password: <span class="text-slate-200">password</span></p>
                            </div>
                        </div>
                        <a href="{{ route('login') }}?email=admin@salescrm.test" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-500 py-2.5 text-xs font-semibold text-white shadow transition">
                            <span>Sign In as Org Admin &rarr;</span>
                        </a>
                    </div>

                    {{-- Role 3: Sales Manager --}}
                    <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6 hover:border-brand-500/40 hover:bg-white/[0.06] transition flex flex-col justify-between space-y-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 px-2.5 py-0.5 text-xs font-semibold text-amber-300 ring-1 ring-amber-500/30">
                                    Enterprise Sales Manager
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Team Lead</span>
                            </div>
                            <h3 class="text-lg font-bold text-white">Tariq Rahman</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Team leader view: track Enterprise Sales team deals, review overdue follow-up escalations, and approve or reject quotation discounts &gt; 15%.
                            </p>
                            <div class="rounded-xl bg-slate-950/60 border border-white/10 p-3 space-y-1 font-mono text-xs">
                                <p class="text-slate-400">Email: <span class="text-brand-300 select-all">manager@salescrm.test</span></p>
                                <p class="text-slate-400">Password: <span class="text-slate-200">password</span></p>
                            </div>
                        </div>
                        <a href="{{ route('login') }}?email=manager@salescrm.test" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 hover:bg-amber-500 py-2.5 text-xs font-semibold text-white shadow transition">
                            <span>Sign In as Sales Manager &rarr;</span>
                        </a>
                    </div>

                    {{-- Role 4: Sales Executive --}}
                    <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6 hover:border-brand-500/40 hover:bg-white/[0.06] transition flex flex-col justify-between space-y-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs font-semibold text-emerald-300 ring-1 ring-emerald-400/30">
                                    Account Executive
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Quota Rep</span>
                            </div>
                            <h3 class="text-lg font-bold text-white">Nadia Karim</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Front-line quota carrier: convert inbound leads into opportunities, drag cards across pipeline stages, draft quotations, and log calls.
                            </p>
                            <div class="rounded-xl bg-slate-950/60 border border-white/10 p-3 space-y-1 font-mono text-xs">
                                <p class="text-slate-400">Email: <span class="text-brand-300 select-all">rep@salescrm.test</span></p>
                                <p class="text-slate-400">Password: <span class="text-slate-200">password</span></p>
                            </div>
                        </div>
                        <a href="{{ route('login') }}?email=rep@salescrm.test" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 py-2.5 text-xs font-semibold text-white shadow transition">
                            <span>Sign In as Sales Rep &rarr;</span>
                        </a>
                    </div>

                    {{-- Role 5: SMB Inbound Rep --}}
                    <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6 hover:border-brand-500/40 hover:bg-white/[0.06] transition flex flex-col justify-between space-y-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-500/15 px-2.5 py-0.5 text-xs font-semibold text-cyan-300 ring-1 ring-cyan-500/30">
                                    SMB Sales Rep
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Team Isolated</span>
                            </div>
                            <h3 class="text-lg font-bold text-white">Imran Hossain</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                SMB Inbound team rep: demonstrates row-level team security. Notice how Enterprise deals and leads are completely hidden from this view.
                            </p>
                            <div class="rounded-xl bg-slate-950/60 border border-white/10 p-3 space-y-1 font-mono text-xs">
                                <p class="text-slate-400">Email: <span class="text-brand-300 select-all">smbrep@salescrm.test</span></p>
                                <p class="text-slate-400">Password: <span class="text-slate-200">password</span></p>
                            </div>
                        </div>
                        <a href="{{ route('login') }}?email=smbrep@salescrm.test" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 py-2.5 text-xs font-semibold text-white shadow transition">
                            <span>Sign In as SMB Rep &rarr;</span>
                        </a>
                    </div>

                    {{-- Role 6: Read-Only Analyst --}}
                    <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6 hover:border-brand-500/40 hover:bg-white/[0.06] transition flex flex-col justify-between space-y-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-500/15 px-2.5 py-0.5 text-xs font-semibold text-slate-300 ring-1 ring-slate-500/30">
                                    Read-Only Analyst
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Auditor</span>
                            </div>
                            <h3 class="text-lg font-bold text-white">Farhana Yasmin</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Revenue analyst: inspect high-level pipeline charts, win-loss conversion curves, and currency totals without permission to modify data.
                            </p>
                            <div class="rounded-xl bg-slate-950/60 border border-white/10 p-3 space-y-1 font-mono text-xs">
                                <p class="text-slate-400">Email: <span class="text-brand-300 select-all">analyst@salescrm.test</span></p>
                                <p class="text-slate-400">Password: <span class="text-slate-200">password</span></p>
                            </div>
                        </div>
                        <a href="{{ route('login') }}?email=analyst@salescrm.test" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-700 hover:bg-slate-600 py-2.5 text-xs font-semibold text-white shadow transition">
                            <span>Sign In as Analyst &rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Mobile View & Offline PWA Section --}}
        <section id="mobile-pwa" class="py-20 md:py-28 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    {{-- Text Info (Left) --}}
                    <div class="lg:col-span-7 space-y-8">
                        <div class="space-y-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-300 ring-1 ring-emerald-400/30">
                                Progressive Web App Included
                            </span>
                            <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-[1.15]">
                                Capture Leads on the Move.
                                <span class="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">Even Offline.</span>
                            </h2>
                            <p class="text-base sm:text-lg text-slate-300/90 leading-relaxed">
                                Whether your team is attending trade shows, exhibitions, or client premises with spotty cellular coverage, the installable PWA keeps reps productive with offline-first capabilities.
                            </p>
                        </div>

                        {{-- Mobile Highlights List --}}
                        <div class="space-y-4">
                            <div class="flex items-start gap-4 p-4 rounded-2xl border border-white/10 bg-white/[0.03]">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Camera Business Card Capture</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">Photograph prospect cards directly from mobile; cached offline and automatically uploaded once signal restores.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 p-4 rounded-2xl border border-white/10 bg-white/[0.03]">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-500/20 text-brand-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Installable PWA Manifest &amp; Service Worker</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">Native app-like feel with standalone display mode, custom app icons (192px/512px), and fast client-side caching.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 p-4 rounded-2xl border border-white/10 bg-white/[0.03]">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-400">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Conference &amp; Trade Show Event Tagging</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">Segregate event leads by expo name to calculate accurate cost-per-lead and ROI per exhibition booth.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Smartphone Mockup (Right) --}}
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative w-72 sm:w-80">
                            {{-- Phone Outer Glow --}}
                            <div class="absolute -inset-2 rounded-[3rem] bg-gradient-to-b from-emerald-500/30 to-brand-600/30 blur-xl opacity-75"></div>

                            {{-- Phone Body --}}
                            <div class="relative rounded-[2.75rem] border-[8px] border-slate-800 bg-slate-950 p-2 shadow-2xl ring-1 ring-white/20">
                                {{-- Speaker / Dynamic Notch --}}
                                <div class="absolute top-4 left-1/2 -translate-x-1/2 w-28 h-4 rounded-full bg-slate-900 flex items-center justify-center gap-1 z-20">
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-950"></span>
                                    <span class="h-1.5 w-10 rounded-full bg-slate-950"></span>
                                </div>

                                {{-- Screen Content with Actual Mobile Screenshot --}}
                                <div class="rounded-[2.25rem] overflow-hidden aspect-9/19 bg-slate-900 relative">
                                    <img
                                        src="{{ asset('images/screenshots/28-mobile-dashboard.png') }}"
                                        alt="Sale Fusion CRM Mobile Responsive PWA Dashboard"
                                        class="w-full h-full object-cover object-top"
                                        loading="lazy"
                                    >

                                    {{-- Overlay PWA Installed Indicator --}}
                                    <div class="absolute bottom-3 inset-x-3 rounded-xl bg-slate-950/90 border border-white/10 p-2.5 backdrop-blur-md flex items-center gap-2">
                                        <div class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></div>
                                        <p class="text-[10px] font-medium text-slate-300">PWA Active &middot; Offline Sync Ready</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Core Features Breakdown Grid --}}
        <section id="features" class="py-20 bg-slate-900/40 border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand-300 ring-1 ring-brand-500/30">
                        Enterprise Capabilities
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white">
                        Engineered for Closed Deals.
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Everything your sales team needs to operate with velocity—from first contact to signed agreements.
                    </p>
                </div>

                {{-- Feature 1: Quotations & Approval Gates --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-3xl border border-white/10 bg-white/[0.02] p-8 lg:p-12">
                    <div class="lg:col-span-6 space-y-5">
                        <div class="inline-flex items-center gap-2 rounded-xl bg-indigo-500/20 px-3 py-1 text-xs font-semibold text-indigo-300">
                            Smart Quotations &amp; Proposals
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-white">Dynamic Line-Item Calculator with Discount Approval Gates</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Draft professional quotes with taxes, custom line items, and validity periods. When a sales rep offers a discount exceeding your company threshold (e.g. 15%), an approval gate locks sending until an authorized Sales Manager approves.
                        </p>
                        <ul class="space-y-2.5 text-xs text-slate-300">
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>High-precision <code>DECIMAL(15,2)</code> server-side currency calculations</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>Export cleanly formatted PDF proposals or share signed web links (<code>/q/{ulid}</code>)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>1-click quote version cloning with auto-numbered revisions</span>
                            </li>
                        </ul>
                    </div>
                    <div class="lg:col-span-6 rounded-2xl border border-white/10 bg-slate-950 overflow-hidden shadow-xl">
                        <img src="{{ asset('images/screenshots/16-quotations-index.png') }}" alt="Sale Fusion CRM Quotations Table and Approval Gating" class="w-full h-auto object-cover hover:scale-[1.02] transition duration-500" loading="lazy">
                    </div>
                </div>

                {{-- Feature 2: Analytics & Multi-Currency Reports --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-3xl border border-white/10 bg-white/[0.02] p-8 lg:p-12">
                    <div class="lg:col-span-6 order-2 lg:order-1 rounded-2xl border border-white/10 bg-slate-950 overflow-hidden shadow-xl">
                        <img src="{{ asset('images/screenshots/21-reports.png') }}" alt="Sale Fusion CRM Revenue Forecasting and Multi-Currency Analytics" class="w-full h-auto object-cover hover:scale-[1.02] transition duration-500" loading="lazy">
                    </div>
                    <div class="lg:col-span-6 order-1 lg:order-2 space-y-5">
                        <div class="inline-flex items-center gap-2 rounded-xl bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-300">
                            Revenue Forecasting &amp; Analytics
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-white">Strict Multi-Currency Pipeline Analytics &amp; Leaderboards</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Keep multi-national pipelines mathematically honest. Sale Fusion CRM never mixes dissimilar currencies into synthetic totals—each currency (USD, EUR, GBP, BDT) maintains its own weighted forecast, win rate, and rep leaderboard.
                        </p>
                        <ul class="space-y-2.5 text-xs text-slate-300">
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>Weighted probability revenue forecasting based on stage maturity</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>Conversion funnel visualizing drop-offs between Prospecting and Closed Won</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>Rep leaderboard ranking wins, deal counts, and average velocity</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Feature 3: Follow-Up SLA & Urgent Attention --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-3xl border border-white/10 bg-white/[0.02] p-8 lg:p-12">
                    <div class="lg:col-span-6 space-y-5">
                        <div class="inline-flex items-center gap-2 rounded-xl bg-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-300">
                            Urgency &amp; Deal Velocity
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-white">Rotting Deal Warnings &amp; Automated Follow-Up SLAs</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Deals go cold when reps lose momentum. Automated rotting alerts highlight opportunities that haven't moved within configured day thresholds. Chained customer follow-ups ensure scheduled next touches are never forgotten.
                        </p>
                        <ul class="space-y-2.5 text-xs text-slate-300">
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>Color-coded rotting badges displayed directly on Kanban deal cards</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>Multi-channel follow-up logging across Call, WhatsApp, Email, and In-Person Meetings</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                <span>Dedicated Urgent Attention dashboard view flags overdue follow-ups to managers</span>
                            </li>
                        </ul>
                    </div>
                    <div class="lg:col-span-6 rounded-2xl border border-white/10 bg-slate-950 overflow-hidden shadow-xl">
                        <img src="{{ asset('images/screenshots/15-follow-ups.png') }}" alt="Sale Fusion CRM Follow-up Management and SLA cadences" class="w-full h-auto object-cover hover:scale-[1.02] transition duration-500" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

        {{-- Comparison Section: Self-Hosted vs Commercial SaaS --}}
        <section class="py-20 bg-slate-950 border-t border-white/10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-300 ring-1 ring-emerald-400/30">
                        Total Cost of Ownership
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">
                        Why Pay $100+ Per User Every Month?
                    </h2>
                    <p class="text-sm sm:text-base text-slate-400">
                        Compare self-hosting Sale Fusion CRM against traditional per-seat CRM subscriptions.
                    </p>
                </div>

                {{-- Comparison Table --}}
                <div class="rounded-3xl border border-white/10 bg-white/[0.02] overflow-hidden shadow-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-white/10 bg-white/[0.04] text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    <th class="p-5 sm:p-6">Feature / Capability</th>
                                    <th class="p-5 sm:p-6 text-brand-300 bg-brand-500/10">Sale Fusion CRM</th>
                                    <th class="p-5 sm:p-6 text-slate-400">Salesforce / HubSpot / Pipedrive</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-slate-300">
                                <tr>
                                    <td class="p-5 sm:p-6 font-semibold text-white">Per-Seat License Cost</td>
                                    <td class="p-5 sm:p-6 font-bold text-emerald-400 bg-brand-500/5">$0 &middot; Unlimited Users Forever</td>
                                    <td class="p-5 sm:p-6 text-rose-400">$50 &ndash; $300 / user / month</td>
                                </tr>
                                <tr>
                                    <td class="p-5 sm:p-6 font-semibold text-white">Data Privacy &amp; Hosting</td>
                                    <td class="p-5 sm:p-6 text-emerald-300 bg-brand-500/5">100% On-Premise or Private VPS</td>
                                    <td class="p-5 sm:p-6 text-slate-400">Vendor multi-tenant cloud lock-in</td>
                                </tr>
                                <tr>
                                    <td class="p-5 sm:p-6 font-semibold text-white">Complete Source Code</td>
                                    <td class="p-5 sm:p-6 text-emerald-300 bg-brand-500/5">Included (PHP 8.3 + Laravel 13)</td>
                                    <td class="p-5 sm:p-6 text-slate-400">No source access (Proprietary)</td>
                                </tr>
                                <tr>
                                    <td class="p-5 sm:p-6 font-semibold text-white">Offline Mobile Lead Capture</td>
                                    <td class="p-5 sm:p-6 text-emerald-300 bg-brand-500/5">Progressive Web App (PWA) Included</td>
                                    <td class="p-5 sm:p-6 text-slate-400">Expensive enterprise tier add-on</td>
                                </tr>
                                <tr>
                                    <td class="p-5 sm:p-6 font-semibold text-white">Custom Pipeline Stages &amp; Settings</td>
                                    <td class="p-5 sm:p-6 text-emerald-300 bg-brand-500/5">Full In-App Visual Customizer</td>
                                    <td class="p-5 sm:p-6 text-slate-400">Gated behind highest pricing tiers</td>
                                </tr>
                                <tr>
                                    <td class="p-5 sm:p-6 font-semibold text-white">Audit Logging &amp; Forensics</td>
                                    <td class="p-5 sm:p-6 text-emerald-300 bg-brand-500/5">Immutable JSON before/after state</td>
                                    <td class="p-5 sm:p-6 text-slate-400">Available only on Enterprise plans</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        {{-- Architecture & Engineering Standards --}}
        <section id="architecture" class="py-20 bg-slate-900/60 border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300 ring-1 ring-cyan-400/30">
                        Technical Architecture
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">
                        Built Clean. No Framework Bloat.
                    </h2>
                    <p class="text-sm sm:text-base text-slate-400">
                        Engineered according to strict Laravel enterprise patterns with 100% type safety and zero heavy JavaScript SPA dependencies.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="rounded-3xl border border-white/10 bg-white/[0.02] p-6 space-y-3">
                        <div class="h-10 w-10 rounded-xl bg-red-500/20 text-red-400 flex items-center justify-center font-bold text-sm">
                            PHP
                        </div>
                        <h4 class="text-base font-bold text-white">PHP 8.3+ &amp; Laravel 13</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Strict typing with <code>declare(strict_types=1);</code>, backed enums, domain-driven Actions, thin resource controllers, and FormRequest authorization.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/[0.02] p-6 space-y-3">
                        <div class="h-10 w-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-sm">
                            CSS
                        </div>
                        <h4 class="text-base font-bold text-white">Tailwind CSS v4 &amp; Vanilla JS</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            CSS-first <code>@theme</code> design tokens compiled via Vite. Lightning-fast page loads without heavy React/Vue runtime script overhead.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/[0.02] p-6 space-y-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-sm">
                            SQL
                        </div>
                        <h4 class="text-base font-bold text-white">MySQL 8.0+ Enterprise Schema</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            InnoDB engine with strict foreign key cascading/restriction, composite performance indexes, soft deletes, and DECIMAL monetary precision.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Final Call to Action Banner --}}
        <section class="py-20 relative overflow-hidden bg-gradient-to-b from-slate-950 via-slate-900 to-brand-950 border-t border-white/10">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8 relative z-10">
                <div class="space-y-4">
                    <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white">
                        Ready to Take Control of Your Sales Stack?
                    </h2>
                    <p class="max-w-xl mx-auto text-base text-slate-300">
                        Deploy Sale Fusion CRM on your servers in under 5 minutes with zero licensing restrictions.
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-4">
                    <a href="#demo-accounts" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-600 px-8 py-4 text-base font-semibold text-white shadow-xl shadow-brand-500/30 hover:scale-[1.02] transition">
                        <span>Test Drive Live Demo Now</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>

                    <a href="{{ route('leads.capture.show') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-6 py-4 text-base font-semibold text-slate-200 hover:bg-white/10 hover:text-white transition">
                        <span>Contact Enterprise Sales</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-slate-950 border-t border-white/10 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6 text-xs text-slate-400">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-brand-600 text-white font-bold">
                        S
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ config('app.name', 'Sale Fusion CRM') }}</p>
                        <p class="text-[11px] text-slate-500">&copy; {{ date('Y') }} All rights reserved. Self-Hosted CRM Suite.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6 text-slate-400">
                    <a href="#features" class="hover:text-white transition">Features</a>
                    <a href="#mobile-pwa" class="hover:text-white transition">Mobile PWA</a>
                    <a href="#demo-accounts" class="hover:text-white transition">Demo Credentials</a>
                    <a href="{{ route('leads.capture.show') }}" class="hover:text-white transition">Public Lead Form</a>
                    <a href="{{ route('login') }}" class="hover:text-white transition">Sign In</a>
                </div>
            </div>
        </footer>

    </div>

    <script>
        document.querySelectorAll('[data-hero-tab]').forEach(tab => {
            tab.addEventListener('click', () => {
                const src = tab.dataset.heroImg;
                const url = tab.dataset.heroUrl;
                const img = document.getElementById('hero-mockup-img');
                const urlElem = document.getElementById('hero-mockup-url');
                if (img && src) {
                    img.classList.add('opacity-40');
                    setTimeout(() => {
                        img.src = src;
                        img.classList.remove('opacity-40');
                    }, 120);
                }
                if (urlElem && url) {
                    urlElem.textContent = url;
                }
                document.querySelectorAll('[data-hero-tab]').forEach(t => {
                    t.classList.remove('border-brand-500/50', 'bg-white/15', 'text-white');
                    t.classList.add('border-transparent', 'text-slate-400');
                });
                tab.classList.add('border-brand-500/50', 'bg-white/15', 'text-white');
                tab.classList.remove('border-transparent', 'text-slate-400');
            });
        });

        // Hero Headline Dynamic Word Animation
        const rotatingWords = [
            'Signed Revenue',
            'Closed Deals',
            'Won Proposals',
            'Faster Pipeline',
        ];
        let wordIndex = 0;
        const rotatingWordElem = document.getElementById('hero-rotating-word');

        if (rotatingWordElem) {
            setInterval(() => {
                wordIndex = (wordIndex + 1) % rotatingWords.length;
                rotatingWordElem.classList.add('opacity-0', '-translate-y-2');
                setTimeout(() => {
                    rotatingWordElem.textContent = rotatingWords[wordIndex];
                    rotatingWordElem.classList.remove('-translate-y-2');
                    rotatingWordElem.classList.add('translate-y-2');
                    setTimeout(() => {
                        rotatingWordElem.classList.remove('opacity-0', 'translate-y-2');
                    }, 50);
                }, 200);
            }, 3000);
        }
    </script>
</body>
</html>
