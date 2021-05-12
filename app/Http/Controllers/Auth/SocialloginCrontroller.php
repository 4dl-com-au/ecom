<?php

namespace App\Http\Controllers\Auth;

use Validator, Redirect, Response, Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Ausi\SlugGenerator\SlugGenerator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Mail\ActivateEmail;
use App\Mail\GeneralMail;
use App\User;
use General;

class SocialloginCrontroller extends Controller{
    #|--------------------------------------------------------------------------
    #| PREV PROFILE BUILDER
    #|--------------------------------------------------------------------------

    public function redirectToGoogle(){
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(User $user){
        $general = new General();
        $slugGenerator = new SlugGenerator;
        try {
            $google = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $google->id)->first();
            if ($finduser) {
                Auth::login($finduser);
                return redirect('dashboard');
            }else{
                $thisuser = User::where('email', $google->email)->first();
                if (!$thisuser) {
                    $username  = $maybe_slug = slugify($google->name, '_');
                    $next = '_';
                    while (User::where('username', '=', $username)->first()) {
                        $username = "{$maybe_slug}{$next}";
                        $next = $next . '_';
                    }
                    $create = User::create([
                      'name'          => ['first_name' => $google->name, 'last_name' => ''],
                      'email'         => $google->email,
                      'username'      => $username,
                      'google_id'     => $google->id,
                      'extra'         => ['template' => env('DEFAULT_TEMPLATE', 'zoa')],
                      'password'      => Hash::make('123456dummy'),
                    ]);
                    subdomain_wildcard_creation($create->id);
                    Auth::login($create);
                    return redirect()->route('user-first-welcome')->with('success', __('Hey, welcome to our platform. Here are what you can do.'));
                }else{
                    $user = User::find($thisuser->id);
                    $user->google_id = $google->id;
                    Auth::login($user);
                }
                return redirect('dashboard');
            }
        } catch (\Exception $e) {
            return redirect()->route('login');
        }
    }

    public function handleFacebookCallback(User $user){
        $general = new General();
        $slugGenerator = new SlugGenerator;
        try {
            $facebook = Socialite::driver('facebook')->user();
            $finduser = User::where('facebook_id', $facebook->id)->first();
            if ($finduser) {
                Auth::login($finduser);
                return redirect('dashboard');
            }else{
                $thisuser = User::where('email', $facebook->email)->first();
                if (!$thisuser) {
                    $username  = $maybe_slug = slugify($facebook->name, '_');
                    $next = '_';
                    while (User::where('username', '=', $username)->first()) {
                        $username = "{$maybe_slug}{$next}";
                        $next = $next . '_';
                    }
                    $create = User::create([
                      'name'          => ['first_name' => $facebook->name, 'last_name' => ''],
                      'email'         => $facebook->email,
                      'username'      => $username,
                      'facebook_id'   => $facebook->id,
                      'extra'         => ['template' => env('DEFAULT_TEMPLATE', 'zoa')],
                      'password'      => Hash::make('123456dummy'),
                    ]);
                    subdomain_wildcard_creation($create->id);
                    Auth::login($create);
                    return redirect()->route('user-first-welcome')->with('success', __('Hey, welcome to our platform. Here are what you can do.'));
                }else{
                    $user = User::find($thisuser->id);
                    $user->facebook_id = $facebook->id;
                    Auth::login($user);
                }
                return redirect('dashboard');
            }
        } catch (\Exception $e) {
            return redirect()->route('login');
        }
    }


    public function redirectToFacebook(){
        return Socialite::driver('facebook')->redirect();
    }


    private function login_user($user){
        return Auth::login($user);
    }
}
