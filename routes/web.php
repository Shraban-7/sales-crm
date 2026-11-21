<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes by Role Guard Architecture
|--------------------------------------------------------------------------
|
| The application routes are separated into discrete, guard-specific files:
|
| 1. routes/public.php      : Public, guest, and cryptographically signed URLs.
| 2. routes/rep.php         : Sales Rep & operational mutation guard (role.rep, role.not_read_only).
| 3. routes/manager.php     : Sales Manager approval, bulk import, and targets guard (role.manager).
| 4. routes/admin.php       : Org Admin & Super Admin settings and pipeline schema guard (role.admin).
| 5. routes/super_admin.php : Super Admin exclusive maintenance and diagnostics guard (role.super_admin).
| 6. routes/shared.php      : Shared authenticated workspaces and read views (all roles & analysts).
|
*/

// 1. Public & Guest Guard
require __DIR__.'/public.php';

// 2. Sales Rep Guard (Create forms & business mutations gated against Read-Only Analyst)
Route::middleware(['auth', 'role.rep', 'role.not_read_only'])->group(function (): void {
    require __DIR__.'/rep.php';
});

// 3. Sales Manager Guard (Quotation discount approvals, bulk imports, sales targets)
Route::middleware(['auth', 'role.manager'])->group(function (): void {
    require __DIR__.'/manager.php';
});

// 4. Org Admin & Super Admin Guard (System settings, users, teams, stages, webhooks, audit logs)
Route::middleware(['auth', 'role.admin'])->group(function (): void {
    require __DIR__.'/admin.php';
});

// 5. Super Admin Exclusive Guard
Route::middleware(['auth', 'role.super_admin'])->group(function (): void {
    require __DIR__.'/super_admin.php';
});

// 6. Shared Authenticated Guard (Dashboards, read-only entity lists & details, user profile)
Route::middleware('auth')->group(function (): void {
    require __DIR__.'/shared.php';
});
