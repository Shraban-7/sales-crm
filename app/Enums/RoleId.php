<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleId: string
{
    case HeadOfSales = 'Head of Sales';
    case SalesManager = 'Sales Manager';
    case SalesExecutive = 'Sales Executive';
    case Viewer = 'Viewer';

    // Core SaaS Role Aliases
    case SuperAdmin = 'Super Admin';
    case OrgAdmin = 'Org Admin';
    case SalesRep = 'Sales Rep';
    case ReadOnlyAnalyst = 'Read-Only Analyst';
}
