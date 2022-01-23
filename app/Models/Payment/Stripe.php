<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class Stripe extends Model
{
    protected $fillable = ['stripe_payment_id', 'balance_transaction', 'payment_id'];

    public function payment() {
        return $this->belongsTo(Payment::class);
    }
}
