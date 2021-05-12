<?php

namespace App\Http\Controllers\Profile\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Model\Product_Orders;
use App\Model\Domains;
use App\Model\Products;
use App\Http\Controllers\Profile\ProfileController as Profile;
use App\Cart;

class StripeController extends Controller{
    private $user;
    private $profile;
    public function __construct(Request $request){
        $general = new General();
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
    }

	public function create($profile = null, Request $request){
        if (license('license') !== 'Extended License') {
            return back()->with('error', __('Payment gateway not available'));
        }
        $cart = new Cart;
        $usercart = $cart->getAll($this->user->id);
        $total = 0;
        if (empty($usercart)) {
          return redirect($this->profile);
        }
        $total = Cart::total($this->user->id);
        $country = json_decode($request->details)->country;
        $shipping = $request->get('shipping');
        if ($request->get('shipping') !== 'none') {
            if (array_key_exists($country, $this->user->shipping) && array_key_exists($shipping, $this->user->shipping[$country])) {
                if (in_array(user('shipping.'.$country.'.'.$shipping.'.type', $this->user->id), ['pickup', 'flat']) && user('shipping.'.$country.'.'.$shipping.'.cost', $this->user->id) !== '0') {
                    $total = $total + user('shipping.'.$country.'.'.$shipping.'.cost', $this->user->id);
                }
            }
        }
        $callback_url = route('user-stripe-verify', ['profile' => $this->profile, 'details' => $request->details, 'total' => $total]);
        $details = json_decode($request->details);
	    Stripe::setApiKey(user('gateway.stripe_secret', $this->user->id));
	    $price = in_array(user('gateway.currency', $this->user->id), ['MGA', 'BIF', 'CLP', 'PYG', 'DJF', 'RWF', 'GNF', 'UGX', 'JPY', 'VND', 'VUV', 'XAF', 'KMF', 'KRW', 'XOF', 'XPF']) ? $total : $total * 100;
	    $stripe = \Stripe\Checkout\Session::create([
	        'payment_method_types' => ['card'],
	        'line_items' => [[
	            'name'        => json_decode($request->details)->first_name ?? '',
	            'description' => "Purchasing ".count($usercart)." products on " . $this->user->username,
	            'amount'      => $price,
	            'currency'    => user('gateway.currency', $this->user->id),
	            'quantity'    => 1,
	        ]],
	        'success_url' => $callback_url,
	        'cancel_url' => route('user-profile', $this->profile),
	    ]);

	    return view('profile.payment.stripe', ['client_id' => user('gateway.stripe_client', $this->user->id), 'stripe' => $stripe]);
	}

	public function verify($profile = null, Request $request){
        $order = new Profile($request);
     	  $orders = ['details' => $request->get('details'), 'total' => $request->get('total')];
        $order = $order->insertordersinit($this->user, $orders, 'Stripe');

        if ($order['response'] == 'success') {
            return redirect()->route('user-store-success', ['profile' => $this->profile, 'order_id' => $order['order_id']]);
        }
	}
}
