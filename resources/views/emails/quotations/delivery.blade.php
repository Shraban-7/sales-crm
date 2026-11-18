<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quote_number }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#0f172a;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:24px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                <tr>
                    <td style="background-color:#0f172a; padding:20px 28px; color:#ffffff;">
                        <h1 style="margin:0; font-size:20px;">{{ config('app.name') }}</h1>
                        <p style="margin:4px 0 0; font-size:13px; color:#cbd5e1;">Quotation {{ $quotation->quote_number }} (v{{ $quotation->version }})</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 12px;">Hello {{ $quotation->contact?->first_name ?? $quotation->company?->name ?? 'there' }},</p>
                        <p style="margin:0 0 16px; line-height:1.6;">
                            Please find attached quotation <strong>{{ $quotation->quote_number }}</strong> for
                            <strong>{{ $quotation->company?->name }}</strong>, totaling
                            <strong>{{ $quotation->currency }} {{ number_format((float) $quotation->grand_total, 2) }}</strong>.
                            This quotation is valid until {{ $quotation->valid_until->toFormattedDateString() }}.
                        </p>
                        <p style="margin:0 0 24px; line-height:1.6;">
                            You can review the full quotation and accept or decline it online:
                        </p>
                        <p style="margin:0 0 24px;">
                            <a href="{{ $publicUrl }}" style="display:inline-block; background-color:#0f172a; color:#ffffff; text-decoration:none; padding:12px 22px; border-radius:6px; font-weight:bold;">Review quotation</a>
                        </p>
                        <p style="margin:0; font-size:12px; color:#64748b; line-height:1.6;">
                            If the button does not work, copy this link into your browser:<br>
                            <a href="{{ $publicUrl }}" style="color:#2563eb; word-break:break-all;">{{ $publicUrl }}</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px; border-top:1px solid #e2e8f0; font-size:11px; color:#94a3b8;">
                        Sent by {{ $quotation->owner?->name }} &middot; {{ config('app.name') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
