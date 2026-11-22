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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Quotation extends Model
{
    use HasFactory;
    use HasVisibilityScope;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation): void {
            if (empty($quotation->ulid)) {
                $quotation->ulid = (string) Str::ulid();
            }
        });
    }

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
        'discount_approved_by_id',
        'discount_approved_at',
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
            'discount_approved_at' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function isDraft(): bool
    {
        return $this->status === QuotationStatus::Draft;
    }

    public function isExpired(): bool
    {
        return $this->status === QuotationStatus::Expired
            || ($this->status === QuotationStatus::Sent && $this->valid_until !== null && $this->valid_until->isPast());
    }

    public function canBeAccepted(): bool
    {
        return $this->status === QuotationStatus::Sent && ! $this->isExpired();
    }

    public function discountPercent(): float
    {
        $subtotal = (float) $this->subtotal;
        $discount = (float) $this->discount_total;

        return $subtotal > 0 ? round(($discount / $subtotal) * 100, 2) : 0.0;
    }

    public function requiresDiscountApproval(): bool
    {
        return $this->discountPercent() > (float) config('crm.quotations.discount_approval_threshold', 15.00);
    }

    public function getPublicSignedUrl(?int $days = null): string
    {
        $validDays = $days ?? (int) config('crm.quotations.public_link_days', 30);

        return URL::temporarySignedRoute(
            'quotations.public.show',
            now()->addDays($validDays),
            ['quotation' => $this->ulid]
        );
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

    public function discountApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discount_approved_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('position');
    }

    /**
     * Determine if this quotation's public access link has expired.
     */
    public function isPublicLinkExpired(): bool
    {
        $validDays = (int) config('crm.quotations.public_link_days', 30);

        return ($this->valid_until !== null && $this->valid_until->isPast())
            || ($this->sent_at !== null && $this->sent_at->addDays($validDays)->isPast());
    }
}
