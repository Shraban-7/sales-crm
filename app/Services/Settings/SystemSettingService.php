<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Models\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SystemSettingService
{
    /**
     * Default settings dictionary with group and type definitions.
     *
     * @var array<string, array{group: string, type: string, default: mixed, label: string}>
     */
    public const DEFAULTS = [
        // General & Branding
        'company_name' => ['group' => 'general', 'type' => 'string', 'default' => 'Sale Fusion CRM', 'label' => 'Site / Organization Name'],
        'company_tagline' => ['group' => 'general', 'type' => 'string', 'default' => 'All-in-one B2B Revenue Workspace', 'label' => 'Tagline / Slogan'],
        'company_email' => ['group' => 'general', 'type' => 'string', 'default' => 'support@salescrm.test', 'label' => 'Company Support Email'],
        'company_phone' => ['group' => 'general', 'type' => 'string', 'default' => '+1 (555) 019-2834', 'label' => 'Company Phone'],
        'company_address' => ['group' => 'general', 'type' => 'string', 'default' => '100 Innovation Way, Suite 400, San Francisco, CA 94105', 'label' => 'Headquarters Address'],
        'company_tax_id' => ['group' => 'general', 'type' => 'string', 'default' => 'US-EIN-987654321', 'label' => 'Tax ID / VAT Registration #'],
        'company_logo_url' => ['group' => 'general', 'type' => 'string', 'default' => '', 'label' => 'Company Logo URL'],
        'demo_mode' => ['group' => 'general', 'type' => 'boolean', 'default' => false, 'label' => 'Interactive Demo Mode Active'],

        // Localization
        'default_currency' => ['group' => 'localization', 'type' => 'string', 'default' => 'USD', 'label' => 'Default Currency Code'],
        'currency_position' => ['group' => 'localization', 'type' => 'string', 'default' => 'before', 'label' => 'Currency Symbol Position'],
        'default_country' => ['group' => 'localization', 'type' => 'string', 'default' => 'US', 'label' => 'Default Country Code'],
        'timezone' => ['group' => 'localization', 'type' => 'string', 'default' => 'UTC', 'label' => 'System Timezone'],
        'date_format' => ['group' => 'localization', 'type' => 'string', 'default' => 'Y-m-d', 'label' => 'Date Display Format'],

        // Sales & Policies
        'quotation_number_prefix' => ['group' => 'sales', 'type' => 'string', 'default' => 'QT', 'label' => 'Quotation Number Prefix'],
        'proposal_number_prefix' => ['group' => 'sales', 'type' => 'string', 'default' => 'PR', 'label' => 'Proposal Number Prefix'],
        'discount_approval_threshold' => ['group' => 'sales', 'type' => 'float', 'default' => 15.00, 'label' => 'Discount Approval Threshold (%)'],
        'quote_public_link_days' => ['group' => 'sales', 'type' => 'integer', 'default' => 30, 'label' => 'Public Quotation Validity (Days)'],
        'deal_rotting_days_default' => ['group' => 'sales', 'type' => 'integer', 'default' => 14, 'label' => 'Deal Rotting Alert Threshold (Days)'],
        'lead_round_robin' => ['group' => 'sales', 'type' => 'boolean', 'default' => true, 'label' => 'Auto-Assign Leads via Round-Robin'],
        'records_per_page' => ['group' => 'sales', 'type' => 'integer', 'default' => 20, 'label' => 'Records Displayed Per Page'],
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        $fallback = $default ?? (self::DEFAULTS[$key]['default'] ?? null);

        return SystemSetting::get($key, $fallback);
    }

    public function set(string $key, mixed $value): void
    {
        $meta = self::DEFAULTS[$key] ?? ['group' => 'general', 'type' => 'string'];
        SystemSetting::set($key, $value, $meta['group'], $meta['type']);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $cached = SystemSetting::getAllCached();
        $result = [];

        foreach (self::DEFAULTS as $key => $meta) {
            $result[$key] = array_key_exists($key, $cached) ? $cached[$key] : $meta['default'];
        }

        return $result;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allByGroup(): array
    {
        $all = $this->all();
        $grouped = [];

        foreach (self::DEFAULTS as $key => $meta) {
            $grouped[$meta['group']][$key] = [
                'key' => $key,
                'value' => $all[$key],
                'label' => $meta['label'],
                'type' => $meta['type'],
            ];
        }

        return $grouped;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function saveSettings(array $input, ?UploadedFile $logoFile = null): void
    {
        if ($logoFile !== null && $logoFile->isValid()) {
            $extension = $logoFile->getClientOriginalExtension();
            $filename = 'logo-'.Str::random(12).'.'.$extension;
            $path = $logoFile->storeAs('branding', $filename, 'public');
            $this->set('company_logo_url', Storage::disk('public')->url($path));
        }

        foreach (self::DEFAULTS as $key => $meta) {
            if ($key === 'company_logo_url' && $logoFile === null) {
                continue;
            }

            if ($meta['type'] === 'boolean') {
                $this->set($key, isset($input[$key]));
            } elseif (array_key_exists($key, $input)) {
                $this->set($key, $input[$key]);
            }
        }

        $this->applyToConfig();
    }

    public function applyToConfig(): void
    {
        $companyName = $this->get('company_name');
        if ($companyName) {
            Config::set('app.name', $companyName);
        }

        $currency = $this->get('default_currency');
        if ($currency) {
            Config::set('crm.currency', $currency);
        }

        $country = $this->get('default_country');
        if ($country) {
            Config::set('crm.country', $country);
        }

        $discountThreshold = $this->get('discount_approval_threshold');
        if ($discountThreshold !== null) {
            Config::set('crm.quotations.discount_approval_threshold', (float) $discountThreshold);
        }

        $publicLinkDays = $this->get('quote_public_link_days');
        if ($publicLinkDays !== null) {
            Config::set('crm.quotations.public_link_days', (int) $publicLinkDays);
        }

        $perPage = $this->get('records_per_page');
        if ($perPage !== null) {
            Config::set('crm.pagination.per_page', (int) $perPage);
        }

        $dbDemoMode = SystemSetting::get('demo_mode') ?? SystemSetting::get('crm_demo_mode');
        if ($dbDemoMode !== null) {
            Config::set('crm.demo_mode', filter_var($dbDemoMode, FILTER_VALIDATE_BOOLEAN));
        }
    }
}
