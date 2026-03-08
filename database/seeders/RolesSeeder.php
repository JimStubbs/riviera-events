<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view events',
            'submit events',
            'edit own events',
            'manage events',
            'manage users',
            'manage ads',
            'manage locations',
            'manage categories',
            'manage sponsorships',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $guest = Role::firstOrCreate(['name' => 'guest']);
        $guest->syncPermissions(['view events']);

        $organizer = Role::firstOrCreate(['name' => 'organizer']);
        $organizer->syncPermissions(['view events', 'submit events', 'edit own events']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);
    }
}
