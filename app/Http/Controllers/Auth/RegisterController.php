<?php

namespace App\Http\Controllers\Auth;

use Validator,Redirect,Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Ausi\SlugGenerator\SlugGenerator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Mail\ActivateEmail;
use App\Mail\GeneralMail;
use App\User;
use General;

class RegisterController extends Controller{
    #|--------------------------------------------------------------------------
    #| PREV PROFILE BUILDER
    #|--------------------------------------------------------------------------

    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this fundctionality without requiring any additional code.
    |
    */
    public function showRegistrationForm(){
        if (!settings('registration')) {
            return redirect()->route('login')->with('error', 'No registration allowed');
        }
        return view('auth.register');
    }


    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    # devine general settings
    private $settings;
    
    # construct
    public function __construct(){
        # if it's a guest
        $this->middleware('guest');
    }

    public function register(Request $request){
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
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        return redirect()->route('login')->with('success', __('Registration successful, please login with registered credentials'));

        Auth::login($user);
        
        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return redirect()->route('user-dashboard');
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data){
        return Validator::make($data, [
            'first_name'      => ['required', 'string', 'max:191'],
            'last_name'      => ['required', 'string', 'max:191'],
            'email'     => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'username'  => ['required', 'string', 'max:20'],
            'password'  => ['required', 'string', 'min:8', 'confirmed', 'regex:/[@$!%*#?&]/', 'regex:/[A-Z]/', 'regex:/[a-z]/'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function create(array $data){
      $general = new General();
      $slugGenerator = new SlugGenerator;
      $username  = $maybe_slug = slugify($data['username']);
      $next = '_';
      while (User::where('username', '=', $username)->first()) {
          $username = "{$maybe_slug}{$next}";
          $next = $next . '_';
      }
      $name = ['first_name' => $data['first_name'], 'last_name' => $data['last_name']];
      $create = User::create([
        'name'          => $name,
        'email'         => $data['email'],
        'username'      => $username,
        'password'      => Hash::make($data['password']),
      ]);
      generatePages($create->id);
      subdomain_wildcard_creation($create->id);
      if (settings('email_activation')) {
        $code = md5(microtime());
        User::where('id', $create->id)->update(['active' => 0, 'email_token' => $code]);
        $user = User::where('id', $create->id)->first();
        try {
         Mail::to($create->email)->send(new ActivateEmail($user));
        } catch (\Exception $e) {
            return back()->with('error', 'technical error. could not send email.');
         }
      }
      if (settings('email_notify.user')) {
           $emails = settings('email_notify.emails');
           $emails = explode(',', $emails);
           $emails = str_replace(' ', '', $emails);
           $email = (object) array('subject' => __('New user registration'), 'message' => 'New user registration on your site with the username: ' . $create->username);
           try {
            Mail::to($emails)->send(new GeneralMail($email));
           } catch (\Exception $e) {

          }
      }
      return $create;
    }
}
