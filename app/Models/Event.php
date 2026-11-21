<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory;
    use HasVisibilityScope;
    use SoftDeletes;

    protected string $visibilityOwnerColumn = 'created_by_id';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'created_by_id',
        'name',
        'location',
        'start_date',
        'end_date',
        'budget',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
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

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
