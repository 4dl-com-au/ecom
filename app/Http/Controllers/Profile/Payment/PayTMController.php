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
use Anand\LaravelPaytmWallet\Facades\PaytmWallet;
use App\Cart;

class PayTMController extends Controller{
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

        # Credentials
        config(['services.paytm-wallet.merchant_id' => user('gateway.paytm_merchant_id', $this->user->id)]);
	    config(['services.paytm-wallet.merchant_key' => user('gateway.paytm_merchant_key', $this->user->id)]);
	    config(['services.paytm-wallet.channel' => user('gateway.paytm_channel', $this->user->id)]);
	    config(['services.paytm-wallet.merchant_website' => user('gateway.paytm_merchant_website', $this->user->id)]);
	    config(['services.paytm-wallet.env' => user('gateway.paytm_environment', $this->user->id)]);
	    config(['services.paytm-wallet.industry_type' => user('gateway.paytm_industrytype', $this->user->id)]);



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




        $callback_url = route('user-paytm-verify', ['profile' => $this->profile, 'details' => $request->details, 'total' => $total]);

        $details = json_decode($request->details);


        try {
	        $transaction = PaytmWallet::with('receive');
		    $transaction->prepare([
		       'order' 		   => \Str::random(10),
		       'user'  		   => $this->user->id, 
		       'mobile_number' => $details->phone ?? '',
		       'email' 		   => $details->email ?? '',
		       'amount' 	   => $total,
		       'callback_url'  => $callback_url
		    ]);

		    return $transaction->receive();
        } catch (\Exception $e) {
		    return back()->with('error', $e->getMessage());
        }
	}

	public function verify($profile = null, Request $request){
        # Credentials
        config(['services.paytm-wallet.merchant_id' => user('gateway.paytm_merchant_id', $this->user->id)]);
	    config(['services.paytm-wallet.merchant_key' => user('gateway.paytm_merchant_key', $this->user->id)]);
	    config(['services.paytm-wallet.channel' => user('gateway.paytm_channel', $this->user->id)]);
	    config(['services.paytm-wallet.merchant_website' => user('gateway.paytm_merchant_website', $this->user->id)]);
	    config(['services.paytm-wallet.env' => user('gateway.paytm_environment', $this->user->id)]);
	    config(['services.paytm-wallet.industry_type' => user('gateway.paytm_industrytype', $this->user->id)]);

        $transaction = PaytmWallet::with('receive');

	    $response = $transaction->response();
        
        $order_id = $transaction->getOrderId(); // return a order id
      
        $transaction->getTransactionId();

        if ($transaction->isSuccessful()) {
	        $order = new Profile($request);
	     	$orders = ['details' => $request->get('details'), 'total' => $request->get('total')];
	        $order = $order->insertordersinit($this->user, $orders, 'PayTM');
	        if ($order['response'] == 'success') {
	            return redirect()->route('user-store-success', ['profile' => $this->profile, 'order_id' => $order['order_id']]);
	        }

        } else if ($transaction->isFailed()) {
	        return redirect()->route('user-profile', ['profile' => $this->profile])->with('error', __('Transaction Failed'));
            
        } else if ($transaction->isOpen()) {
	        return redirect()->route('user-profile', ['profile' => $this->profile])->with('error', __('Your payment is processing.'));
        }
        return redirect()->route('user-profile', ['profile' => $this->profile])->with('info', $transaction->getResponseMessage());
	}
}
