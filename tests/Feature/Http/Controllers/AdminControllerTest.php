<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use App\User;
use Auth;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRoutesWithoutAuth()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/perfil')->assertRedirect('/login');
        $this->get('/admin/perfil/editar')->assertRedirect('/login');
        $this->put('/admin/perfil')->assertRedirect('/login');
    }

    public function testRoutesWithAuth()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $this->get('/admin')->assertStatus(200);
        $this->get('/admin/perfil')->assertStatus(200);
        $this->get('/admin/perfil/editar')->assertStatus(200);
    }

    public function testProfileUpdate()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'phone' => '12345678', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->put('/admin/perfil', $data)->assertRedirect('/admin/perfil/editar');
        $this->assertDatabaseHas('users', ['name' => 'Nuevo', 'lastname' => 'Usuario', 'phone' => '12345678']);

        $data=array('name' => 'Editado', 'lastname' => 'User', 'phone' => '12345678');
        $this->put('/admin/perfil', $data)->assertRedirect('/admin/perfil/editar');
        $this->assertDatabaseHas('users', ['name' => 'Editado', 'lastname' => 'User', 'phone' => '12345678']);
    }

    public function testValidateUpdate()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $data=array('name' => '', 'lastname' => 'Usuario', 'phone' => '12345678', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->put('/admin/perfil', $data)->assertSessionHasErrors('name');

        $data=array('name' => 'Editado', 'lastname' => '', 'phone' => '12345678', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->put('/admin/perfil', $data)->assertSessionHasErrors('lastname');

        $data=array('name' => 'Editado', 'lastname' => 'Usuario', 'phone' => '', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->put('/admin/perfil', $data)->assertSessionHasErrors('phone');
    }

    public function permissionsCreate() {
        // Permiso para Acceder al Panel Administrativo
        Permission::create(['name' => 'dashboard']);

        $superadmin=Role::create(['name' => 'Super Admin']);
        $superadmin->givePermissionTo(Permission::all());
    }
}
