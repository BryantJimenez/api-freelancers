<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Model;

class CategoryFreelancer extends Model
{
    protected $table = 'category_freelancer';

    protected $fillable = ['category_id', 'freelancer_id'];
}
