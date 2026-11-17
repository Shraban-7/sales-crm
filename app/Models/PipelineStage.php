<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipelineStage extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pipeline_id',
        'name',
        'slug',
        'position',
        'default_probability',
        'rotting_days',
        'is_won_stage',
        'is_lost_stage',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'default_probability' => 'decimal:2',
            'rotting_days' => 'integer',
            'is_won_stage' => 'boolean',
            'is_lost_stage' => 'boolean',
        ];
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'pipeline_stage_id');
    }
}
