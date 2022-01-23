<?php

use App\Models\User;
use App\Models\Chat\ChatRoom;
use App\Models\Chat\RoomUser;
use Illuminate\Database\Seeder;

class ChatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ChatRoom::class, 10)->create(['state' => '1']);

        $chat_rooms=ChatRoom::with(['publication.freelancer.user'])->get();
        foreach ($chat_rooms as $chat) {
            factory(RoomUser::class, 1)->create(['user_id' => $chat['publication']['freelancer']['user']->id, 'chat_room_id' => $chat->id]);
            $user=User::where('id', '!=', $chat['publication']['freelancer']['user']->id)->inRandomOrder()->first();
            factory(RoomUser::class, 1)->create(['user_id' => $user->id, 'chat_room_id' => $chat->id]);
        }
    }
}
