<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotDemoMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('crm.demo_mode', false)) {
            return $next($request);
        }

        // Safe HTTP read methods are always allowed so visitors can explore all features.
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        // Public intake, login, logout, 2FA, and signed client routes are exempt.
        if ($this->isExemptRoute($request)) {
            return $next($request);
        }

        $user = $request->user();

        // Non-demo users (real admins, normal users like superadmin@salescrm.test) or verified owners have full write access.
        if ($user && (! $user->isDemoUser() || $user->isOwner())) {
            return $next($request);
        }

        // Block mutating actions in live demo mode.
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Action disabled in live demo mode. In your private installation, you have complete administrative control.',
            ], Response::HTTP_FORBIDDEN);
        }

        return back()->with('warning', 'Demo Mode Notice: Modifications to site settings and sample records are disabled in the live demonstration to protect data for other evaluators. In your private installation, all features are fully interactive and editable.');
    }

    protected function isExemptRoute(Request $request): bool
    {
        return $request->routeIs([
            'login',
            'login.store',
            'logout',
            'two-factor.*',
            'leads.capture.*',
            'leads.capture.webhook',
            'quotations.public.*',
        ]);
    }
}
