<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Freelancer\Freelancer;
use Faker\Generator as Faker;

$factory->define(Freelancer::class, function (Faker $faker) {
	$users=User::all()->pluck('id');
    return [
        'description' => $faker->sentence(20),
        'user_id' => $faker->randomElement($users)
    ];
});
