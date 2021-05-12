<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Http\Request;
use App\Model\Packages;

class MidTransController extends Controller{
	public $settings;
    public function __construct(){
        $this->middleware('auth');
        $general = new General();
        $this->settings = $general->settings();
    }

	public function create($plan, $duration){
        if (license('license') !== 'Extended License') {
            return back()->with('error', __('Payment gateway not available'));
        }
		$user = Auth::user();
        if (!empty($duration) && !in_array($duration, ['annual', 'month', 'quarter'])) {
            abort(404);
        }
		if (!$package = Packages::where('slug', $plan)->first()) {
			abort(404);
		}
		if (empty($package->price->{$duration})) {
			return back()->with('error', 'Empty Pricing');
		}
		Config::$serverKey = env('MIDTRANS_SERVER_KEY');
		Config::$isProduction = (env('MIDTRANS_MODE') == 'live' ? true : false);
		Config::$appendNotifUrl = "".env('APP_URL').", http://example.com";
		Config::$overrideNotifUrl = "".route('midtrans-verify', ['plan' => $package->slug, 'duration' => $duration])."";
        $item = [
        	'id' 		=> $package->id,
        	'price' 	=> $package->price->{$duration},
        	'quantity' 	=> 1,
        	'name'	    => $package->name,
        ];
		$transaction = array(
		    'order_id' => rand(),
		    'gross_amount' => $package->price->{$duration},
		);
		$customer = array(
		    'first_name'    => full_name($user->id),
		    'email'         => $user->email,
		);
		$callbacks = [
			'finish' =>	route('midtrans-verify', ['plan' => $package->slug, 'duration' => $duration]),
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
			return redirect()->route('pricing')->with('error', $e->getMessage());
		}
	}

	public function verify($plan, $duration, Request $request){
		$user = Auth::user();
		$paymentCon = new PaymentController;
		if (!$package = Packages::where('slug', $plan)->first()) {
			abort(404);
		}
		Config::$serverKey = env('MIDTRANS_SERVER_KEY');
		Config::$isProduction = (env('MIDTRANS_MODE') == 'live' ? true : false);
		$status = \Midtrans\Transaction::status($request->get('order_id'));
		if (in_array($status->transaction_status, ['capture', 'settlement'])) {
	        $post = $paymentCon->addPlanToUser($user->id, $package->id, $duration, 'stripe');
	        if ($post->status == 'success') {
	            $email = $paymentCon->sendPayment($user, $package);
	            if (!empty($email->status) && $email->status == 'success') {
	                return redirect()->route('pricing')->with('success', 'Package activated');
	            }else{
	                return redirect()->route('pricing')->with('success', 'payment successful with little errors');
	            }
	        }
		}else{
			return redirect()->route('pricing')->with('error', $status->status_message);
		}
	}
}
