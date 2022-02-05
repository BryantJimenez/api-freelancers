<?php

namespace App\Models\Publication;

use App\Models\Favorite;
use App\Models\Category;
use App\Models\Freelancer\Freelancer;
use App\Models\Chat\ChatRoom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Publication extends Model implements Searchable
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
        $publication=$this->with(['freelancer.user', 'categories'])->where($field, $value)->first();
        if (!is_null($publication)) {
            return $publication;
        }

        return abort(404);
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(191)->doNotGenerateSlugsOnUpdate();
    }

    public function getSearchResult(): SearchResult
    {
        $url=url('api/v1/publications', [$this->id]);
        return new SearchResult($this, $this->name, $url);
    }

    public function freelancer() {
        return $this->belongsTo(Freelancer::class);
    }

    public function categories() {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function favorites() {
        return $this->hasMany(Favorite::class);
    }

    public function chats() {
        return $this->hasMany(ChatRoom::class);
    }
}
