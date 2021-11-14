<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Specialization extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = ['name', 'slug', 'state'];

    /**
     * Get the state start.
     *
     * @return string
     */
    public function getStateAttribute($value)
    {
        return ($value=='1') ? 'Activo' : 'Inactivo';
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
        $specialization=$this->where($field, $value)->first();
        if (!is_null($specialization)) {
            return $specialization;
        }

        return abort(404);
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(191);
    }
}
