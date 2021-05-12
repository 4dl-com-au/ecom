<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Product_Category extends Model
{
    protected $table = 'product_categories';

    protected $casts = [
        'extra'        => 'object',
    ];
}
