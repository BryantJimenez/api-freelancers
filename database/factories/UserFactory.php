<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Country;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
	$countries=Country::all()->pluck('id');
    return [
        'name' => $faker->firstName,
        'lastname' => $faker->lastName,
        'username' => $faker->unique()->word,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => Hash::make('12345678'),
        'remember_token' => Str::random(10),
        'state' => $faker->randomElement(['1', '0']),
        'country_id' => $faker->randomElement($countries)
    ];
});
