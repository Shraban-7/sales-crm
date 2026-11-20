<?php

declare(strict_types=1);

namespace App\Http\Controllers\Imports;

use App\Enums\ImportExportModule;
use App\Enums\ImportExportStatus;
use App\Enums\ImportExportType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\StoreImportRequest;
use App\Jobs\ProcessCsvImportJob;
use App\Models\ImportExportJob;
use App\Services\Import\ImportFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class BulkImportController extends Controller
{
    public function index(): View
    {
        $this->authorize('createImport', ImportExportJob::class);

        $expectedHeaders = ImportFormat::expectedHeaders();

        return view('pages.imports.bulk', [
            'expectedHeaders' => $expectedHeaders,
        ]);
    }

    public function store(StoreImportRequest $request): RedirectResponse
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

        return redirect()->route('imports.index')->with('status', 'Bulk import queued successfully.');
    }
}
