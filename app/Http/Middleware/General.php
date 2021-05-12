<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Validator,Redirect,Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Model\Settings;
use App\Model\Packages;
use App\Model\Pages;
use App\User;
use App\Model\Domains;

use Closure;

class General
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        if (!file_exists(storage_path('installed'))) {
            return $next($request);
        }

        if (empty(env('APP_URL')) && $_POST) {
            return redirect()->back()->with('error', __('Could not complete your request'));
        }

        if (file_exists(base_path('.env')) && empty(env('APP_URL'))) {
            return redirect(url()->full());
        }

        if (Schema::hasTable('domains') && isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $parse = parse_url(env('APP_URL'))['host'] ?? '';
             if ($host !== $parse) {
                 if (!$domains = Domains::where('host', $host)->first()) {
                     return redirect(env('APP_URL'));
                 }
              }
        }

        if (file_exists(resource_path('others/appversion.php'))) {
            $appversion = getOtherResourceFile('appversion');
            foreach ($appversion as $key => $value) {
                if (env('APP_VERSION') !== $key) {
                    env_update(['APP_VERSION' => $key]);
                }
            }
        }

        return $next($request);
    }
}
