<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use App\User;
use Auth;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRoutesWithoutAuth()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        $this->get('/admin/usuarios')->assertRedirect('/login');
        $this->get('/admin/usuarios/registrar')->assertRedirect('/login');
        $this->post('/admin/usuarios')->assertRedirect('/login');
        $this->get('/admin/usuarios/'.$user->slug)->assertRedirect('/login');
        $this->get('/admin/usuarios/'.$user->slug.'/editar')->assertRedirect('/login');
        $this->put('/admin/usuarios/'.$user->slug)->assertRedirect('/login');
        $this->delete('/admin/usuarios/'.$user->slug)->assertRedirect('/login');
        $this->put('/admin/usuarios/'.$user->slug.'/activar')->assertRedirect('/login');
        $this->put('/admin/usuarios/'.$user->slug.'/desactivar')->assertRedirect('/login');
    }

    public function testRoutesWithAuthWithoutPermissions()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Cliente');

        Auth::login($user);

        $this->get('/admin/usuarios')->assertForbidden();
        $this->get('/admin/usuarios/registrar')->assertForbidden();
        $this->post('/admin/usuarios')->assertForbidden();
        $this->get('/admin/usuarios/'.$user->slug)->assertForbidden();
        $this->get('/admin/usuarios/'.$user->slug.'/editar')->assertForbidden();
        $this->put('/admin/usuarios/'.$user->slug)->assertForbidden();
        $this->delete('/admin/usuarios/'.$user->slug)->assertForbidden();
        $this->put('/admin/usuarios/'.$user->slug.'/activar')->assertForbidden();
        $this->put('/admin/usuarios/'.$user->slug.'/desactivar')->assertForbidden();
    }

    public function testRoutesWithAuthAndPermissions()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $user=factory(User::class)->create();
        $user->assignRole('Cliente');

        $this->get('/admin/usuarios')->assertStatus(200);
        $this->get('/admin/usuarios/registrar')->assertStatus(200);
        $this->get('/admin/usuarios/'.$user->slug)->assertStatus(200);
        $this->get('/admin/usuarios/'.$user->slug.'/editar')->assertStatus(200);
    }

    public function testStore()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'email' => 'admin@gmail.com', 'phone' => '12345678', 'type' => 'Cliente', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->post('/admin/usuarios', $data)->assertRedirect('/admin/usuarios');
        $this->assertDatabaseHas('users', ['email' => 'admin@gmail.com']);
    }

    public function testEditUserAuth()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $this->get('/admin/usuarios/'.$user->slug.'/editar')->assertRedirect('/admin/perfil/editar');
    }

    public function testUpdate()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'email' => 'admin@gmail.com', 'phone' => '12345678', 'type' => 'Cliente', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->post('/admin/usuarios', $data)->assertRedirect('/admin/usuarios');
        $this->assertDatabaseHas('users', ['name' => 'Nuevo', 'email' => 'admin@gmail.com']);

        $data=array('name' => 'Editado', 'lastname' => 'Usuario', 'phone' => '12345678', 'type' => 'Cliente', 'state' => '1');
        $this->put('/admin/usuarios/nuevo-usuario', $data)->assertRedirect('/admin/usuarios/nuevo-usuario/editar');
        $this->assertDatabaseHas('users', ['name' => 'Editado', 'email' => 'admin@gmail.com']);
    }

    public function testDestroy()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $user=factory(User::class)->create();

        $this->delete('/admin/usuarios/'.$user->slug)->assertRedirect('/admin/usuarios');
        $this->assertSoftDeleted('users', ['slug' => $user->slug]);
    }

    public function testActivate()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $user=factory(User::class)->create();

        $this->put('/admin/usuarios/'.$user->slug.'/activar')->assertRedirect('/admin/usuarios');
    }

    public function testDeactivate()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $user=factory(User::class)->create();

        $this->put('/admin/usuarios/'.$user->slug.'/desactivar')->assertRedirect('/admin/usuarios');
    }

    public function testValidateStore()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        // Validación de campos requeridos
        $data=array('name' => '', 'lastname' => '', 'email' => '', 'phone' => '', 'type' => '', 'password' => '', 'password_confirmation' => '');
        $this->post('/admin/usuarios', $data)->assertSessionHasErrors(['name', 'lastname', 'email', 'phone', 'type', 'password']);

        // Validación de campos con cantidad mínima de caracteres
        $data=array('name' => 'N', 'lastname' => 'U', 'email' => 'admin@gmail.com', 'phone' => '1234', 'type' => 'Cliente', 'password' => '1234567', 'password_confirmation' => '1234567');
        $this->post('/admin/usuarios', $data)->assertSessionHasErrors(['name', 'lastname', 'phone', 'password']);

        // Validación de campos con cantidad máxima de caracteres
        $email="";
        $string="";
        for ($i=0; $i < 30; $i++) { 
            $email.="gmailgun";
        }
        for ($i=0; $i < 36; $i++) { 
            $string.="Esta es una frase de prueba.";
        }
        $data=array('name' => $string, 'lastname' => $string, 'email' => 'admin@'.$email.'.com', 'phone' => $string, 'type' => 'Cliente', 'password' => $string, 'password_confirmation' => $string);
        $this->post('/admin/usuarios', $data)->assertSessionHasErrors(['name', 'lastname', 'email', 'phone', 'password']);

        // Validación de campos valores validos
        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'email' => 'admin@gmail.com', 'phone' => '12345678', 'type' => 'Clientee', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->post('/admin/usuarios', $data)->assertSessionHasErrors('type');

        // Validación de campos email valido
        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'email' => 'admin@gmail.', 'phone' => '12345678', 'type' => 'Cliente', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->post('/admin/usuarios', $data)->assertSessionHasErrors('email');

        // Validación de campos email unico
        $user=factory(User::class)->create(['email' => 'correo@gmail.com']);
        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'email' => 'correo@gmail.com', 'phone' => '12345678', 'type' => 'Cliente', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->post('/admin/usuarios', $data)->assertSessionHasErrors('email');

        // Validación de campos confirmacion de contraseña
        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'email' => 'admin@gmail.com', 'phone' => '12345678', 'type' => 'Cliente', 'password' => '12345678', 'password_confirmation' => '12345679');
        $this->post('/admin/usuarios', $data)->assertSessionHasErrors('password');
    }

    public function testValidateUpdate()
    {
        $this->permissionsCreate();

        $user=factory(User::class)->create();
        $user->assignRole('Super Admin');

        Auth::login($user);

        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'email' => 'admin@gmail.com', 'phone' => '12345678', 'type' => 'Cliente', 'password' => '12345678', 'password_confirmation' => '12345678');
        $this->post('/admin/usuarios', $data)->assertRedirect('/admin/usuarios');
        $this->assertDatabaseHas('users', ['name' => 'Nuevo', 'email' => 'admin@gmail.com']);

        // Validación de campos requeridos
        $data=array('name' => '', 'lastname' => '', 'phone' => '', 'type' => '', 'state' => '');
        $this->put('/admin/usuarios/nuevo-usuario', $data)->assertSessionHasErrors(['name', 'lastname', 'phone', 'type', 'state']);

        // Validación de campos con cantidad mínima de caracteres
        $data=array('name' => 'N', 'lastname' => 'U', 'phone' => '1234', 'type' => 'Cliente', 'state' => '1');
        $this->put('/admin/usuarios/nuevo-usuario', $data)->assertSessionHasErrors(['name', 'lastname', 'phone']);

        // Validación de campos con cantidad máxima de caracteres
        $string="";
        for ($i=0; $i < 36; $i++) { 
            $string.="Esta es una frase de prueba.";
        }
        $data=array('name' => $string, 'lastname' => $string, 'phone' => $string, 'type' => 'Cliente', 'state' => '1');
        $this->put('/admin/usuarios/nuevo-usuario', $data)->assertSessionHasErrors(['name', 'lastname', 'phone']);

        // Validación de campos valores validos
        $data=array('name' => 'Nuevo', 'lastname' => 'Usuario', 'phone' => '12345678', 'type' => 'Clientee', 'state' => '10');
        $this->put('/admin/usuarios/nuevo-usuario', $data)->assertSessionHasErrors(['type', 'state']);
    }

    public function permissionsCreate() {
        // Permiso para Acceder al Panel Administrativo
        Permission::create(['name' => 'dashboard']);

        // Permisos de Usuarios
        Permission::create(['name' => 'users.index']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.show']);
        Permission::create(['name' => 'users.edit']);
        Permission::create(['name' => 'users.delete']);
        Permission::create(['name' => 'users.active']);
        Permission::create(['name' => 'users.deactive']);

        $superadmin=Role::create(['name' => 'Super Admin']);
        $superadmin->givePermissionTo(Permission::all());
        
        $admin=Role::create(['name' => 'Administrador']);
        $admin->givePermissionTo(Permission::all());

        $client=Role::create(['name' => 'Cliente']);
        $client->givePermissionTo(['dashboard']);
    }
}
