<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class RoomUser extends Model
{
    protected $table = 'room_user';

    protected $fillable = ['user_id', 'chat_room_id'];
}
