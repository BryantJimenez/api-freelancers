<?php

namespace App\Models;

use App\Models\Freelancer\Freelancer;
use App\Models\Chat\ChatRoom;
use App\Models\Chat\ChatMessage;
use App\Models\Payment\Payment;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Laravel\Passport\HasApiTokens;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles, SoftDeletes, HasSlug, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'lastname', 'username', 'photo', 'slug', 'email', 'password', 'state', 'country_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

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
        $user=$this->with(['roles'])->where($field, $value)->first();
        if (!is_null($user)) {
            return $user;
        }

        return abort(404);
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('username')->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(191)->doNotGenerateSlugsOnUpdate();
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function freelancer() {
        return $this->hasOne(Freelancer::class);
    }

    public function favorites() {
        return $this->hasMany(Favorite::class);
    }

    public function my_proposals() {
        return $this->hasMany(Proposal::class, 'owner_id');
    }

    public function received_proposals() {
        return $this->hasMany(Proposal::class, 'receiver_id');
    }

    public function chats() {
        return $this->belongsToMany(ChatRoom::class, 'room_user')->withTimestamps();
    }

    public function messages() {
        return $this->hasMany(ChatMessage::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }
}
