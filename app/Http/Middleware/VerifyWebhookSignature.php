<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('crm.lead_capture.webhook_secret', '');

        if ($secret === '') {
            abort(503, 'Lead webhook is not configured.');
        }

        $signature = (string) $request->header('X-CRM-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if ($signature === '' || ! hash_equals($expected, $signature)) {
            abort(401, 'Invalid signature.');
        }

        $timestamp = $request->header('X-CRM-Timestamp');
        if ($timestamp !== null && $timestamp !== '') {
            if (! is_numeric($timestamp) || abs(now()->timestamp - (int) $timestamp) > 300) {
                abort(401, 'Webhook timestamp expired or invalid.');
            }
        }

        return $next($request);
    }
}
