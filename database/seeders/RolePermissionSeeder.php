<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Leads
            'leads.view.own',
            'leads.view.team',
            'leads.view.all',
            'leads.create',
            'leads.update.own',
            'leads.update.team',
            'leads.update.all',
            'leads.convert',
            'leads.delete',

            // Contacts
            'contacts.view.own',
            'contacts.view.team',
            'contacts.view.all',
            'contacts.create',
            'contacts.update.own',
            'contacts.update.team',
            'contacts.update.all',
            'contacts.delete',

            // Companies
            'companies.view.own',
            'companies.view.team',
            'companies.view.all',
            'companies.create',
            'companies.update.own',
            'companies.update.team',
            'companies.update.all',
            'companies.delete',

            // Pipelines
            'pipelines.view',
            'pipelines.manage',

            // Deals
            'deals.view.own',
            'deals.view.team',
            'deals.view.all',
            'deals.create',
            'deals.update.own',
            'deals.update.team',
            'deals.update.all',
            'deals.move_stage.own',
            'deals.reassign',
            'deals.delete',

            // Activities
            'activities.view.own',
            'activities.view.team',
            'activities.view.all',
            'activities.create',
            'activities.update.own',
            'activities.delete',

            // Tasks
            'tasks.view.own',
            'tasks.view.team',
            'tasks.view.all',
            'tasks.create',
            'tasks.assign.team',
            'tasks.update.own',
            'tasks.delete',

            // Follow-Ups
            'follow_ups.view.own',
            'follow_ups.view.team',
            'follow_ups.view.all',
            'follow_ups.create',
            'follow_ups.update.own',
            'follow_ups.delete',

            // Quotations
            'quotations.view.own',
            'quotations.view.team',
            'quotations.view.all',
            'quotations.create',
            'quotations.update.own',
            'quotations.send',
            'quotations.approve_discount',
            'quotations.delete',

            // Reports
            'reports.view.own',
            'reports.view.team',
            'reports.view.all',

            // Imports & Exports
            'imports.create',
            'exports.create.own',
            'exports.create.team',
            'exports.create.all',

            // Users, Teams, Roles, Webhooks & Audit Logs
            'users.view',
            'users.manage',
            'teams.manage',
            'roles.manage',
            'webhooks.manage',
            'settings.manage',
            'audit_logs.view.team',
            'audit_logs.view.all',

            // Notifications
            'notifications.view.own',
            'notifications.manage_preferences.own',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $superAdmin->syncPermissions($permissions);

        $orgAdmin = Role::findOrCreate('Org Admin', 'web');
        $orgAdmin->syncPermissions(array_values(array_diff($permissions, ['roles.manage', 'audit_logs.view.all'])));

        $salesManager = Role::findOrCreate('Sales Manager', 'web');
        $salesManager->syncPermissions([
            'leads.view.own', 'leads.view.team', 'leads.create', 'leads.update.own', 'leads.update.team', 'leads.convert', 'leads.delete',
            'contacts.view.own', 'contacts.view.team', 'contacts.create', 'contacts.update.own', 'contacts.update.team', 'contacts.delete',
            'companies.view.own', 'companies.view.team', 'companies.create', 'companies.update.own', 'companies.update.team', 'companies.delete',
            'pipelines.view',
            'deals.view.own', 'deals.view.team', 'deals.create', 'deals.update.own', 'deals.update.team', 'deals.move_stage.own', 'deals.reassign', 'deals.delete',
            'activities.view.own', 'activities.view.team', 'activities.create', 'activities.update.own', 'activities.delete',
            'tasks.view.own', 'tasks.view.team', 'tasks.create', 'tasks.assign.team', 'tasks.update.own', 'tasks.delete',
            'follow_ups.view.own', 'follow_ups.view.team', 'follow_ups.create', 'follow_ups.update.own', 'follow_ups.delete',
            'quotations.view.own', 'quotations.view.team', 'quotations.create', 'quotations.update.own', 'quotations.send', 'quotations.approve_discount', 'quotations.delete',
            'reports.view.own', 'reports.view.team',
            'imports.create', 'exports.create.own', 'exports.create.team',
            'users.view',
            'notifications.view.own', 'notifications.manage_preferences.own',
        ]);

        $salesRep = Role::findOrCreate('Sales Rep', 'web');
        $salesRep->syncPermissions([
            'leads.view.own', 'leads.create', 'leads.update.own', 'leads.convert',
            'contacts.view.own', 'contacts.create', 'contacts.update.own',
            'companies.view.own', 'companies.create', 'companies.update.own',
            'pipelines.view',
            'deals.view.own', 'deals.create', 'deals.update.own', 'deals.move_stage.own',
            'activities.view.own', 'activities.create', 'activities.update.own',
            'tasks.view.own', 'tasks.create', 'tasks.update.own',
            'follow_ups.view.own', 'follow_ups.create', 'follow_ups.update.own',
            'quotations.view.own', 'quotations.create', 'quotations.update.own', 'quotations.send',
            'reports.view.own',
            'exports.create.own',
            'notifications.view.own', 'notifications.manage_preferences.own',
        ]);

        $analyst = Role::findOrCreate('Read-Only Analyst', 'web');
        $analyst->syncPermissions([
            'leads.view.own', 'leads.view.team', 'leads.view.all',
            'contacts.view.own', 'contacts.view.team', 'contacts.view.all',
            'companies.view.own', 'companies.view.team', 'companies.view.all',
            'pipelines.view',
            'deals.view.own', 'deals.view.team', 'deals.view.all',
            'activities.view.own', 'activities.view.team', 'activities.view.all',
            'tasks.view.own', 'tasks.view.team', 'tasks.view.all',
            'follow_ups.view.own', 'follow_ups.view.team', 'follow_ups.view.all',
            'quotations.view.own', 'quotations.view.team', 'quotations.view.all',
            'reports.view.own', 'reports.view.team', 'reports.view.all',
            'exports.create.own', 'exports.create.all',
            'notifications.view.own', 'notifications.manage_preferences.own',
        ]);

        // Role aliases matching 4-role enterprise specifications
        $headOfSales = Role::findOrCreate('Head of Sales', 'web');
        $headOfSales->syncPermissions($orgAdmin->permissions);

        $salesExecutive = Role::findOrCreate('Sales Executive', 'web');
        $salesExecutive->syncPermissions($salesRep->permissions);

        $viewer = Role::findOrCreate('Viewer', 'web');
        $viewer->syncPermissions($analyst->permissions);
    }
}
