<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class Paypal extends Model
{
	protected $fillable = ['paypal_payer_id', 'paypal_payment_id', 'data', 'payment_id'];

	/**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
	protected $casts = [
		'data' => 'array'
	];

	public function payment() {
		return $this->belongsTo(Payment::class);
	}
}
