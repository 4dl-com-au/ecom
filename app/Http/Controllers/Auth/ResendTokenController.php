<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator,Redirect,Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ActivateEmail;
use Carbon\Carbon;
Use App\User;
use Session;
use General;
class ResendTokenController extends Controller{

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    private $settings;

    public function __construct()
    {
        $general = new General();
        $this->settings = $general->settings();
    }

    public function resendemailtoken(){
        return view('auth.resend-token');
    }

    public function resendemailtoken_send(Request $request){
        if (config('app.captcha_status') && config('app.captcha_type') == 'recaptcha') {
            $messages = [
                'g-recaptcha-response.recaptcha' => 'Invalid recaptcha response',
            ];
            $request->validate([
                'g-recaptcha-response' => 'recaptcha',
            ], $messages);
        }
        if (config('app.captcha_status') && config('app.captcha_type') == 'default') {
            $messages = [
                'captcha.captcha' => 'Invalid captcha',
                'captcha.required' => 'Captcha is required',
            ];
            $request->validate([
                'captcha' => 'required|captcha',
            ], $messages);
        }
        # Check if the user exists
        if (!User::where('email', $request->email)->exists()) {
            return redirect()->back()->withErrors(['email' => trans('User does not exist')]);
        }
        # Get user details
        $user = User::where('email', $request->email)->first(); 

        # Update token in DB
        $code = md5(microtime());
        User::where('id', $user->id)->update(['active' => 0, 'email_token' => $code]);

        $Tuser = User::where('email', $request->email)->first(); 
        # Send to mail
        try {
        Mail::to($Tuser->email)->send(new ActivateEmail($Tuser));
       } catch (\Exception $e) {
            return back()->with('error', 'Technical issue. Could not send email.');
        }

        return back()->with('success', 'Email activation sent.');
    }
}
