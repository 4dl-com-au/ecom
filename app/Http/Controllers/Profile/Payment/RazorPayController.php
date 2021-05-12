<?php

namespace App\Http\Controllers\Profile\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Domains;
use GuzzleHttp\Client;
use Razorpay\Api\Api;
use App\User;
use App\Model\Product_Orders;
use App\Model\Products;
use App\Http\Controllers\Profile\ProfileController as Profile;
use App\Cart;

class RazorPayController extends Controller{
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
        $callback_url = route('user-razor-verify', ['profile' => $this->profile, 'details' => $request->details, 'cart' => json_encode(session()->get('cart_'.$this->profile)), 'total' => $total]);
        $details = json_decode($request->details);
        $api = new Api(user('gateway.razor_key_id', $this->user->id), user('gateway.razor_secret_key', $this->user->id));
		$orderData = [
		    'receipt'         => rand(),
		    'amount'          => ($total * 100),
		    'currency'        => strtoupper(user('gateway.currency', $this->user->id)),
		    'payment_capture' => 1
		];
		try {
			 $razorpayOrder = $api->order->create($orderData);
		} catch (\Exception $e) {
			return redirect()->route('user-profile', $this->profile)->with('error', $e->getMessage());
		}
        $razorpayOrderId = $razorpayOrder['id'];
		$data = [
	        "key"               => user('gateway.razor_key_id', $this->user->id),
	        "amount"            => ($total * 100),
	        "name"              => $details->first_name ?? '',
	        "description"       => "Purchasing ".count($usercart)." products on " . $this->user->username,
	        "prefill"           => [
	            "name"              => $details->first_name ?? '',
	            "email"             => $details->email ?? '',
	        ],
            "theme"             => [
                "color"             => "#4353ff"
            ],
	        "order_id"          => $razorpayOrderId,
	    ];
	    $data = json_encode($data);
    	return view('profile.payment.razorPay', ['profile' => $this->profile, 'details' => $details, 'total' => $total, 'data' => $data]);
    }

    public function verify($profile = null, Request $request){
        $order = new Profile($request);
        $api = new Api(user('gateway.razor_key_id', $this->user->id), user('gateway.razor_secret_key', $this->user->id));
        if(!empty($request->get('razorpay_payment_id'))) {
            $success = true;
            try {
                $payment = $api->payment->fetch($request->get('razorpay_payment_id'));
            } catch (\Exception $e) {
                $success == false;
                return redirect()->back()->withError($e->getMessage());
            }
            if ($success == true) {
                # Check if the amount is same as duration amount
                # if (substr($payment['amount'], 0, -2) !== $package->price->{$duration}) {
                #    return redirect()->route('pricing')->with('error', 'Cannot proceed');
                # }
                $orders = ['details' => $request->get('details'), 'total' => $request->get('total')];
                $order = $order->insertordersinit($this->user, $orders, 'Razorpay');

                if ($order['response'] == 'success') {
                    return redirect()->route('user-store-success', ['profile' => $this->profile, 'order_id' => $order['order_id']]);
                }
            }else{
             return redirect()->route('user-profile', $this->profile)->with('error', 'Payment was not successful.');
            }
        }
    }
}
