@extends('errors.layout')

@section('code', '419')
@section('label', 'Session Expired')
@section('tone', 'amber')
@section('title', 'Page session expired')
@section('message', 'Your security token expired before the form was submitted. Refresh the page and try submitting your action again.')
@section('tip', 'For security, CRM forms expire after prolonged inactivity.')

@section('icon')
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
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
