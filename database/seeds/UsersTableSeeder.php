<?php

use App\Models\User;
use App\Models\OptionRetreat;
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
        	'state' => "1",
            'country_id' => "4"
        ]);
        factory(User::class, 5)->create(['state' => '1']);
        factory(User::class, 5)->create([
            'state' => '1',
            'country_id' => NULL
        ]);

        factory(OptionRetreat::class, 1)->create(['paypal_email' => 'admin@gmail.com', 'user_id' => '1']);
        $users=User::where('id', '!=', '1')->get();
        foreach ($users as $user) {
            factory(OptionRetreat::class, 1)->create(['paypal_email' => NULL, 'user_id' => $user->id]);
        }
    }
}
