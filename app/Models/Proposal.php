<?php

namespace App\Models;

use App\Models\Publication\Publication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use SoftDeletes;

    protected $fillable = ['start', 'end', 'content', 'amount', 'state', 'receiver_id', 'owner_id', 'chat_room_id'];

    /**
     * Get the state.
     *
     * @return string
     */
    public function getStateAttribute($value)
    {
    	if ($value=='2') {
    		return 'In Process';
    	} elseif ($value=='1') {
    		return 'Accepted';
    	}
        return 'Cancelled';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $proposal=$this->with(['owner', 'receiver', 'chat_room.publication.categories', 'chat_room.publication.freelancer.user'])->where($field, $value)->first();
        if (!is_null($proposal)) {
            return $proposal;
        }

        return abort(404);
    }

    public function receiver() {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function chat_room() {
        return $this->belongsTo(ChatRoom::class);
    }

    public function project() {
        return $this->hasOne(Project::class);
    }
}
