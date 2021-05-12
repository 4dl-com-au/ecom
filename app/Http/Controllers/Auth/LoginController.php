<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Validator,Redirect,Response;
Use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Session;
use Jenssegers\Agent\Agent;
use Illuminate\Validation\ValidationException;
use General;
class LoginController extends Controller{

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


    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if(!file_exists(storage_path('installed'))) {
                return redirect()->route('install');
            }
            return $next($request);
        });
        $this->middleware('guest')->except('logout');
        if(file_exists(storage_path('installed'))) {
          $general = new General();
          $this->settings = $general->settings();
        }
    }


    private $settings;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    public function showLoginForm(Request $request)
    {
        $reset = $request->get('reset-password');
        $email = $request->get('email');
        $token = $request->get('token');
        if ($reset == true && !empty($email) && !empty($token)) {
            if (DB::table('password_resets')->where('email', $email)->where('token', $token)->count() > 0) {
                $user = User::where('email', $email)->first();
                return view('auth.passwords.confirm', ['user' => $user, 'token' => $token]);
            }
        }
        return view('auth.login');
    }

    public function login(Request $request){
        $agent = new Agent();
        if (config('app.captcha_status') && config('app.captcha_type') == 'recaptcha') {
            $messages = [
                'g-recaptcha-response.recaptcha' => __('Invalid recaptcha response'),
            ];
            $request->validate([
                'g-recaptcha-response' => 'recaptcha',
            ], $messages);
        }
        if (config('app.captcha_status') && config('app.captcha_type') == 'default') {
            $messages = [
                'captcha.captcha' => __('Invalid captcha'),
                'captcha.required' => __('Captcha is required'),
            ];
            $request->validate([
                'captcha' => 'required|captcha',
            ], $messages);
        }
        $this->validateLogin($request);
        if ($this->attemptLogin($request)) {

            # Check maintenance mode
            if (!empty($this->settings->maintenance) && $this->settings->maintenance->enabled && Auth()->user()->role !== 1) {
                $this->logout($request);
                return back()->with('info', __('Our website is currently undergoing scheduled maintenance. Please check back later.'));
            }
            # Authentication passed...
            $values = array('user' => Auth()->user()->id, 'ip' => $request->ip(), 'os' => $agent->platform(), 'browser' => $agent->browser(), 'what' => 'login successfully', 'date' => Carbon::now(settings('timezone')));
            DB::table('users_logs')->insert($values);
            $activity = array('last_activity' => Carbon::now(settings('timezone')), 'last_agent' => $agent->platform());
            User::where('id', Auth()->user()->id)->update($activity);

            if (!user('first_welcome_screen')) {
              return redirect()->route('user-first-welcome')->with('success', __('Hey, welcome to our platform. Here are what you can do.'));
            }


            return redirect()->route('user-dashboard')->with('success', __("You're welcome") .' '. full_name(user('id')));
        }else{
            $field = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            if (DB::table('users')->where($field, $request->user)->count() > 0) {
                $user = DB::table('users')->where($field, $request->user)->first();
                $values = array('user' => $user->id, 'ip' => $request->ip(), 'os' => $agent->platform(), 'browser' => $agent->browser(), 'what' => 'invalid password', 'date' => Carbon::now(settings('timezone')));
                DB::table('users_logs')->insert($values);
            }
        }

        return back()->with('error', __('Oppes! Invalid credentials'));
    }

    protected function validateLogin(Request $request)
    {
        $messages = [
            'user.required' => 'Email or username cannot be empty',
            'email.exists'      => 'Email or username already registered',
            'username.exists'   => 'Username is already registered',
            'password.required' => 'Password cannot be empty',
        ];

        $request->validate([
            'user' => 'required|string',
            'password' => 'required|string',
            'email' => 'string|exists:users',
            'username' => 'string|exists:users',
        ], $messages);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        $login = request()->input('user');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $login]);

        return $field;
    }


    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request){
        $admin_user = session()->get('admin_overhead_user');
        if (!empty($admin_user) && isset($request->returnAdmin)) {
            $user = User::find($admin_user);
            $request->session()->forget('admin_overhead');
            Auth::login($user);
            return redirect()->route('admin-users')->with('success', __('Welcome back'));
        }
        $request->session()->forget('admin_overhead');
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

}
