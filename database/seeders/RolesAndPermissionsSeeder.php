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

        // 2) Roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'administrator', 'guard_name' => 'web'],
            ['id' => (string) Str::orderedUuid()]
        );

        $editorRole = Role::firstOrCreate(
            ['name' => 'editor', 'guard_name' => 'web'],
            ['id' => (string) Str::orderedUuid()]
        );

        $viewerRole = Role::firstOrCreate(
            ['name' => 'viewer', 'guard_name' => 'web'],
            ['id' => (string) Str::orderedUuid()]
        );

        // Admin gets everything
        $adminRole->givePermissionTo(Permission::all());

        // Editor
        $editorRole->syncPermissions([
            'auditlog.view',
            'auditlog.create',
            'auditlog.update',
            'operations.view',
            'operations.create',
            'operations.update',
        ]);

        // Viewer
        $viewerRole->syncPermissions([
            'auditlog.view',
            'operations.view',
        ]);

        // 3) Seed users

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => (string) Str::orderedUuid(),
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $admin->hasRole('administrator')) {
            $admin->assignRole('administrator');
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
