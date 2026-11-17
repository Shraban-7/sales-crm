<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
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
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'job_title',
        'department',
        'is_primary_decision_maker',
        'do_not_contact',
        'lead_source',
    ];

    protected function casts(): array
    {
        return [
            'is_primary_decision_maker' => 'boolean',
            'do_not_contact' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function deals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'deal_contact')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
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
