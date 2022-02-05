<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\OptionRetreat;
use Faker\Generator as Faker;

$factory->define(OptionRetreat::class, function (Faker $faker) {
    $users=User::all()->pluck('id');
    return [
        'paypal_email' => $faker->safeEmail,
        'user_id' => $faker->randomElement($users)
    ];
});
