<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityDirection;
use App\Enums\ActivityType;
use App\Models\Concerns\HasVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory;
    use HasVisibilityScope;
    use SoftDeletes;

    protected string $visibilityOwnerColumn = 'user_id';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'subject_type',
        'subject_id',
        'type',
        'direction',
        'outcome',
        'summary',
        'body',
        'duration_minutes',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'direction' => ActivityDirection::class,
            'duration_minutes' => 'integer',
            'occurred_at' => 'datetime',
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

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
