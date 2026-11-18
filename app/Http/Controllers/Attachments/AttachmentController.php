<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attachments;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Services\Audit\AuditLoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AttachmentController extends Controller
{
    /**
     * @var list<string>
     */
    private const ALLOWED_TYPES = ['lead', 'deal', 'company', 'contact', 'activity'];

    public function store(Request $request, AuditLoggerService $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'attachable_type' => ['required', 'string', 'in:'.implode(',', self::ALLOWED_TYPES)],
            'attachable_id' => ['required', 'integer'],
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif,webp,txt,csv,zip',
            ],
        ]);

        $modelClass = Relation::getMorphedModel((string) $data['attachable_type']);

        if ($modelClass === null) {
            abort(422, 'Invalid attachment target.');
        }

        /** @var Model $subject */
        $subject = $modelClass::query()->findOrFail((int) $data['attachable_id']);
        $this->authorize('view', $subject);
        $this->authorize('update', $subject);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $storedName = Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');
        $path = $file->storeAs('attachments/'.$subject->getAttribute('team_id'), $storedName, 'private');

        $attachment = Attachment::query()->create([
            'team_id' => (int) $subject->getAttribute('team_id'),
            'uploaded_by_id' => $request->user()->id,
            'attachable_type' => $subject->getMorphClass(),
            'attachable_id' => $subject->getKey(),
            'original_name' => $file->getClientOriginalName(),
            'storage_disk' => 'private',
            'storage_path' => $path,
            'mime_type' => (string) $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'sha256_checksum' => (string) hash_file('sha256', $file->getRealPath()),
        ]);

        $auditLogger->log(
            event: 'attached',
            auditable: $attachment,
            actor: $request->user(),
            newValues: ['original_name' => $attachment->original_name, 'size_bytes' => $attachment->size_bytes],
            teamId: $attachment->team_id,
        );

        return back()->with('status', 'Attachment uploaded.');
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment);

        if (! Storage::disk($attachment->storage_disk)->exists($attachment->storage_path)) {
            abort(404);
        }

        return Storage::disk($attachment->storage_disk)->download($attachment->storage_path, $attachment->original_name);
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        if (Storage::disk($attachment->storage_disk)->exists($attachment->storage_path)) {
            Storage::disk($attachment->storage_disk)->delete($attachment->storage_path);
        }

        $attachment->delete();

        return back()->with('status', 'Attachment removed.');
    }
}
