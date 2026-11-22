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

    /** @return list<string> */
    public static function organizationWriters(): array
    {
        return self::values([self::SuperAdmin, self::OrgAdmin, self::HeadOfSales]);
    }

    /** @return list<string> */
    public static function organizationReaders(): array
    {
        return self::values([
            self::SuperAdmin,
            self::OrgAdmin,
            self::HeadOfSales,
            self::ReadOnlyAnalyst,
            self::Viewer,
        ]);
    }

    /** @return list<string> */
    public static function readOnly(): array
    {
        return self::values([self::ReadOnlyAnalyst, self::Viewer]);
    }

    /** @return list<string> */
    public static function managerRoutes(): array
    {
        return [...self::organizationWriters(), self::SalesManager->value];
    }

    /** @return list<string> */
    public static function salesRoutes(): array
    {
        return [...self::managerRoutes(), self::SalesRep->value, self::SalesExecutive->value];
    }

    /** @return list<string> */
    public static function analyticsRoutes(): array
    {
        return [...self::managerRoutes(), ...self::readOnly()];
    }

    /** @return list<string> */
    public static function allNames(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }

    /** @return list<string> */
    public static function routeGroup(string $group): array
    {
        return match ($group) {
            'super_admin' => [self::SuperAdmin->value],
            'organization_admin' => self::organizationWriters(),
            'sales_manager' => self::managerRoutes(),
            'sales_rep' => self::salesRoutes(),
            default => throw new \InvalidArgumentException("Unknown route role group [{$group}]."),
        };
    }

    /** @param list<self> $roles
     *  @return list<string>
     */
    private static function values(array $roles): array
    {
        return array_map(static fn (self $role): string => $role->value, $roles);
    }
}
