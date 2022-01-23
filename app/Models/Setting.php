<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['stripe_public', 'stripe_secret', 'paypal_public', 'paypal_secret'];
}
