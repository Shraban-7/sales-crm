<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureNotReadOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        if ($user->hasAnyRole(['Read-Only Analyst', 'Viewer'])) {
            abort(403, 'Read-only accounts cannot modify CRM records.');
        }

        return $next($request);
    }
}
