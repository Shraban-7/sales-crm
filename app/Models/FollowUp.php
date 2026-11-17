<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FollowUpChannel;
use App\Enums\FollowUpStatus;
use App\Models\Concerns\HasVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
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
        'subject_type',
        'subject_id',
        'channel',
        'status',
        'scheduled_at',
        'completed_at',
        'objective',
        'outcome_notes',
        'next_follow_up_id',
    ];

    protected function casts(): array
    {
        return [
            'channel' => FollowUpChannel::class,
            'status' => FollowUpStatus::class,
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function nextFollowUp(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_follow_up_id');
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }
}
