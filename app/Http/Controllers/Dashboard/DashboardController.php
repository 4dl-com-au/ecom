<?php

namespace App\Http\Controllers\Dashboard;

use Linker, QrCode, Cart;
use App\User;
use App\Model\Products;
use App\Model\Product_Orders;
use App\Model\Settings;
use App\Model\Faq;
use App\Model\TrackLinks;
use App\Model\Payments;
use App\Model\UserPages;
use App\Model\PagesSections;
use App\Model\Track;
use App\Model\Blog;
use Validator,Redirect,Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Mail\SendMailable;
use GladePay\GladePay;
use Carbon\Carbon;
use Herbert\EnvatoClient;
use App\Model\Customers;
use App\Model\Conversations;
use App\Model\Messages;
use App\Model\Domains;
use App\Model\ProductRefund;
use Herbert\Envato\Auth\Token as EnvatoToken;
use Chat;
use Anand\LaravelPaytmWallet\Facades\PaytmWallet;

class DashboardController extends Controller{

    function __construct(){
      if (settings('email_activation')) {
        $this->middleware('activeEmail');
      }
      $general = new \General();
      $general->cron($type = 'soft');

    }

    public function create_customers(Request $request){
      $user = Auth::user();
      $request->validate([
        'customer_name' => 'required|min:2|string',
        'customer_email' => 'required|email'
      ]);

      if (Customers::where('storeuser', $user->id)->where('email', $request->customer_email)->first()) {
        return back()->with('error', __('Customer Exists'))->withInput();
      }


      $new = new Customers;
      $new->storeuser = $user->id;
      $new->email = $request->customer_email;
      $new->name = $request->customer_name;
      $new->save();

      return redirect()->route('user-customers')->with('success', __('Customer Created'));
    }

    public function add_customers(){
      return view('dashboard.customers.add');
    }

    public function all_customers(){
      $user = Auth::user();
      $customers = Customers::where('storeuser', $user->id)->get();



      return view('dashboard.customers.all', ['customers' => $customers]);
    }

    public function dashboard(){
        $user = Auth::user();
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $thisMonth = Carbon::now()->startOfMonth()->toDateString();
        $thisYear = Carbon::now()->startOfYear()->toDateString();

        $customers_chart = [];

        # Sales chart
        $customers_chart = [];
        $customers_chart_fetch = Customers::select(\DB::raw("*, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))->where('storeuser', $user->id)->where('created_at', '>=', $thisMonth)->get();

        foreach ($customers_chart_fetch as $key) {
           $key->formatted_date = Carbon::parse($key->formatted_date)->toFormattedDateString();
           if(!array_key_exists($key->formatted_date, $customers_chart)) {
               $customers_chart[$key->formatted_date] = [
                   'count'        => 0,
               ];
           }
           $customers_chart[$key->formatted_date]['count']++;
        }
        asort($customers_chart);
        $customers_chart = get_chart_data($customers_chart);

        # This month customers
        $customers_this_month = Customers::where('storeuser', $user->id)->where('created_at', '>=', $thisMonth)->count();

        # Sales chart
        $sales_chart = [];
        $sales_chart_fetch = Product_Orders::select(\DB::raw("*, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))->where('storeuser', $user->id)->where('created_at', '>=', $thisMonth)->get();

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

        # Top products
        $orders = Product_Orders::where('storeuser', $user->id)->get();
        $topproducts = [];
        foreach ($orders as $order) {
           foreach ($order->products as $key => $value) {
            $product = Products::where('id', $key)->first();
            if (!array_key_exists($key, $topproducts)) {
               $topproducts[$key] = [
                'sold'  => 0,
                'earned' => 0,
                'name'  => '',
                'price'  => '',
                'media'  => '',
               ];
            }
            $price = $value['price'];

            /**
            *
            * Use Product price
            * $price = !empty($product->salePrice) ? $product->salePrice : $product->price;
            *
            **/

            $topproducts[$key]['sold'] += $value['qty'];
            $topproducts[$key]['name'] = $product->title ?? $value['name'] ?? '';
            $topproducts[$key]['price'] = $price;
            $topproducts[$key]['media'] = $product->media ?? '';
            $topproducts[$key]['earned'] += ($value['qty'] * $price);
           }
        }
        $topproducts = array_slice($topproducts, 0, 5);
        # Sales chart
        $sales = ['total' => 0, 'last_month' => 0, 'this_month' => 0, 'last_month_percent' => 0, 'total_orders' => 0, 'recent_orders' => Product_Orders::where('storeuser', $user->id)->orderBy('id', 'DESC')->limit(5)->get()];
        $last_month = Carbon::now()->subMonth()->startOfMonth()->toDateString();

        # Total sales
        $orders = Product_Orders::where('storeuser', $user->id)->get();
        foreach ($orders as $order) {
           foreach ($order->products as $key => $value) {
            $price = $value['price'];
            /**
            *
            * Use Product price
            * $price = !empty($product->salePrice) ? $product->salePrice : $product->price;
            *
            **/
             $sales['total']  += ($value['qty'] * $price);
             $sales['total_orders'] += ($value['qty']);
           }
        }

        # last month sales
        $orders = Product_Orders::where('storeuser', $user->id)->where('created_at', '>=', $last_month)->where('created_at', '<=', $thisMonth)->get();
        foreach ($orders as $order) {
           foreach ($order->products as $key => $value) {
            $price = $value['price'];
             $sales['last_month']  += ($value['qty'] * $price);
           }
        }

        # This month sales
        $orders = Product_Orders::where('storeuser', $user->id)->where('created_at', '>=', $thisMonth)->get();
        foreach ($orders as $order) {
           foreach ($order->products as $key => $value) {
            $price = $value['price'];
             $sales['this_month']  += ($value['qty'] * $price);
           }
        }

        # Last month percentage
        try {
            $sales['last_month_percent'] = $this->calculatePercent($sales['last_month'], $sales['this_month']);
        } catch (\Exception $e) {
            $sales['last_month_percent'] = 0;
        }

        # Store Visits

        $visit_chart = Track::select(\DB::raw("`country`,`count`, YEAR(`date`) AS `year`"))->where('user', Auth()->user()->id)->get();

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
        foreach ($visit_chart as $key) {
         if(!array_key_exists($key->year, $year)) {
             $year[$key->year] = [
                 'impression'        => 0,
                 'unique'            => 0,
             ];
         }
         /* Distribute the data from the database key */
         $year[$key->year]['unique']++;
         $year[$key->year]['impression'] += $key->count;
        }
        $year = get_chart_data($year);
        $month = get_chart_data($month);
        $this_month_chart = get_chart_data($this_month_chart);
        $month = preg_replace('/[^0-9]/', '', $month);
        $year = preg_replace('/[^0-9]/', '', $year);

        # Qr Code
        QrCode::format('png')->size(500)->generate(url("$user->username"), media_path('user/qrcode/'.strtolower($user->username).'.png'));

        $options = (object) ['topproducts' => $topproducts, 'sales' => $sales, 'sales_chart' => $sales_chart, 'month' => $month, 'year' => $year, 'this_month_chart' => $this_month_chart, 'customers_chart' => $customers_chart, 'customers_this_month' => $customers_this_month];

        return view('dashboard.dashboard', ['options' => $options]);
    }

    private function calculatePercent($first_number, $last_number){
      if ($first_number >= $last_number) {
        $numbers = $last_number / $first_number;
        $updown = 'down';
      }
      if ($last_number >= $first_number) {
        $numbers = $first_number / $last_number;
        $updown = 'up';
      }
      $percent = (1 - $numbers) * 100;
      return ['percent' => $percent, 'updown' => $updown];
    }
    public function delete_shipping ($key, Request $request){
      $allShipping = user('shipping');

      if (array_key_exists($key, $allShipping)) {
        unset($allShipping[$key]);
      }

      $update = User::find(user('id'));
      $update->shipping = $allShipping;
      $update->save();

      return back()->with('success', __('That shipping location was deleted'));
    }

    public function shipping(){

      return view('dashboard.shipping.all');
    }

    public function add_shipping(){
      $countries = countries();

      return view('dashboard.shipping.add', ['countries' => $countries]);
    }

    public function edit_shipping($slug){
        $countries = countries();
        $user = Auth::user();
        $province = user('shipping.'.$slug);
        if (!array_key_exists($slug, $user->shipping)) {
          abort(404);
        }

        return view('dashboard.shipping.edit', ['countries' => $countries, 'province' => $province, 'slug' => $slug]);      
    }

    public function post_shipping(Request $request, $type){
      $user = Auth::user();
      if (!in_array($type, ['new', 'edit', 'delete'])) {
        abort(404);
      }
      $country = $request->country;
      $province = [];
      if (is_array($request->shipping)) {
        foreach ($request->shipping as $value) {
          $province[$value['province']] = ['type' => $value['type'], 'cost' => $value['cost']];
        }
      }
      if ($type == 'new') {
        $user_shipping = $user->shipping ?? [];
        $user_shipping[$country] = $province;
        $update = User::find($user->id);
        $update->shipping = $user_shipping;
        $user_shipping = json_encode($user_shipping);
        $update->save();
        return redirect()->route('user-shipping')->with(__('saved successfully'));
      }
    }
    public function transactions_history(Payments $payments, Request $request){
        $user = Auth::user();
        $invoice_id = $request->get('invoice_id');

        if (!empty($invoice_id)) {
            if (!settings('business.enabled')) {
                abort(404);
            }
            if (!$invoice = $payments->where('id', $invoice_id)->first()) {
                abort(404);
            }

            return view('dashboard.payments.transaction-invoice', ['invoice' => $invoice]);
        }

        $allpayments = $payments->where('user', $user->id)->orderBy('id', 'DESC')->paginate(10);
        # Payments Chart
        $paymentschart = [];
        $results = $payments->select(\DB::raw("COUNT(*) as count, DATE_FORMAT(`date`, '%Y-%m-%d') AS `formatted_date`, TRUNCATE(SUM(`price`), 2) AS `amount`"))->where('user', $user->id)->groupBy('formatted_date')->get();

        foreach ($results as $value) {
            $value->formatted_date = Carbon::parse($value->formatted_date)->toFormattedDateString();
            $paymentschart[$value->formatted_date] = [
                'count' => $value->count,
                'amount' => $value->amount
            ];
        }

        $paymentschart = get_chart_data($paymentschart);
        
        return view('dashboard.payments.transactions-history', ['payments' => $allpayments, 'paymentschart' => $paymentschart]);
    }

    public function settings(){
      $user = Auth::user();
      subdomain_wildcard_creation(user('id'));
      $socials    = getOtherResourceFile('socials');
      $templates  = \Theme::all();
      $json = !empty(package('domains', user())) && is_array(json_decode(package('domains', user()), true)) ? json_decode(package('domains', user())) : [];
      $domains = [];
      $user_domains = Domains::where('user', $user->id)->where('status', 1)->get();
      foreach ($user_domains as $value) {
          $json[] = $value->id;
      }
      foreach($json as $value){
          if ($domain = Domains::where('id', $value)->where('status', 1)->first()) {
              $domains[$domain->id] = (object) ['domain' => $domain->host];
          }
      }
      if (file_exists($sc = resource_path('custom/socials.php'))) {
          $sc = require $sc;
          if (is_array($sc)) {
              foreach ($sc as $key => $value) {
                  $socials[$key] = $value;
              }
          }
      }

      return view('dashboard.settings', ['socials' => $socials, 'domains' => $domains, 'templates' => $templates]);
    }

    public function blog(Request $request){
        $user = Auth::user();
        if (!package('settings.blogs')) {
            return redirect()->route('user-dashboard')->with('info', __('You cant access that page'));
        }
        if ($request->get('remove-image') == true) {
            $blog = Blog::find($request->get('id'));
            if (!empty($blog->image)) {
                if(mediaExists('media/user/blog', $blog->image)){
                    storageDelete('media/user/blog', $blog->image);
                }
            }

            return redirect()->route('user-blog')->with('success', __('Image removed successfully'));
        }
        $blogs = Blog::leftJoin('track', 'track.dyid', '=', 'blog.id')
            ->select('blog.*', DB::raw("count(track.dyid) AS track_portfolio"))
            ->groupBy('blog.id')->where('blog.user', $user->id)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        return view('dashboard.blog.blog', ['blogs' => $blogs]);
    }

    public function blog_delete($id){
        if (!Blog::where('id', $id)->exists()) {
            abort(404);
        }
        $blog = Blog::find($id);
        if (!empty($blog->image)) {
            if(mediaExists('media/user/blog', $blog->image)){
                storageDelete('media/user/blog', $blog->image);
            }
        }
        $blog->delete();
        Track::where('dyid', $id)->delete();
        return redirect()->route('user-blog')->with('success', __('That blog was successfully removed'));
    }

    public function blog_sortable(Request $request){
     foreach($request->data as $key) {
        $key['id'] = (int) $key['id'];
        $key['position'] = (int) $key['position'];
        $update = Blog::find($key['id']);
        $update->order = $key['position'];
        $update->save();
     }
    }

    public function post_blog(Request $request){
        $request->validate([
            'name' => 'required|string|min:3|max:255',
        ]);
        if (!empty($request->image)) {
          $request->validate([
              'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
          ]);
        }
        // Define request
        $name = $request->name;
        $note = $request->note;
        $slug = slugify($request->name);
        $user = Auth::user();
        $settings = ['media_url' => $request->media_url];
        $blogs = Blog::where('user', $user->id)->get();
        if (!isset($request->blog_id)) {
            if(package('settings.blogs_limits') != -1 && count($blogs) >= package('settings.blogs_limits')) {
                return back()->with('error', __("You've reached your package limit."));
            }
            $insert = new Blog;
            $insert->user = $user->id;
            $insert->name = $name;
            $insert->note = $note;
            $insert->slug = $slug;
            $insert->extra = $settings;
            $insert->save();
            if (!empty($request->image)) {
                $imageName = putStorage('media/user/blog', $request->image);
                $values = array('image' => $imageName);
                Blog::where('id', $insert->id)->update($values);
            }
          return redirect()->route('user-blog')->with('success', __('Blog posted'));
        }else{
            $blog = Blog::find($request->blog_id);
            if (!empty($request->image)) {
                if (!empty($blog->image)) {
                  if(mediaExists('media/user/blog', $blog->image)){
                      storageDelete('media/user/blog', $blog->image);
                  }
                }
                $imageName = putStorage('media/user/blog', $request->image);
                $blog->image = $imageName;
           }
           $blog->name = $name;
           $blog->note = $note;
           $blog->extra = $settings;
           $blog->slug = $slug;
           $blog->save();
          return redirect()->route('user-blog')->with('success', __('Blog updated'));
        }
    }

    public function user_stats(Request $request, TrackLinks $track_links, Linker $linker){
        $user = Auth::user();
        $type = $request->get('type');
        $url = $request->get('url');
        $link = $request->get('link');
        $url_slug = $request->get('url_slug');
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $thisMonth = Carbon::now()->startOfMonth()->toDateString();
        if (!package('settings.statistics', $user)) {
            return redirect()->route('user-dashboard')->with('info', 'You cant access that page');
        }
        if ($type == 'links') {
            if (!empty($link)) {
                if (!$track_links->where('slug', $link)->exists()) {
                    abort(404);
                }
                $linksLogs = $track_links->select(\DB::raw("*, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))
                ->where('user', $user->id)
                ->where('slug', $link)
                ->get();

                $linksLogsFD = $track_links->select(\DB::raw("*, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))
                ->where('user', $user->id)
                ->where('created_at', '>=', $thisMonth)
                ->where('slug', $link)
                ->get();
                $data = ['os' => [], 'browser' => [], 'country' => [], 'slug' => []];
                $log = [];
                $logsFD = [];
                foreach ($linksLogsFD as $value) {
                    if(!array_key_exists($value->formatted_date, $logsFD)) {
                        $logsFD[$value->formatted_date] = [
                            'impression'        => 0,
                            'unique'            => 0,
                        ];
                    }
                    /* Distribute the data from the database key */
                    $logsFD[$value->formatted_date]['unique']++;
                    $logsFD[$value->formatted_date]['impression'] += $value->views;
                }
                foreach ($linksLogs as $value) {
                    if(!array_key_exists($value->slug, $log)) {
                        $log[$value->slug] = [
                            'impression'        => 0,
                            'unique'            => 0,
                        ];
                    }
                    /* Distribute the data from the database key */
                    $log[$value->slug]['unique']++;
                    $log[$value->slug]['impression'] += $value->views;
                    if(!array_key_exists($value->os, $data['os'])) {
                        $data['os'][$value->os ?? 'N/A'] = 1;
                    } else {
                        $data['os'][$value->os]++;
                    }

                    if(!array_key_exists($value->country, $data['country'])) {
                        $data['country'][$value->country ?? 'false'] = 1;
                    } else {
                        $data['country'][$value->country]++;
                    }

                    if(!array_key_exists($value->browser, $data['browser'])) {
                        $data['browser'][$value->browser ?? 'N/A'] = 1;
                    } else {
                        $data['browser'][$value->browser]++;
                    }

                    if(!array_key_exists($value->slug, $data['slug'])) {
                        $data['slug'][$value->slug ?? 'N/A'] = 1;
                    } else {
                        $data['slug'][$value->slug]++;
                    }
                }
                unset($data['country']['false']);
                unset($data['country']['']);
                $logsFD = get_chart_data($logsFD);
                $options = (object) ['data' => $data, 'logs' => $log, 'logsFD' => $logsFD];

                return view('dashboard.stats.singlelinks-stats', ['options' => $options]);
            }
            $linksLogs = $track_links->select(\DB::raw("*, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `formatted_date`"))->where('user', $user->id)->get();

            $getAll = $track_links
            ->leftJoin('linker', 'linker.slug', '=', 'track_links.slug')
            ->select('track_links.*', 'linker.url as link_url');
            $getAll2 = $track_links
            ->leftJoin('linker', 'linker.slug', '=', 'track_links.slug')
            ->select('track_links.*', 'linker.url as link_url')->where('track_links.user', $user->id);
            if (!empty($url)) {
              $getAll->where('linker.url','LIKE','%'.$url.'%');
            }
            if (!empty($url_slug)) {
              $getAll->where('track_links.slug','LIKE','%'.$url_slug.'%');
            }
            $getAll = $getAll->where('track_links.user', $user->id)
            ->groupBy('track_links.slug')
            ->orderBy('track_links.views', 'DESC');
            $data = ['os' => [], 'browser' => [], 'country' => [], 'slug' => []];
            $log = [];
            foreach ($linksLogs as $value) {
                if(!array_key_exists($value->slug, $log)) {
                    $log[$value->slug] = [
                        'impression'        => 0,
                        'unique'            => 0,
                    ];
                }
                /* Distribute the data from the database key */
                $log[$value->slug]['unique']++;
                $log[$value->slug]['impression'] += $value->views;
                if(!array_key_exists($value->os, $data['os'])) {
                    $data['os'][$value->os ?? 'N/A'] = 1;
                } else {
                    $data['os'][$value->os]++;
                }
                if(!array_key_exists($value->country, $data['country'])) {
                    $data['country'][$value->country ?? 'false'] = 1;
                } else {
                    $data['country'][$value->country]++;
                }
                if(!array_key_exists($value->browser, $data['browser'])) {
                    $data['browser'][$value->browser ?? 'N/A'] = 1;
                } else {
                    $data['browser'][$value->browser]++;
                }

                if(!array_key_exists($value->slug, $data['slug'])) {
                    $data['slug'][$value->slug ?? 'N/A'] = 1;
                } else {
                    $data['slug'][$value->slug]++;
                }
            }
            unset($data['country']['false']);
            unset($data['country']['']);
            $logs_chart = get_chart_data($log);
            $options = (object) ['getAll' => $getAll, 'getAll2' => $getAll2, 'data' => $data, 'logs' => $log, 'logs_chart' => $logs_chart];
            return view('dashboard.stats.links-stats', ['options' => $options]);
        }


        $visit_chart_date = Track::select(\DB::raw("DATE_FORMAT(`date`, '%Y-%m') AS `formatted_date`"))->where('user', Auth()->user()->id)->where('type', 'profile')->groupBy(\DB::raw("formatted_date"))->distinct()->get();
        $visit_chart_date_fetch = [];
        foreach ($visit_chart_date as $key => $value) {
            $visit_chart_date_fetch[] = date("F", strtotime($value->formatted_date));
        }

        $visit_chart = Track::select(\DB::raw("`country`,`os`,`browser`,`count`, DATE_FORMAT(`date`, '%Y-%m-%d') AS `formatted_date`"))->where('user', Auth()->user()->id)->where('type', 'profile')->get();

        $logs_chart = Track::select(\DB::raw("`count`, DATE_FORMAT(`date`, '%Y-%m-%d') AS `formatted_date`"))
        ->where('user', $user->id)
        ->where('type', 'profile')
        ->where('date', '>=', $thisMonth)
        ->get();

        $dataTrack = Track::where('user', Auth()->user()->id)->get()
                ->groupBy(function($val) {
                    return Carbon::parse($val->date)->format('m');
                });
        $total_visits = [];
        foreach ($dataTrack as $value) {
            $total_visits[] = count($value);
        }

        $total_visits_count = Track::select(\DB::raw("COUNT(*) as count"))->where('user', Auth()->user()->id)->where('type', 'profile')->where('date', '>=', $fromDate)->first();

        $logs_data = ['country' => [],'os' => [],'browser'  => []];
        $log = [];
        foreach ($logs_chart as $key) {
            if(!array_key_exists($key->formatted_date, $log)) {
                $log[$key->formatted_date] = [
                    'impression'        => 0,
                    'unique'            => 0,
                ];
            }
            /* Distribute the data from the database key */
            $log[$key->formatted_date]['unique']++;
            $log[$key->formatted_date]['impression'] += $key->count;
        }
        foreach ($visit_chart as $key) {
            if(!array_key_exists($key->country, $logs_data['country'])) {
                $logs_data['country'][$key->country ?? 'false'] = 1;
            } else {
                $logs_data['country'][$key->country]++;
            }

            if(!array_key_exists($key->os, $logs_data['os'])) {
                $logs_data['os'][$key->os ?? 'N/A'] = 1;
            } else {
                $logs_data['os'][$key->os]++;
            }

            if(!array_key_exists($key->browser, $logs_data['browser'])) {
                $logs_data['browser'][$key->browser ?? 'N/A'] = 1;
            } else {
                $logs_data['browser'][$key->browser]++;
            }
        }
        arsort($logs_data['browser']);
        arsort($logs_data['os']);
        arsort($logs_data['country']);
        unset($logs_data['country']['false']);
        unset($logs_data['country']['']);
        $logs_chart = get_chart_data($log);

        $countryPercent = [];
        $count = 0;
        foreach ($logs_data['country'] as $key => $value) {
            $count = ($count + $value);
        }
        foreach ($logs_data['country'] as $key => $value) {
            $countryPercent[$key] = [$value, round($value / ($count / 100),2)];
        }
        $products = Products::where('user', $user->id)->get();

        $total_visits_chart = ['total_visits' => $total_visits, 'visit_chart_date' => $visit_chart_date_fetch, 'total_visits_count' => $total_visits_count];
        $options = (object) ['countryPercent' => $countryPercent, 'logs_chart' => $logs_chart];
        return view('dashboard.stats.stats', ['total_visits' => $total_visits_chart, 'products' => $products, 'logs_data' => $logs_data, 'options' => $options]);
    }


    public function postSettings(Request $request){
        $request->validate([
          'username' => 'required|min:2',
          'email' => 'email|required',
          'name_first_name' => 'required|string',
        ]);

        $username = $maybe_slug = slugify($request->username);
        $next = '_';
        $settings = [];

        while (User::where('username', '=', $username)->where('id', '!=', user('id'))->first()) {
            $username = "{$maybe_slug}{$next}";
            $next = $next . '_';
        }
        if (!empty($request->gateway_paypal_client_id) && !empty($request->gateway_paypal_secret_key)) {
            $request->merge(['gateway_paypal_status' => true]);
        }else{
            $request->merge(['gateway_paypal_status' => false]);
        }

        if (!empty($request->gateway_paystack_secret_key)) {
            $request->merge(['gateway_paystack_status' => true]);
        }else{
            $request->merge(['gateway_paystack_status' => false]);
        }

        if (!empty($request->gateway_bank_details)) {
            $request->merge(['gateway_bank_status' => true]);
        }else{
            $request->merge(['gateway_bank_status' => false]);
        }

        if (!empty($request->gateway_stripe_client) && !empty($request->gateway_stripe_secret)) {
            $request->merge(['gateway_stripe_status' => true]);
        }else{
            $request->merge(['gateway_stripe_status' => false]);
        }

        if (!empty($request->gateway_razor_key_id) && !empty($request->gateway_razor_secret_key)) {
            $request->merge(['gateway_razor_status' => true]);
        }else{
            $request->merge(['gateway_razor_status' => false]);
        }

        if (!empty($request->gateway_midtrans_client_key) && !empty($request->gateway_midtrans_server_key)) {
            $request->merge(['gateway_midtrans_status' => true]);
        }else{
            $request->merge(['gateway_midtrans_status' => false]);
        }

        if (!empty($request->gateway_mercadopago_access_token) && !empty($request->gateway_mercadopago_access_token)) {
            $request->merge(['gateway_mercadopago_status' => true]);
        }else{
            $request->merge(['gateway_mercadopago_status' => false]);
        }

        $request->gateway_cash_status = (bool) $request->gateway_cash_status;

        $settings_keys = ['name' => ['first_name', 'last_name'], 'address', 'email', 'domain', 'extra' => ['banner_url', 'shipping_types', 'invoicing', 'refund_request', 'custom_branding', 'guest_checkout', 'google_analytics', 'facebook_pixel', 'template', 'about', 'background_text_color', 'background_color'], 'gateway' => ['currency', 'paypal_status', 'paypal_mode', 'paypal_client_id', 'paypal_secret_key', 'paystack_status', 'paystack_secret_key', 'bank_status', 'bank_details', 'stripe_status', 'stripe_client', 'stripe_secret', 'razor_status', 'razor_key_id', 'razor_secret_key', 'midtrans_mode', 'midtrans_status', 'cash_status', 'midtrans_client_key', 'midtrans_server_key', 'mercadopago_status', 'mercadopago_access_token']];
        if (session()->get('admin_overhead') && user('role') == 0) {
          $settings_keys[] = 'active';
          $settings_keys[] = 'verified';
          $settings_keys[] = 'package';
          $settings_keys[] = 'package_due';
        }
        if (!package('settings.domains', user())) {
            $request->domain = 'main';
            $_POST['domain'] = 'main';
        }

        foreach ($settings_keys as $key => $value) {
            if(is_array($value)) {
                $values_array = [];
                foreach ($value as $sub_key) {
                    $values_array[$sub_key] = $request->{$key . '_' . $sub_key};
                }
                $value = json_encode($values_array);
            } else {
                $key = $value;
                $value = $_POST[$key];
            }
            $value = [$key => $value];

            $settings[$key] = $value;
            User::where('id', user('id'))->update($value);
        }
        $update = User::find(user('id'));

        if (user('shipping') == null) {
          $update->shipping = [];
        }

        $gateways = $update->gateway;
        if (!empty($request->gateway)) {
          foreach ($request->gateway as $key => $value) {
            $gateways[$key] = $value;
          }
        }
        $update->gateway = $gateways;

        $extra = $update->extra;
        if (!empty($request->settings)) {
          foreach ($request->settings as $key => $value) {
            $extra[$key] = $value;
          }
        }
        $update->extra = $extra;

        if (isset($request->theme_extra)) {
          foreach ($request->theme_extra as $key => $value) {
            $extra = json_decode($settings['extra']['extra'], true);
            $extra[$key] = $value;
            $extra = json_encode($extra);

            User::where('id', user('id'))->update(['extra' => $extra]);
          }
        }

        $socials = $request->socials;
        subdomain_wildcard_creation(user('id'));
        $media = is_array(user('media')) ? user('media') : [];

        if (!empty($socials)) {
            foreach ($socials as $key => $value) {
                $update->socials = $socials;
            }
        }
        if (!empty($request->avatar)) {
            $request->validate([
                'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            ]);
            if (!empty(user('media.avatar'))) {
                if(mediaExists('media/user/avatar', user('media.avatar'))){
                    storageDelete('media/user/avatar', user('media.avatar')); 
                }
            }
            $imageName = putStorage('media/user/avatar', $request->avatar);
            $media['avatar'] = $imageName;
        }

        if (!empty($request->favicon)) {
            $request->validate([
                'favicon' => 'image|mimes:jpeg,png,jpg,svg|max:300',
            ]);

            if (!empty(user('media.favicon'))) {
                if(mediaExists('media/user/favicon', user('media.favicon'))){
                    storageDelete('media/user/favicon', user('media.favicon')); 
                }
            }
            $imageName = putStorage('media/user/favicon', $request->favicon);
            $media['favicon'] = $imageName;
        }

        generatePages(user('id'));
        $update->media = $media;
        $update->username = $username;
        $update->save();


        if (!empty($request->password)) {
            $request->validate([
              'password' => 'min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[@$!%*#?&]/',
            ]);
            $update->password = Hash::make($request->password);
            $update->save();
        }


        return back()->with('success', 'saved successfully');
    }

    public function domains(Domains $domains){
        $user = Auth::user();
        $allDomains = $domains->where('user', $user->id)->get();
        return view('dashboard.domains.domains', ['domains' => $allDomains]);
    }

    public function domains_post_get(Domains $domains, Request $request){
        $domain_id = $request->get('id');
        $domain = null;
        $user = Auth::user();
        $user = User::find($user->id);
        $domains = Domains::where('user', $user->id)->get();
        $type = $request->get('type');

        if (empty($domain_id)) {
          if(package('settings.custom_domain_limit') != -1 && count($domains) >= package('settings.custom_domain_limit')) {
              return back()->with('error', __("You've reached your package limit."));
          }
        }

        if ($request->get('delete') == true) {
            Domains::find($request->get('id'))->delete();
            return back()->with('success', __('Deleted successfully'));
        }

        if ($type == 'setDomain') {
          $user->domain = $domain_id;
          $user->save();

          return back()->with('success', 'Saved successfully');
        }

        if (!empty($domain_id) && !$domain = $domains->where('id', $domain_id)->where('user', $user->id)->first()) {
          abort(404);
        }

        return view('dashboard.domains.post-domain', ['domain' => $domain]);
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
        $requests['user'] = $request->user;
        $requests = $request->all();
        unset($requests['_token'], $requests['submit'], $requests['domain_id']);
        $requests['created_at'] = Carbon::now(settings('timezone'));
        if (isset($request->domain_id)) {
            $request->validate([
                'host' => 'required|unique:domains,host,'.$request->domain_id,
            ]);
            unset($requests['created_at']);
            $requests['updated_at'] = Carbon::now(settings('timezone'));
            $update = $domains->where('id', $request->domain_id)->update($requests);
            return back()->with('success', __('Domain updated successfully'));
        }
        $new = $domains->insert($requests);
        return redirect()->route('user-domains')->with('success', __('Domain created successfully'));
    }

    public function login_activity(){
        $activities = DB::table('users_logs')->where('user', Auth()->user()->id)->orderBy('id', 'DESC')->paginate(10);
        return view('dashboard.activities', ['activities' => $activities]);
    }

    public function back_to_free(Request $request){
        if (strtolower($request->free) !== 'free') {
            return back()->with('error', __('Type FREE'));
        }
        $user = User::find(Auth()->user()->id);
        $user->package = 'free';
        $user->package_due = NULL;
        $user->save();
        return back()->with('success', 'Plan activated');
    }

    public function faq(){
        $faq = Faq::where('status', 1)->get();
        return view('dashboard.faq', ['faqs' => $faq]);    
    }

    public function deleteActivities(){
        $activities = DB::table('users_logs')->where('user', Auth()->user()->id)->delete();
        return back()->with('success', 'Deleted successfully');
    }

    public function delete_banner(){
        $user = Auth::user();
        if (!empty(user('media.banner', $user->id))) {
            if(file_exists(public_path('media/user/banner/' . user('media.banner', $user->id)))){
                unlink(public_path('media/user/banner/' . user('media.banner', $user->id))); 
                return redirect()->route('user-settings')->with('success', __('successfully removed your banner'));
            }
        }
        return redirect()->route('user-settings');
    }

    public function all_chats() {
      $user = Auth::user();
      $customers = Customers::where('storeuser', $user->id)->get();
      $convo = Conversations::where('user', $user->id)->get();

      return view('dashboard.chats.view', ['customers' => $customers, 'convo' => $convo]);
    }

    public function post_messages ($convo_id, Request $request){
      $user = Auth::user();
      $type = $request->from;
      $data = $request->data;

      if (isset($request->user)) {
        $user = User::find($request->user);
      }

      if (!in_array($type, ['store', 'customer'])) {
        abort(404);
      }

      if ($request->type == 'images' && $request->hasFile('data_image')) {
        $data = putStorage('media/user/chat/images', $request->data_image);
      }

      if ($request->type == 'file' && $request->hasFile('data_file')) {
        $data = putStorage('media/user/chat/files', $request->data_file);
      }

      if ($request->type == 'link') {
        $data = $request->data_link;
      }

      if ($request->type == 'text') {
        $data = $request->data_text;
      }

      $message = new Messages;
      $message->conversation_id = $convo_id;
      $message->from = $type;
      $message->user_id = $user->id;
      $message->type = $request->type;
      $message->data = $data;
      $message->save();

      return back();
    }

    public function single_chat ($id){
      $user = Auth::user();
      $customers = Customers::where('storeuser', $user->id)->get();


      if (!$convo = Conversations::where('id', $id)->where('user', $user->id)->first()) {
        abort(404);
      }

      $conversations = Conversations::where('user', $user->id)->get();

      return view('dashboard.chats.single', ['convo' => $convo, 'conversations' => $conversations, 'customers' => $customers]);
    }


    public function get_chat_messages($id, $view){


      return convo_messages_html($id, $view);
    }

    public function single_customer ($id) {
      $user = Auth::user();

      if (!$customer = Customers::where('storeuser', $user->id)->where('id', $id)->first()) {
        abort(404);
      }

      $sales = $this->get_customer_sales($id);

      $all_orders = Product_Orders::where('storeuser', $user->id)->where('customer', $id)->orderBy('id', 'DESC');

      $count_orders = $all_orders->count();

      $all_orders = $all_orders->paginate(8);

      return view('dashboard.customers.single', ['customer' => $customer, 'count_orders' => $count_orders, 'sales' => $sales, 'all_orders' => $all_orders]);
    }

    public function start_convo(Request $request) {
      $user = Auth::user();

      if ($convo = Conversations::where('user', $user->id)->where('customer', $request->customer)->first()) {
        return redirect()->route('user-chat', $convo->id);
      }

      $convo = new Conversations;
      $convo->user = $user->id;
      $convo->customer = $request->customer;
      $convo->save();

      return redirect()->route('user-chat', $convo->id);
    }

    public function refunds(){
      $user = Auth::user();

      $refunds = ProductRefund::where('user', $user->id)->get();

      return view('dashboard.orders.refunds', ['refunds' => $refunds]);
    }

    public function post_refunds($type, Request $request){

      if (!$refund = ProductRefund::find($request->refund_id)) {
        abort(404);
      }

      if ($type == 'refund-status') {
        $refund->status = $request->refund_status;
        $refund->save();
        
        return back()->with('success', __('Refund status changed'));
      }

      if ($type == 'send-refund') {
          $email = $request->email ?? '';
          $mail = (object) ['subject' => $request->mail_subject, 'message' => $request->mail_message];

           try {
            Mail::to($email)->send(new GeneralMail($mail));
           } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
          }

          return back()->with('success', __('Email Sent'));
      }

      abort(404);
    }


    public function single_refunds ($id){
      $user = Auth::user();

      if (!$refund = ProductRefund::where('user', $user->id)->where('id', $id)->first()) {
        abort(404);
      }

      $order = Product_Orders::where('storeuser', user('id'))->where('id', $refund->order_id)->first();

      if (!$order) {
        return back()->with('error', __('Order doesnt exist'));
      }


      $products = [];

      $customer = '';

      if ($customer = Customers::where('id', $order->customer)->first()) {
        $customer = $customer;
      }


      foreach ($order->products as $key => $value) {
       $product = Products::where('id', $key)->first();
       if (!array_key_exists($key, $products)) {
          $products[$key] = [
           'qty'  => 0,
           'name'  => '',
           'price'  => '',
           'media'  => '',
           'options' => ''
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
      }




      return view('dashboard.orders.single-refund', ['refund' => $refund, 'products' => $products, 'customer' => $customer, 'order' => $order]);
    }




    public function pages(){
      $user = Auth::user();

      $pages = UserPages::where('user', $user->id)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

      return view('dashboard.pages.all-pages', ['pages' => $pages]);
    }

    public function pages_sortable(Request $request){
     foreach($request->data as $key) {
        $key['id'] = (int) $key['id'];
        $key['position'] = (int) $key['position'];
        $update = UserPages::find($key['id']);
        $update->order = $key['position'];
        $update->save();
     }
    }

    public function post_page(Request $request){
      $user = Auth::user();

      $request->validate([
        'name' => 'required',
        'slug' => 'required',
      ]);

      if (UserPages::where('user', $user->id)->where('slug', $request->slug)->first()) {
        return back()->with('error', __('A page with same slug exists. Slug has to be unique'));
      }


      $page = new UserPages;
      $page->user = $user->id;
      $page->name = $request->name;
      $page->slug = $request->slug;
      $page->save();


      return redirect()->route('user-edit-pages', $page->id)->with('success', __('Page Created. Please add sections'));
    }

    public function edit_pages ($id){
      $user = Auth::user();
      if (!$page = UserPages::where('id', $id)->where('user', $user->id)->first()) {
        abort(404);
      }

      $sections = PagesSections::where('page_id', $id)->where('theme', user('extra.template', $user))->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

      $dynamic_sections = get_theme_blocks($user->id, user('extra.template', $user));

      $allpages = UserPages::where('user', $user->id)->get();


      $pages = [];

      foreach ($allpages as $value) {
        if (empty($value->parent) && $value->id !== $page->id) {
          $pages[] = $value;
        }
      }

      return view('dashboard.pages.edit', ['page' => $page, 'sections' => $sections, 'dynamic_sections' => $dynamic_sections, 'pages' => $pages]);
    }

    public function add_pages (){

      return view('dashboard.pages.add');
    }

    public function pages_sections_sortable(Request $request){
     foreach($request->data as $key) {
        $key['id'] = (int) $key['id'];
        $key['position'] = (int) $key['position'];
        $update = PagesSections::find($key['id']);
        $update->order = $key['position'];
        $update->save();
     }
    }

    public function post_sections($type, Request $request){
      $user = Auth::user();

      if (!in_array($type, ['new', 'edit', 'delete', 'set-status'])) {
        abort(404);
      }

      if (in_array($type, ['new', 'edit'])) {
        $data = '';
      }

      $data = [];
      $hasNewImage = [];

      if ($type == 'set-status') {
        $section = PagesSections::find($request->section_id);
        $section->status = $request->status;

        $section->save();


        return 'success';
      }


      if ($type == 'delete') {
        $delete = PagesSections::find($request->section_id);

        if ($delete) {
          foreach ($delete->data as $key => $value) {
            if ($value->type == 'image') {
              mediaExists('media/user/pages', $value->value) ? storageDelete('media/user/pages', $value->value) : '';
            }
          }

          $delete->delete();

          return back()->with('success', __('Section Deleted'));
        }

          return back()->with('error', __('Could not delete section'));
      }

      if ($type == 'new') {
        if (!empty($request->data)) {
          foreach ($request->data as $key => $value) {
            $inner_data = [];
            foreach ($value as $value_key => $value_value) {

              if ($value_key == 'image') {
                  if (!empty($value_value)) {
                    $imageName = putStorage('media/user/pages', $value_value);
                    $value_value = $imageName;
                    $hasNewImage[$key] = true;
                  }
              }

              $data[$key] = ['type' => $value_key, 'value' => $value_value];
            }
          }
        }
        $new = new PagesSections;
        $new->user = $user->id;
        $new->data = $data;
        $new->theme = \Theme::get(user('extra.template', $user->id))['name'];
        $new->page_id = $request->page_id;
        $new->block_slug = $request->section_slug;
        $new->save();
      }

      if ($type == 'edit') {
        $edit = PagesSections::find($request->section_id);

        $data = (array) $edit->data;

        if (!empty($request->data)) {
          foreach ($request->data as $key => $value) {
            $inner_data = [];
            foreach ($value as $value_key => $value_value) {

              if ($value_key == 'image') {
                  if (!empty($value_value)) {
                    $imageName = putStorage('media/user/pages', $value_value);
                    $value_value = $imageName;
                    $hasNewImage[$key] = true;
                  }else{
                    $value_value = $data[$key]->value ?? '';
                  }
              }

              $data[$key] = ['type' => $value_key, 'value' => $value_value];
            }
          }
        }
        foreach ($edit->data as $key => $value) {
          if ($value->type == 'image') {
            if (array_key_exists($key, $hasNewImage)) {
              mediaExists('media/user/pages/', $value->value) ? storageDelete('media/user/pages/', $value->value) : '';
            }
          }
        }

        $edit->data = $data;

        $edit->save();



        return back()->with('success', __('Saved successfully'));

      }


      return back();
    }


    public function delete_page(Request $request){
      $page = UserPages::find($request->page_id);

      $sections = PagesSections::where('page_id', $page->id)->get();

      foreach ($sections as $delete) {
        $delete = PagesSections::find($delete->id);

        foreach ($delete->data as $key => $value) {
          if ($value->type == 'image') {
            mediaExists('media/user/pages', $value->value) ? storageDelete('media/user/pages', $value->value) : '';
          }
        }

        $delete->delete();
      }

      $page->delete();


      return back()->with('success', __('Page deleted'));

    }

    public function edit_post_page (Request $request){
      $parent = null;

      if ($request->parent !== 'none') {
        $parent = $request->parent;
      }

      $user = Auth::user();
      $page = UserPages::find($request->id);
      $page->name = $request->name;
      $page->slug = $request->slug;
      $page->parent = $parent;
      $page->is_home = $request->set_as_home;
      $page->save();

      return back()->with('success', __('Page Saved'));
    }



    private function get_customer_sales ($customer_id) {
      $user = Auth::user();
      $last_month = Carbon::now()->subMonth()->startOfMonth()->toDateString();
      $thisMonth = Carbon::now()->startOfMonth()->toDateString();

      if (!$customer = Customers::where('storeuser', $user->id)->where('id', $customer_id)->first()) {
        abort(404);
      }


      $sales = ['this_month' => 0, 'last_month' => 0, 'sales_chart' => 0, 'overall_sale' => 0];


      $orders = Product_Orders::where('storeuser', $user->id)->where('customer', $customer_id)->where('created_at', '>=', $last_month)->where('created_at', '<=', $thisMonth)->get();

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

    public function first_welcome(Request $request){
      $user = Auth::user();

      generatePages(user('id'));

      if ($user->first_welcome_screen) {
        abort(404);
      }

      if ($user->first_welcome_screen == 0) {
        $user = User::find($user->id);
        $user->first_welcome_screen = 1;
        $user->save();
      }

      return view('dashboard.utility.welcome');
    }

  public function export_orders_to_csv(Request $request){
    $headers = [
       'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
       'Content-type'        => 'text/csv',
       'Content-Disposition' => 'attachment; filename=export.csv',
       'Expires'             => '0',
       'Pragma'              => 'public'
    ];
    $user = Auth::user();

    $list = Product_Orders::where('storeuser', $user->id)->get()->toArray();

    $newlist = [];

    foreach ($list as $value) {
     $values = [];

     foreach ($value as $key => $items) {
       if(is_object($items)){
         foreach($items as $array_key => $array_val){
           $values[$array_key] = $array_val;
         }
       }

      if ($key == 'products') {
        foreach ($items as $pkey => $pValue) {
          $pname = $pValue['name'] ?? '';
          $pqty = $pValue['qty'] ?? '';
          $pprice = $pValue['price'] ?? '';
          $poptions = $pValue['options'] ?? '';

          $name = slugify($pname, '_');

          $product = 'NAME - ' . $pname . ' QTY - ' . $pqty . ' PRICE - ' . $pprice . ' OPTIONS - ' . $poptions;

          $values[$name] = $product;
        }
      }

      $values[$key] = $items;

      unset($values['extra']);
      unset($values['products']);
      unset($values['details']);
      unset($values['delivered']);
      unset($values['status']);
      unset($values['updated_at']);
     }
     $newlist[] = $values;
    }

    if (empty($newlist[0])) {
      return back()->with('error', __('No order found'));
    }

    # add headers for each column in the CSV download
    array_unshift($newlist, array_keys($newlist[0]));

    $callback = function() use ($newlist) {
       $FH = fopen('php://output', 'w');
       foreach ($newlist as $row) { 
           fputcsv($FH, $row);
       }
       fclose($FH);
     };

     return response()->stream($callback, 200, $headers);
  }
}