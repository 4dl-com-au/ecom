<?php

namespace App\Http\Controllers\Admin;

use Validator, Redirect, Response, uri, File,  Storage, Crypt;
use Ausi\SlugGenerator\SlugGenerator;
use Camroncade\Timezone\Facades\Timezone;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Redirector;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Mail\GeneralMail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Model\Settings;
use App\User;
use App\Model\Pages;
use App\Model\Pages_Category;
use App\Model\Faq;
use App\Model\PendingPayments;
use App\Model\Payments;
use App\Model\Packages;
use App\Model\Track;
use GuzzleHttp\Client;
use App\Model\Domains;
use App\Model\Locale;
use App\Model\Products;
use App\Model\Product_Category;
use General;

class LoginAsController extends Controller{
    # construct
    public function __construct(){
        # check if user is logged in
        $this->middleware('auth');
    }

    public function admin_login_as_user(Request $request){
        $id = $request->id;
        $user = User::find($id);
        if (!session()->get('admin_overhead')) {
            session()->put('admin_overhead', true);
            if (Auth()->user()->role == 1) {
                session()->put('admin_overhead_user', Auth()->user()->id);
            }
        }
        Auth::login($user);
        if (isset($request->settings) && $request->settings == 'true') {
            return redirect()->route('user-settings')->with('success', 'Logged in as ' . full_name($user->id));
        }
        return redirect()->route('user-dashboard')->with('success', 'Logged in as ' . full_name($user->id));
    }
}
