<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
        	'name' => 'Super',
            'lastname' => 'Admin',
            'username' => 'super_admin',
        	'photo' => 'usuario.png',
        	'slug' => 'super-admin',
        	'email' => 'admin@gmail.com',
        	'password' => bcrypt('12345678'),
        	'state' => "1"
        ]);
    }
}
