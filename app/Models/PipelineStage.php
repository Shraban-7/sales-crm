<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
        'probability',
        'rotting_days',
        'is_won_stage',
        'is_won',
        'is_lost_stage',
        'is_lost',
        'color',
    ];

    public function setProbabilityAttribute($value): void
    {
        $this->attributes['default_probability'] = $value;
    }

    public function getProbabilityAttribute()
    {
        return $this->attributes['default_probability'] ?? null;
    }

    public function setIsWonAttribute($value): void
    {
        $this->attributes['is_won_stage'] = $value;
    }

    public function getIsWonAttribute()
    {
        return $this->attributes['is_won_stage'] ?? false;
    }

    public function setIsLostAttribute($value): void
    {
        $this->attributes['is_lost_stage'] = $value;
    }

    public function getIsLostAttribute()
    {
        return $this->attributes['is_lost_stage'] ?? false;
    }

    protected static function booted(): void
    {
        static::creating(function (self $stage): void {
            if (empty($stage->slug)) {
                $stage->slug = Str::slug($stage->name);
            }
            if ($stage->position === null) {
                $max = (int) static::where('pipeline_id', $stage->pipeline_id)->max('position');
                $stage->position = $max + 1;
            }
        });
    }

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
