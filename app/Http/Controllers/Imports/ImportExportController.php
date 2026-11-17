<?php

declare(strict_types=1);

namespace App\Http\Controllers\Imports;

use App\Enums\ImportExportModule;
use App\Enums\ImportExportStatus;
use App\Enums\ImportExportType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\StoreExportRequest;
use App\Http\Requests\Imports\StoreImportRequest;
use App\Jobs\GenerateExportFileJob;
use App\Jobs\ProcessCsvImportJob;
use App\Models\ImportExportJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ImportExportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ImportExportJob::class);

        $user = $request->user();
        $jobs = ImportExportJob::query()
            ->with('user')
            ->when(
                ! $user->hasAnyRole(['Super Admin', 'Org Admin']),
                fn ($q) => $q->where('team_id', $user->team_id)->where('user_id', $user->id)
            )
            ->latest()
            ->paginate(20);

        return view('pages.imports.index', compact('jobs'));
    }

    public function storeImport(StoreImportRequest $request): RedirectResponse
    {
        $this->authorize('createImport', ImportExportJob::class);

        $user = $request->user();
        $path = $request->file('file')->store('imports', 'private');

        $jobRecord = ImportExportJob::query()->create([
            'team_id' => $user->team_id,
            'user_id' => $user->id,
            'type' => ImportExportType::Import,
            'module' => ImportExportModule::from($request->validated('module')),
            'status' => ImportExportStatus::Pending,
            'file_disk' => 'private',
            'source_file_path' => $path,
        ]);

        ProcessCsvImportJob::dispatch($jobRecord->id, $request->validated('duplicate_strategy'));

        return back()->with('status', 'CSV import queued.');
    }

    public function storeExport(StoreExportRequest $request): RedirectResponse
    {
        $this->authorize('createExport', ImportExportJob::class);

        $user = $request->user();
        $jobRecord = ImportExportJob::query()->create([
            'team_id' => $user->team_id,
            'user_id' => $user->id,
            'type' => ImportExportType::Export,
            'module' => ImportExportModule::from($request->validated('module')),
            'status' => ImportExportStatus::Pending,
            'file_disk' => 'private',
        ]);

        GenerateExportFileJob::dispatch($jobRecord->id);

        return back()->with('status', 'Export queued and ready for download.');
    }

    public function download(ImportExportJob $job): StreamedResponse
    {
        $this->authorize('view', $job);

        if (empty($job->result_file_path) || ! Storage::disk($job->file_disk)->exists($job->result_file_path)) {
            abort(404);
        }

        return Storage::disk($job->file_disk)->download($job->result_file_path);
    }
}
