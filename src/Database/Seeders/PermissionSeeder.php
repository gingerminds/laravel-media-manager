<?php

namespace Gingerminds\LaravelMediaManager\Database\Seeders;

use Gingerminds\LaravelCore\Models\Permission\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::updateOrCreate(['name' => 'view medias', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'edit medias', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'delete medias', 'guard_name' => 'web']);

        Permission::updateOrCreate(['name' => 'view media_categories', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'edit media_categories', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'delete media_categories', 'guard_name' => 'web']);

        $this->command->info('Permissions table seeded!');
        // updateOrCreate roles and assign existing permissions
    }
}
