<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuotationStatus;
use App\Models\Concerns\HasVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory;
    use HasVisibilityScope;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ulid',
        'team_id',
        'owner_id',
        'deal_id',
        'company_id',
        'contact_id',
        'quote_number',
        'version',
        'status',
        'currency',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'issue_date',
        'valid_until',
        'terms_and_conditions',
        'customer_notes',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'status' => QuotationStatus::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'issue_date' => 'date',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function isDraft(): bool
    {
        return $this->status === QuotationStatus::Draft;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('position');
    }
}
