<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
	use SoftDeletes;

    protected $fillable = ['name', 'code', 'native_name', 'state'];

    /**
     * Get the state.
     *
     * @return string
     */
    public function getStateAttribute($value)
    {
        return ($value=='1') ? 'Active' : 'Inactive';
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
        $language=$this->where($field, $value)->first();
        if (!is_null($language)) {
            return $language;
        }

        return abort(404);
    }

    public function freelancers() {
        return $this->belongsToMany(Freelancer\Freelancer::class)->withTimestamps();
    }
}
