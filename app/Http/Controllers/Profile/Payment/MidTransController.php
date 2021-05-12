<?php

namespace App\Http\Controllers\Profile\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Http\Request;
use App\User;
use App\Model\Product_Orders;
use App\Model\Domains;
use App\Model\Products;
use App\Http\Controllers\Profile\ProfileController as Profile;
use App\Cart;

class MidTransController extends Controller{
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
        $session = session();
        $session->put('details_'.$this->profile, $request->details);
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
        $details = json_decode($request->details);
		Config::$serverKey = user('gateway.midtrans_server_key', $this->user->id);
		Config::$isProduction = (user('gateway.midtrans_mode', $this->user->id) == 'live' ? true : false);
		Config::$appendNotifUrl = "".route('user-profile', $this->profile).", http://example.com";
		Config::$overrideNotifUrl = "".route('user-midtrans-verify', ['profile' => $this->profile, 'total' => $total])."";
        $item = [
        	'id' 		=> 1,
        	'price' 	=> $total,
        	'quantity' 	=> 1,
        	'name'	    => "Purchasing ".count($usercart)." products on " . $this->user->username,
        ];
		$transaction = array(
		    'order_id' => rand(),
		    'gross_amount' => $total,
		);
		$customer = array(
		    'first_name'    => $details->first_name ?? '',
		    'email'         => $details->email ?? '',
		);
		$callbacks = [
			'finish' =>	route('user-midtrans-verify', ['profile' => $this->profile, 'total' => $total]),
		];
		$item = [$item];
		$params = array(
		    'transaction_details' => $transaction,
		    'customer_details' => $customer,
		    'item_details' => $item,
		    'callbacks'		=> $callbacks,
		);
		try {
		    $paymentUrl = Snap::createTransaction($params)->redirect_url;
		    return redirect($paymentUrl);
		}
		catch (\Exception $e) {
			return redirect()->route('user-profile', $this->profile)->with('error', $e->getMessage());
		}
	}

	public function verify($profile = null, Request $request){
        $order = new Profile($request);
		Config::$serverKey = user('gateway.midtrans_server_key', $this->user->id);
		Config::$isProduction = (user('gateway.midtrans_mode', $this->user->id) == 'live' ? true : false);
		$status = \Midtrans\Transaction::status($request->get('order_id'));
		if (in_array($status->transaction_status, ['capture', 'settlement'])) {
	        $details = session()->get('details_'.$this->profile);

	        $orders = ['details' => $details, 'total' => $request->get('total')];
	        $order = $order->insertordersinit($this->user, $orders, 'Midtrans');

	        if ($order['response'] == 'success') {
	            return redirect()->route('user-store-success', ['profile' => $this->profile, 'order_id' => $order['order_id']]);
	        }
		}else{
			return redirect()->route('user-profile', $this->profile)->with('error', $status->status_message);
		}
	}
}
