<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Schema;
use App\Model\Domains;
use Closure;

class RemoveParmsFromDomain
{

    public function handle($request, Closure $next){
      if (!empty($_GET['profile']) && !$request->ajax()) {
        #return redirect()->to(url()->current().http_build_query($request->except('profile')));
      }
      return $next($request);
    }
}
