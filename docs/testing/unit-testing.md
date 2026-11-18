# Unit Testing Guide — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/testing/testing-strategy.md`, `docs/modules/quotations-and-proposals.md`

## 1. Scope of Unit Tests (`tests/Unit/`)

Unit tests verify isolated domain logic, financial calculations, value objects/DTOs, CSV cell sanitization, and Enum state machines with zero HTTP overhead.

## 2. Practical Pest & PHPUnit Examples

### 2.1 Quotation Total Calculation Unit Test (Pest Syntax)
```php
<?php

declare(strict_types=1);

use App\Actions\Quotations\CalculateQuotationTotalsAction;
use App\Data\Quotations\QuotationLineItemData;

it('calculates exact subtotal, discount, tax, and grand total without float drift', function (): void {
    $calculator = new CalculateQuotationTotalsAction();

    $items = [
        new QuotationLineItemData(
            itemName: 'Enterprise CRM License',
            quantity: '10.00',
            unitPrice: '1250.50',
            discountPercent: '10.00',
            taxPercent: '15.00',
        ),
        new QuotationLineItemData(
            itemName: 'Onboarding Package',
            quantity: '1.00',
            unitPrice: '5000.00',
            discountPercent: '0.00',
            taxPercent: '15.00',
        ),
    ];

    $totals = $calculator->calculate($items);

    // Item 1: gross 12505.00, discount 1250.50, taxable 11254.50, tax 1688.18, total 12942.68
    // Item 2: gross 5000.00, discount 0.00, taxable 5000.00, tax 750.00, total 5750.00
    expect($totals->subtotal)->toBe('17505.00')
        ->and($totals->discountTotal)->toBe('1250.50')
        ->and($totals->taxTotal)->toBe('2438.18')
        ->and($totals->grandTotal)->toBe('18692.68');
});
```

### 2.2 Quotation Status State Machine Unit Test (PHPUnit Syntax)
```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\QuotationStatus;
use PHPUnit\Framework\TestCase;

final class QuotationStatusTest extends TestCase
{
    public function test_sent_quotation_can_transition_to_accepted_or_rejected_but_not_draft(): void
    {
        $status = QuotationStatus::Sent;

        $this->assertTrue($status->canTransitionTo(QuotationStatus::Accepted));
        $this->assertTrue($status->canTransitionTo(QuotationStatus::Rejected));
        $this->assertFalse($status->canTransitionTo(QuotationStatus::Draft));
    }
}
```
