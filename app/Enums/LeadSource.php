<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSource: string
{
    case Website = 'website';
    case OrganicSearch = 'organic_search';
    case PaidSearch = 'paid_search';
    case PaidSocial = 'paid_social';
    case SocialMedia = 'social_media';
    case LinkedIn = 'linkedin';
    case EmailCampaign = 'email_campaign';
    case OutboundEmail = 'outbound_email';
    case ColdCall = 'cold_call';
    case WhatsApp = 'whatsapp';
    case LiveChat = 'live_chat';
    case Referral = 'referral';
    case Partner = 'partner';
    case Event = 'event';
    case TradeShow = 'trade_show';
    case Webinar = 'webinar';
    case Marketplace = 'marketplace';
    case WalkIn = 'walk_in';
    case Import = 'import';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'Website',
            self::OrganicSearch => 'Organic Search (SEO)',
            self::PaidSearch => 'Paid Search Ads',
            self::PaidSocial => 'Paid Social Ads',
            self::SocialMedia => 'Social Media',
            self::LinkedIn => 'LinkedIn',
            self::EmailCampaign => 'Email Campaign',
            self::OutboundEmail => 'Outbound Email',
            self::ColdCall => 'Cold Call',
            self::WhatsApp => 'WhatsApp',
            self::LiveChat => 'Live Chat',
            self::Referral => 'Referral',
            self::Partner => 'Partner / Reseller',
            self::Event => 'Event',
            self::TradeShow => 'Trade Show',
            self::Webinar => 'Webinar',
            self::Marketplace => 'Marketplace / Directory',
            self::WalkIn => 'Walk-In / Direct',
            self::Import => 'Import',
            self::Other => 'Other',
        };
    }

    /**
     * Sources selectable on the public inbound enquiry form.
     *
     * @return list<self>
     */
    public static function publicOptions(): array
    {
        return [
            self::Website,
            self::OrganicSearch,
            self::PaidSearch,
            self::PaidSocial,
            self::SocialMedia,
            self::LinkedIn,
            self::EmailCampaign,
            self::WhatsApp,
            self::Referral,
            self::Partner,
            self::Event,
            self::TradeShow,
            self::Webinar,
            self::Marketplace,
            self::ColdCall,
            self::Other,
        ];
    }
}
