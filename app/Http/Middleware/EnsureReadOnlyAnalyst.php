<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\RoleId;
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

        if (! $user->hasAnyRole(RoleId::analyticsRoutes())) {
            abort(403, 'This action requires Analyst or Management privileges.');
        }

        return $next($request);
    }
}
