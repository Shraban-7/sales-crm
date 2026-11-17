<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
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
        'parent_company_id',
        'name',
        'registration_number',
        'industry',
        'company_size',
        'annual_revenue',
        'website',
        'phone',
        'billing_address',
        'shipping_address',
        'city',
        'country',
    ];

    protected function casts(): array
    {
        return [
            'annual_revenue' => 'decimal:2',
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

    public function parentCompany(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_company_id');
    }

    public function subsidiaries(): HasMany
    {
        return $this->hasMany(self::class, 'parent_company_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
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
