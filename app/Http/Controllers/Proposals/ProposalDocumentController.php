<?php

declare(strict_types=1);

namespace App\Http\Controllers\Proposals;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ProposalDocumentController extends Controller
{
    public function store(Request $request, Proposal $proposal): RedirectResponse
    {
        $this->authorize('update', $proposal);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:15360', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt'],
        ]);

        $file = $request->file('file');
        $storedName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('proposals/'.$proposal->id, $storedName, 'private');

        ProposalDocument::query()->create([
            'proposal_id' => $proposal->id,
            'uploaded_by_id' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'storage_disk' => 'private',
            'storage_path' => $path,
            'mime_type' => (string) $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
        ]);

        return back()->with('status', 'Document uploaded to proposal.');
    }

    public function download(ProposalDocument $document): StreamedResponse
    {
        $this->authorize('view', $document->proposal);

        if (! Storage::disk($document->storage_disk)->exists($document->storage_path)) {
            abort(404, 'File not found in secure storage.');
        }

        return Storage::disk($document->storage_disk)->download($document->storage_path, $document->original_name);
    }

    public function destroy(ProposalDocument $document): RedirectResponse
    {
        $this->authorize('update', $document->proposal);

        if (Storage::disk($document->storage_disk)->exists($document->storage_path)) {
            Storage::disk($document->storage_disk)->delete($document->storage_path);
        }

        $document->delete();

        return back()->with('status', 'Document removed.');
    }
}
