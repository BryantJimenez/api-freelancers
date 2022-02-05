<?php

namespace App\Models\Retreat;

use Illuminate\Database\Eloquent\Model;

class PaypalRetreat extends Model
{
	protected $table = 'paypal_retreat';

    protected $fillable = ['email', 'retreat_id'];

    public function retreat() {
        return $this->belongsTo(Retreat::class);
    }
}
