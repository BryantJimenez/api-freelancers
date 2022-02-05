<?php

namespace App\Models;

use App\Models\Freelancer\Freelancer;
use App\Models\Publication\Publication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Category extends Model implements Searchable
{
    use SoftDeletes, HasSlug;

    protected $fillable = ['name', 'slug', 'order', 'state', 'category_id'];

    /**
     * Get the state.
     *
     * @return string
     */
    public function getStateAttribute($value)
    {
        if ($value=='1') {
            return 'Active';
        } elseif ($value=='0') {
            return 'Inactive';
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
        $category=$this->with(['parent', 'childrens'])->where($field, $value)->first();
        if (!is_null($category)) {
            return $category;
        }

        return abort(404);
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(191)->doNotGenerateSlugsOnUpdate();
    }

    public function getSearchResult(): SearchResult
    {
        $url=url('api/v1/categories', [$this->id]);
        return new SearchResult($this, $this->name, $url);
    }

    public function parent() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function childrens() {
        return $this->hasMany(Category::class);
    }

    public function freelancers() {
        return $this->belongsToMany(Freelancer::class)->withTimestamps();
    }

    public function publications() {
        return $this->belongsToMany(Publication::class)->withTimestamps();
    }
}
