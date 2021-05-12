<?php

namespace App\Linker;
use Validator,Redirect,Response, Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;
use App\Model\Linker;
use App\Model\Track;
use App\User;
use General;
use App\Model\Package;
use App\Linker\LinksGen;

class Linkers {
    public static function url($link, $parms = []){
        $slug = $parms['slug'] ?? NULL;
        $linkers = new Linkers();
        $link = $linkers->Urlscheme($link);
        $linkGen = new LinksGen($link, $slug);
        $parms['slug'] = $linkGen->link->slug;
        return route('linker', $parms);
    }

    public static function use($slug, $parms = []){
        if (! $link = Linker::where('slug', $slug)->first()) {
            $link = Linker::create([
                'url'   => $parms['url'],
                'slug'  => $slug ?? $this->randomString(6),
            ]);
        }
        unset($parms['url']);
        $parms['slug'] = $link->slug;

        return route('linker', $parms);
    }

    public static function frameUse($slug, $parms = []){
        if (! $link = Linker::where('slug', $slug)->first()) {
            $link = Linker::create([
                'url'   => $parms['url'],
                'slug'  => $slug ?? $this->randomString(6),
            ]);
        }
        unset($parms['url']);
        $parms['url'] = $link->slug;
        $user = User::where('id', $link->user)->first();
        return route('frame-route', $parms);
    }

    public function randomString($length = 10){
        return Str::random($length);
    }

    function Urlscheme($url, $scheme = 'https://') {
        return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
    }
}
