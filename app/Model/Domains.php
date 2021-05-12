<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Storage, Crypt;

class Domains extends Model{
    protected $table = 'domains';
}
