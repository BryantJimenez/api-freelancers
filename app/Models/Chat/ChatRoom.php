<?php

namespace App\Models\Chat;

use App\Models\User;
use App\Models\Publication\Publication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ChatRoom extends Model
{
	use SoftDeletes, HasSlug;

    protected $table = 'chat_room';

    protected $fillable = ['name', 'slug', 'state', 'publication_id'];

    /**
     * Get the state.
     *
     * @return string
     */
    public function getStateAttribute($value)
    {
        if ($value=='1') {
            return 'Active';
        } elseif ($value=='0') {
            return 'Inactive';
        }
        return 'Unknown';
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
        $category=$this->with(['publication', 'users', 'messages'])->where($field, $value)->first();
        if (!is_null($category)) {
            return $category;
        }

        return abort(404);
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(191)->doNotGenerateSlugsOnUpdate();
    }

    public function publication() {
        return $this->belongsTo(Publication::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'room_user')->withTimestamps();
    }

    public function proposals() {
        return $this->hasMany(Proposal::class);
    }

    public function messages() {
        return $this->hasMany(ChatMessage::class);
    }
}
