<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'options';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'user', 'product', 'type', 'name', 'is_required', 'is_global', 'order',
    ];
}
