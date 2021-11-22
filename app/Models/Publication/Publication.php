<?php

namespace App\Models\Publication;

use App\Models\Category;
use App\Models\Freelancer\Freelancer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Publication extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = ['name', 'slug', 'description', 'content', 'state', 'freelancer_id'];

    /**
     * Get the state start.
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
        $publication=$this->with(['categories'])->where($field, $value)->first();
        if (!is_null($publication)) {
            return $publication;
        }

        return abort(404);
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(191);
    }

    public function freelancer() {
        return $this->belongsTo(Freelancer::class);
    }

    public function categories() {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }
}
