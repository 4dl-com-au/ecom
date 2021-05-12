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

class AdminController extends Controller{
   # devine general settings
    private $settings;
    private $code;
    
    # construct
    public function __construct(){
        # check if user is logged in
        $this->middleware('auth');
        # check if user is admin
        $this->middleware('admin');

         # get all from GENERAL CONTROLLER
         $general = new General();
         $this->middleware(function ($request, $next) {
            if (env('APP_DEMO') && isset($request->_token)) {
                return back()->with('error', 'Option not available in demo mode');
            }
            return $next($request);
         });
         if (file_exists(storage_path('app/.code'))) {
            try {
                $this->code = json_decode(Crypt::decryptString(Storage::get('.code')));
            } catch (\Exception $e) {
                $this->code = null;
            }
         }
         # move general settings into variable
         $this->settings = $general->settings();
    }

    # Admin home function
    public function home_admin(User $user, Track $track){
        $general = new General();
        $settings = $general->settings();
        # get previous month start date
        $fromDate = Carbon::now($this->settings->timezone)->subDay()->startOfWeek()->toDateString();
        $thisMonth = Carbon::now($this->settings->timezone)->startOfMonth()->toDateString();
        $fromYear = Carbon::now($this->settings->timezone)->startOfYear();

        # get previous month end date
        $tillDate = Carbon::now($this->settings->timezone)->subDay()->toDateString();

        # get total visits of current month
        $total_visits = Track::select(\DB::raw("COUNT(*) as count"))->groupBy(\DB::raw("Month(date)"))->first();

        # get all payment
        $payments   = DB::select("SELECT COUNT(*) AS `payment`, IFNULL(TRUNCATE(SUM(`price`), 2), 0) AS `earnings` FROM `payments` WHERE `currency` = '{$settings->currency}' AND MONTH(`date`) = MONTH(CURRENT_DATE()) AND YEAR(`date`) = YEAR(CURRENT_DATE())");

        # get all users
        $users = DB::select('SELECT (SELECT COUNT(*) FROM `users` WHERE MONTH(`last_activity`) = MONTH(CURRENT_DATE()) AND YEAR(`last_activity`) = YEAR(CURRENT_DATE())) AS `active_users_month`,
              (SELECT COUNT(*) FROM `users`) AS `all_users`');

        # get new users from last month stat date
        $newusers = User::select(DB::raw('*, DATE(`created_at`) as `date`'), DB::raw('COUNT(*) as `count`'))
                    ->where('created_at', '>=', $fromDate)
                    ->groupBy('email')
                    ->limit(4)
                    ->orderBy('date', 'DESC')
                    ->get();

        $topUsers = $track
        ->leftJoin('users', 'users.id', '=', 'track.user')
        ->select('users.name', 'users.username', 'users.id as user_id', DB::raw('count(user) as total'))
        ->groupBy('user')
        ->where('track.date', '>=', $fromYear)
        ->orderBy('total', 'DESC')
        ->limit(5)
        ->get();

        # Payments Chart
        $paymentschart = [];
        $results = Payments::select(\DB::raw("COUNT(*) as count, DATE_FORMAT(`date`, '%Y-%m-%d') AS `formatted_date`, TRUNCATE(SUM(`price`), 2) AS `amount`"))
        ->where('date', '>=', $thisMonth)
        ->groupBy('formatted_date')
        ->get();

        foreach ($results as $value) {
            $value->formatted_date = Carbon::parse($value->formatted_date)->toFormattedDateString();
            $paymentschart[$value->formatted_date] = [
                'sales' => $value->count,
                'amount' => $value->amount
            ];
        }
        $sidebarR = (object) ['countPackages' => Packages::count()];
        /*
        if (!$settings->registration) {
            $messageSetting = 'You can enable user registration here';
        }elseif (!$settings->) {
            # code...
        }*/

        $paymentschart = $general->get_chart_data($paymentschart);

        # get current user
        $user = Auth::user();

        # define array of counters
        $count = ['total_visits' => $total_visits, 'users' => count($users)];

        # view page with array
        return view('admin.admin', ['count' => $count, 'newusers' => $newusers, 'topusers' => $topUsers, 'payments' => $payments, 'user' => $user, 'paymentschart' => $paymentschart, 'users' => $users, 'sidebarR' => $sidebarR]);
    }

    public function all_product(){
      $user = Auth::user();
      $products = Products::orderBy('id', 'DESC')->paginate(12);
      return view('admin.products.all', ['products' => $products]);
    }

    public function edit_product($id){
      if (!$product = Products::find($id)) {
        abort(404);
      }
      $categories = Product_Category::where('user', $product->user)->get();
      return view('admin.products.edit', ['product' => $product, 'categories' => $categories]);
    }

    public function admin_login_as_user($id, Request $request){
        $user = User::find($id);
        Auth::login($user);
        if ($request->settings == true) {
            if (!session()->get('admin_overhead')) {
                session()->put('admin_overhead', true);
                return redirect()->route('user-settings')->with('success', 'Logged in as ' . full_name($user->id));
            }
        }
        return redirect()->route('user-dashboard')->with('success', 'Logged in as ' . full_name($user->id));
    }

    public function admin_create_user(){
        return view('admin.users.add-new');
    }

    public function admin_create_user_post(Request $request){
        $slugGenerator = new SlugGenerator;
        $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => 'required|string|max:191|unique:users',
            'username' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);
        $username  = $maybe_slug = $slugGenerator->generate($request->username);
        $next = '_';
        while (User::where('username', '=', $username)->first()) {
            $username = "{$maybe_slug}{$next}";
            $next = $next . '_';
        }
        $name = ['first_name' => $request->first_name, 'last_name' => $request->last_name];
        $create = User::create([
          'name'          => $name,
          'email'         => $request->email,
          'username'      => $username,
          'password'      => Hash::make($request->password),
        ]);
        return redirect()->route('admin-users')->with('success', 'New user created');
    }

    public function domains(Domains $domains){
        $allDomains = $domains->leftJoin('users', 'users.domain', '=', 'domains.id')->select('domains.*', DB::raw("count(users.domain) AS total_domain"))->groupBy('domains.id')->get();
        return view('admin.domains.domains', ['domains' => $allDomains]);
    }

    public function domains_post_get(Domains $domains, Request $request){
        $domain_id = $request->get('id');
        $domain = null;
        if ($request->get('delete') == true) {
            $domains->find($request->get('id'))->delete();
            return back()->with('success', __('Deleted successfully'));
        }
        if (!empty($domain_id) && $domain = $domains->where('id', $domain_id)->first()) {
        }

        return view('admin.domains.post-domain', ['domain' => $domain]);
    }

    public function domains_post(Domains $domains, Request $request){
        if (!empty($this->code) && is_object($this->code) && $this->code->license !== 'Extended License') {
            #return back()->with('error', 'License needed or Extended license needed. Kindly visit admin - updates to update your license');
        }
        $parse_env_url = parse_url(env('APP_URL'))['host'];
        if ($request->host == $parse_env_url) {
            return back()->with('error', __('You cant add main domain'));
        }
        $request->validate([
            'scheme' => 'required',
        ]);
        if (!isset($request->domain_id)) {
            $request->validate([
                'host' => 'required|unique:domains',
            ]);
        }
        if (isset($request->user)) {
            $requests['user'] = $request->user;
        }
        $requests = $request->all();
        unset($requests['_token'], $requests['submit'], $requests['domain_id']);
        $requests['created_at'] = Carbon::now($this->settings->timezone);
        if (isset($request->domain_id)) {
            $request->validate([
                'host' => 'required|unique:domains,host,'.$request->domain_id,
            ]);
            unset($requests['created_at']);
            $requests['updated_at'] = Carbon::now($this->settings->timezone);
            $update = $domains->where('id', $request->domain_id)->update($requests);
            return back()->with('success', __('Domain updated successfully'));
        }
        $new = $domains->insert($requests);
        if (isset($request->user)) {
            return redirect()->route('user-domains')->with('success', __('Domain created successfully'));
        }
        return redirect()->route('admin-domains')->with('success', __('Domain created successfully'));
    }

    public function admin_trans(Request $request){
       $trans = $request->get('trans');
       $locale = config('app.locale');
       try {
           $transpath = file_get_contents(resource_path('lang/'.config('app.locale').'.json'));
       } catch (\Exception $e) {
           $transpath = NULL;
       }
       if (!empty($trans)) {
           if (file_exists(resource_path('lang/'.$trans.'.json'))) {
            $locale = $trans;
            $transpath = file_get_contents(resource_path('lang/'.$trans.'.json'));
           }else{
             return redirect()->route('admin-translation');
           }
       }
       $transpath = json_decode($transpath, true);
       !empty($transpath) ? asort($transpath) : '';
       $path = resource_path('lang');
       $alltransfiles = File::files($path);

       return view('admin.translation.trans', ['alltrans' => $alltransfiles, 'trans_files' => $transpath, 'locale' => $locale]);
    }

    public function admin_post_trans(Request $request, $type){
        $slugGenerator = new SlugGenerator;
        if (file_exists(resource_path('lang/'.$request->trans.'.json'))) {
            $transpath = file_get_contents(resource_path('lang/'.$request->trans.'.json'));
            $transpath = json_decode($transpath);
        }
        if ($type == 'post') {
            $transpath->{$request->key} = $request->value;
            file_put_contents(resource_path('lang/'.$request->trans.'.json'), json_encode($transpath));
        }elseif ($type == 'delete') {
            unset($transpath->{$request->key});
            file_put_contents(resource_path('lang/'.$request->trans.'.json'), json_encode($transpath));
        }elseif ($type == 'edit') {
            unset($transpath->{$request->previous_key});
            $transpath->{$request->key} = $request->value;
            file_put_contents(resource_path('lang/'.$request->trans.'.json'), json_encode($transpath));
        }elseif ($type == 'new') {
            if (file_exists(resource_path('lang/'.strtolower($request->name).'.json'))) {
                return back()->with('error', __('Translation file exists'));
            }
            file_put_contents(resource_path('lang/'.$slugGenerator->generate($request->name, ['delimiter' => '_']).'.json'), '{}');
        }elseif ($type == 'delete-trans') {
            unlink(resource_path('lang/'.strtolower($request->trans).'.json'));
            return back()->with('success', 'Saved successfully');
        }elseif ($type == 'set-active') {
            $update_env = [
                 'APP_LOCALE'            => $request->locale,
            ];
            $this->changeEnv($update_env);
        }elseif ($type == 'edit-trans-name') {
            File::move(resource_path('lang/'.$request->trans.'.json'), resource_path('lang/'.$request->trans_name.'.json'));
            return redirect()->route('admin-translation', ['trans' => $request->trans_name])->with('success', __('Saved successfully'));
        }elseif ($type == 'copy') {
            File::copy(resource_path('lang/'.$request->trans.'.json'), resource_path('lang/'.$request->trans.'_copy.json'));
        }
        return back()->with('success', 'Saved successfully');
    }

    # settings function
    public function settings(Domains $domains){
        $path = resource_path('lang');
        $alltransfiles = File::files($path);
        $domains    = $domains->where('status', 1)->get();
        # get all timezone and of current admin user
        $timezone_select = Timezone::selectForm((!empty($this->settings->timezone) ? $this->settings->timezone : "Africa/Lagos"), '', ['class' => 'form-select', 'name' => 'timezone', 'data-ui' => 'lg', 'data-search' => 'on']);

        # view page with array
        return view('admin.settings', ['timezone' => $timezone_select, 'alltransfiles' => $alltransfiles, 'domains' => $domains]);
    }

    public function delete_category(Request $request){
        if (strtoupper($request->delete) !== strtoupper("delete")) {
            # redirect to page with error
            return back()->with('error', 'Word not correct');
        }
        if (!Pages_Category::where('id', $request->category_id)->exists()) {
            return back()->with('Category does not exists');
        }
        $category = Pages_Category::find($request->category_id);
        $category->delete();
        return redirect()->route('category')->with('success', 'That category was successfully deleted');
    }

    # categroy function
    public function category() {
       # get all categories
       $categories = Pages_Category::get();

        # view page with array
       return view('admin.page.category.all', ['categories' => $categories]);
    }
    # add category function
    public function add_category() {
        # view page
        return view('admin.page.category.add');
    }
    # post category function
    public function post_category(Request $request) {
        $general = new General();
        $slug = $general->generate_slug($request->url);
        # get values from request
        $value = array('title' => $request->title, 'description' => $request->description, 'icon' => $request->icon, 'url' => $slug, 'order' => $request->order, 'status' => $request->status, 'created_at' => Carbon::now(settings('timezone')));

        # insert category
        Pages_Category::insert($value);

        # redirect to categories page with success
        return redirect()->route('category')->with("success", "saved successfully");
    }

    # edit category function
    public function edit_category($id) {
        #check if category exists and continue
        if (Pages_Category::where('id', $id)->count() > 0) {
            #get current category
            $category = Pages_Category::where('id', $id)->first();
            #view page with array
            return view('admin.page.category.edit', ['category' => $category]);
        }else{
            abort(404, 'Not Found');
        }
    }

    # edit post category
    public function edit_post_category(Request $request) {
        $general = new General();
        $slug = $general->generate_slug($request->url);
        
        # get values from request
        $value = array('title' => $request->title, 'description' => $request->description, 'icon' => $request->icon, 'url' => $slug, 'order' => $request->order, 'status' => $request->status, 'updated_at' => Carbon::now(settings('timezone')));

        # update category
        Pages_Category::where('id', $request->category_id)->update($value);
        return back()->with("success", "saved successfully");
    }


    
    # faq function
    public function faq() {
        #get faq
        $faqs = Faq::get();
        #view page with array
        return view('admin.faq', ['faqs' => $faqs]);
    }

    # post faq
    public function post_faq(Request $request) {
        # get values from request
        $value = array('name' => $request->name, 'note' => $request->note, 'status' => $request->status, 'created_at' => Carbon::now(settings('timezone')));

        # insert Faq
        Faq::insert($value);
        # redirect to faq page with success
        return redirect()->route('faq')->with("success", "saved successfully");
    }

    public function delete_faq(Request $request){
        if (strtoupper($request->delete) !== strtoupper("delete")) {
            # redirect to page with error
            return back()->with('error', 'word not correct');
        }
        if (!Faq::where('id', $request->faq_id)->exists()) {
            return back()->with('faq does not exists');
        }
        $faq = Faq::find($request->faq_id);
        $faq->delete();
        return redirect()->route('faq')->with('success', 'That faq was successfully deleted');
    }

    #edit faq
    public function edit_faq(Request $request) {
        # get values from request
        $value = array('name' => $request->name, 'note' => $request->note, 'status' => $request->status, 'updated_at' => Carbon::now(settings('timezone')));

        # update faq
        Faq::where('id', $request->faq_id)->update($value);

        # redirect to faq page with success
        return back()->with("success", "saved successfully");
    }

    public function send_usermail(Request $request){
        #get user from request
        $user = User::find($request->user_id);
        # get all from GENERAL CONTROLLER
        $general = new General();
        #define shortcodes for subject
        $subject = $request->subject;
        $subject = str_replace("{{username}}", $user->username, $subject);
        $subject = str_replace("{{name}}", full_name($user->id), $subject);
        $subject = str_replace("{{email}}", $user->email, $subject);
        #define shortcode for messages
        $message = $request->message;
        $message = str_replace("{{username}}", $user->username, $message);
        $message = str_replace("{{name}}", full_name($user->id), $message);
        $message = str_replace("{{email}}", $user->email, $message);
        $message = str_replace("{{tagline}}", $user->settings->tagline ?? '', $message);
        $message = str_replace("{{last_login}}", $user->last_activity, $message);
        $message = str_replace("{{package_name}}", package('name', $user->id), $message);
        $message = str_replace("{{count_product}}", Products::where('user', $user->id)->count(), $message);
        $message = str_replace("{{package_due}}", Carbon::parse($user->package_due)->toFormattedDateString(), $message);

        # send the email 
        $email = (object) array('subject' => $subject, 'message' => $message);
        try {
         Mail::to($user->email)->send(new GeneralMail($email));
        } catch (\Exception $e) {
            return back()->with('error', 'could not send email. smtp error.');
         }
        # redirect to page with success
        return back()->with('success', 'Email sent');
    }

    public function view_user ($id, Request $request) {
        $user = User::find($id);
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $thisMonth = Carbon::now()->startOfMonth()->toDateString();
        $thisYear = Carbon::now()->startOfYear()->toDateString();

        if (!$user) {
            abort(404);
        }


        # Sales chart
        $sales_chart = [];
        $sales_chart_fetch = \App\Model\Product_Orders::select(\DB::raw("*, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))->where('storeuser', $user->id)->where('created_at', '>=', $thisMonth)->get();

        foreach ($sales_chart_fetch as $key) {
         foreach ($key->products as $prices => $price) {
           $key->formatted_date = Carbon::parse($key->formatted_date)->toFormattedDateString();
           if(!array_key_exists($key->formatted_date, $sales_chart)) {
               $sales_chart[$key->formatted_date] = [
                   'sales'        => 0,
               ];
           }
           $prices = $price['price'];
           $sales_chart[$key->formatted_date]['sales'] += ($price['qty'] * $prices);
         }
        }
        asort($sales_chart);
        $sales_chart = get_chart_data($sales_chart);



        $month_visits_all = Track::select(\DB::raw("*, MONTH(`date`) AS `month`, DATE_FORMAT(`date`, '%Y-%m-%d') AS `formatted_date`"))->where('user', $user->id)->where('date', '>=', $thisMonth)->get();
        $year = [];
        $month = [];
        $this_month_chart = [];
        foreach ($month_visits_all as $key) {
         if(!array_key_exists($key->month, $month)) {
             $month[$key->month] = [
                 'impression'        => 0,
                 'unique'            => 0,
             ];
         }
         $month[$key->month]['unique']++;
         $month[$key->month]['impression'] += $key->count;
         $key->formatted_date = Carbon::parse($key->formatted_date)->toFormattedDateString();

         if(!array_key_exists($key->formatted_date, $this_month_chart)) {
             $this_month_chart[$key->formatted_date] = [
                 'impression'        => 0,
                 'unique'            => 0,
             ];
         }
         $this_month_chart[$key->formatted_date]['unique']++;
         $this_month_chart[$key->formatted_date]['impression'] += $key->count;
        }
        $allpayments = Payments::where('user', $user->id)->orderBy('id', 'DESC')->paginate(10);
        $this_month_chart = get_chart_data($this_month_chart);

        $options = (object) ['sales_chart' => $sales_chart, 'this_month_chart' => $this_month_chart, 'payments' => $allpayments];


        return view('admin.users.view', ['user' => $user, 'options' => $options]);
    }

    public function view_user_post($id, Request $request){
        $update = User::find($id);
        $update->package = $request->package;
        $update->package_due = $request->package_due;
        $update->active = $request->active;
        $update->save();


        return back()->with('success', __('Saved successfully'));

    }

    public function users(Request $request) {
        #get users
        $users = User::leftJoin('products', 'products.user', '=', 'users.id')
            ->select('users.*', DB::raw("count(products.user) AS total_products"))
            ->groupBy('users.id');
        $query = $request->get('query');
        $type = $request->get('type');
        if (!empty($type) && !empty($query)) {
            if($type == 'email'):
               $users->where('users.email', 'LIKE','%'.$query.'%');
           elseif($type == 'username'):
             $users->where('users.username', 'LIKE','%'.$query.'%');
            endif;
        }

        $users = $users->orderBy('users.id', 'DESC')->paginate(10);


        #view page with array
        return view('admin.users.users', ['users' => $users]);
    }

    public function delete_user(Request $request){
        if ($request->user_id == Auth()->user()->id) {
            return back()->with('error', 'cant delete yourself');
        }
        if (strtoupper($request->delete) !== strtoupper("delete")) {
            # redirect to page with error
            return back()->with('error', 'Word not correct');
        }
        $user = User::find($request->user_id);
        $products = Products::where('user', $user->id)->get();
        $media = $user->media;
        if (!empty($media['banner'])) {
            # check if file exisits
            if(file_exists(media_path('user/banner/' . $media['banner']))){
                #unlink the file
                unlink(media_path('user/banner/' . $media['banner']));
            }
        }
        if (!empty($media['avatar'])) {
            # check if file exisits
            if(file_exists(media_path('user/avatar/' . $media['avatar']))){
                #unlink the file
                unlink(media_path('user/avatar/' . $media['avatar']));
            }
        }
        foreach ($products as $key) {
            if (!empty($key->media)) {
                $media = $key->media;
                foreach ($media as $key => $value) {
                    if(file_exists(media_path('user/products/' . $value))){
                        unlink(media_path('user/products/' . $value)); 
                    }
                }
            }
        }
        foreach (Product_Category::where('user', $user->id)->get() as $key) {
            if (!empty($key->media)) {
                $media = $key->media;
                if(file_exists(media_path('user/categories/' . $media))){
                    unlink(media_path('user/categories/' . $media)); 
                }
            }
        }
        if(file_exists(media_path('user/qrcode/' . $user->username))){
            unlink(media_path('user/qrcode/' . $user->username)); 
        }
        Track::where('user', $request->user_id)->delete();
        Products::where('user', $request->user_id)->delete();
        DB::table('users_logs')->where('user', $request->user_id)->delete();
        $user->delete();
        return back()->with('success', 'That user was deleted');
    }
    // Payments

    public function payments(Request $request) {
        $email = $request->get('email');
        $ref   = $request->get('ref');
        $payments = Payments::leftJoin('users', 'users.id', '=', 'payments.user')
            ->leftJoin('packages', 'packages.id', '=', 'payments.package')
            ->select('users.username', DB::raw("packages.name AS packages_name"), 'payments.*', DB::raw("count(payments.user) AS total_links"), DB::raw('COUNT(*) as `count`'));

        if (!empty($email)) {
            $payments->where('payments.email','LIKE','%'.$email.'%');
        }

        if (!empty($ref)) {
            $payments->where('payments.ref','LIKE','%'.$ref.'%');
        }

        $payments = $payments->groupBy('payments.id')->orderBy('id', 'DESC')->paginate(10);
        #view page with array
        return view('admin.payments.payments', ['payments' => $payments]);
    }

    public function pending_payments(PendingPayments $payments){
        $allpayments = $payments->
                       leftJoin('users', 'users.id', '=', 'pending_payments.user')
                        ->leftJoin('packages', 'packages.id', '=', 'pending_payments.package')
                        ->select('users.username', DB::raw("packages.name AS package_name"), 'pending_payments.*')
                        ->groupBy('pending_payments.id')
                        ->orderBy('id', 'DESC')
                       ->paginate(15);
        $options = (object) ['payments' => $allpayments];
        return view('admin.payments.pending-payments', ['options' => $options]);
    }

    public function activate_pending_payment($type, $id, PendingPayments $payments, User $user){
        if (!$payments->where('id', $id)->exists()) {
            abort(404);
        }
        $pending = $payments->find($id);
        $user = $user->find($pending->user);
        $package = Packages::where('id', $pending->package)->first();
        if ($type == 'approve') {
            $newdue = Carbon::now(settings('timezone'));
            $payment_date = NULL;
            if ($pending->duration == "month") {
                $newdue->addMonths(1);
                $payment_date = $newdue;
            }
            if ($pending->duration == "quarter") {
                $newdue->addMonths(6);
                $payment_date = $newdue;
            }
            if ($pending->duration == "annual") {
                $newdue->addMonths(12);
                $payment_date = $newdue;
            }
            $user->package = $pending->package;
            $user->package_due = $payment_date;
            $user->save();

            $newPayment = new Payments;
            $newPayment->user = $user->id;
            $newPayment->email = $user->email;
            $newPayment->name = full_name($user->id);
            $newPayment->ref = 'PR_'. $this->randomShortname();
            $newPayment->package_name  = $package->name;
            $newPayment->price     = $package->price->{$pending->duration} ?? Null;
            $newPayment->currency  = $this->settings->currency;
            $newPayment->package = $pending->package;
            $newPayment->duration = $pending->duration;
            $newPayment->gateway = "Bank transfer";
            $newPayment->date = Carbon::now(settings('timezone'));
            $newPayment->save();
            
            $pending->status = 1;
            $pending->save();
            return back()->with('success', 'Approved');
        }elseif ($type == 'decline') {
            $pending->status = 2;
            $pending->save();
            return back()->with('success', 'Payment Declined');
        }elseif ($type == 'delete') {
            if (!empty($pending->proof)) {
                if(file_exists(public_path('media/user/bankProof/' . $pending->proof))){
                     unlink(public_path('media/user/bankProof/' . $pending->proof)); 
                }
            }
            $pending->delete();
            return back()->with('success', 'Payment Deleted');
        }
        return back()->with('error', 'Undefined error');
    }


    // Packages

    public function packages() {
        if (empty($this->settings->package_trial)) {
            Settings::insert(['key' => 'package_trial', 'value' => '{"id":"trial","name":"Trial","slug":"trial","status":"1","price":{"month":"FREE","quarter":"FREE","annual":"FREE","expiry":"7"},"settings":{"expiry":true,"ads":true,"branding":true,"custom_branding":true,"statistics":true,"verified":true,"support":true,"social":true,"custom_background":true,"links_style":true,"links":true,"portfolio":true,"domains":true,"google_analytics":true,"facebook_pixel":true,"links_limit":"-1","support_limit":"-1","portfolio_limit":"-1","trial":true}}']);

            return redirect()->route('admin-packages');
        }
        $packages = Packages::leftJoin('users', 'users.package', '=', 'packages.id')
                    ->select('packages.*', DB::raw("count(users.package) AS total_package"))
                    ->groupBy('packages.id')
                    ->get();
        $free_count = User::where('package', 'free')->count();
        $trial_count = User::where('package', 'trial')->count();
        #view page with array
        return view('admin.packages.all', ['packages' => $packages, 'free_count' => $free_count, 'trial_count' => $trial_count]);
    }

    public function create_packages() {
        $general = new General();
        $domains = Domains::where('status', 1)->where('user', null)->get();
        $gateways = getOtherResourceFile('store_gateways');
        return view('admin.packages.create', ['domains' => $domains, 'gateways' => $gateways]);
    }


    public function post_packages(Request $request) {
        $general = new General();

        $form = $request->all();
        unset($form['_token'], $form['package_name'], $form['status'], $form['month'], $form['quarter'], $form['annual'], $form['products_limit'], $form['blogs_limits'], $form['custom_domain_limit']);
        $dataSettings = [];
        foreach ($form as $key => $values) {
            $dataSettings[$key] = (bool) $values;

        }
        $dataSettings['products_limit'] = $request->products_limit;
        $dataSettings['blogs_limits'] = $request->blogs_limits;
        $dataSettings['custom_domain_limit'] = $request->custom_domain_limit;
        $prices = $request->only('month', 'quarter', 'annual');
        $domains = json_encode($request->domains);
        $gateways = json_encode($request->gateways);

        $plan = new Packages();
        $plan->name     = $request->package_name;
        $plan->slug     = $general->generate_slug($request->package_name);
        $plan->status   = $request->status;
        $plan->price    = $prices;
        $plan->domains  = $domains;
        $plan->gateways = $gateways;
        $plan->settings = $dataSettings;
        $plan->created_at     = Carbon::now(settings('timezone'));
        $plan->save();
        return back()->with("success", __('Package created'));
    }
    
    public function edit_package($id) {
        $general = new General();
        if (!Packages::where('id', $id)->exists() && $id !== 'free' && $id !== 'trial') {
            abort(404);
        }
        if($id == 'trial'){
            $package = $this->settings->package_trial;
        }elseif($id == 'free'){
            $package = $this->settings->package_free;
        }else{
            $package = Packages::where('id', $id)->first();
        }
        $gateways = getOtherResourceFile('store_gateways');
        $domains = Domains::where('status', 1)->where('user', null)->get();
        return view('admin.packages.edit', ['package' => $package, 'domains' => $domains, 'gateways' => $gateways]);
    }
    
    public function edit_post_package(Request $request, $id) {
        $general = new General();
        $slugify = new SlugGenerator;
        $form = $request->all();
        unset($form['_token'], $form['package_name'], $form['status'], $form['month'], $form['quarter'], $form['annual'], $form['products_limit'], $form['blogs_limits'], $form['custom_domain_limit']);
        $dataSettings = [];
        foreach ($form as $key => $values) {
            $dataSettings[$key] = (bool) $values;
        }
        $dataSettings['products_limit'] = $request->products_limit;
        $dataSettings['blogs_limits'] = $request->blogs_limits;
        $dataSettings['custom_domain_limit'] = $request->custom_domain_limit;
        $domains = json_encode($request->domains);
        $gateways = json_encode($request->gateways);
        $prices = $request->only('month', 'quarter', 'annual');
        if ($id == 'free' || $id == 'trial') {
            $prices = array("month" => "FREE", "quarter" =>  "FREE", "annual" =>  "FREE");
            if ($id == 'trial') {
                $request->validate([
                    'expiry' => 'required|numeric',
                ]);
            }
            if ($id == 'trial') {
                $prices['expiry']       = $request->expiry;
                $dataSettings['trial']  = true;
            }
            $values = ["id" => $id, "name" => $request->package_name, 'slug' => $slugify->generate($request->package_name, ['delimiter' => '_']), "status" => $request->status, "price" => $prices, "settings" => $dataSettings, 'domains' => $domains, 'gateways' => $gateways];
            $values = json_encode($values);
            $values = array("value" => $values);
            Settings::where('key', 'package_' . $id)->update($values);
        }else{
            $plan = Packages::find($id);
            $plan->name     = $request->package_name;
            $plan->slug     = $general->generate_slug($request->package_name);
            $plan->status   = $request->status;
            $plan->domains  = $domains;
            $plan->gateways = $gateways;
            $plan->price    = $prices;
            $plan->settings = $dataSettings;
            $plan->updated_at     = Carbon::now(settings('timezone'));
            $plan->save();
        }

        return back()->with("success", __('Package edited'));
    }

    public function delete_package(Request $request){
        if (strtoupper($request->delete) !== strtoupper("delete")) {
            # redirect to page with error
            return back()->with('error', 'word not correct');
        }
        if (!Packages::where('id', $request->package_id)->exists()) {
            return back()->with('package does not exists');
        }
        $package = Packages::find($request->package_id);
        User::where('package', $package->id)->update(array('package' => 'free', 'package_due' => NULL));
        $package->delete();
        return redirect()->route('admin-packages')->with('success', 'That package was successfully deleted');
    }



    // Stats 
    public function stats(Request $request){
        $general = new General();
        $start_date = $request->get('start_date');
        $end_date   = $request->get('end_date');
        $username   = $request->get('username');
        $start_date = isset($start_date) ? $start_date : Carbon::now($this->settings->timezone)->subDays(30)->format('Y-m-d');
        $end_date = isset($end_date) ? $end_date : Carbon::now($this->settings->timezone)->format('Y-m-d');
        $date = $general->get_start_end_dates($start_date, $end_date);


        # User Chart
        $userschart = [];
        $results = User::select(\DB::raw("COUNT(*) as count, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))->whereBetween('created_at', [$date->start_date_query, $date->end_date_query])->groupBy('formatted_date')->orderBy('formatted_date')->get();

        foreach ($results as $value) {
            $value->formatted_date = Carbon::parse($value->formatted_date)->toFormattedDateString();
            $userschart[$value->formatted_date] = [
                'users' => $value->count
            ];
        }

        $userschart = $general->get_chart_data($userschart);


        # Products Chart
        $productschart = [];
        $results = Products::select(\DB::raw("COUNT(*) as count, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))->whereBetween('created_at', [$date->start_date_query, $date->end_date_query])->groupBy('formatted_date')->orderBy('formatted_date')->get();

        foreach ($results as $value) {
            $value->formatted_date = Carbon::parse($value->formatted_date)->toFormattedDateString();
            $productschart[$value->formatted_date] = [
                'count' => $value->count
            ];
        }

        $productschart = get_chart_data($productschart);


        # Payments Chart
        $paymentschart = [];
        $results = Payments::select(\DB::raw("COUNT(*) as count, DATE_FORMAT(`date`, '%Y-%m-%d') AS `formatted_date`, TRUNCATE(SUM(`price`), 2) AS `amount`"))->whereBetween('date', [$date->start_date_query, $date->end_date_query])->groupBy('formatted_date')->get();

        foreach ($results as $value) {
            $value->formatted_date = Carbon::parse($value->formatted_date)->toFormattedDateString();
            $paymentschart[$value->formatted_date] = [
                'count' => $value->count,
                'amount' => $value->amount
            ];
        }

        $paymentschart = $general->get_chart_data($paymentschart);

        # Profile visits
        $profilevisitschart = [];
        $results = Track::leftJoin('users', 'users.id', '=', 'track.user')
        ->select(\DB::raw("COUNT(*) as count, track.count as views, users.username as username, DATE_FORMAT(`date`, '%Y-%m-%d') AS `formatted_date`"))->whereBetween('date', [$date->start_date_query, $date->end_date_query]);
        if (!empty($username)) {
            $results->where('username','LIKE','%'.$username.'%');
        }
        $results = $results->groupBy('formatted_date')->orderBy('formatted_date')->get();

        foreach ($results as $value) {
            $value->formatted_date = Carbon::parse($value->formatted_date)->toFormattedDateString();
            if(!array_key_exists($value->formatted_date, $profilevisitschart)) {
                $profilevisitschart[$value->formatted_date] = [
                    'impression'        => 0,
                    'unique'            => 0,
                    'count' => $value->count
                ];
            }
            $profilevisitschart[$value->formatted_date]['unique']++;
            $profilevisitschart[$value->formatted_date]['impression'] += $value->views;
        }

        $profilevisitschart = $general->get_chart_data($profilevisitschart);

        $options = (object) ['start_date' => $start_date, 'end_date' => $end_date, 'date' => $date, 'userschart' => $userschart, 'productschart' => $productschart, 'profilevisitschart' => $profilevisitschart, 'paymentschart' => $paymentschart];

        #view page with array
        return view('admin.stats.stats', ['options' => $options]);
    }


    public function get_chart_data(Array $main_array) {

        $results = [];

        foreach($main_array as $date_label => $data) {

            foreach($data as $label_key => $label_value) {

                if(!isset($results[$label_key])) {
                    $results[$label_key] = [];
                }

                $results[$label_key][] = $label_value;

            }

        }

        foreach($results as $key => $value) {
            $results[$key] = '["' . implode('", "', $value) . '"]';
        }

        $results['labels'] = '["' . implode('", "', array_keys($main_array)) . '"]';

        return $results;
    }



    // Pages
    public function delete_page(Request $request){
        if (strtoupper($request->delete) !== strtoupper("delete")) {
            # redirect to page with error
            return back()->with('error', 'Word not correct');
        }
        if (!Pages::where('id', $request->page_id)->exists()) {
            return back();
        }
        $page = Pages::find($request->page_id);
        if (!empty($page->image)) {
            if(file_exists(public_path('media/pages/' . $page->image))){
                unlink(public_path('media/pages/' . $page->image)); 
            }
        }
        $page->delete();
        return redirect()->route('pages')->with('success', 'That page was successfully deleted');
    }
    public function pages() {
       $pages = Pages::orderBy('id', 'DESC')->get();
       #view page with array
       return view('admin.page.all', ['pages' => $pages]);
    }

    public function add_pages(){
        $categories = Pages_Category::where('status', 1)->get();
        #view page with array
        return view('admin.page.add', ['categories' => $categories]);
    }

    public function edit_pages($id){
        if (Pages::where('id', $id)->count() > 0) {
            $page = Pages::where('id', $id)->first();
            $categories = Pages_Category::where('status', 1)->get();
            $page->settings = json_decode($page->settings);
            #view page with array
            return view('admin.page.edit', ['page' => $page, 'categories' => $categories]);
        }else{
            abort(404, 'Not Found');
        }
    }

    public function edit_post_page(Request $request){
        $general = new General();
        $page = Pages::where('id', $request->page_id)->first();
        $json = array('sh_description' => $request->sh_description, 'content' => $request->content);
        $json = json_encode($json);
        $url = $request->url;
        if ($request->type == 'internal') {
            $url = $general->generate_slug($request->url);
        }
        $value = array('title' => $request->title, 'category' => $request->category, 'type' => $request->type, 'status' => $request->status, 'url' => $url, 'order' => $request->order, 'settings' => $json, 'updated_at' => Carbon::now(settings('timezone')));

        Pages::where('id', $page->id)->update($value);
        if (!empty($request->image)) {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if (!empty($page->image)) {
                if(mediaExists('media/pages', $page->image)){
                    storageDelete('media/pages', $page->image); 
                }
            }

            $imageName = putStorage('media/pages', $request->image);

            $values = array('image' => $imageName);
            Pages::where('id', $page->id)->update($values);
        }

        return back()->with("success", "saved successfully");
    }

    public function post_page(Request $request){
        
        $general = new General();
        $url = $general->generate_slug($request->url);
        $request->validate([
            'url' => 'required|string|max:25|unique:pages',
        ]);

        $json = array('sh_description' => $request->sh_description, 'content' => $request->content);
        $json = json_encode($json);
        $value = array('title' => $request->title, 'category' => $request->category, 'type' => $request->type, 'status' => $request->status, 'url' => $url, 'order' => $request->order, 'settings' => $json, 'created_at' => Carbon::now(settings('timezone')));

        $id = Pages::insertGetId($value);
        if (!empty($request->image)) {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $imageName = putStorage('media/pages', $request->image);
            $values = array('image' => $imageName);
            Pages::where('id', $id)->update($values);
        }

        return redirect()->route('pages')->with("success", "saved successfully");
    }


    public function randomnumbers($min = 4, $max = 9) {
      $length = rand($min, $max);
      $chars = array_merge(range("0", "9"));
      $max = count($chars) - 1;
      $randomnumbers = '';
      for($i = 0; $i < $length; $i++) {
        $char = random_int(0, $max);
        $randomnumbers .= $chars[$char];
      }
      return $randomnumbers;
    }

    private function writeHttps($sch){
$https = '
    RewriteCond %{HTTPS} !=on
    RewriteRule .* https://%{HTTP_HOST}%{REQUEST_URI} [R,L]';
$scheme = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews
    </IfModule>

    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} -d [OR]
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ ^$1 [N]

    RewriteCond %{REQUEST_URI} (\.\w+$) [NC]
    RewriteRule ^(.*)$ public/$1 

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ server.php
    '.($sch == 'https' ? $https : '').'
</IfModule>';

        $update = fopen(base_path('.htaccess'), 'w');
        fwrite($update, $scheme);
    }

    // Settings
    public function post_settings(Request $request, Domains $domains) {
        $domains = $domains->get();
        $appurl = url('/');
        foreach ($domains as $item) {
            if (parse_url(url('/'))['host'] == $item->host) {
                $appurl = env('APP_URL');
            }
        }
        $scheme = 'http';
        if ($request->scheme == 'https') {
            $scheme = 'https';
            if (env('APP_SCHEME') !== 'https') {
             $this->writeHttps($request->scheme);
            }
        }
        if ($request->scheme == 'http') {
          $scheme = 'http';
          if (env('APP_SCHEME') !== 'http') {
           $this->writeHttps($request->scheme);
          }
        }
        $wildcard_domain = $request->envUpdate['APP_USER_WILDCARD_DOMAIN'] ?? '';
        if (!empty($wildcard_domain)) {
            $wildcard_domain = getHost($wildcard_domain);
        }


        $update_env = [
            'CAPTCHA_STATUS'        => $request->captcha_status,
            'CAPTCHA_TYPE'          => $request->captcha_type,
            'RECAPTCHA_SITE_KEY'    => $request->recaptcha_site_key,
            'RECAPTCHA_SECRET_KEY'  => $request->recaptcha_secret_key,
            'STRIPE_STATUS'         => $request->stripe_status,
            'STRIPE_CLIENT'         => $request->stripe_client,
            'STRIPE_SECRET'         => $request->stripe_secret,
            'GOOGLE_STATUS'         => $request->google_status,
            'GOOGLE_CLIENT_ID'      => $request->google_client_id,
            'GOOGLE_SECRET'         => $request->google_secret_key,
            'FACEBOOK_STATUS'       => $request->facebook_status,
            'FACEBOOK_CLIENT_ID'    => $request->facebook_client_id,
            'FACEBOOK_SECRET'       => $request->facebook_secret_key,
            'APP_LOCALE'            => $request->locale,
            'APP_URL'               => $appurl,
            'APP_SCHEME'            => $scheme,
            'MERCADOPAGO_STATUS'    => $request->mercadopago_status,
            'MERCADOPAGO_ACCESS_TOKEN'       => $request->mercadopago_access_token,
            'MIDTRANS_STATUS'       => $request->midtrans_status,
            'MIDTRANS_CLIENT_KEY'   => $request->midtrans_client_key,
            'MIDTRANS_SERVER_KEY'   => $request->midtrans_server_key,
            'MIDTRANS_MODE'         => $request->midtrans_mode,
            'APP_USER_WILDCARD'     => $request->userwildcard,
            'AWS_ACCESS_KEY_ID'     => $request->aws_access_key_id,
            'AWS_SECRET_ACCESS_KEY' => $request->aws_secret_access_key,
            'AWS_DEFAULT_REGION'    => $request->aws_default_region,
            'AWS_BUCKET'            => $request->aws_bucket,
            'FILESYSTEM'            => $request->file_system,
            'PAYTM_STATUS'          => $request->paytm_status,
            'PAYTM_ENVIRONMENT'     => $request->paytm_environment,
            'PAYTM_MERCHANT_ID'     => $request->paytm_merchant_id,
            'PAYTM_MERCHANT_KEY'    => $request->paytm_merchant_key,
            'PAYTM_MERCHANT_WEBSITE'=> $request->paytm_merchant_website,
            'PAYTM_CHANNEL'         => $request->paytm_channel,
            'PAYTM_INDUSTRY_TYPE'   => $request->paytm_industrytype,
        ];
        $update_env["MAIL_HOST"]            = $request->smtp_host;
        $update_env["MAIL_FROM_ADDRESS"]    = $request->smtp_from_address;
        $update_env["MAIL_FROM_NAME"]       = (!empty($request->smtp_from_name) ? $request->smtp_from_name : '${APP_NAME}');
        $update_env["MAIL_ENCRYPTION"]      = $request->smtp_encryption;
        $update_env["MAIL_PORT"]            = $request->smtp_port;
        $update_env["MAIL_USERNAME"]        = $request->smtp_username;
        $update_env["MAIL_PASSWORD"]        = $request->smtp_password;
        $update_env["APP_NAME"]             = $request->title;
        $update_env["PAYPAL_STATUS"]        = $request->paypal_status;
        $update_env["PAYPAL_CLIENT_ID"]     = $request->paypal_client_id;
        $update_env["PAYPAL_SECRET"]        = $request->paypal_secret;
        $update_env["PAYPAL_MODE"]          = $request->paypal_mode;
        $update_env["PAYSTACK_STATUS"]      = $request->paystack_status;
        $update_env["PAYSTACK_PUBLIC_KEY"]  = $request->paystack_public_key;
        $update_env["PAYSTACK_SECRET_KEY"]  = $request->paystack_secret_key;
        $update_env["RAZOR_STATUS"]         = $request->rozor_status;
        $update_env["RAZOR_KEYID"]          = $request->razor_key_id;
        $update_env["RAZOR_SECRET"]         = $request->rozor_secret_key;
        $update_env["BANK_DETAILS"]         = $request->bank_details;
        $update_env["BANK_STATUS"]          = $request->bank_status;
        if (!empty($request->envUpdate)) {
            foreach ($request->envUpdate as $key => $value) {
                $update_env[$key] = $value;
                if ($key == 'APP_USER_WILDCARD_DOMAIN') {
                    $update_env[$key] = $wildcard_domain;
                }
            }
        }
        $this->changeEnv($update_env);
        $request->registration              = (bool) $request->registration;
        $_POST['registration']              = (bool) $_POST['registration'];
        $request->email_activation          = (bool) $request->email_activation;
        $request->email_notify_bank_transfer  = (bool) $request->email_notify_bank_transfer;
        $request->business_enabeled                  = (bool) $request->business_enabeled;
        $request->email_notify_user                     = (bool) $request->email_notify_user;
        $request->email_notify_payment                  = (bool) $request->email_notify_payment;
        $request->under_construction_enabled            = (bool) $request->under_construction_enabled;
        $request->contact = (bool) $request->contact;
        $request->payment_system = (bool) $request->payment_system;

        $request->custom_code_enabled            = (bool) $request->custom_code_enabled;

        $settings_keys = [
            # Main
            'email',
            'email_activation',
            'registration',
            'payment_system',
            'timezone',
            'currency',
            'location',
            'terms',
            'privacy',
            'contact',
            'custom_home',
            'site' =>[
                'store_count',
                'show_pages',
            ],
            'user' => [
                'domains_restrict',
                'demo_user',
                'products_image_size',
                'products_image_limit',
            ],
            # Ads
            'ads' => [
                'enabled',
                'store_header',
                'store_footer',
                'site_header',
                'site_footer',
            ],

            # Social
            'social' => [
                'facebook',
                'instagram',
                'youtube',
                'whatsapp',
                'twitter',
            ],
            # Custom code
            'custom_code' => [
                'enabled',
                'head',
            ],
            /* Business */
            'business' => [
                'enabled',
                'name',
                'address',
                'city',
                'county',
                'zip',
                'country',
                'email',
                'phone',
                'tax_type',
                'tax_id',
                'custom_key_one',
                'custom_value_one',
                'custom_key_two',
                'custom_value_two'
            ],
            # Email notify
            'email_notify' => [
                'emails',
                'user',
                'payment',
                'bank_transfer',
            ],
        ];

        foreach ($settings_keys as $key => $value) {

            if(is_array($value)) {

                $values_array = [];

                foreach ($value as $sub_key) {

                    /* Check if the field needs cleaning */
                    $values_array[$sub_key] = $request->{$key . '_' . $sub_key};
                }

                $value = json_encode($values_array);

            } else {
                $key = $value;
                $value = $_POST[$key];
            }
           if (Settings::where('key', $key)->count() > 0) {
             $value = array("value" => $value);
             Settings::where('key', $key)->update($value);
           }else{
             $value = array("key" => $key, "value" => $value);
             Settings::insert($value);
           }

        }
        if (!empty($request->logo)) {
            $slug = md5(microtime());
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if (!empty($this->settings->logo)) {
                if(mediaExists('media/logo', $this->settings->logo)){
                    storageDelete('media/logo', $this->settings->logo); 
                }
            }

            $imageName = putStorage('media/logo', $request->logo);

            $values = array('value' => $imageName);
            Settings::where('key', 'logo')->count() > 0 ? Settings::where('key', 'logo')->update($values) : Settings::insert(['key' => 'logo', 'value' => $imageName]);
        }
        if (!empty($request->favicon)) {
            $slug = md5(microtime());
            $request->validate([
                'favicon' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if (!empty($this->settings->favicon)) {
                if(mediaExists('media/favicon', $this->settings->favicon)){
                    storageDelete('media/favicon', $this->settings->favicon); 
                }
            }

            $imageName = putStorage('media/favicon', $request->favicon);


            $values = array('value' => $imageName);
            Settings::where('key', 'favicon')->count() > 0 ? Settings::where('key', 'favicon')->update($values) : Settings::insert(['key' => 'favicon', 'value' => $imageName]);
        }
        // Email

        return back()->with("success", "saved successfully");
    }


    public function adminUpdates(){
        return view('admin.updates');
    }

    public function updateMigrate(Request $request){
        if ($request->get('steps') == 1) {
            DB::table('migrations')->where('migration', '2020_11_08_173354_updates')->delete();
        }elseif ($request->get('steps') == 2) {
            try {
                Artisan::call('migrate', ["--force"=> true]);
                $update_env["APP_MIGRATION"]  = 'no';
                $this->changeEnv($update_env);
                return "SUCCESS";
            } catch(\Exception $e) {
                return "FAILED _ " . $e->getMessage();
            }
        }
    }


    public function runUpdateOnline(Request $request){
        return false;
    }
    public function update_license_code(Request $request){
        $request->validate([
            'license_code' => 'required|min:15',
        ]);
        $license = verify_license($request->license_code);
        if (!$license->status) {
            $update_env["LICENSE_KEY"]  = '';
            $update_env["LICENSE_NAME"] = '';
            $update_env["LICENSE_TYPE"] = '';
            env_update($update_env);
            if (file_exists(storage_path('app/.code'))) {
                unlink(storage_path('app/.code'));
            }
            return back()->with('error', 'Invalid license code');
        }
        return back()->with('success', 'License key updated');
    }
    public function updateManual(Request $request){
        $file = $request->file('zipFile');
        if($file) {
            $request->zipFile->move(base_path(), "publish.zip");
            $zipper = new \Madnest\Madzipper\Madzipper;
            $zipper->make('publish.zip')->extractTo(base_path());
            $zipper->close();
            unlink("publish.zip");
            try {
                Artisan::call('migrate', ["--force"=> true]);
                $update_env["APP_MIGRATION"]  = 'no';
                $this->changeEnv($update_env);
            } catch(\Exception $e) {
                return "FAILED _ " . $e->getMessage();
            }
            return back()->with('success', 'App Updated');
        }

        return back()->with('error', 'Try again');
    }

    // Helpers



    protected function changeEnv($data = array()){
        if(count($data) > 0){
            $env = file_get_contents(base_path() . '/.env');
            $env = explode("\n", $env);
            foreach((array)$data as $key => $value) {
                if($key == "_token") {
                    continue;
                }
                $notfound = true;
                foreach($env as $env_key => $env_value) {
                    $entry = explode("=", $env_value, 2);
                    if($entry[0] == $key){
                        $env[$env_key] = $key . "=\"" . $value."\"";
                        $notfound = false;
                    } else {
                        $env[$env_key] = $env_value;
                    }
                }
                if($notfound) {
                    $env[$env_key + 1] = "\n".$key . "=\"" . $value."\"";
                }
            }
            $env = implode("\n", $env);
            file_put_contents(base_path() . '/.env', $env);
            return true;
        } else {
            return false;
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
