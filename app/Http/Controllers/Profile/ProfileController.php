<?php

namespace App\Http\Controllers\Profile;

use General, Location, Str, Theme;
use App\User;
use App\Model\Track;
use App\Model\Products;
use App\Model\Product_Reviews;
use App\Model\Product_Category;
use App\Model\Product_Orders;
use App\Model\Domains;
use App\Model\Option;
use App\Model\OptionValues;
use App\Model\UserPages;
use App\Model\PagesSections;
use App\Model\Blog;
use App\Mail\GeneralMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;
use App\Cart;
use App\Model\Customers;
use App\Model\Conversations;
use App\Model\Messages;
use App\Model\ProductRefund;

class ProfileController extends Controller{
  public $user;
  private $uid;
  private $template;
  public  $package;
  private $profile;
  function __construct(Request $request){
    $this->init($request);
  }

  private function init($request){
    $this->middleware(function ($request, $next) {
      if (isset($_GET['profile']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
        $url = url()->current();

        $query = $request->query();

        unset($query['profile']);

        return redirect($query ? $url . '?' . http_build_query($query) : $url);
      }
      return $next($request);
    });
    $host = $_SERVER['HTTP_HOST'];
    $parse = parse_url(env('APP_URL'))['host'] ?? '';
    foreach (Domains::get() as $value) {
       if ($host == $value->host && $value->user !== null) {
        if ($user = User::where('id', $value->user)->first()) {
          request()->merge(['profile' => $user->username]);
          $this->profile = $user->username;
        }
       }
    }
    $this->profile = $request->profile;
    if (!$this->user = User::where('username', $this->profile)->first()) {
        abort(404);
    }
    $this->uid = $this->user->id;
    $uid = $this->uid;
    $this->domain();
    # init views
    $this->initViews($request);
    $this->template = !empty(settings('user.default_template')) ? settings('user.default_template') : 'doveshop';
    if (!empty(user('extra.template', $uid))) {
      if (Theme::has(user('extra.template', $uid))) {
        $this->template = user('extra.template', $uid);
      }
    }
    Theme::set($this->template);
  }

    private function domain(){
        $domain = $this->user->domain;
        # If domain is main
        if (empty(env('APP_URL')) && $_POST) {
            return redirect()->back()->with('error', __('Could not complete your request'));
        }

        if (file_exists(base_path('.env')) && empty(env('APP_URL'))) {
            return redirect(url()->full());
        }

        if ($domain == 'main') {
          $domain = env('APP_URL');
          # If Domain is not main and exists
        }elseif ($domain = Domains::where('id', $this->user->domain)->first()) {
          # If domain is in package
            $domain = $domain->scheme.$domain->host;
        }else{
          $domain = env('APP_URL');
        }
        $host     = parse_url($domain);

        $thishost = $_SERVER['HTTP_HOST'];

        if ($host['host'] == $thishost) {
          # Proceed with request
        }else{
          if (settings('user.domains_restrict')) {
          # Proceed with request
          }else{
            redirect("$domain")->send();
          }
        }
    }

  private function initViews($request){
    $general = new General();
    $package = $general->package($this->user);
    $productPaginate = Products::where('user', $this->user->id)->orderBy('position', 'ASC')->orderBy('id', 'DESC');
    $categories = Product_Category::where('user', $this->user->id)->get();
    $max_price   = Products::where('user', $this->user->id)->max('price');
    $min_price   = Products::where('user', $this->user->id)->min('price');
    $gateways   = getOtherResourceFile('store_gateways');
    if (license('license') !== 'Extended License') {
      $gateways = getOtherResourceFile('store_gateways-regular');
    }
    $socials    = getOtherResourceFile('socials');
    if ($request->get('min-price')) {
      $min_price = $request->get('min-price');
      $productPaginate->where('price', '>=', $min_price);
    }
    if ($request->get('max-price')) {
      $max_price = $request->get('max-price');
      $productPaginate->where('price', '<=', $max_price);
    }
    if (!empty($request->get('query'))) {
      $productPaginate = $productPaginate->where('title', 'LIKE','%'.$request->get('query').'%');
    }
    if (!empty($request->get('category'))) {
      $pCate = $productPaginate->get();
      $productPaginate = [];


      foreach ($pCate as $value) {
        if (in_array($request->get('category'), $value->categories)) {
          $productPaginate[] = $value;
        }
      }
    }else{
      $productPaginate = $productPaginate->paginate(8);
    }
    if (file_exists($sc = resource_path('custom/socials.php'))) {
        $sc = require $sc;
        if (is_array($sc)) {
            foreach ($sc as $key => $value) {
                $socials[$key] = $value;
            }
        }
    }

    $options = (object) ['socials' => $socials];
    View::share('productPaginate', $productPaginate);
    View::share('options', $options);
    View::share('min_price', $min_price);
    View::share('max_price', $max_price);
    View::share('uid', $this->uid);
    View::share('package', $package);
    View::share('categories', $categories);
    View::share('gateways', $gateways);
    View::composer('*', function($view){
        $view->with('user', $this->user);
    });
  }

  public function login(Request $request){
    if (auth_user($this->user->id, 'check')) {
      return redirect()->route('user-profile-dashboard', $this->user->username);
    }
    if ($request->get('reset-password') == true && !empty($request->get('code'))) {
      $customer = Customers::where('storeuser', $this->user->id)->where('resetPassword', $request->get('code'))->first();

      if (!$customer) {
        abort(404);
      }

      return view('auth.reset-reset', ['code' => $request->get('code')]);
    }


    if ($request->get('reset-password') == true) {
      return view('auth.reset');
    }

    if ($request->get('step') == '2') {
      return view('auth.login-password');
    }
    return view('auth.login');
  }

  public function post_login($profile = null, $type = null, Request $request){
    $type = $request->type;
    $email = $request->email;
    $session = $request->session();
    $passcode = \Str::random(5);
    $activateCode = \Str::random(5);
    $passwordCode = \Str::random(5);

    $reset_route = route('user-profile-login', ['profile' => $this->user->username, 'reset-password' => true, 'code' => $passwordCode]);

    # Mail

    $Activatemail = (object) ['subject' => __('Activate account'), 'message' => '<p>'.__("Here's the generated activation code for you to activate your account on") .' '. full_name($this->uid) .'</p>' . '<h1 style="margin-top: 30px; margin-bottom: 30px; text-align:center">'. $activateCode .'</h1>'];

    $passwordMail = (object) ['subject' => __('Password reset'), 'message' => '<p>'.__("Use the link below to reset your password") .' '. full_name($this->uid) .'</p>' . '<a style="margin-top: 30px; margin-bottom: 30px; text-align:center" href="'. $reset_route .'">'. $reset_route .'</a>'];


    if ($type == 'login') {
      $customer = Customers::where('storeuser', $this->user->id)->where('email', $email)->first();
      if ($customer) {
        // Check if user is active
        if (!empty($customer->activateCode) && $customer->active == 0) {

          $update = Customers::find($customer->id);
          $update->activateCode = $activateCode;
          $update->active = 0;
          $update->save();
          try {
            Mail::to($customer->email)->send(new GeneralMail($Activatemail));
          } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
          }
          
          return view('auth.activateEmail');
        }



        if (Hash::check($request->password, $customer->password)) {
          $session->put('auth-customer-'. $this->uid, ['auth' => true, 'store' => $this->user->id, 'customer' => $customer->id]);
          $update = Customers::find($customer->id);
          $update->resetPassword = null;
          $update->save();

          return back()->with('success', __('Logged in successfully'));
        }
        
        return back()->with('error', __('Invalid credentials'));


        # Login
      }else{
        $newCustomer = new Customers;
        $newCustomer->email = $email;
        $newCustomer->storeuser = $this->user->id;
        $newCustomer->password = Hash::make($request->password);
        $newCustomer->active = 0;
        $newCustomer->activateCode = $activateCode;
        $newCustomer->save();
        // Activate account


        try {
          Mail::to($newCustomer->email)->send(new GeneralMail($Activatemail));
        } catch (\Exception $e) {
          return back()->with('error', $e->getMessage());
        }
          
        return view('auth.activateEmail');
      }
    }


    if ($type == 'reset-password-send') {
      $customer = Customers::where('storeuser', $this->user->id)->where('email', $request->email)->first();

      if (!$customer) {
        return back()->with('error', __('User doesnt exist'));
      }


      $update = Customers::find($customer->id);
      $update->resetPassword = $passwordCode;
      $update->save();

      try {
        Mail::to($customer->email)->send(new GeneralMail($passwordMail));
      } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
      }

      return back()->with('success', __('Reset email sent. Please check your email'));
    }

    if ($type == 'reset-reset') {
      $customer = Customers::where('storeuser', $this->user->id)->where('resetPassword', $request->code)->first();

      if (!$customer) {
        return back()->with('error', __('User doesnt exist'));
      }

      $update = Customers::find($customer->id);
      $update->resetPassword = null;
      $update->password = Hash::make($request->password);
      $update->save();

      return redirect()->route('user-profile-login', ['profile' => $this->user->username])->with('success', __('Password has been reset. Please login'));
    }

    if ($type == 'activate') {
      $customer = Customers::where('storeuser', $this->user->id)->where('activateCode', $request->code)->first();

      if ($customer) {
        $update = Customers::find($customer->id);
        $update->activateCode = null;
        $update->active = 1;
        $update->save();


        $session->put('auth-customer-'. $this->uid, ['auth' => true, 'store' => $this->user->id, 'customer' => $customer->id]);

        return redirect()->route('user-profile-dashboard', ['profile' => $this->user->username])->with('success', __('Logged in successfully'));
      }


      return redirect()->route('user-profile-login', ['profile' => $this->user->username])->with('success', __('Invalid activation code'));
    }
  }

  public function index(){
    # Track activity
    if (!$page = UserPages::where('user', $this->user->id)->where('is_home', 1)->first()) {
      abort(404);
    }

    $data = pages_sections_values($this->user->id, $page->id);
    $data_includes = pages_sections_values($this->user->id, $page->id, 'other_data');
    $sections = PagesSections::where('page_id', $page->id)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();


    $this->track($this->user);
    return view('profile', ['page_id' => $page->id, 'sections' => $sections, 'sections_data' => $data, 'data_includes' => $data_includes]);
  }

  public function pages($profile = null, $page = null, Request $request){
    $page = $request->page;
    if (!$page = UserPages::where('user', $this->user->id)->where('slug', $page)->first()) {
      abort(404);
    }

    $data = pages_sections_values($this->user->id, $page->id);
    $data_includes = pages_sections_values($this->user->id, $page->id, 'other_data');
    $sections = PagesSections::where('page_id', $page->id)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

    return view('pages', ['page_id' => $page->id, 'sections' => $sections, 'sections_data' => $data, 'data_includes' => $data_includes]);


  }

  public function categories($profile = null){
    return view('pages.categories');
  }

  public function blogs(){
    return view('blogs');
  }

  public function track($user, $dyid = Null, $type = 'profile'){
    $agent = new Agent();
    $general = new General();
    $ip = $general->getIP();
    $visitor_id = md5(microtime());
    session()->put('visitor', 1);
    $session  = session();
    if (empty($session->get('visitor_id'))) {
      session()->put('visitor_id', $visitor_id);
    }

    if (Track::where('visitor_id', $session->get('visitor_id'))->count() > 0 ) {
      $track = Track::where('visitor_id', $session->get('visitor_id'))->first();
          $values = array('count' => ($track->count + 1), 'date' => Carbon::now(settings('timezone')));
          Track::where('visitor_id', $session->get('visitor_id'))->update($values);
    }else{
          $values = array('user' => $user->id, 'visitor_id' => $session->get('visitor_id'), 'country' => (!empty(Location::get($general->getIP())->countryCode)) ? Location::get($general->getIP())->countryCode : Null, 'type' => $type, 'dyid' => $dyid, 'ip' => $general->getIP(), 'os' => $agent->platform(), 'browser' => $agent->browser(), 'count' => 1, 'referer' => url(request()->profile), 'date' => Carbon::now(settings('timezone')));
          Track::insert($values);
    }
  }


  public function checkout($profile = null, Request $request){
    $session = session();
    $session_cart = $session->get('cart_'.$this->user->username) ?? [];
    $cart = [];
    foreach ($session_cart as $key => $value) {
      $val = Products::where('id', $key)->where('user', $this->user->id)->first();
      $val->qty = $value['qty'];
      $cart[$key] = $val;
    }
    if (empty($cart)) {
      #return redirect($this->profile ?? '')->with('error', __('Cart empty'));
    }
    $newcart = new \App\Cart;
    $cart = $newcart->getAll($this->uid);
    return view('pages.checkout', ['cart' => $cart]);
  }

  public function add_to_cart(Request $request, $id){
    $session = $request->session();
    $id = $request->id;
    $product_id = $id;
    if (!$product = Products::where('id', $id)->where('user', $this->user->id)->first()) {
      abort(404);
    }
    $action = $request->action;
    $quantity = (!empty($request->quantity) ? $request->quantity : 1);
    if ($action == 'add') {
      $cart = $session->get('cart_' . $this->profile) ? $session->get('cart_' . $this->profile) : [];
      if (array_key_exists($product_id, $cart)) {
        $qty = $cart[$product_id]['qty'];
        $cart[$product_id]['qty'] = ($qty + $quantity);
      }else{
        $cart[$product_id] = [
          'id' => $id,
          'qty' => $quantity
        ];
      }
      $session->put('cart_' . $this->profile, $cart);
    }elseif ($action == 'remove') {
      $session->forget('cart_' . $this->profile . '.' . $product_id);
      return back()->with('success', 'Item removed');
    }elseif ($action == 'remove_all') {
      $session->forget('cart_' . $this->profile);
    }elseif ($action == 'quantity_change') {
      $cart = ($session->get('cart_' . $this->profile)) ? $session->get('cart_' . $this->profile) : [];
      if (array_key_exists($product_id, $cart)) {
          $cart[$product_id]['qty'] = $request->quantity;
      }else{
        $cart[$product_id] = [
          'id' => $id,
          'qty' => $request->quantity
        ];
      }
      $session->put('cart_' . $this->profile, $cart);
    }
    $return = ['status' => 'success', 'cart_count' => count(\Session::get('cart_'.$this->profile))];
    return $return;
  }


  public function single_blogs($id, Request $request){
      $id = $request->id;
      if (package('settings.blog', $this->uid)) {
          return Redirect::to($this->profile = '');
      }

      if (!$blog = Blog::where('id', $id)->where('user', $this->uid)->first()) {
          abort(404);
      }
      $blog_name = $blog->name;
      $blog_name = str_replace("{{title}}", $blog->name, $blog_name);
      $blog_note = $blog->note;
      $blog_note = str_replace("{{title}}", $blog_name, $blog_note);
      $this->track_blog($this->user, $request, $blog->id, "portfolio");
      return view('pages.single-blog', ['blog' => $blog, 'blog_note' => $blog_note, 'blog_title' => $blog_name]);
  }
    public function track_blog($user, $request, $dyid = Null, $type = 'profile'){
        $agent = new Agent();
        $general = new General();
        $visitor_id = md5(microtime());
        $request->session()->put('visitor', 1);
        $session  = $request->session();
        if (empty($session->get('visitor_id'))) {
            $request->session()->put('visitor_id', $visitor_id);
        }

        if (Track::where('visitor_id', $session->get('visitor_id'))->where('dyid', $dyid)->where('type', $type)->count() > 0 ) {
            $track = Track::where('visitor_id', $session->get('visitor_id'))->where('dyid', $dyid)->where('type', $type)->first();
            $values = array('count' => ($track->count + 1), 'date' => Carbon::now(settings('timezone')));
            Track::where('visitor_id', $session->get('visitor_id'))->update($values);
        }else{
            $values = array('user' => $user->id, 'visitor_id' => $session->get('visitor_id'), 'country' => (!empty(Location::get($general->getIP())->countryCode)) ? Location::get($general->getIP())->countryCode : Null, 'type' => $type, 'dyid' => $dyid, 'ip' => $this->getIp(), 'os' => $agent->platform(), 'browser' => $agent->browser(), 'count' => 1, 'date' => Carbon::now(settings('timezone')));
            Track::insert($values);
        }

    }

    public function getIp(){
        if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {

            if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                return trim(reset($ips));
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }

        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            return $_SERVER['REMOTE_ADDR'];
        } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        return '';
    }
  public function single_product($id, Request $request){
   $id = $request->id;
   if (!$product = Products::where('id', $request->id)->where('user', $this->user->id)->first()) {
    abort(404);
   }

   if ($product->stock == 0) {
     $update = Products::find($id);
     $update->stock_status = 0;
     $update->save();
   }

   #$options = Option::where('product', $id)->get();
   #$options_values = OptionValues::leftJoin('options', 'options.id', '=', 'option_values.option_id')
   #         ->select('option_values.*')
   #         ->groupBy('option_values.id')
   #         ->where('option_values.user', $this->uid)
   #         ->orderBy('order', 'ASC')
   #         ->orderBy('id', 'DESC')->get();

   $reviews = Product_Reviews::where('product_id', $id)->where('storeuser', $this->user->id)->count();
   return view('pages.single_product', ['product' => $product, 'reviews' => $reviews]);
  }


  public function reviews(Request $request){
   $id = $request->id;
   if (!$product = Products::where('id', $id)->where('user', $this->user->id)->first()) {
    abort(404);
   }
   $reviews = Product_Reviews::where('product_id', $id)->where('storeuser', $this->user->id)->orderBy('id', 'desc')->get();

   return view('pages.reviews', ['product' => $product, 'reviews' => $reviews]);
  }

  public function postReviews(Request $request){
   $id = $request->id;
   if (!$product = Products::where('id', $id)->where('user', $this->user->id)->first()) {
    abort(404);
   }
    $request->validate([
      'name' => 'required|min:2|string',
      'email' => 'required|email',
      'review' => 'required|string|min:2',
      'rating' => 'required',
    ]);
    $avatar = glob(media_path('avatars/').'*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
    $avatar = $avatar[array_rand($avatar)];
    $avatar = basename($avatar);
    $array = ['name' => $request->name, 'email' => $request->email, 'review' => $request->review, 'avatar' => $avatar];
    $review = new Product_Reviews;
    $review->storeuser = $this->user->id;
    $review->product_id = $id;
    $review->rating = $request->rating;
    $review->review = $array;
    $review->save();

    return back()->with('success', 'Review posted');
  }

  public function orders($profile = null, Request $request){
    $session = $request->session();
    $GetOrderId = $request->get('order_id');
    if (empty($GetOrderId)) {
      return view('pages.set-order');
    }
    if (!$storeorder = DB::table('store_orders')->where('slug', $GetOrderId)->first()) {
      abort(404);
    }
    if ($order = Product_Orders::where('storeuser', $this->user->id)->where('id', $storeorder->order_id)->first()) {
      $products = [];
      foreach ($order->products as $key => $value) {
       $product = Products::where('id', $key)->first();
       if (!array_key_exists($key, $products)) {
          $products[$key] = [
           'qty'  => 0,
           'name'  => '',
           'price'  => '',
           'media'  => '',
           'options'  => '',
           'downloadables' => null,
          ];
       }
       $price = $value['price'];

       /**
       *
       * Use Product price
       * $price = !empty($product->salePrice) ? $product->salePrice : $product->price;
       *
       **/

       $products[$key]['qty'] = $value['qty'];
       $products[$key]['name'] = $product->title ?? $value['name'] ?? '';
       $products[$key]['price'] = $price;
       $products[$key]['media'] = $product->media ?? '';
       $products[$key]['options'] = $value['options'] ?? '';
       $products[$key]['downloadables'] = $product->files;
      }
    }else{
      abort(404);
    }
    return view('pages.view-orders', ['order' => $order, 'products' => $products]);
  }

  public function products($profile = null){

    return view('products');
  }


    public function insertordersinit($user, $orders, $gateway = ''){
      $cart = new Cart;
      $cart = $cart->getAll($user->id);
      $details = json_decode($orders['details']);
      $message = __('Order completed, thanks for purchasing');
      $products = [];

      foreach ($cart as $key => $value) {
          $id = $value->associatedModel->id;
          if (array_key_exists($id, $products)) {
            $products[$id] = [
              'qty'   => 0,
              'name'  => '',
              'price' => 0,
              'options' => '',
            ];
          }
          $products[$id]['qty'] = $value->quantity;
          $products[$id]['price'] = $value->price;
          $products[$id]['name'] = $value->name;
          $products[$id]['options'] = Cart::getOptionsAttr($value->attributes->options, 'name_string');

          $product = Products::find($id);

          if ($product->stock_management == 1 && $product->stock > 0) {
            $product->stock = ($product->stock - $value->quantity);
          }
          if ($product->stock < 1) {
            $product->stock_status = 0;
          }

          $product->save();
      }
      $products = json_encode($products);

      $customer = null;

      if (auth_user($this->user->id, 'check')) {
        $customer = auth_user($this->user->id, 'get');
        $customer = $customer->id;
      }

      # Insert order
      $insert = ['storeuser' => $user->id, 'products' => $products, 'customer' => $customer, 'details' => $orders['details'], 'currency' => user('gateway.currency', $user->id), 'gateway' => $gateway, 'order_status' => 2, 'ref' => Str::random(10), 'price' => Cart::total($user->id), 'created_at' => Carbon::now(settings('timezone'))];



      $id = Product_Orders::insertGetId($insert);

      # Store orders
      $storeOrderId = DB::table('store_orders')->insertGetId(['slug' => Str::random(10), 'order_id' => $id, 'created_at' => Carbon::now(settings('timezone'))]);

      $storeOrderId = DB::table('store_orders')->where('id', $storeOrderId)->first();


      $email = (object) ['subject' => __('New Payment from') . $details->first_name ?? '', 'message' => '<p> <b>'. $details->first_name .'</b> Just paid for <b>'.count($cart).'</b> Products. <br> Head to your dashboard to view earnings and orders</p><br><a href="'. route('login') .'" style="background-color:#6576ff;border-radius:4px;color:#ffffff;display:inline-block;font-size:13px;font-weight:600;line-height:44px;margin-top: 20px;text-align:center;text-decoration:none;text-transform: uppercase; padding: 13px 50px; width: 100%">Login</a>'];
      try {
        Mail::to($user->email)->send(new GeneralMail($email));
      } catch (\Exception $e) {
        $message = 'Order completed and error sending email';
      }

      $email = (object) ['subject' => __('Thanks for purchasing'), 'message' => '<p> You just purchased <b>'.count($cart).'</b> Products from our store. To view purchased orders, kindly click the button below </p><br><a href="'. route('user-profile-orders', ['profile' => $user->username, 'order_id' => $storeOrderId->slug]) .'" style="background-color:#6576ff;border-radius:4px;color:#ffffff;display:inline-block;font-size:13px;font-weight:600;line-height:44px;margin-top: 20px;text-align:center;text-decoration:none;text-transform: uppercase; padding: 13px 50px; width: 100%">View orders</a> <p style="margin-top: 20px; display: block">'. route('user-profile-orders', ['profile' => $user->username, 'order_id' => $storeOrderId->slug]) .'</p>'];


      try {
        Mail::to($details->email)->send(new GeneralMail($email));
      } catch (\Exception $e) {
        $message = 'Order completed and error sending email';
      }

      return ['response' => 'success', 'message' => $message, 'order_id' => $storeOrderId->slug];
    }

    public function postcheckout(Request $request){
      $request->validate([
        'gateway' => 'required',
      ]);

      $details = [
           'first_name'     => $request->first_name,
           'last_name'      => $request->last_name,
           'email'          => $request->email,
           'phone'          => $request->phone,
           'house_number'   => $request->billing_number,
           'city'           => $request->city,
           'state'          => $request->state,
           'street'         => $request->street,
           'postal_code'    => $request->postal_code,
           'country'        => $request->country,
           'shipping'       => $request->shipping_location,
           'note'           => $request->note
      ];

      $details = json_encode($details);
      $gateways = getOtherResourceFile('store_gateways');
      if (!array_key_exists($request->gateway, $gateways)) {
        return back()->with('error', __('Gateway doesnt exists'));
      }
      $shipping = $request->shipping_location;


      if (user('extra.invoicing', $this->uid) == 1 && $request->get('proceed') == false) {
        $newcart = new \App\Cart;
        $cart = $newcart->getAll($this->uid);
        $requests = (object) $request->all();
        unset($requests->_token);

        $customer = null;

        if (auth_user($this->user->id, 'check')) {
          $customer = auth_user($this->user->id, 'get');
        }

        return view('pages.invoice', ['details' => $requests, 'customer' => $customer, 'cart' => $cart]);
      }

      try {
         return redirect()->route('user-'.$request->gateway.'-create', ['profile' => $this->profile, 'shipping' => $shipping, 'details' => $details]);
      } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
      }
    }

    public function success($profile = null){

      return view('pages.success');
    }






    // Dashboard

    public function dashboard($profile = null){
      if (!auth_user($this->user->id, 'check')) {
        return redirect()->route('user-profile-login', $this->user->username);
      }
      $cID = auth_user($this->user->id, 'get')->id;
      $customer = auth_user($this->user->id, 'get');

      $all_orders = Product_Orders::where('storeuser', $this->user->id)->where('customer', $cID)->orderBy('id', 'DESC')->get();

      $sales = $this->get_customer_sales($profile, $cID);
      return view('dashboard.dashboard', ['all_orders' => $all_orders, 'user' => $this->user, 'sales' => $sales, 'customer' => $customer]);
    }

    public function dashboard_orders($profile = null){
      if (!auth_user($this->user->id, 'check')) {
        return redirect()->route('user-profile-login', $this->user->username);
      }
      $cID = auth_user($this->user->id, 'get')->id;

      $all_orders = Product_Orders::where('storeuser', $this->user->id)->where('customer', $cID)->orderBy('id', 'DESC')->paginate(8);


      return view('dashboard.orders', ['all_orders' => $all_orders, 'user' => $this->user]);
    }

    public function dashboard_single_order (Request $request){
      $id = $request->id;
      if (!auth_user($this->user->id, 'check')) {
        return redirect()->route('user-profile-login', $this->user->username);
      }
      $cID = auth_user($this->user->id, 'get')->id;
      $customer = auth_user($this->user->id, 'get');
      
      $order = Product_Orders::where('storeuser', user('id', $this->user->id))->where('customer', $cID)->where('id', $id)->first();

      if (!$order) {
        abort(404);
      }
      $refund = ProductRefund::where('user', $this->uid)->where('customer', $customer->id)->where('order_id', $id)->first();

      $products = [];
      foreach ($order->products as $key => $value) {
       $product = Products::where('id', $key)->first();
       if (!array_key_exists($key, $products)) {
          $products[$key] = [
           'qty'  => 0,
           'name'  => '',
           'price'  => '',
           'media'  => '',
           'options' => '',
           'downloadables' => null,
          ];
       }
       $price = $value['price'];

       /**
       *
       * Use Product price
       * $price = !empty($product->salePrice) ? $product->salePrice : $product->price;
       *
       **/

       $products[$key]['qty'] = $value['qty'];
       $products[$key]['options'] = $value['options'] ?? '';
       $products[$key]['name'] = $product->title ?? $value['name'] ?? '';
       $products[$key]['price'] = $price;
       $products[$key]['media'] = $product->media ?? '';
       $products[$key]['downloadables'] = $product->files ?? '';
      }


      if ($request->get('type') == 'invoice') {
        if (!user('extra.invoicing', $this->uid)) {
          abort(404);
        }

        return view('dashboard.single-order-invoice', ['user' => $this->user, 'order' => $order, 'products' => $products, 'customer' => $customer]);
      }

      return view('dashboard.single-order', ['user' => $this->user, 'order' => $order, 'products' => $products, 'refund' => $refund]);
    }

    public function dashboard_settings($profile = null){
      if (!auth_user($this->user->id, 'check')) {
        return redirect()->route('user-profile-login', $this->user->username);
      }

      return view('dashboard.settings', ['user' => $this->user]);
    }

    public function edit_account_post($profile = null, Request $request){
      $customer_id = auth_user($this->user->id, 'get')->id;
      $customer = auth_user($this->user->id, 'get');


      $details = $request->details;

      $name = $details['first_name'] ?? '' .' '. $details['last_name'] ?? '';
      $email = $details['email'] ?? '';

      $yoo = Customers::where('storeuser', $this->user->id)->where('email', $email)->first();

      if (!empty($yoo->id) && $yoo->id !== $customer_id) {
        return back()->with('error', __('Email exists'));
      }

      $customer = Customers::find($customer_id);
      $customer->name = $name;
      $customer->email = $email;
      $customer->details = $request->details;

      if (!empty($request->avatar)) {
          $request->validate([
              'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
          ]);
          if (!empty($customer->avatar)) {
              if(mediaExists('media/user/customers/avatar', $customer->avatar)){
                  storageDelete('media/user/customers/avatar', $customer->avatar); 
              }
          }
          $imageName = putStorage('media/user/customers/avatar', $request->avatar);
          $customer->avatar = $imageName;
      }



      $customer->save();

      return back()->with('success', __('Saved successfully'));
    }

    public function customer_chat($profile = null) {
      if (!auth_user($this->user->id, 'check')) {
        return redirect()->route('user-profile-login', $this->user->username);
      }
      $customer = auth_user($this->user->id, 'get');

      if (!$convo = Conversations::where('customer', $customer->id)->where('user', $this->user->id)->first()) {
        $convo = new Conversations;
        $convo->user = $this->user->id;
        $convo->customer = $customer->id;
        $convo->save();
      }


      return view('dashboard.chat', ['convo' => $convo, 'user' => $this->user]);
    }

    public function RequestRefund($profile = null, Request $request){
      if (!auth_user($this->uid, 'check')) {
        return redirect()->route('user-profile-login', $this->user->username);
      }
      $customer = auth_user($this->uid, 'get');

      $Erefund = ProductRefund::where('user', $this->uid)->where('customer', $customer->id)->where('order_id', $request->order_id)->where('status', 0)->first();

      if ($Erefund) {
        return back()->with('error', __('Refund already submitted'));
      }


      $refund = new ProductRefund;
      $refund->user = $this->uid;
      $refund->customer = $customer->id;
      $refund->order_id = $request->order_id;
      $refund->status = 0;
      $refund->save();

      return back()->with('success', __('Refund requested'));
    }




    private function get_customer_sales ($profile = null, $customer_id) {
      $user = $this->user;
      $last_month = Carbon::now()->subMonth()->startOfMonth()->toDateString();
      $thisMonth = Carbon::now()->startOfMonth()->toDateString();

      if (!$customer = Customers::where('storeuser', $user->id)->where('id', $customer_id)->first()) {
        abort(404);
      }


      $sales = ['this_month' => 0, 'last_month' => 0, 'sales_chart' => 0, 'overall_sale' => 0];


      $orders = Product_Orders::where('storeuser', $this->user->id)->where('customer', $customer_id)->where('created_at', '>=', $last_month)->where('created_at', '<=', $thisMonth)->get();

      foreach ($orders as $order) {
          foreach ($order->products as $key => $value) {
          $price = $value['price'];
           $sales['last_month']  += ($value['qty'] * $price);
         }
      }

      $orders = Product_Orders::where('storeuser', $user->id)->where('customer', $customer_id)->where('created_at', '>=', $thisMonth)->get();
      foreach ($orders as $order) {
         foreach ($order->products as $key => $value) {
          $price = $value['price'];
           $sales['this_month']  += ($value['qty'] * $price);
         }
      }

      $orders = Product_Orders::where('storeuser', $user->id)->where('customer', $customer_id)->get();
      foreach ($orders as $order) {
         foreach ($order->products as $key => $value) {
          $price = $value['price'];
           $sales['overall_sale']  += ($value['qty'] * $price);
         }
      }



        # Sales chart
        $sales_chart = [];
        $sales_chart_fetch = Product_Orders::select(\DB::raw("*, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))->where('storeuser', $user->id)->where('customer', $customer_id)->where('created_at', '>=', $thisMonth)->get();

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

      $sales['sales_chart'] = $sales_chart;


      return $sales;
    }

    public function dashboard_logout($profile = null){
      auth_user($this->user->id, 'logout');

      return back();
    }
}
