<?php

namespace App\Models\Retreat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retreat extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['subject', 'amount', 'method', 'state', 'currency', 'user_id'];

    /**
     * Get the method.
     *
     * @return string
     */
    public function getMethodAttribute($value)
    {
        if ($value=='1') {
            return 'PayPal';
        }
        return 'Unknown';
    }

    /**
     * Get the state.
     *
     * @return string
     */
    public function getStateAttribute($value)
    {
        if ($value=='2') {
            return 'Pending';
        } elseif ($value=='1') {
            return 'Paid';
        } elseif ($value=='0') {
            return 'Cancelled';
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
        $retreat=$this->with(['user', 'paypal'])->where($field, $value)->first();
        if (!is_null($retreat)) {
            return $retreat;
        }

        return abort(404);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function paypal() {
        return $this->hasOne(PaypalRetreat::class);
    }
}
