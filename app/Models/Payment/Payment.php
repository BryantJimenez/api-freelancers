<?php

namespace App\Models\Payment;

use App\Models\User;
use App\Models\Currency;
use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['subject', 'total', 'fee', 'balance', 'method', 'state', 'currency', 'user_id'];

    /**
     * Get the method.
     *
     * @return string
     */
    public function getMethodAttribute($value)
    {
        if ($value=='1') {
            return 'Tarjeta';
        }
        return 'Desconocido';
    }

    /**
     * Get the state.
     *
     * @return string
     */
    public function getStateAttribute($value)
    {
        if ($value=='2') {
            return 'Pendiente';
        } elseif ($value=='1') {
            return 'Confirmado';
        } elseif ($value=='0') {
            return 'Rechazado';
        }
        return 'Desconocido';
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function stripe() {
        return $this->hasOne(Stripe::class);
    }
}
