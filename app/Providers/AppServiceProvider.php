<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Model\Settings;
use App\Model\Packages;
use App\Model\Pages;
use App\User;
use App\Model\Domains;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(){
        Schema::defaultStringLength(191);
        $this->https_scheme();
        $this->init();
    }
    public function init(){
     if ($this->checkDBcon()) {
        if (file_exists(storage_path('.migrate'))) {
          try {
            DB::table('migrations')->where('migration', '2020_11_08_173354_updates')->delete();
            Artisan::call('migrate', ["--force" => true]);
          }catch(\Exception $e) {

          }

          #unlink(storage_path('.migrate'));
        }
        if (Schema::hasTable('settings')) {
           $getsettings = \App\Model\Settings::all()
           ->keyBy('key')
           ->transform(function ($setting) {
                 $value = json_decode($setting->value, true);
                 $value = (json_last_error() === JSON_ERROR_NONE) ? $value : $setting->value;
                 return $value;
            })->toArray();
           config()->set('settings', $getsettings);
        }
        if (Schema::hasTable('users')) {
            if (license('license') !== 'Extended License' && count(User::get()) >= 1000) {
                #\App\Model\Settings::where('key', 'registration')->update(['value' => 0]);
            }
        }
              View::composer('*', function ($view){
                    if (Auth::check()) {
                        $user = Auth::user();
                        generatePages($user->id);
                        
                        if (!\Theme::has(user('extra.template', $user->id))) {
                            $settings = user('extra', $user->id) ?? [];
                            $settings['template'] = (!empty(settings('user.default_template')) ? settings('user.default_template') : 'zoa');
                            $update = User::find($user->id);
                            $update->extra = $settings;
                            $update->save();
                        }
                        if ($user->package == 'free') {
                            $package = Settings::where('key', 'package_free')->first();
                            $package = json_decode($package->value);
                            $package = (json_last_error() === JSON_ERROR_NONE) ? $package : $package->value;
                        }elseif($user->package == 'trial'){
                            $package = Settings::where('key', 'package_trial')->first();
                            $package = json_decode($package->value);
                            $package = (json_last_error() === JSON_ERROR_NONE) ? $package : $package->value;
                        }else{
                            $package = Packages::where('id', Auth::user()->package)->first();
                        }
                        $domain = $user->domain;
                        if (Schema::hasTable('domains')) {
                            if ($domain == 'main') {
                              $domain = env('APP_URL').'/'.$user->username;
                            }elseif ($domain = Domains::where('id', $user->domain)->first()) {
                                if ($domain->user !== null) {
                                    $domain = $domain->scheme.$domain->host;
                                }else{
                                    $domain = $domain->scheme.$domain->host.'/'.$user->username;
                                }
                            }else{
                              $domain = env('APP_URL').'/'.$user->username;
                            }
                        }
                        $profile_url = $domain;
                        View::share('profile_url', $profile_url);
                        View::share('package', $package);
                        View::share('user', $user);
                    }
                });
                if (Schema::hasTable('pages')) {
                    $pages = Pages::where('status', 1)->limit(4)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
                    View::share('allPages', $pages);
                }
                if (Schema::hasTable('pages_categories')) {
                    $categories = DB::table('pages_categories')->limit(4)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
                    View::share('allCategories', $categories);
                }
            }
    }
    public function https_scheme(){
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {

        }else{
           \URL::forceScheme('https');
        }
    }

    public function checkDBcon(){
        try {
            DB::connection()->getPdo();
            if(DB::connection()->getDatabaseName()){
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
