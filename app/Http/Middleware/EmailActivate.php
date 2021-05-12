<?php

namespace App\Http\Middleware;
use Validator,Redirect,Response;

use Closure;

class EmailActivate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() && !$request->user()->active && !empty($request->user()->email_token)) {
            return response(view('errors.activateEmail'));
        }
        return $next($request);
    }
}
