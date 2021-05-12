<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Schema;
use App\Model\Domains;
use Closure;

class UserDomain
{

    public function handle($request, Closure $next){
        $domain = '';
        if (file_exists(storage_path('installed')) && Schema::hasTable('domains') && isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
          foreach (Domains::get() as $value) {
             if ($host == $value->host && $value->user !== null) {
                $domain = 'user';
             }
          }
        }
        if ($domain == 'user') {
            return abort(404);
        }
        return $next($request);
    }
}
