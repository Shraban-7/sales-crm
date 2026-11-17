<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'quotation_id',
        'position',
        'item_name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'tax_percent',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
