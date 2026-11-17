<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ImportExportModule;
use App\Enums\ImportExportStatus;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\ImportExportJob;
use App\Models\Lead;
use App\Services\Audit\AuditLoggerService;
use App\Support\CsvSanitizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

final class GenerateExportFileJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $importExportJobId,
    ) {}

    public function handle(AuditLoggerService $auditLogger): void
    {
        $jobRecord = ImportExportJob::query()->with('user')->findOrFail($this->importExportJobId);
        $user = $jobRecord->user;

        if ($user === null) {
            $jobRecord->update(['status' => ImportExportStatus::Failed]);

            return;
        }

        $jobRecord->update([
            'status' => ImportExportStatus::Processing,
            'started_at' => now(),
        ]);

        $stream = fopen('php://temp', 'r+b');
        if ($stream === false) {
            $jobRecord->update(['status' => ImportExportStatus::Failed]);

            return;
        }

        $count = 0;

        if ($jobRecord->module === ImportExportModule::Leads) {
            fputcsv($stream, ['ID', 'First Name', 'Last Name', 'Company', 'Email', 'Phone', 'Source', 'Status', 'Score']);
            Lead::query()->visibleTo($user)->orderBy('id')->chunk(500, function ($leads) use ($stream, &$count): void {
                foreach ($leads as $lead) {
                    fputcsv($stream, [
                        (string) $lead->id,
                        CsvSanitizer::sanitizeCell($lead->first_name),
                        CsvSanitizer::sanitizeCell($lead->last_name),
                        CsvSanitizer::sanitizeCell($lead->company_name),
                        CsvSanitizer::sanitizeCell($lead->email),
                        CsvSanitizer::sanitizeCell($lead->phone),
                        CsvSanitizer::sanitizeCell($lead->source?->value),
                        CsvSanitizer::sanitizeCell($lead->status?->value),
                        (string) $lead->score,
                    ]);
                    $count++;
                }
            });
        } elseif ($jobRecord->module === ImportExportModule::Contacts) {
            fputcsv($stream, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Job Title']);
            Contact::query()->visibleTo($user)->orderBy('id')->chunk(500, function ($contacts) use ($stream, &$count): void {
                foreach ($contacts as $contact) {
                    fputcsv($stream, [
                        (string) $contact->id,
                        CsvSanitizer::sanitizeCell($contact->first_name),
                        CsvSanitizer::sanitizeCell($contact->last_name),
                        CsvSanitizer::sanitizeCell($contact->email),
                        CsvSanitizer::sanitizeCell($contact->phone),
                        CsvSanitizer::sanitizeCell($contact->job_title),
                    ]);
                    $count++;
                }
            });
        } elseif ($jobRecord->module === ImportExportModule::Companies) {
            fputcsv($stream, ['ID', 'Name', 'Industry', 'Phone', 'City', 'Country']);
            Company::query()->visibleTo($user)->orderBy('id')->chunk(500, function ($companies) use ($stream, &$count): void {
                foreach ($companies as $company) {
                    fputcsv($stream, [
                        (string) $company->id,
                        CsvSanitizer::sanitizeCell($company->name),
                        CsvSanitizer::sanitizeCell($company->industry),
                        CsvSanitizer::sanitizeCell($company->phone),
                        CsvSanitizer::sanitizeCell($company->city),
                        CsvSanitizer::sanitizeCell($company->country),
                    ]);
                    $count++;
                }
            });
        } else {
            fputcsv($stream, ['ID', 'Title', 'Value', 'Currency', 'Probability', 'Status']);
            Deal::query()->visibleTo($user)->orderBy('id')->chunk(500, function ($deals) use ($stream, &$count): void {
                foreach ($deals as $deal) {
                    fputcsv($stream, [
                        (string) $deal->id,
                        CsvSanitizer::sanitizeCell($deal->title),
                        (string) $deal->value,
                        CsvSanitizer::sanitizeCell($deal->currency),
                        (string) $deal->probability,
                        CsvSanitizer::sanitizeCell($deal->status?->value),
                    ]);
                    $count++;
                }
            });
        }

        rewind($stream);
        $path = "exports/{$jobRecord->module->value}-export-{$jobRecord->id}.csv";
        Storage::disk($jobRecord->file_disk)->put($path, stream_get_contents($stream) ?: '');
        fclose($stream);

        $jobRecord->update([
            'status' => ImportExportStatus::Completed,
            'result_file_path' => $path,
            'total_rows' => $count,
            'processed_rows' => $count,
            'completed_at' => now(),
        ]);

        $auditLogger->log(
            event: 'exported',
            auditable: $jobRecord,
            actor: $user,
            newValues: ['module' => $jobRecord->module->value, 'rows' => $count],
            teamId: $jobRecord->team_id,
        );
    }
}
