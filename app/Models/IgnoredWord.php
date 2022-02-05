<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class IgnoredWord extends Model
{
    use HasSlug;

    protected $table = 'ignored_words';

    protected $fillable = ['words', 'slug'];

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $word=$this->where($field, $value)->first();
        if (!is_null($word)) {
            return $word;
        }

        return abort(404);
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('words')->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(191);
    }
}
