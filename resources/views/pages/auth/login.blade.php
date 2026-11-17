<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Sales CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6">
<div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Sales CRM</h1>
        <p class="text-sm text-slate-500 mt-1">Sign in to your revenue workspace</p>
    </div>

    @if ($errors->any())
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" name="password" type="password" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
            <span>Remember me</span>
        </label>
        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Sign In</button>
    </form>
</div>
</body>
</html>
