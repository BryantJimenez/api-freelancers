<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Proposal;
use App\Models\Chat\ChatRoom;
use Faker\Generator as Faker;

$factory->define(Proposal::class, function (Faker $faker) {
	$chat_rooms=ChatRoom::all()->pluck('id');
	$chat_room_id=$faker->randomElement($chat_rooms);
    $chat_room=ChatRoom::with(['publication.freelancer.user'])->where('id', $chat_room_id)->first();
    $receiver_id=$chat_room['publication']['freelancer']['user']->id;
    $owner=User::where('id', '!=', $receiver_id)->inRandomOrder()->first();
    $owner_id=$owner->id;
    return [
        'start' => $faker->dateTimeBetween('now', '+ 2 days')->format('Y-m-d'),
        'end' => $faker->dateTimeBetween('+ 7 days', '+ 2 months')->format('Y-m-d'),
        'content' => $faker->text($maxNbChars=1000),
        'amount' => $faker->randomFloat(2, 30, 1000),
        'state' => $faker->randomElement(['2', '1', '0']),
        'owner_id' => $owner_id,
        'receiver_id' => $receiver_id,
        'chat_room_id' => $chat_room_id
    ];
});
