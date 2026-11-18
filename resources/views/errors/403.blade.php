@extends('errors.layout')

@php
    $reason = isset($exception) && $exception->getMessage() !== ''
        ? $exception->getMessage()
        : 'You do not have permission to view or modify this record.';
@endphp

@section('code', '403')
@section('label', 'Access Denied')
@section('tone', 'rose')
@section('title', 'Access restricted')
@section('message', $reason)
@section('tip', 'Sales CRM isolates records by role and team ownership, and signed quotation links expire automatically. Contact your Sales Manager or Org Admin if you need access.')

@section('icon')
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
    </svg>
@endsection

@section('secondary_action')
    <button
        type="button"
        onclick="window.history.back()"
        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
    >
        Go Back
    </button>
@endsection
