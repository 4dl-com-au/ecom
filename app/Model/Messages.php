<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Storage, Crypt;

class Messages extends Model{
    protected $table = 'messages';


    protected $casts = [
        'extra'   => 'object',
    ];
}
