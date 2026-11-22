<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\RoleId;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureRoleGroup
{
    public function handle(Request $request, Closure $next, string $group): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        abort_unless($user->hasAnyRole(RoleId::routeGroup($group)), 403, 'Your role cannot access this area.');

        return $next($request);
    }
}
