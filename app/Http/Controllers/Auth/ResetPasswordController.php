<?php

namespace App\Http\Controllers\Auth;

use Validator,Redirect,Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Mail\ResetPassword;
use App\User;
use General;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */
    function __construct(){
        $this->middleware(function ($request, $next) {
            if(!file_exists(storage_path('installed'))) {
                return redirect()->route('install');
            }
            return $next($request);
        });
        if(file_exists(storage_path('installed'))) {
          $general = new General();
          $this->settings = $general->settings();
        }
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function validatePasswordRequest(Request $request)
    {
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
        $user = User::where('email', '=', $request->email)->get(); //Check if the user exists
        if (count($user) < 1) {
            return redirect()->back()->withErrors(['email' => trans('User does not exist')]);
        }
        DB::table('password_resets')->where('email', $request->email)->delete();
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => md5(time().rand()),
            'created_at' => Carbon::now()
        ]);
        $tokenData = DB::table('password_resets')->where('email', $request->email)->first();

        $user = User::where('email', $request->email)->select('name', 'email')->first();
        $content = (object) array('name' => full_name($user->id), 'email' => $user->email, 'token' =>  $tokenData->token);
        try {
        Mail::to($user->email)->send(new ResetPassword($content));
            return back()
                    ->with('success', 'A reset link has been sent to your email address.');
       } catch (\Exception $e) {
            return back()->with('error', 'technical issue. could not send email.');
        }
    }

    public function resetPassword(Request $request){
        //Validate input
        $request->validate([
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'
        ]);

        $password = $request->password;// Validate the token
        $token    = $request->token;
        $tokenData = DB::table('password_resets')->where('token', $token)->first();
        if (!$tokenData) return back()->withErrors(['error' => 'Fatal error!']);

        $user = User::where('email', $tokenData->email)->first();
        // Redirect the user back if the email is invalid
        if (!$user) return redirect()->back()->withErrors(['email' => 'Email not found']);

        //Hash and update the new password
        $user->password = Hash::make($password);
        $user->update(); //or $user->save();

        //login the user immediately they change password successfully
        Auth::login($user);

        //Delete the token
        DB::table('password_resets')->where('email', $user->email)->delete();

        return redirect()->route('user-dashboard')
                    ->with('success', 'Successfully');
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
}
