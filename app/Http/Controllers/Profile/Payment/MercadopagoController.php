<?php

namespace App\Http\Controllers\Profile\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Http\Request;
use App\User;
use App\Model\Product_Orders;
use App\Model\Domains;
use App\Model\Products;
use App\Http\Controllers\Profile\ProfileController as Profile;
use App\Cart;

class MercadopagoController extends Controller{
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
        $details = json_decode($request->details);
        // Add Your credentials
        \MercadoPago\SDK::setAccessToken(user('gateway.mercadopago_access_token', $this->user->id));

        // Create a preference object
        $preference = new \MercadoPago\Preference();
        $payer = new \MercadoPago\Payer();
        $payer->name = $details->first_name ?? '';
        $payer->surname = $details->last_name ?? '';
        $payer->email = $details->email ?? '';
        $payer->date_created = \Carbon\Carbon::now(settings('timezone'));
        // Create a preference item

        $item = new \MercadoPago\Item();
        $item->title = "Purchasing ".count($usercart)." products on " . $this->user->username;
        $item->quantity = 1;
        $item->currency_id = user('gateway.currency', $this->user->id);
        $item->unit_price = $total;
        $preference->items = [$item];
        $preference->back_urls = [
            "success" => route('user-mercadopago-verify', ['profile' => $this->profile, 'total' => $total]),
            "failure" => route('user-profile', ['profile' => $this->user->username])
        ];
        $preference->auto_return = "approved";
        $preference->payer = $payer;
        $preference->save();

        return redirect($preference->init_point);
    }

    public function verify($profile, Request $request){
        $order = new Profile($request);
        $details = session()->get('details_'.$this->profile);

        $orders = ['details' => $details, 'total' => $request->get('total')];
        $order = $order->insertordersinit($this->user, $orders, 'Mercado Pago');

        if ($order['response'] == 'success') {
            return redirect()->route('user-store-success', ['profile' => $this->profile, 'order_id' => $order['order_id']]);
        }
    }
}
