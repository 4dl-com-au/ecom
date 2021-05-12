<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Product_Orders extends Model
{
    protected $table = 'product_orders';

    protected $casts = [
        'products'     => 'array',
        'extra'        => 'object',
        'details'      => 'object',
    ];
}
