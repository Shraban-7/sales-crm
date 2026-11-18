@extends('errors.layout')

@section('code', '404')
@section('label', 'Not Found')
@section('tone', 'brand')
@section('title', 'Page or record not found')
@section('message', 'The page, lead, deal, or quotation you requested could not be found—it may have been moved, archived, or deleted.')
@section('tip', 'Check the URL spelling or return to your workspace dashboard to search for the record.')

@section('icon')
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.15 6.15a7.5 7.5 0 0 0 10.5 10.5Z" />
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
