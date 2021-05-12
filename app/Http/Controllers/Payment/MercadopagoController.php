<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Razorpay\Api\Api;
use App\Model\Packages;

class MercadopagoController extends Controller{
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
        // Add Your credentials
        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

        // Create a preference object
        $preference = new \MercadoPago\Preference();
        $payer = new \MercadoPago\Payer();
        $payer->name = user('name.first_name');
        $payer->surname = user('name.last_name');
        $payer->email = user('email');
        $payer->date_created = Carbon::now(settings('timezone'));
        // Create a preference item
        $item = new \MercadoPago\Item();
        $item->title = $package->name;
        $item->quantity = 1;
        $item->currency_id = settings('currency');
        $item->unit_price = $package->price->{$duration};
        $preference->items = [$item];
        $preference->back_urls = [
            "success" => route('mercadopago-verify', ['plan' => $package->slug, 'duration' => $duration]),
            "failure" => route('pricing')
        ];
        $preference->auto_return = "approved";
        $preference->payer = $payer;
        $preference->save();

        return redirect($preference->init_point);
    }

    public function verify($plan, $duration, Request $request){
		$user = Auth::user();
		$paymentCon = new PaymentController;
        $client = new Client();
		if (!$package = Packages::where('slug', $plan)->first()) {
			abort(404);
		}

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
