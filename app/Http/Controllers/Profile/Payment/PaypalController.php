<?php

namespace App\Http\Controllers\Profile\Payment;

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
use App\Http\Controllers\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Model\Domains;
use App\Model\Product_Orders;
use App\Model\Products;
use App\Http\Controllers\Profile\ProfileController as Profile;
use App\Cart;

class PaypalController extends Controller{
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
        $session = session();
        $names = [];
        $session->put('details_'.$this->profile, $request->details);

        $cart = new Cart;
        $usercart = $cart->getAll($this->user->id);

        foreach ($usercart as $key => $value) {
            $names[] = $value->name;
        }
        
        $names = implode(', ', $names);
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
        $price = in_array(user('gateway.currency', $this->user->id), ['JPY', 'TWD', 'HUF']) ? number_format($total, 0, '.', '') : number_format($total, 2, '.', '');
        $paypal_conf = ['client_id' => 
            user('gateway.paypal_client_id', $this->user->id), 'secret' => user('gateway.paypal_secret_key', $this->user->id), 'settings' => array('mode' => user('gateway.paypal_mode', $this->user->id), 'http.ConnectionTimeOut' => 60)];
        $this->api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->api_context->setConfig($paypal_conf['settings']);
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($names)->setCurrency(user('gateway.currency', $this->user->id))->setQuantity(1)->setPrice($price);

        $itemList = new ItemList();
        $itemList->setItems(array($item));

        $amount = new Amount();
        $amount->setCurrency(user('gateway.currency', $this->user->id))->setTotal($price);

        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($itemList)
        ->setDescription("Purchasing ".count($usercart)." products on " . $this->user->username);

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(route('user-paypal-verify', ['profile' => $this->profile, 'details' => $request->details, 'cart' => json_encode(session()->get('cart_'.$this->profile)), 'total' => $total]))
        ->setCancelUrl(url()->current());

        $payment = new Payment();
        $payment->setIntent('Sale')->setPayer($payer)->setRedirectUrls($redirect_urls)
        ->setTransactions(array($transaction));

        try {
            $payment->create($this->api_context);
        } catch (\Exception $ex) {
            return back()->with('error', 'Paypal response: ' . json_decode($ex->getData())->error_description);
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

    public function verify($profile = null, Request $request){
        $paypal_conf = ['client_id' => user('gateway.paypal_client_id', $this->user->id), 'secret' => user('gateway.paypal_secret_key', $this->user->id), 'settings' => array('mode' => user('gateway.paypal_mode', $this->user->id), 'http.ConnectionTimeOut' => 60)];
        $this->api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->api_context->setConfig($paypal_conf['settings']);
        if (empty($request->query('paymentId')) || empty($request->query('PayerID')) || empty($request->query('token'))){
            return redirect()->route('user-profile', $this->profile)->with('error', 'Payment was not successful.');
        }
        $payment = Payment::get($request->query('paymentId'), $this->api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->query('PayerID'));
        $result = $payment->execute($execution, $this->api_context);
        if ($result->getState() != 'approved') {
            return redirect()->rroute('user-profile', $this->profile)->with('error', 'Payment was not successful.');
        }
        if ($result->getState() == 'approved'){
            $order = new Profile($request);
            $orders = ['details' => $request->get('details'), 'total' => $request->get('total')];
            $order = $order->insertordersinit($this->user, $orders, 'PayPal');

            if ($order['response'] == 'success') {
                return redirect()->route('user-store-success', ['profile' => $this->profile, 'order_id' => $order['order_id']]);
            }
        }

    }
}
