<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserPages extends Model
{
    protected $table = 'user_pages';

    protected $casts = [
        'extra'        => 'object',
    ];

    public function childs() {
        return $this->hasMany('App\Model\UserPages','parent','id') ;
    }
}
