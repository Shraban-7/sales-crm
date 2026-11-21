<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureReadOnlyAnalyst
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

        if (! $user->hasAnyRole(['Super Admin', 'Org Admin', 'Head of Sales', 'Sales Manager', 'Read-Only Analyst', 'Viewer'])) {
            abort(403, 'This action requires Analyst or Management privileges.');
        }

        return $next($request);
    }
}
