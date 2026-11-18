@extends('errors.layout')

@section('code', '429')
@section('label', 'Rate Limit Reached')
@section('tone', 'amber')
@section('title', 'Too many requests')
@section('message', 'You have sent too many requests in a short period. Please wait a moment before trying again.')
@section('tip', 'Login attempts, lead capture forms, and public quotation responses are rate-limited to protect your workspace.')

@section('icon')
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>
@endsection

@section('secondary_action')
    <button
        type="button"
        onclick="window.location.reload()"
        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
    >
        Try Again
    </button>
@endsection
