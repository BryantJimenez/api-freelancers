<?php

namespace App\Models\Freelancer;

use App\Models\User;
use App\Models\Language;
use App\Models\Category;
use App\Models\Publication\Publication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Freelancer extends Model
{
    use SoftDeletes;

    protected $fillable = ['description', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function languages() {
        return $this->belongsToMany(Language::class)->withTimestamps();
    }

    public function categories() {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function publications() {
        return $this->hasMany(Publication::class);
    }
}
