<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
	use SoftDeletes;

    protected $table = 'chat_message';

    protected $fillable = ['message', 'read', 'user_id', 'chat_room_id'];

    /**
     * Get the read.
     *
     * @return string
     */
    public function getReadAttribute($value)
    {
        if ($value=='1') {
            return 'Read';
        } elseif ($value=='0') {
            return 'No Read';
        }
        return 'Unknown';
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function chat_room() {
        return $this->belongsTo(ChatRoom::class);
    }
}
