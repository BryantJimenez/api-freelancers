<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Proposal;
use App\Models\Publication\Publication;
use Faker\Generator as Faker;

$factory->define(Proposal::class, function (Faker $faker) {
	$publications=Publication::all()->pluck('id');
	$publication_id=$faker->randomElement($publications);
    $publication=Publication::with(['freelancer.user'])->where('id', $publication_id)->first();
    $receiver_id=$publication['freelancer']['user']->id;
    $owner=User::where('id', '!=', $receiver_id)->inRandomOrder()->first();
    $owner_id=$owner->id;
    return [
        'start' => $faker->dateTimeBetween('now', '+ 2 days')->format('Y-m-d'),
        'end' => $faker->dateTimeBetween('+ 7 days', '+ 2 months')->format('Y-m-d'),
        'content' => $faker->text($maxNbChars=1000),
        'amount' => $sale_price=$faker->randomFloat(2, 30, 1000),
        'state' => $faker->randomElement(['2', '1', '0']),
        'owner_id' => $owner_id,
        'receiver_id' => $receiver_id,
        'publication_id' => $publication_id
    ];
});
