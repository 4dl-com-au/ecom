<?php

namespace App\Linker;
use Str;
use App\Model\Linker;

class LinksGen{
    public $link;
    public function __construct($url, $slug){
        if (! $link = Linker::where('url', $url)->first()) {
            $link = Linker::create([
                'url'   => $url,
                'slug'  => $slug ?? $this->randomString(6),
            ]);
        }
        $this->link = $link;
    }

    public function randomString($length = 10){
        return Str::random($length);
    }
}
