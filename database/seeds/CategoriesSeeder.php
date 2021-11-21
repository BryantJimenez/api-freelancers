<?php

use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
    		['name' => 'PHP', 'slug' => 'php', 'state' => '1'],
    		['name' => 'Javascript', 'slug' => 'javascript', 'state' => '1'],
    		['name' => 'Java', 'slug' => 'java', 'state' => '1'],
    		['name' => 'CSS', 'slug' => 'css', 'state' => '1']
    	];
    	DB::table('categories')->insert($categories);
    }
}
