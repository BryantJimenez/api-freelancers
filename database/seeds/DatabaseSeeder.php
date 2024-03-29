<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountriesTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(CategoriesSeeder::class);
        $this->call(FreelancersSeeder::class);
        $this->call(PublicationsSeeder::class);
        $this->call(ChatsSeeder::class);
        $this->call(ProposalsSeeder::class);
        $this->call(SettingsSeeder::class);
    }
}
