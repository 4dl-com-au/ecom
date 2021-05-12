<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Model\Packages;

class PaystackController extends Controller{
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
        $callback_url = route('paystack-verify', ['plan' => $package->slug, 'duration' => $duration]);  

        $client = new Client(['http_errors' => false]);
        $headers = [
            'Content-Type' => 'application/json',
            'cache-control' => 'no-cache',
            'authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
        ];
        $body = json_encode(['amount' => ($package->price->{$duration} * 100), 'email' => $user->email, 'callback_url' => $callback_url ]);
        $result = $client->request('POST', 'https://api.paystack.co/transaction/initialize', ['headers' => $headers, 'body' => $body, 'verify' => false]);
        $statuscode = $result->getStatusCode();
        if (404 === $statuscode) {
         return back()->with('error', 'Paystack response: 404');
        }
        elseif (401 === $statuscode) {
         return back()->with('error', 'Paystack response: unauthorised');
        }
        return redirect(json_decode($result->getBody()->getContents())->data->authorization_url);
    }

    public function verify($plan, $duration, Request $request){
		$user = Auth::user();
		$paymentCon = new PaymentController;
        $client = new Client();
		if (!$package = Packages::where('slug', $plan)->first()) {
			abort(404);
		}
        $headers = [
            'Content-Type' => 'application/json',
            'cache-control' => 'no-cache',
            'authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
        ];
        $reference = !empty($request->get('reference')) ? $request->get('reference') : '';
        if(empty($reference)){
            return redirect()->route('plans')->with('error', 'No reference supplied.');
        }
        $result = $client->request('GET', 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference), ['headers' => $headers]);
        $tranx = json_decode($result->getBody()->getContents());
        if(!$tranx->status){
          return redirect()->route()->with('error', 'API returned error: ' . $tranx->message);
        }

        if($tranx->data->status == "success"){
        $post = $paymentCon->addPlanToUser($user->id, $package->id, $duration, 'paystack');
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
}
