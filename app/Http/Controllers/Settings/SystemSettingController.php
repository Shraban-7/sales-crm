<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\Audit\AuditLoggerService;
use App\Services\Settings\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SystemSettingController extends Controller
{
    public function edit(SystemSettingService $settingService): View
    {
        $groupedSettings = $settingService->allByGroup();

        return view('pages.settings.system', [
            'groupedSettings' => $groupedSettings,
            'allSettings' => $settingService->all(),
        ]);
    }

    public function update(
        Request $request,
        SystemSettingService $settingService,
        AuditLoggerService $auditLogger,
    ): RedirectResponse {
        $data = $request->validate([
            // General
            'company_name' => ['required', 'string', 'max:150'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_email' => ['required', 'email:rfc', 'max:191'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_tax_id' => ['nullable', 'string', 'max:100'],
            'company_logo' => ['nullable', 'image', 'max:5120', 'mimes:png,jpg,jpeg,svg,webp'],

            // Localization
            'default_currency' => ['required', 'string', 'size:3'],
            'currency_position' => ['required', 'string', 'in:before,after'],
            'default_country' => ['required', 'string', 'size:2'],
            'timezone' => ['required', 'string', 'max:100'],
            'date_format' => ['required', 'string', 'max:30'],

            // Sales & Policies
            'quotation_number_prefix' => ['required', 'string', 'max:10'],
            'proposal_number_prefix' => ['required', 'string', 'max:10'],
            'discount_approval_threshold' => ['required', 'numeric', 'between:0,100'],
            'quote_public_link_days' => ['required', 'integer', 'between:1,365'],
            'deal_rotting_days_default' => ['required', 'integer', 'between:1,180'],
            'lead_round_robin' => ['nullable', 'boolean'],
            'demo_mode' => ['nullable', 'boolean'],
            'records_per_page' => ['required', 'integer', 'in:10,15,20,25,50,100'],
        ]);

        $oldValues = $settingService->all();
        $settingService->saveSettings($data, $request->file('company_logo'));
        $newValues = $settingService->all();

        $auditLogger->log(
            event: 'system_settings_updated',
            auditable: $request->user(),
            actor: $request->user(),
            oldValues: $oldValues,
            newValues: $newValues,
            teamId: $request->user()->team_id,
        );

        return back()->with('status', 'System and site settings have been updated successfully.');
    }
}
