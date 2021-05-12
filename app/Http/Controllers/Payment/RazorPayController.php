<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Razorpay\Api\Api;
use App\Model\Packages;

class RazorPayController extends Controller{
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
        $api = new Api(config('app.razor_key'), config('app.razor_secret'));
		$orderData = [
		    'receipt'         => rand(),
		    'amount'          => ($package->price->{$duration} * 100),
		    'currency'        => strtoupper($this->settings->currency),
		    'payment_capture' => 1
		];
		try {
			 $razorpayOrder = $api->order->create($orderData);
		} catch (\Exception $e) {
			return redirect()->route('pricing')->with('error', $e->getMessage());
		}
        $razorpayOrderId = $razorpayOrder['id'];
		$data = [
	        "key"               => config('app.razor_key'),
	        "amount"            => ($package->price->{$duration} * 100),
	        "name"              => $user->name,
	        "description"       => "Purchasing $package->name Package on ". config('app.name'),
	        "image"             => url('img/favicon/' . $this->settings->favicon),
	        "prefill"           => [
	            "name"              => $user->name,
	            "email"             => $user->email,
	        ],
	        "theme"             => [
	            "color"             => "#4353ff"
	        ],
	        "order_id"          => $razorpayOrderId,
	    ];
	    $data = json_encode($data);
    	return view('payment.razorPay', ['duration' => $duration, 'package' => $package, 'data' => $data]);
    }

    public function verify($plan, $duration, Request $request){
		$user = Auth::user();
		$paymentCon = new PaymentController;
        $client = new Client();
		if (!$package = Packages::where('slug', $plan)->first()) {
			abort(404);
		}
        $api = new Api(config('app.razor_key'), config('app.razor_secret'));
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
             return redirect()->route('pricing')->with('error', 'Payment was not successful.');
            }
        }
    }
}
