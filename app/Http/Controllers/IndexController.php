<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Mail\GeneralMail;
use Carbon\Carbon;
use App\Model\Settings;
use App\User;
use App\Model\Pages;
use App\Model\Packages;
use App\Model\Domains;
use App\Model\Track;
use General;
use GuzzleHttp\Client;
use App\Model\Pages_Category;

class IndexController extends Controller{
    #|--------------------------------------------------------------------------
    #| PREV PROFILE BUILDER
    #|--------------------------------------------------------------------------


    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the front acepect of this script including the front page and pages
    |
    */

    # devine general settings
    private $settings;
    
    # construct
    public function __construct(){
        # get all from GENERAL CONTROLLER
        $general = new General();

        # move general settings into variable
        $this->settings = $general->settings();
    }

    # all pages funtion
    public function pages(Request $request){
        # get all category
        $categories = Pages_Category::leftJoin('pages', 'pages.category', '=', 'pages_categories.id')
                ->select('pages_categories.*', DB::raw("count(pages.category) AS count_pages"))
                ->where('pages_categories.status', 1)
                ->groupBy('pages_categories.id')
                ->orderBy('order', 'ASC')
                ->orderBy('id', 'DESC')
                ->get();


        $pages = Pages::leftJoin('pages_categories', 'pages_categories.id', '=', 'pages.category')
                ->select('pages.*', 'pages_categories.title as pages_categories_name', 'pages_categories.url as pages_categories_url')
                ->where('pages.status', 1)
                ->orderBy('order', 'ASC')
                ->orderBy('id', 'DESC');

        if ($query = $request->get('query')) {
            $pages->where('pages.title', 'LIKE','%'.$query.'%');
        }

        if ($category = $request->get('category')) {
            $pages->where('pages.category', 'LIKE','%'.$category.'%');
        }


        $pages = $pages->get();



        # get the popular pages
        $popular_pages = Pages::where('status', 1)->orderBy('total_views', 'DESC')->limit(4)->get();

        # view page
        return view('index.pages.pages', ['categories' => $categories, 'pages' => $pages, 'popular_pages' => $popular_pages]);
    }
    # pages categories funtion
    public function innerpages($slug){
        # check if category exists
        if (!Pages_Category::where('url', $slug)->exists()) {
            abort(404);
        }

        # get pages and categories
        $get = Pages_Category::where('url', $slug)->first();
        $pages = Pages::where('status', 1)
        ->where('category', $get->id)
        ->orderBy('order', 'ASC')
        ->orderBy('id', 'DESC')
        ->get();

        # view page
        return view('index.pages.innerpages', ['category' => $get, 'pages' => $pages]);
    }

    # page funtion
    public function page($page, Request $request){
        # check if page exists
        if (!Pages::where('url', $page)->where('status', 1)->where('type', 'internal')->exists()) {
            abort(404);
        }

        # get pages and categories
        $page = Pages::where('status', 1)->where('url', $page)->first();
        $get = Pages_Category::where('id', $page->category)->first();
        $page->settings = json_decode($page->settings);

        # track page
        $this->track_pages($page->url, $request);

        # view page
        return view('index.pages.page', ['category' => $get, 'page' => $page]);
    }

    # track pages
    public function track_pages($slug, $request){
        # get page 
        $page = Pages::where('url', $slug)->first();
        $page = Pages::find($page->id);
        # define session
        $session  = $request->session();
        # check if visits exisits in session and preceed
        if (empty($session->get('page_visit_' . $slug))) {
            # update current page with new page visits
            $page->total_views = ($page->total_views + 1);
            $page->save();
        }
        # add visits to session
        $session->put('page_visit_' . $slug, 'true');
    }

    #index funtion
    public function index(Track $track){
        $fromYear = Carbon::now(config('settings.timezone'))->startOfYear();
        if (Schema::hasTable('domains')) {
            $domains = Domains::where('status', 1)->get();
            foreach ($domains as $item) {
                if ($_SERVER['HTTP_HOST'] == $item->host) {
                    if (!empty($item->index_url)) {
                        redirect($item->index_url)->send();
                    }
                }
            }
        }


        $pages = Pages::leftJoin('pages_categories', 'pages_categories.id', '=', 'pages.category')
                ->select('pages.*', 'pages_categories.title as pages_categories_name', 'pages_categories.url as pages_categories_url')
                ->where('pages.status', 1)
                ->orderBy('order', 'ASC')
                ->orderBy('id', 'DESC')->get();
        $demo_user = User::where('id', settings('user.demo_user'))->first();
        # define greetings
        $greeting = Carbon::now(settings('timezone'));
        # greentings by hours
        if($greeting->hour < 12) {
            $greeting = 'Good morning';
        }elseif ($greeting->hour >= "12" && $greeting->hour < "17") {
            $greeting = 'Good afternoon';
        }elseif ($greeting->hour >= "17" && $greeting->hour < "19") {
            $greeting = "Good evening";
        }elseif ($greeting->hour >= "19") {
            $greeting = "Good day";
        }
        # check if is a custom home and redirect if needed
        if (!empty($this->settings->custom_home)) {
            return redirect($this->settings->custom_home);
        }
        # get two packages for home page 
        $packages = Packages::where('status', 1)->get();
        $newPackages = [];

        $free = settings('package_free');

        $trial = settings('package_trial');
        if (settings('package_free.status') == 1) {
            $newPackages[] = (object) $free;
        }

        if (settings('package_trial.status') == 1) {
            if (Auth::check() && !auth()->user()->package_trial_done) {
                $newPackages[] = (object) $trial;
            }
        }

        foreach ($packages as $value) {
            $newPackages[] = $value;
        }
        # view page
        return view('index.index', ['packages' => $newPackages, 'greeting' => $greeting, 'demo_user' => $demo_user, 'pages' => $pages]);
    }

    # pricing funtion
    public function pricing(){


        # get all active packages
        $packages = Packages::where('status', 1)->get();
        $newPackages = [];

        $free = settings('package_free');

        $trial = settings('package_trial');

        if (settings('package_free.status') == 1) {
         $newPackages[] = (object) $free;
        }

        if (settings('package_trial.status') == 1) {
            if (Auth::check() && !auth()->user()->package_trial_done) {
                $newPackages[] = (object) $trial;
            }
        }
        foreach ($packages as $value) {
            $newPackages[] = $value;
        }
        # view page
        return view('index.pricing', ['packages' => $newPackages]);
    }

    # post contact funtion
    public function contact(Request $request){
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
        # define message to be sent
        $message = " <p> FirstName: $request->firstname </p> <br> <p> LastName: $request->lastname </p> <br><p> Email Address: $request->email </p> <br><p> Subject: $request->subject </p> <br><p> Message: $request->message </p> <br>";
        # define email subject and message
        $email = (object) array('subject' => 'Contact us', 'message' => $message);
        # check if admin has email
        if (!empty($this->settings->email)) {
             # send the email 
            try {
              Mail::to($this->settings->email)->send(new GeneralMail($email));
           } catch (\Exception $e) {
              return back()->with('error', 'technical issue. could not send email.');
            }
        }
        # return with success
        return back()->with('success', 'Message sent!');
    }
}
