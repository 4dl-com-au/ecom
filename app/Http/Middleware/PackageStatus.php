<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Validator,Redirect,Response;

use Closure;
use General;

class PackageStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    private $settings;
    
    public function __construct(){
        $general = new General();
        $this->settings = $general->settings();
    }
    public function handle($request, Closure $next){
        $general = new General();
        if ($request->user()) {
            $package = $general->package($request->user());
            if ($package['status'] == 2) {
                return response(view('errors.no-package'));
            }
        }
        return $next($request);
    }
}
