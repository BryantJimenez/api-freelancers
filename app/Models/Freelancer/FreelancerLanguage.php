<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Model;

class FreelancerLanguage extends Model
{
    protected $table = 'freelancer_language';

    protected $fillable = ['language_id', 'freelancer_id'];
}
