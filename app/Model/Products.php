<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';

    protected $casts = [
        'categories'   => 'object',
        'media'        => 'object',
        'variation'    => 'object',
        'extra'        => 'object',
    ];
}
