<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'email',
        'phone',
        'job_title',
        'password',
        'is_active',
        'two_factor_enabled',
        'two_factor_code_hash',
        'two_factor_expires_at',
        'two_factor_last_sent_at',
        'last_login_at',
        'last_login_ip',
        'notification_preferences',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'two_factor_last_sent_at' => 'datetime',
            'last_login_at' => 'datetime',
            'notification_preferences' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'owner_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'owner_id');
    }

    public function wantsNotificationChannel(string $category, string $channel): bool
    {
        $prefs = $this->notification_preferences ?? [];

        return (bool) ($prefs[$category][$channel] ?? true);
    }
}
