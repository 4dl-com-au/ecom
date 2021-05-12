<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Product_Reviews extends Model
{
    protected $table = 'product_reviews';

    protected $casts = [
        'extra'        => 'object',
        'review'       => 'object',
    ];
}
