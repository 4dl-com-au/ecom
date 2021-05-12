<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Musonza\Chat\Traits\Messageable;
use Illuminate\Support\Facades\DB;
use Storage, Crypt;

class Customers extends Model{
	use Messageable;
	
    protected $table = 'customers';


    protected $casts = [
        'details'   => 'object',
    ];
}
