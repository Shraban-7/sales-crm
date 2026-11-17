<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImportExportModule;
use App\Enums\ImportExportStatus;
use App\Enums\ImportExportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportExportJob extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'type',
        'module',
        'status',
        'file_disk',
        'source_file_path',
        'result_file_path',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'error_summary',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ImportExportType::class,
            'module' => ImportExportModule::class,
            'status' => ImportExportStatus::class,
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'failed_rows' => 'integer',
            'error_summary' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
