<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Category;
use App\Models\Publication\Publication;
use App\Models\Publication\CategoryPublication;
use Faker\Generator as Faker;

$factory->define(CategoryPublication::class, function (Faker $faker) {
    $categories=Category::all()->pluck('id');
	$publications=Publication::all()->pluck('id');
    return [
        'category_id' => $faker->randomElement($categories),
        'publication_id' => $faker->randomElement($publications)
    ];
});
