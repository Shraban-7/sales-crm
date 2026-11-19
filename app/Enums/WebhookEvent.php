<?php

declare(strict_types=1);

namespace App\Enums;

enum WebhookEvent: string
{
    case LeadCreated = 'lead.created';
    case LeadConverted = 'lead.converted';
    case DealCreated = 'deal.created';
    case DealStageMoved = 'deal.stage_moved';
    case DealWon = 'deal.won';
    case DealLost = 'deal.lost';
    case QuotationSent = 'quotation.sent';
    case QuotationAccepted = 'quotation.accepted';
    case TaskCreated = 'task.created';

    public function label(): string
    {
        return match ($this) {
            self::LeadCreated => 'Lead Created',
            self::LeadConverted => 'Lead Converted to Deal',
            self::DealCreated => 'Deal Created',
            self::DealStageMoved => 'Deal Stage Moved',
            self::DealWon => 'Deal Closed Won',
            self::DealLost => 'Deal Closed Lost',
            self::QuotationSent => 'Quotation Sent',
            self::QuotationAccepted => 'Quotation Accepted',
            self::TaskCreated => 'Task Created',
        };
    }
}
