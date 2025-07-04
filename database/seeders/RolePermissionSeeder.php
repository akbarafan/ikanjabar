<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            // User management
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Branch management
            'manage-branches',
            'view-branches',
            'create-branches',
            'edit-branches',
            'delete-branches',

            // Pond management
            'manage-ponds',
            'view-ponds',
            'create-ponds',
            'edit-ponds',
            'delete-ponds',

            // Fish management
            'manage-fish',
            'view-fish',
            'create-fish',
            'edit-fish',
            'delete-fish',


            // Fish Batch management
            'manage-fish-batches',
            'view-fish-batches',
            'create-fish-batches',
            'edit-fish-batches',
            'delete-fish-batches',

            // Growth logs
            'manage-growth-logs',
            'view-growth-logs',
            'create-growth-logs',
            'edit-growth-logs',
            'delete-growth-logs',

            // Mortality management
            'manage-mortalities',
            'view-mortalities',
            'create-mortalities',
            'edit-mortalities',
            'delete-mortalities',

            // Water quality
            'manage-water-quality',
            'view-water-quality',
            'create-water-quality',
            'edit-water-quality',
            'delete-water-quality',

            // Feeding management
            'manage-feedings',
            'view-feedings',
            'create-feedings',
            'edit-feedings',
            'delete-feedings',

            // Sales management
            'manage-sales',
            'view-sales',
            'create-sales',
            'edit-sales',
            'delete-sales',

            // Transfer management
            'manage-transfers',
            'view-transfers',
            'create-transfers',
            'edit-transfers',
            'delete-transfers',

            // Reports
            'view-reports',
            'export-reports',

            // Dashboard
            'view-admin-dashboard',
            'view-branch-dashboard',
            'view-student-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin role - full access
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Branches role - branch-specific management
        $branchRole = Role::create(['name' => 'branches']);
        $branchRole->givePermissionTo([
            'view-users',
            'create-users',
            'edit-users',
            'view-branches',
            'manage-ponds',
            'view-ponds',
            'create-ponds',
            'edit-ponds',
            'delete-ponds',
            'manage-fish',
            'view-fish',
            'create-fish',
            'edit-fish',
            'delete-fish',
            'manage-fish-batches',
            'view-fish-batches',
            'create-fish-batches',
            'edit-fish-batches',
            'delete-fish-batches',
            'manage-growth-logs',
            'view-growth-logs',
            'create-growth-logs',
            'edit-growth-logs',
            'delete-growth-logs',
            'manage-mortalities',
            'view-mortalities',
            'create-mortalities',
            'edit-mortalities',
            'delete-mortalities',
            'manage-water-quality',
            'view-water-quality',
            'create-water-quality',
            'edit-water-quality',
            'delete-water-quality',
            'manage-feedings',
            'view-feedings',
            'create-feedings',
            'edit-feedings',
            'delete-feedings',
            'manage-sales',
            'view-sales',
            'create-sales',
            'edit-sales',
            'delete-sales',
            'manage-transfers',
            'view-transfers',
            'create-transfers',
            'edit-transfers',
            'delete-transfers',
            'view-reports',
            'export-reports',
            'view-branch-dashboard',
        ]);

        // Student role - limited access
        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'view-ponds',
            'view-fish',
            'view-fish-batches',
            'view-growth-logs',
            'create-growth-logs',
            'view-mortalities',
            'create-mortalities',
            'view-water-quality',
            'create-water-quality',
            'view-feedings',
            'create-feedings',
            'view-sales',
            'view-transfers',
            'view-reports',
            'view-student-dashboard',
        ]);
    }
}
