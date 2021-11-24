<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = ['price', 'state', 'pay_state', 'proposal_id'];

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
        $project=$this->where($field, $value)->first();
        if (!is_null($project)) {
            return $project;
        }

        return abort(404);
    }

    public function proposal() {
        return $this->belongsTo(Proposal::class);
    }
}
