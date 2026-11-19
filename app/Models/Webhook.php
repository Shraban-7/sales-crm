<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'created_by_id',
        'name',
        'url',
        'events',
        'secret',
        'is_active',
        'last_triggered_at',
        'last_status_code',
        'failure_count',
    ];

    protected $hidden = [
        'secret',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'secret' => 'encrypted',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
            'last_status_code' => 'integer',
            'failure_count' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('webhooks.manage')) {
            return $query;
        }

        return $query->where('id', '<', 0);
    }
}
