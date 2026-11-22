<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureNotDemoMode;
use App\Http\Middleware\EnsureNotReadOnly;
use App\Http\Middleware\EnsureReadOnlyAnalyst;
use App\Http\Middleware\EnsureRoleGroup;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureUserIsActive::class,
            EnsureNotDemoMode::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'demo.protect' => EnsureNotDemoMode::class,
            'webhook.hmac' => VerifyWebhookSignature::class,
            'active' => EnsureUserIsActive::class,

            'role.group' => EnsureRoleGroup::class,
            'role.not_read_only' => EnsureNotReadOnly::class,
            'role.analyst' => EnsureReadOnlyAnalyst::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
