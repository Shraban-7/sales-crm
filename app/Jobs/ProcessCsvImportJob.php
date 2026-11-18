<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ImportExportModule;
use App\Enums\ImportExportStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ImportExportJob;
use App\Models\Lead;
use App\Models\Team;
use App\Notifications\CrmAlertNotification;
use App\Services\Leads\LeadDuplicateService;
use App\Support\CsvSanitizer;
use App\Support\PhoneNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

final class ProcessCsvImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $importExportJobId,
        public readonly string $duplicateStrategy = 'skip',
    ) {}

    public function handle(): void
    {
        $jobRecord = ImportExportJob::query()->with('user')->findOrFail($this->importExportJobId);

        $jobRecord->update([
            'status' => ImportExportStatus::Processing,
            'started_at' => now(),
        ]);

        $disk = Storage::disk($jobRecord->file_disk);
        $fullPath = $disk->path((string) $jobRecord->source_file_path);

        if (! is_file($fullPath)) {
            $jobRecord->update([
                'status' => ImportExportStatus::Failed,
                'error_summary' => ['Source CSV file could not be located.'],
                'completed_at' => now(),
            ]);

            return;
        }

        $handle = fopen($fullPath, 'rb');
        if ($handle === false) {
            $jobRecord->update([
                'status' => ImportExportStatus::Failed,
                'error_summary' => ['Unable to open source CSV file.'],
                'completed_at' => now(),
            ]);

            return;
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            $jobRecord->update([
                'status' => ImportExportStatus::Failed,
                'error_summary' => ['CSV file is empty or missing headers.'],
                'completed_at' => now(),
            ]);

            return;
        }

        $headers = array_map(
            fn ($h): string => mb_strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))),
            $headers
        );

        $requiredHeader = $jobRecord->module === ImportExportModule::Companies ? 'name' : 'first_name';
        if (! in_array($requiredHeader, $headers, true)) {
            fclose($handle);
            $jobRecord->update([
                'status' => ImportExportStatus::Failed,
                'error_summary' => ["Missing required '{$requiredHeader}' column in CSV headers."],
                'completed_at' => now(),
            ]);

            return;
        }

        $totalRows = 0;
        $processedRows = 0;
        $failedRows = 0;
        $errorRows = [];
        $lineNumber = 1;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                if ($row === [null] || $row === []) {
                    continue;
                }

                $totalRows++;
                $assoc = [];
                foreach ($headers as $idx => $key) {
                    // Inbound data is validated/normalised per field. Export-only
                    // formula escaping must never be applied to stored values.
                    $assoc[$key] = trim((string) ($row[$idx] ?? ''));
                }

                $error = $this->processRow($jobRecord, $assoc);
                if ($error !== null) {
                    $failedRows++;
                    $errorRows[] = ['line' => $lineNumber, 'error' => $error, 'data' => $assoc];
                } else {
                    $processedRows++;
                }
            }
        } catch (\Throwable $e) {
            fclose($handle);
            $jobRecord->update([
                'status' => ImportExportStatus::Failed,
                'total_rows' => $totalRows,
                'processed_rows' => $processedRows,
                'failed_rows' => $failedRows,
                'error_summary' => ['Import aborted: '.$e->getMessage()],
                'completed_at' => now(),
            ]);

            throw $e;
        }

        fclose($handle);

        $resultFilePath = null;
        if ($errorRows !== []) {
            $resultFilePath = "exports/import-errors-{$jobRecord->id}.csv";
            $csvStream = fopen('php://temp', 'r+b');
            if ($csvStream !== false) {
                fputcsv($csvStream, ['line', 'error', 'raw_json']);
                foreach ($errorRows as $err) {
                    fputcsv($csvStream, [
                        (string) $err['line'],
                        CsvSanitizer::sanitizeCell($err['error']),
                        json_encode($err['data'], JSON_UNESCAPED_UNICODE),
                    ]);
                }
                rewind($csvStream);
                $disk->put($resultFilePath, stream_get_contents($csvStream) ?: '');
                fclose($csvStream);
            }
        }

        $jobRecord->update([
            'status' => ImportExportStatus::Completed,
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'failed_rows' => $failedRows,
            'result_file_path' => $resultFilePath,
            'error_summary' => $failedRows > 0 ? array_slice(array_column($errorRows, 'error'), 0, 20) : null,
            'completed_at' => now(),
        ]);

        $jobRecord->user?->notify(new CrmAlertNotification(
            category: 'import_completed',
            title: 'CSV Import Completed',
            message: "Imported {$processedRows} of {$totalRows} rows ({$failedRows} failed).",
            actionUrl: route('imports.index'),
        ));
    }

    /**
     * @param  array<string, string>  $row
     */
    private function processRow(ImportExportJob $job, array $row): ?string
    {
        return match ($job->module) {
            ImportExportModule::Leads => $this->importLead($job, $row),
            ImportExportModule::Contacts => $this->importContact($job, $row),
            ImportExportModule::Companies => $this->importCompany($job, $row),
            default => 'Unsupported module for CSV import.',
        };
    }

    /**
     * @param  array<string, string>  $row
     */
    private function importLead(ImportExportJob $job, array $row): ?string
    {
        $email = $this->normalizeEmail($row['email'] ?? null);
        $phone = PhoneNumber::normalize($row['phone'] ?? null);

        $row['email'] = $email ?? '';
        $row['phone'] = $phone ?? '';

        $validator = Validator::make($row, [
            'first_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email:rfc', 'max:191', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
        ]);

        if ($validator->fails()) {
            return implode('; ', $validator->errors()->all());
        }

        $team = Team::query()->findOrFail($job->team_id);

        $existing = app(LeadDuplicateService::class)->find($team, $email, $phone);

        if ($existing !== null) {
            if ($this->duplicateStrategy === 'skip') {
                return 'Duplicate lead skipped.';
            }

            $existing->update([
                'first_name' => $row['first_name'],
                'last_name' => ($row['last_name'] ?? '') ?: $existing->last_name,
                'company_name' => ($row['company_name'] ?? '') ?: $existing->company_name,
                'job_title' => ($row['job_title'] ?? '') ?: $existing->job_title,
                'email' => $email ?? $existing->email,
                'phone' => $phone ?? $existing->phone,
            ]);

            return null;
        }

        Lead::query()->create([
            'team_id' => $job->team_id,
            'owner_id' => $job->user_id,
            'first_name' => $row['first_name'],
            'last_name' => ($row['last_name'] ?? '') ?: null,
            'company_name' => ($row['company_name'] ?? '') ?: null,
            'job_title' => ($row['job_title'] ?? '') ?: null,
            'email' => $email,
            'phone' => $phone,
            'source' => LeadSource::Import,
            'status' => LeadStatus::New,
            'score' => 20,
        ]);

        return null;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function importContact(ImportExportJob $job, array $row): ?string
    {
        $email = $this->normalizeEmail($row['email'] ?? null);
        $phone = PhoneNumber::normalize($row['phone'] ?? null);

        $row['email'] = $email ?? '';
        $row['phone'] = $phone ?? '';

        $validator = Validator::make($row, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        if ($validator->fails()) {
            return implode('; ', $validator->errors()->all());
        }

        $existing = null;

        if ($email !== null || $phone !== null) {
            $comparablePhone = PhoneNumber::comparable($phone);

            $existing = Contact::query()
                ->where('team_id', $job->team_id)
                ->where(function ($sub) use ($email, $comparablePhone): void {
                    if ($email !== null) {
                        $sub->whereRaw('LOWER(email) = ?', [$email]);
                    }

                    if ($comparablePhone !== null) {
                        $method = $email !== null ? 'orWhereRaw' : 'whereRaw';
                        $sub->{$method}(
                            "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ?",
                            [$comparablePhone],
                        );
                    }
                })
                ->orderBy('id')
                ->first();
        }

        if ($existing !== null) {
            if ($this->duplicateStrategy === 'skip') {
                return 'Duplicate contact skipped.';
            }

            $existing->update([
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $email ?? $existing->email,
                'phone' => $phone ?? $existing->phone,
                'job_title' => ($row['job_title'] ?? '') ?: $existing->job_title,
            ]);

            return null;
        }

        Contact::query()->create([
            'team_id' => $job->team_id,
            'owner_id' => $job->user_id,
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $email,
            'phone' => $phone,
            'job_title' => ($row['job_title'] ?? '') ?: null,
        ]);

        return null;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function importCompany(ImportExportJob $job, array $row): ?string
    {
        $website = $this->normalizeWebsite($row['website'] ?? null);
        $phone = PhoneNumber::normalize($row['phone'] ?? null);

        $row['phone'] = $phone ?? '';

        $validator = Validator::make($row, [
            'name' => ['required', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        if ($validator->fails()) {
            return implode('; ', $validator->errors()->all());
        }

        $name = trim($row['name']);

        $existing = Company::query()
            ->where('team_id', $job->team_id)
            ->where(function ($query) use ($name, $website): void {
                $query->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);

                if ($website !== null) {
                    $query->orWhereRaw('LOWER(website) = ?', [$website]);
                }
            })
            ->orderBy('id')
            ->first();

        if ($existing !== null) {
            if ($this->duplicateStrategy === 'skip') {
                return 'Duplicate company skipped.';
            }

            $existing->update([
                'name' => $name,
                'industry' => ($row['industry'] ?? '') ?: $existing->industry,
                'phone' => $phone ?? $existing->phone,
                'website' => $website ?? $existing->website,
                'city' => ($row['city'] ?? '') ?: $existing->city,
                'country' => ($row['country'] ?? '') ?: $existing->country,
            ]);

            return null;
        }

        Company::query()->create([
            'team_id' => $job->team_id,
            'owner_id' => $job->user_id,
            'name' => $name,
            'industry' => ($row['industry'] ?? '') ?: null,
            'phone' => $phone,
            'website' => $website,
            'city' => ($row['city'] ?? '') ?: null,
            'country' => ($row['country'] ?? '') ?: config('crm.country', 'BD'),
        ]);

        return null;
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = $email !== null ? mb_strtolower(trim($email)) : null;

        return $email !== '' ? $email : null;
    }

    private function normalizeWebsite(?string $website): ?string
    {
        $website = $website !== null ? trim($website) : null;

        if ($website === null || $website === '') {
            return null;
        }

        $url = str_contains($website, '://') ? $website : 'https://'.$website;
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? mb_strtolower($host) : mb_strtolower($website);
    }
}
