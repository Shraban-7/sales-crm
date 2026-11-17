<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DealStatus;
use App\Models\Concerns\HasVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory;
    use HasVisibilityScope;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'owner_id',
        'pipeline_id',
        'pipeline_stage_id',
        'company_id',
        'primary_contact_id',
        'title',
        'value',
        'currency',
        'probability',
        'status',
        'expected_close_date',
        'closed_at',
        'won_notes',
        'lost_reason',
        'lost_competitor',
        'stage_changed_at',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'probability' => 'decimal:2',
            'status' => DealStatus::class,
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
            'stage_changed_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function getWeightedValueAttribute(): string
    {
        return number_format((float) $this->value * ((float) $this->probability / 100), 2, '.', '');
    }

    public function isRotting(): bool
    {
        if ($this->status !== DealStatus::Open) {
            return false;
        }

        $rottingDays = $this->stage?->rotting_days ?? 14;
        $reference = $this->last_activity_at ?? $this->stage_changed_at ?? $this->created_at;

        return $reference !== null && $reference->diffInDays(now()) >= $rottingDays;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function primaryContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'deal_contact')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(DealStageHistory::class)->orderByDesc('created_at');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'subject');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
