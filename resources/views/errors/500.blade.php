@extends('errors.layout')

@section('code', '500')
@section('label', 'Server Error')
@section('tone', 'rose')
@section('title', 'Unexpected server error')
@section('message', 'Something unexpected interrupted your request. Your existing CRM records remain safe and unchanged.')
@section('tip', 'Try refreshing in a moment. If the issue persists, notify your system administrator with the time of this error.')

@section('icon')
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
    </svg>
@endsection

@section('secondary_action')
    <button
        type="button"
        onclick="window.location.reload()"
        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
    >
        Reload Page
    </button>
@endsection
