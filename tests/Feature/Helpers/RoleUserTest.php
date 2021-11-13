<?php

namespace Tests\Feature\Helpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use App\User;

class RoleUserTest extends TestCase
{
    use RefreshDatabase;
    
    public function testRoleUser()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');
        $this->assertEquals(roleUser($user), '<span class="badge badge-primary">Super Admin</span>');

        $user=factory(User::class)->create();
        $user->assignRole(['Super Admin', 'Administrador']);
        $this->assertEquals(roleUser($user), '<span class="badge badge-primary">Super Admin<br>Administrador</span>');

        $user=factory(User::class)->create();
        $this->assertEquals(roleUser($user), '<span class="badge badge-dark">Desconocido</span>');

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');
        $this->assertEquals(roleUser($user, false), 'Super Admin');

        $user=factory(User::class)->create();
        $user->assignRole(['Super Admin', 'Administrador']);
        $this->assertEquals(roleUser($user, false), 'Super Admin<br>Administrador');

        $user=factory(User::class)->create();
        $this->assertEquals(roleUser($user, false), 'Desconocido');
    }

    public function permissionsCreate() {
        // Permiso para Acceder al Panel Administrativo
        Permission::create(['name' => 'dashboard']);

        $superadmin=Role::create(['name' => 'Super Admin']);
        $superadmin->givePermissionTo(Permission::all());
        
        $admin=Role::create(['name' => 'Administrador']);
        $admin->givePermissionTo(Permission::all());
    }
}
