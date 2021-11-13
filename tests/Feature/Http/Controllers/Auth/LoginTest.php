<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use App\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    
    public function testView()
    {
        $this->get('/login')->assertStatus(200);
    }

    public function testLogin()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        $this->get('/login')->assertStatus(200);
        $credentials=array("email" => $user->email, "password" => "password");

        $this->post('/login', $credentials)->assertRedirect('/admin');
        $this->assertCredentials($credentials);
    }

    public function testLoginInvalidCredentials()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        $this->get('/login')->assertStatus(200);
        $credentials=array("email" => $user->email, "password" => "secret");

        $this->assertInvalidCredentials($credentials);
    }

    public function testValidate()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        $this->get('/login')->assertStatus(200);
        $credentials=array('email' => '', 'password' => 'password');
        $this->post('/login', $credentials)->assertRedirect('/login')->assertSessionHasErrors('email');

        $credentials=array('email' => 'admin@gmail.com', 'password' => '');
        $this->post('/login', $credentials)->assertRedirect('/login')->assertSessionHasErrors('password');
    }

    public function permissionsCreate() {
        // Permiso para Acceder al Panel Administrativo
        Permission::create(['name' => 'dashboard']);

        $superadmin=Role::create(['name' => 'Super Admin']);
        $superadmin->givePermissionTo(Permission::all());
    }
}
