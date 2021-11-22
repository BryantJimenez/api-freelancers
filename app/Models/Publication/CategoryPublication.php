<?php

namespace App\Models\Publication;

use Illuminate\Database\Eloquent\Model;

class CategoryPublication extends Model
{
    protected $table = 'category_publication';

    protected $fillable = ['category_id', 'publication_id'];
}
