<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PagesSections extends Model
{
    protected $table = 'pages_sections';

    protected $casts = [
        'extra'        => 'object',
        'data'         => 'object',
    ];
}
