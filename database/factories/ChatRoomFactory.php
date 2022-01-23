<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Chat\ChatRoom;
use App\Models\Publication\Publication;
use Faker\Generator as Faker;

$factory->define(ChatRoom::class, function (Faker $faker) {
	$publication=Publication::inRandomOrder()->first();
    return [
        'name' => $publication->name,
        'state' => $faker->randomElement(['1', '0']),
        'publication_id' => $publication->id
    ];
});
