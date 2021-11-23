<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Category;
use App\Models\Freelancer\Freelancer;
use App\Models\Freelancer\CategoryFreelancer;
use Faker\Generator as Faker;

$factory->define(CategoryFreelancer::class, function (Faker $faker) {
    $categories=Category::all()->pluck('id');
	$freelancers=Freelancer::all()->pluck('id');
    return [
        'category_id' => $faker->randomElement($categories),
        'freelancer_id' => $faker->randomElement($freelancers)
    ];
});
