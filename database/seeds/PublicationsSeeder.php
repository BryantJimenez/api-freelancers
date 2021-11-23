<?php

use App\Models\Publication\Publication;
use App\Models\Publication\CategoryPublication;
use Illuminate\Database\Seeder;

class PublicationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Publication::class, 100)->create();

        $publications=Publication::get();
        foreach($publications as $publication) {
            factory(CategoryPublication::class)->create(['publication_id' => $publication->id]);
        }
    }
}
