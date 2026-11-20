<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\Mail\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class SmtpController extends Controller
{
    public function index(MailSettings $mailSettings): View
    {
        return view('pages.settings.smtp', [
            'settings' => $mailSettings->getSettings(),
        ]);
    }

    public function store(Request $request, MailSettings $mailSettings): RedirectResponse
    {
        $data = $request->validate([
            'mail_host' => ['required', 'string', 'max:191'],
            'mail_port' => ['required', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:191'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'mail_from_address' => ['required', 'email:rfc', 'max:191'],
            'mail_from_name' => ['required', 'string', 'max:150'],
        ]);

        $mailSettings->saveSettings($data);

        return back()->with('status', 'SMTP configuration updated and encrypted securely.');
    }

    public function testEmail(Request $request, MailSettings $mailSettings): RedirectResponse
    {
        $data = $request->validate([
            'test_email' => ['required', 'email:rfc'],
        ]);

        $mailSettings->applyToMailer();

        try {
            Mail::raw('This is a test email from Sale Fusion CRM SMTP configuration.', function ($message) use ($data): void {
                $message->to($data['test_email'])
                    ->subject('Sale Fusion CRM — SMTP Connection Test');
            });

            return back()->with('status', "Test email successfully sent to {$data['test_email']}.");
        } catch (\Throwable $e) {
            return back()->withErrors(['test_email' => 'Failed to send test email: '.$e->getMessage()]);
        }
    }
}
