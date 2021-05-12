<?php

namespace App\Http\Controllers\Profile\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use Redirect, URL, Session, General, Str;
use Illuminate\Support\Facades\DB;
use App\Mail\GeneralMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Model\Domains;
use App\User;
use App\Model\Product_Orders;
use App\Model\Products;
use Carbon\Carbon;
use App\Http\Controllers\Profile\ProfileController as Profile;
use App\Cart;

class PaystackController extends Controller{
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

        $callback_url = route('user-paystack-verify', ['profile' => $this->profile, 'details' => $request->details, 'cart' => json_encode(session()->get('cart_'.$this->profile)), 'total' => $total]);
        $details = json_decode($request->details);
        $client = new Client(['http_errors' => false]);
        $headers = [
            'Content-Type' => 'application/json',
            'cache-control' => 'no-cache',
            'authorization' => 'Bearer ' . user('gateway.paystack_secret_key', $this->user->id),
        ];
        $body = json_encode(['amount' => ($total * 100), 'email' => $details->email, 'callback_url' => $callback_url ]);

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

    public function verify($profile = null, Request $request){
        $client = new Client(['verify' => false]);
        $order = new Profile($request);
        $headers = [
            'Content-Type' => 'application/json',
            'cache-control' => 'no-cache',
            'authorization' => 'Bearer ' . user('gateway.paystack_secret_key', $this->user->id),
        ];
        $reference = !empty($request->get('reference')) ? $request->get('reference') : '';
        if(empty($reference)){
            return redirect($this->profile)->with('error', 'No reference supplied.');
        }
        $result = $client->request('GET', 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference), ['headers' => $headers]);
        $tranx = json_decode($result->getBody()->getContents());
        if(!$tranx->status){
          return redirect($this->profile)->with('error', 'API returned error: ' . $tranx->message);
        }
        if($tranx->data->status == "success"){
            $orders = ['details' => $request->get('details'), 'total' => $request->get('total')];
            $order = $order->insertordersinit($this->user, $orders, 'Paystack');

            if ($order['response'] == 'success') {
                return redirect()->route('user-store-success', ['profile' => $this->profile, 'order_id' => $order['order_id']]);
            }
        }
    }
}
