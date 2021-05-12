<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\PaymentController;
use Anand\LaravelPaytmWallet\Facades\PaytmWallet;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Stripe\Stripe;
use App\Model\Packages;

class PayTMController extends Controller{
	public $settings;
    public function __construct(){
        #$this->middleware('auth');
        $general = new General();
        $this->settings = $general->settings();
    }

	public function create($plan, $duration, Request $request){
		if (!Auth::check()) {
			abort(404);
		}
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

		if ($request->get('proceed') == true) {
		    $transaction = PaytmWallet::with('receive');
		    try {
			    $transaction->prepare([
			       'order' 		   => \Str::random(10),
			       'user'  		   => $user->id, 
			       'mobile_number' => $request->get('phone'),
			       'email' 		   => $user->email,
			       'amount' 	   => $package->price->{$duration},
			       'callback_url'  => route('paytm-verify', ['plan' => $package->slug, 'duration' => $duration, 'user_id' => $user->id])
			    ]);
		    	 return $transaction->receive();
		    } catch (\Exception $e) {
		    	return back()->with('error', $e->getMessage());
		    }
		}


	    return view('payment.paytm', ['plan' => $package->slug, 'duration' => $duration]);
	}

	public function verify($plan, $duration){
		$user = Auth::user();
		if (!$package = Packages::where('slug', $plan)->first()) {
			abort(404);
		}
		$paymentCon = new PaymentController;
        $post = $paymentCon->addPlanToUser($user->id, $package->id, $duration, 'stripe');
        if ($post->status == 'success') {
            $email = $paymentCon->sendPayment($user, $package);
            if (!empty($email->status) && $email->status == 'success') {
                return redirect()->route('pricing')->with('success', 'Package activated');
            }else{
                return redirect()->route('pricing')->with('success', 'payment successful with little errors');
            }
        }
	}
}
