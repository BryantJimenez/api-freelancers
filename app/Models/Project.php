<?php

namespace App\Models;

use App\Models\Payment\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = ['start', 'end', 'content', 'amount', 'state', 'pay_state', 'user_id', 'employer_id', 'proposal_id', 'payment_id'];

    /**
     * Get the state.
     *
     * @return string
     */
    public function getStateAttribute($value)
    {
    	if ($value=='2') {
    		return 'In Process';
    	} elseif ($value=='1') {
    		return 'Finalized';
    	}
        return 'Cancelled';
    }

    /**
     * Get the pay state.
     *
     * @return string
     */
    public function getPayStateAttribute($value)
    {
    	if ($value=='2') {
    		return 'Pending';
    	} elseif ($value=='1') {
    		return 'Paid';
    	}
        return 'Unpaid';
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
        $project=$this->with(['user', 'employer', 'proposal', 'payment'])->where($field, $value)->first();
        if (!is_null($project)) {
            return $project;
        }

        return abort(404);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employer() {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function proposal() {
        return $this->belongsTo(Proposal::class);
    }

    public function payment() {
        return $this->belongsTo(Payment::class);
    }
}
