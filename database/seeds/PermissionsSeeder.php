<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // User Permissions
        Permission::create(['name' => 'users.index']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.show']);
        Permission::create(['name' => 'users.edit']);
        Permission::create(['name' => 'users.delete']);
        Permission::create(['name' => 'users.active']);
        Permission::create(['name' => 'users.deactive']);

        // Category Permissions
        Permission::create(['name' => 'categories.index']);
        Permission::create(['name' => 'categories.create']);
        Permission::create(['name' => 'categories.show']);
        Permission::create(['name' => 'categories.edit']);
        Permission::create(['name' => 'categories.delete']);
        Permission::create(['name' => 'categories.active']);
        Permission::create(['name' => 'categories.deactive']);

        // Language Permissions
        Permission::create(['name' => 'languages.index']);
        Permission::create(['name' => 'languages.create']);
        Permission::create(['name' => 'languages.show']);
        Permission::create(['name' => 'languages.edit']);
        Permission::create(['name' => 'languages.delete']);
        Permission::create(['name' => 'languages.active']);
        Permission::create(['name' => 'languages.deactive']);

        // Publication Permissions
        Permission::create(['name' => 'publications.index']);
        Permission::create(['name' => 'publications.show']);
        Permission::create(['name' => 'publications.delete']);

        // Chat Permissions
        Permission::create(['name' => 'chats.active']);
        Permission::create(['name' => 'chats.deactive']);

        // Proposal Permissions
        Permission::create(['name' => 'proposals.index']);
        Permission::create(['name' => 'proposals.show']);

        // Project Permissions
        Permission::create(['name' => 'projects.index']);
        Permission::create(['name' => 'projects.show']);

        // Payment Permissions
        Permission::create(['name' => 'payments.index']);
        Permission::create(['name' => 'payments.show']);

        // Setting Permissions
        Permission::create(['name' => 'settings.index']);
        Permission::create(['name' => 'settings.edit']);

    	$superadmin=Role::create(['name' => 'Super Admin']);
        $superadmin->givePermissionTo(Permission::all());
        
        $admin=Role::create(['name' => 'Administrator']);
    	$admin->givePermissionTo(Permission::all());

        $user=Role::create(['name' => 'User']);

    	$user=User::find(1);
    	$user->assignRole('Super Admin');
    }
}
