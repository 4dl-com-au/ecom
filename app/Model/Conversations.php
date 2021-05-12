<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Storage, Crypt;

class Conversations extends Model{
    protected $table = 'conversations';


    protected $casts = [
        'extra'   => 'object',
    ];
}
