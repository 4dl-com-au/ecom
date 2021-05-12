<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Validator,Redirect,Response;

use Closure;

class Banned
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
        if ($request->user() && !$request->user()->active && empty($request->user()->email_token)) {
            return response(view('errors.banned'));
        }
        return $next($request);
    }
}
