<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\PaymentController;
use Redirect, URL, Session, General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralMail;
use Illuminate\Http\Request;
use App\Model\Packages;
use App\Model\PendingPayments;

class BankController extends Controller{
	public $settings;
    public function __construct(){
        $this->middleware('auth');
        $general = new General();
        $this->settings = $general->settings();
    }
    public function create($plan, $duration){
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
        $pending = PendingPayments::where('duration', $duration)->where('package', $package->id)->where('status', 0)->get();

        return view('payment.bank', ['plan' => $package, 'duration' => $duration, 'pending' => $pending]);
    }

    public function post($plan, $duration, Request $request){
        $user = Auth::user();
        if (!empty($duration) && !in_array($duration, ['annual', 'month', 'quarter'])) {
            abort(404);
        }
        if (!$package = Packages::where('slug', $plan)->first()) {
            abort(404);
        }
        if (PendingPayments::where('duration', $duration)->where('package', $package->id)->where('status', 0)->exists()) {
            return back()->with('error', 'A pending transaction already exists');
        }
        $payment = new PendingPayments;
        $payment->user = Auth()->user()->id;
        $payment->email = $request->email;
        $payment->name = $request->name;
        $payment->bankName = $request->bank_name;

        $payment->ref = 'PR_'. $this->randomShortname();
        $payment->package = $package->id;
        $payment->duration = $duration;

        if (!empty($request->proof)) {
            $request->validate([
                'proof' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            ]);
            $imageName = md5(microtime());
            $imageName = $imageName . '.' .$request->proof->extension();
            $request->proof->move(public_path('media/user/bankProof'), $imageName);
            $payment->proof = $imageName;
         }
         $payment->save();

         if (settings('email_notify.bank_transfer')) {
                $emails = settings('email_notify.emails');
                $emails = explode(',', $emails);
                $emails = str_replace(' ', '', $emails);
                $email = (object) array('subject' => 'New Pending Payment', 'message' => '<p> <b>'.full_name($user->id).'</b> Just submitted the manual payment form for <b>'.ucfirst($package->name).'</b>. <br> Head to your dashboard to view payment.</p><br>');
                try {
                    Mail::to($emails)->send(new GeneralMail($email));
                 } catch (\Exception $e) {
                     return (object) ['status' => 'error', 'response' => 'send mail error'];
                 }
        }
        return back()->with('success', 'Pending Transaction');
    }
    public function delete($id, Request $request){
        $pending = PendingPayments::find($id);
        if (!empty($pending->proof)) {
            if(file_exists(public_path('media/user/bankProof/' . $pending->proof))){
                 unlink(public_path('media/user/bankProof/' . $pending->proof)); 
            }
        }
        $pending->delete();
        return back()->with('success', 'Deleted successfully');
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
