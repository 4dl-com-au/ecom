<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use App\Http\Controllers\Dashboard\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use App\Model\Packages;

class PaypalController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        $general = new General();
        $this->settings = $general->settings();
    }
    public function create($plan, $duration, Request $request){
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
        $price = in_array($this->settings->currency, ['JPY', 'TWD', 'HUF']) ? number_format($package->price->{$duration}, 0, '.', '') : number_format($package->price->{$duration}, 2, '.', '');
        $paypal_conf = config('paypal');
        $this->api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $request->session()->put('price', $price);
        $this->api_context->setConfig($paypal_conf['settings']);
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($package->name)->setCurrency($this->settings->currency)->setQuantity(1)->setPrice($price);

        $itemList = new ItemList();
        $itemList->setItems(array($item));

        $amount = new Amount();
        $amount->setCurrency($this->settings->currency)->setTotal($price);

        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($itemList)
        ->setDescription('Payment for' . $package->name);

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(route('paypal-verify', ['plan' => $package->slug, 'duration' => $duration]))
        ->setCancelUrl(url()->current());

        $payment = new Payment();
        $payment->setIntent('Sale')->setPayer($payer)->setRedirectUrls($redirect_urls)
        ->setTransactions(array($transaction));

        try {
            $payment->create($this->api_context);
        } catch (\Exception $ex) {
            return back()->with('error', 'Paypal response: ' . json_decode($ex->getData())->error_description ?? '');
        }
        foreach($payment->getLinks() as $link) {
            if($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        if(isset($redirect_url)) {
            return redirect($redirect_url);
        }// If we don't have redirect url, we have unknown error.
        return redirect()->back()->withError('Unknown error occurred');
    }

    public function verify($plan, $duration, Request $request){
		$user = Auth::user();
		if (!$package = Packages::where('slug', $plan)->first()) {
			abort(404);
		}
		$paymentCon = new PaymentController;
        $paypal_conf = config('paypal');
        $this->api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->api_context->setConfig($paypal_conf['settings']);
        if (empty($request->query('paymentId')) || empty($request->query('PayerID')) || empty($request->query('token'))){
            return redirect(route('pricing'))->withError('Payment was not successful.');
        }
        $payment = Payment::get($request->query('paymentId'), $this->api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->query('PayerID'));
        $result = $payment->execute($execution, $this->api_context);
        if ($result->getState() != 'approved') {
            return redirect()->route('pricing')->with('error', 'Payment was not successful.');
        }
        if ($result->getState() == 'approved'){
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
}
