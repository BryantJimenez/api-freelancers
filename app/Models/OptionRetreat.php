<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OptionRetreat extends Model
{
    use SoftDeletes;

    protected $table = 'option_retreats';

    protected $fillable = ['paypal_email', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
