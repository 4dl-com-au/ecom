<?php

namespace App\Http\Controllers;

use Validator,Redirect,Response,Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;
use App\Model\Track;
use App\Model\TrackLinks;
use App\Model\Linker;
use App\User;
use General;
use App\Model\Package;
use Location;

class RedirectController extends Controller{

    # devine general settings
    private $settings;
    
    # construct
    public function __construct(){
        $general = new General();
        $this->settings = $general->settings();
    }

    public function linkerRedirect($slug, Linker $link, Request $request){
        if (!$linker = $link->where('slug', $slug)->first()) {
            abort(404);
        }
        $user = User::where('username', $request->get('ref'))->first();
        $this->linkertrack($user->id ?? NULL, $request, $linker->slug);
        return redirect($linker->url);
    }

    public function linkertrack($user, $request, $slug = Null, $type = 'profile'){
        $agent = new Agent();
        $general = new General();
        $track = new TrackLinks();
        $visitor_id = Str::random(14);
        $session  = $request->session();
        $visitor  = $session->get('visitor_id');
        if (empty($visitor)) {
            $request->session()->put('visitor_id', $visitor_id);
        }

        if ($linkertrack = $track->where('visitor_id', $visitor)->where('slug', $slug)->where('type', $type)->first()) {
            $update = $track->find($linkertrack->id);
            $update->user       = $user;
            $update->views = ($linkertrack->views + 1);
            $update->updated_at = Carbon::now($this->settings->timezone);
            $update->save();
        }else{
            $track->user       = $user;
            $track->type       = $type; 
            $track->slug       = $slug;
            $track->visitor_id = $visitor;
            $track->country    = (!empty(Location::get($general->getIP())->countryCode) ? Location::get($general->getIP())->countryCode : Null);
            $track->ip         = $request->ip();
            $track->os         = $agent->platform();
            $track->browser    = $agent->browser();
            $track->views      = 1;
            $track->created_at = Carbon::now($this->settings->timezone);
            $track->save();
        }
    }

    public function frame(Request $request, $url){
        if (Links::where('url_slug', $url)->count() > 0) {
            $link = Links::where('url_slug', $url)->first();
            $user = User::where('id', $link->user)->first();
            $this->linkertrack($user->id ?? NULL, $request, $link->url_slug);
            return view('index.frame-redirect', ['link' => $link, 'user' => $user]);
        }else{
            abort(404);
        }
    }


    public function link(Request $request, $url){
        if (Links::where('url_slug', $url)->count() > 0) {
            $link = Links::where('url_slug', $url)->first();
            $user = User::where('id', $link->user)->first();
            $this->track($user, $request, $link->id, 'link');
            return Redirect::to($link->url);
        }else{
            abort(404);
        }
    }

    public function track($user, $request, $dyid = Null, $type = 'profile'){
        $agent = new Agent();
        $visitor_id = md5(microtime());
        $general = new General();
        $request->session()->put('visitor', 1);
        $session  = $request->session();
        if (empty($session->get('visitor_id'))) {
            $request->session()->put('visitor_id', $visitor_id);
        }

        if (Track::where('visitor_id', $session->get('visitor_id'))->where('dyid', $dyid)->where('type', $type)->count() > 0 ) {
            $track = Track::where('visitor_id', $session->get('visitor_id'))->where('dyid', $dyid)->where('type', $type)->first();
            $values = array('count' => ($track->count + 1), 'date' => Carbon::now($this->settings->timezone));
            Track::where('visitor_id', $session->get('visitor_id'))->update($values);
        }else{
            $values = array('user' => $user->id, 'visitor_id' => $session->get('visitor_id'), 'country' => (!empty(Location::get($general->getIP())->countryCode)) ? Location::get($general->getIP())->countryCode : Null, 'type' => $type, 'dyid' => $dyid, 'ip' => $request->ip(), 'os' => $agent->platform(), 'browser' => $agent->browser(), 'referer' => $request->url(), 'count' => 1, 'date' => Carbon::now($this->settings->timezone));
            Track::insert($values);
        }
    }

    public function randomShortname($min = 3, $max = 9) {
      $length = rand($min, $max);
      $chars = array_merge(range("a", "z"), range("A", "Z"), range("0", "9"));
      $max = count($chars) - 1;
      $url = '';
      for($i = 0; $i < $length; $i++) {
        $char = random_int(0, $max);
        $url .= $chars[$char];
      }
      return $url;
    }
}
