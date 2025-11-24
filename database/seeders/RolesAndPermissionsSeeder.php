<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1) Define permissions
        $permissions = [
            // Audit logs
            'auditlog.view',
            'auditlog.create',
            'auditlog.update',
            'auditlog.delete',

            // Operations
            'operations.view',
            'operations.create',
            'operations.update',
            'operations.delete',

            // User / permission management
            'users.manage_permissions',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['id' => (string) Str::orderedUuid()] // important for UUID PK
            );
        }

        // 2) Platform roles
        $platformRoles = [
            'superadmin' => [
                'permissions' => Permission::all()->pluck('name')->all(),
            ],
            'platform_admin' => [
                'permissions' => Permission::all()->pluck('name')->all(),
            ],
            'support_agent' => [
                'permissions' => ['auditlog.view'],
            ],
        ];

        foreach ($platformRoles as $name => $meta) {
            $role = Role::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                [
                    'id' => (string) Str::orderedUuid(),
                    'role_scope' => 'platform',
                ]
            );
            $role->syncPermissions($meta['permissions']);
        }

        // 3) Company roles (tenant-level)
        $companyRoles = [
            'company_owner' => ['auditlog.view', 'auditlog.create', 'auditlog.update', 'auditlog.delete', 'operations.*'],
            'company_admin' => ['auditlog.view', 'operations.view', 'operations.create', 'operations.update'],
            'hr_admin' => ['operations.view'],
            'manager' => ['operations.view'],
            'employee' => ['operations.view'],
            'finance' => ['operations.view'],
            'recruiter' => ['operations.view'],
        ];

        foreach ($companyRoles as $name => $perms) {
            $role = Role::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                [
                    'id' => (string) Str::orderedUuid(),
                    'role_scope' => 'company',
                ]
            );

            // Expand wildcard ops.* if present
            $resolved = collect($perms)->flatMap(function ($p) {
                if ($p === 'operations.*') {
                    return ['operations.view', 'operations.create', 'operations.update', 'operations.delete'];
                }
                return [$p];
            })->all();

            $role->syncPermissions($resolved);
        }

        // 3) Seed users

        // Admin (platform)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => (string) Str::orderedUuid(),
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $admin->hasRole('platform_admin')) {
            $admin->assignRole('platform_admin');
        }

        // Superadmin (only one)
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'id' => (string) Str::orderedUuid(),
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $superadmin->hasRole('superadmin')) {
            $superadmin->syncRoles(['superadmin']); // ensure exclusive role
        }

        // Test user (no roles/permissions)
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'id' => (string) Str::orderedUuid(),
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $testUser->syncRoles([]);
        $testUser->syncPermissions([]);
    }
}
