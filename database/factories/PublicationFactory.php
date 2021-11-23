<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Freelancer\Freelancer;
use App\Models\Publication\Publication;
use Faker\Generator as Faker;

$factory->define(Publication::class, function (Faker $faker) {
	$freelancers=Freelancer::all()->pluck('id');
	return [
        'name' => $faker->sentence($nbWords=4),
        'description' => $faker->text($maxNbChars=200),
        'content' => $faker->text($maxNbChars=1000),
        'state' => $faker->randomElement(['1', '0']),
        'freelancer_id' => $faker->randomElement($freelancers)
	];
});
