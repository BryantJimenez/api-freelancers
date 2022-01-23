<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Chat\ChatRoom;
use App\Models\Chat\RoomUser;
use Faker\Generator as Faker;

$factory->define(RoomUser::class, function (Faker $faker) {
	$users=User::all()->pluck('id');
	$chat_rooms=ChatRoom::all()->pluck('id');
    return [
        'user_id' => $faker->randomElement($users),
        'chat_room_id' => $faker->randomElement($chat_rooms)
    ];
});
