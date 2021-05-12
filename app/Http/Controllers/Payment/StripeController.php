<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Stripe\Stripe;
use App\Model\Packages;

class StripeController extends Controller{
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
	    Stripe::setApiKey(config('app.stripe_secret'));
	    $price = in_array($this->settings->currency, ['MGA', 'BIF', 'CLP', 'PYG', 'DJF', 'RWF', 'GNF', 'UGX', 'JPY', 'VND', 'VUV', 'XAF', 'KMF', 'KRW', 'XOF', 'XPF']) ? number_format($package->price->{$duration}, 2, '.', '') : number_format($package->price->{$duration}, 2, '.', '') * 100;
	    $stripe = \Stripe\Checkout\Session::create([
	        'payment_method_types' => ['card'],
	        'line_items' => [[
	            'name'        => $package->name,
	            'description' => "Purchasing $package->name Package on " . ucfirst(config('app.name')),
	            'amount'      => $price,
	            'currency'    => $this->settings->currency,
	            'quantity'    => 1,
	        ]],
	        'metadata' => [
	            'user_id'    => $user->id,
	            'package_id' => $package->id,
	            'package'    => $package->name,
	        ],
	        'success_url' => route('stripe-verify', ['plan' => $package->slug, 'duration' => $duration]),
	        'cancel_url' => route('pricing'),
	    ]);
	    Session::put('stripe', $stripe);
	    return view('payment.stripe');
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
