<?php
namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
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
use Stripe\Stripe;
use App\Mail\GeneralMail;
use Razorpay\Api\Api;
use App\PendingPayments;
use App\CurrencySymbol;
use Illuminate\Support\Facades\Config;
use Redirect, URL, Session, General, Paystack;
use App\Model\Packages;
use App\Model\Payments;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;


class PaymentController extends Controller
{
    private $api_context;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $settings;
    
    public function __construct(){
        $this->middleware('auth');
        $general = new General();
        $this->settings = $general->settings();
    }

    public function payment_select($plan, Request $request){
        $general    = new General();
        $gateways   = getOtherResourceFile('gateways');
        if (license('license') !== 'Extended License') {
            $gateways = [];
            $gateways['bank'] = ['name' => 'Bank', 'banner' => 'banktransfer.png'];
            $gateways['paypal'] = ['name' => 'PayPal', 'banner' => 'paypal.png'];
        }
        if (!$this->settings->payment_system) {
            return redirect()->route('home.manage');
        }
        $user = Auth::user();
        if ($plan == 'free') {
            $package = $this->settings->package_free;
            if ($package->status == 2) {
                abort(404);
            }
            return view('dashboard.payments.back-to-free');
        }elseif ($plan == 'trial') {
            $newdue  = Carbon::now($this->settings->timezone);
            $package = $this->settings->package_trial;
            if ($package->status == 2) {
                abort(404);
            }
            if (!$user->package_trial_done) {
                $user = User::find($user->id);
                $user->package_due = $newdue->addDays($package->price->expiry);
                $user->package = 'trial';
                $user->package_trial_done = 1;
                $user->save();
            }else{
                return redirect()->route('pricing')->with('error', 'Pro trial already done');
            }
            return redirect()->route('pricing')->with('success', 'Plan activated');
        }
        if (!$plan = Packages::where('slug', $plan)->first()) {
            abort(404);
        }


        $int_check = ["month" => $plan->price->month, "quarter" => $plan->price->quarter, "annual" => $plan->price->annual];
        $not_int   = [];

        foreach ($int_check as $key => $item) {
          if (!empty($item) && !is_numeric($item)) {
              $not_int[$key] = $item;
          }
        }

        if (!empty($not_int)) {
            return back()->with('error', 'Invalid package price on '.strtoupper(implode(' , ', array_keys($not_int))).'. Prices have to be in numbers. Kindly fix for package to work');
        }
        $yearly_price_savings = ceil(($plan->price->month * 12) - $plan->price->annual);

        $quarterly_price_savings = ceil(($plan->price->month * 6) - $plan->price->quarter);

        $savings = ['yearly' => $yearly_price_savings, 'quarterly' => $quarterly_price_savings];

        if (!empty($request->get('payment_plan')) && !in_array($request->get('payment_plan'), ['annual', 'month', 'quarter'])) {
            abort(404);
        }
        if (!empty($request->get('payment_plan')) && !empty($request->get('gateway'))) {
             try {
                if ($request->get('payment_plan') == 'month' && $plan->price->month == 0) {
                    $this->addPlanToUser($user->id, $plan->id, 'month', __('No Price'));

                    return back()->with('success', __('Plan activated'));
                }

                if ($request->get('payment_plan') == 'quarter' && $plan->price->quarter == 0) {
                    $this->addPlanToUser($user->id, $plan->id, 'quarter', __('No Price'));

                    return back()->with('success', __('Plan activated'));
                }
                if ($request->get('payment_plan') == 'annual' && $plan->price->annual == 0) {
                    $this->addPlanToUser($user->id, $plan->id, 'annual', __('No Price'));

                    return back()->with('success', __('Plan activated'));
                }

                 return redirect()->route($request->get('gateway').'-create', ['plan' => $plan->slug, 'duration' => $request->get('payment_plan')]);
             } catch (\Exception $e) {
                 return back()->with('error', __('Please select a valid gateway'));
             }
        }
        return view('dashboard.payments.purchase', ['plan' => $plan, 'savings' => $savings, 'gateway' => $gateways]);
    }
    public function sendPayment($user, $plan){

     if (!empty($this->settings->email_notify->payment) && $this->settings->email_notify->payment) {
            $emails = $this->settings->email_notify->emails;
            $emails = explode(',', $emails);
            $emails = str_replace(' ', '', $emails);
            $email = (object) array('subject' => 'New Payment', 'message' => '<p> <b>'.ucfirst(user('name.first_name')).'</b> Just paid for <b>'.ucfirst($plan->name).'</b>. <br> Head to your dashboard to view earnings</p><br>');
            try {
                Mail::to($emails)->send(new GeneralMail($email));
             } catch (\Exception $e) {
                 return (object) ['status' => 'error', 'response' => 'send mail error'];
             }
            return (object) ['status' => 'success', 'response' => 'Email Sent'];
      }
    }

    public function payment_invoice($plan, Request $request){
        $gateway = $request->get('gateway');
        $user = Auth::user();
        $duration = $request->get('payment_plan');
        if (!in_array($duration, ['month', 'annual', 'quarter'])) {
            abort(404);
        }
        if (in_array($gateway, ['razor', 'paypal', 'mercadopago', 'paystack', 'bank', 'stripe', 'midtrans', 'paytm'])) {
            $gateway = ucfirst($gateway);
        }else{
            $gateway = false;
        }
        if(!$plan = Packages::where('slug', $plan)->first()){
            if ($this->settings->business->enabled) {
             return abort(404);
            }
            return abort(404);
        }

         if ($request->get('payment_plan') == 'month' && $plan->price->month == 0) {
             $this->addPlanToUser($user->id, $plan->id, 'month', __('No Price'));

             return back()->with('success', __('Plan activated'));
         }

         if ($request->get('payment_plan') == 'quarter' && $plan->price->quarter == 0) {
             $this->addPlanToUser($user->id, $plan->id, 'quarter', __('No Price'));

             return back()->with('success', __('Plan activated'));
         }
         if ($request->get('payment_plan') == 'annual' && $plan->price->annual == 0) {
                    $this->addPlanToUser($user->id, $plan->id, 'annual', __('No Price'));
             return back()->with('success', __('Plan activated'));
         }


         try {
             route(strtolower($gateway).'-create', ['plan' => $plan->slug, 'duration' => $duration]);
         } catch (\Exception $e) {
                 return back()->with('error', __('Please select a valid gateway'));
         }



        return view('dashboard.payments.invoice', ['plan' => $plan, 'gateway' => $gateway, 'duration' => $duration]);
    }


    public function addPlanToUser($user_id, $plan_id, $duration, $gateway){
        $newdue  = Carbon::now(settings('timezone'));
        $user    = User::find($user_id);
        $package = Packages::where('id', $plan_id)->first();
        $payment = new \StdClass();
        $payment->date = "";
        if ($duration == "month") {
            $newdue->addMonths(1);
            $payment->date = $newdue;
        }elseif ($duration == "quarter") {
            $newdue->addMonths(6);
            $payment->date = $newdue;
        }elseif ($duration == "annual") {
            $newdue->addMonths(12);
            $payment->date = $newdue;
        }else{
            $newdue->addMonths(1);
            $payment->date = $newdue;
        }
        $user->package = $plan_id;
        $user->package_due = $payment->date;
        $user->save();
        $new = new Payments();
        $new->user      = $user_id;
        $new->name      = user('name.first_name') .' '. user('name.last_name');
        $new->email     = $user->email;
        $new->duration  = $duration;
        $new->package_name  = $package->name;
        $new->price     = $package->price->{$duration} ?? Null;
        $new->currency  = settings('currency');
        $new->ref       = 'PR_'. $this->randomShortname();
        $new->package   = $plan_id;
        $new->gateway   = $gateway;
        $new->date      = Carbon::now(settings('timezone'));
        $new->save();
        return (object) ['status' => 'success'];
    }

    public function randomShortname($min = 3, $max = 9) {
      $length = rand($min, $max);
      $chars = array_merge(range("a", "z"), range("A", "Z"), range("0", "9"));
      $max = count($chars) - 1;
      $url = '';
      for($i = 0; $i < $length; $i++) {
        $char = random_int(0, $max);
        $url .= $chars[$char];
      }
      return $url;
    }
}