<?php

namespace App\Models\Payment;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['subject', 'total', 'fee', 'balance', 'method', 'type', 'state', 'currency', 'user_id'];

    /**
     * Get the method.
     *
     * @return string
     */
    public function getMethodAttribute($value)
    {
        if ($value=='1') {
            return 'Card';
        } elseif ($value=='2') {
            return 'PayPal';
        }
        return 'Unknown';
    }

    /**
     * Get the type.
     *
     * @return string
     */
    public function getTypeAttribute($value)
    {
        if ($value=='1') {
            return 'Wallet';
        } elseif ($value=='2') {
            return 'Project';
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
            return 'Confirmed';
        } elseif ($value=='0') {
            return 'Rejected';
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
        $payment=$this->with(['user'])->where($field, $value)->first();
        if (!is_null($payment)) {
            return $payment;
        }

        return abort(404);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function stripe() {
        return $this->hasOne(Stripe::class);
    }

    public function paypal() {
        return $this->hasOne(Paypal::class);
    }

    public function project() {
        return $this->hasOne(Project::class);
    }
}
