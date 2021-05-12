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

class CashController extends Controller{
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
        $cart = new Cart;
        $usercart = $cart->getAll($this->user->id);
        $order = new Profile($request);

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

        $orders = ['details' => $request->get('details'), 'total' => $total];
        
        $order = $order->insertordersinit($this->user, $orders, 'Cash');

        if ($order['response'] == 'success') {
            return redirect()->route('user-store-success', ['profile' => $this->profile, 'order_id' => $order['order_id']]);
        }


    }

    public function verify($profile = null, Request $request){
        abort(404);
    }
}
