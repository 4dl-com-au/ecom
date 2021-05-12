<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;
use General, Config, DotenvEditor, Storage, Crypt;
use GuzzleHttp\Client;

class InstallController extends Controller{
    public $logFile;


    public function __construct() {
        $this->logFile = (file_exists(storage_path('logs/install.log')) ? storage_path('logs/install.log') : $this->create_logfile());
        if (!file_exists(base_path('.env'))) {
            copy(base_path('.env.example'), '.env');
            header('location: install');
        }
        $this->middleware(function ($request, $next) {
            if(file_exists(storage_path('installed'))) {
                abort(404);
            }
            return $next($request);
        });
    }


    private function create_logfile(){
        file_put_contents(storage_path('logs/install.log'), '"========================== INSTALLATION START ========================"' . PHP_EOL, FILE_APPEND);
    }

    protected function checkRequirements(){
        $this->log('Check requirements: start');

        $result = [
            'PHP Version' => [
                'result'        => version_compare(PHP_VERSION, 5.6, '>'),
                'message'  => 'You need at least 5.6 PHP Version to install.',
                'current'       => PHP_VERSION
            ],
            'PDO' => [
                'result'        => defined('PDO::ATTR_DRIVER_NAME'),
                'message'  => 'PHP PDO extension is required.',
                'current'       => defined('PDO::ATTR_DRIVER_NAME') ? 'Enabled' : 'Not enabled'
            ],
            'Mbstring' => [
                'result'        => extension_loaded('mbstring'),
                'message'  => 'PHP mbstring extension is required.',
                'current'       => extension_loaded('mbstring') ? 'Enabled' : 'Not enabled'
            ],
            'Intl' => [
                'result'        => extension_loaded('intl'),
                'message'  => 'PHP intl extension is required.',
                'current'       => extension_loaded('intl') ? 'Enabled' : 'Not enabled'
            ],
            'Fileinfo' => [
                'result'        => extension_loaded('fileinfo'),
                'message'  => 'PHP fileinfo extension is required.',
                'current'       => extension_loaded('fileinfo') ? 'Enabled' : 'Not enabled'
            ],
            'OpenSSL' => [
                'result'        => extension_loaded('openssl'),
                'message'  => 'PHP openssl extension is required.',
                'current'       => extension_loaded('openssl') ? 'Enabled' : 'Not enabled'
            ],
            'GD' => [
                'result'        => extension_loaded('gd'),
                'message'  => 'PHP openssl extension is required.',
                'current'       => extension_loaded('gd') ? 'Enabled' : 'Not enabled'
            ],
            'Curl' => [
                'result'        => extension_loaded('curl'),
                'message'  => 'PHP curl functionality needs to be enabled.',
                'current'       => extension_loaded('curl') ? 'Enabled' : 'Not enabled'
            ],
            'Imagick' => [
                'result'        => extension_loaded('imagick'),
                'message'       => 'PHP Imagick functionality needs to be enabled for qrcode to work.',
                'current'       => extension_loaded('imagick') ? 'Enabled' : 'Not enabled'
            ],
            'Zip' => [
                'result'        => class_exists('ZipArchive'),
                'message'  => 'PHP ZipArchive extension needs to be installed.',
                'current'       => class_exists('ZipArchive') ? 'Enabled' : 'Not enabled'
            ],
        ];

        $allPass = array_filter($result, function($item) {
            return !$item['result'];
        });

        $this->log('Check requirements: end', ($allPass ? '+OK' : '=FAIL'));

        return $result;
    }

    protected function checkFileSystem(){
        $this->log('Check filesystem: start');

        $directories = [
            '',
            'storage',
            'bootstrap',
            'bootstrap/cache',
            'storage/app',
            'storage/logs',
            'storage/framework',
        ];

        $results = [];
        foreach ($directories as $directory) {
            $path = rtrim(base_path($directory), '/');
            $writable = is_writable($path);
            $dir = !empty($directory) ? $directory : 'root';
            $result = ['path' => $path, 'result' => $writable, 'writable' => $writable ? 'writable' : 'not writable', 'dir' => $dir, 'message' => ''];
            if ( ! $writable) {
                $result['message'] = is_dir($path) ?
                    'Make this directory writable by giving it 0755 or 0777 permissions via file manager.' :
                    'Make this directory writable by giving it 644 permissions via file manager.';
            }

            $results[] = $result;
        }

        $files = [
            '.htaccess',
            'bootstrap/app.php',
            'public/.htaccess',
        ];

        if ( ! $this->fileExistsAndNotEmpty('.env') && ! $this->fileExistsAndNotEmpty('.env.example')) {
            $results[] = [
                'path' => base_path(),
                'result' => false,
                'writable'  => 'now writable',
                'message' => "Make sure <strong>.env.example</strong> or <strong>.env</strong> file has been uploaded properly to the directory above and is writable.",
            ];
        }

        foreach ($files as $file) {
            $results[] = [
                'path' => base_path($file),
                'result' => $this->fileExistsAndNotEmpty($file),
                'writable' => $this->fileExistsAndNotEmpty($file) ? 'writable' : 'not writable',
                'dir' => $file,
                'message' => (!is_writable($file) ? "Make sure <strong>$file</strong> file has been uploaded properly to your server and is writable." : '')
            ];
        }

        $allPass = array_filter($results, function($item) {
            return !$item['result'];
        });

        $this->log('Check filesystem: end', $results, ($allPass ? '+OK' : '=FAIL'));

        return $results;
    }

    protected function fileExistsAndNotEmpty($path){
        $filePath = base_path($path);
        $writable = is_writable($filePath);
        $content = $writable ? trim(file_get_contents($filePath)) : '';
        return $writable && strlen($content);
    }


    public function log(){
        $args = func_get_args();
        $message = array_shift($args);

        if (is_array($message)) $message = implode(PHP_EOL, $message);

        $message = "[" . date("Y/m/d h:i:s", time()) . "] " . vsprintf($message, $args) . PHP_EOL;
        file_put_contents($this->logFile, $message, FILE_APPEND);
    }




    public function install(Request $request) {
        $steps          = $request->get('steps');
        if ($steps == 'requirements') {
            return $this->requirements();
        }elseif ($steps == 'app') {
            return $this->appDetail();
        }
        return view('installer.install');
    }
    public function appDetail(){
        return view('installer.app');
    }

    public function requirements(){
       $requirements = $this->checkRequirements();
       $filesystem   = $this->checkFileSystem();
       $passAll = true;
       foreach ($requirements as $key => $value) {
           if (!$value['result']) {
               $passAll = false;
           }
       }
       foreach ($filesystem as $key => $value) {
           if (!$value['result']) {
               $passAll = false;
           }
       }
       return view('installer.requirements', ['requirements' => $requirements, 'filesystem' => $filesystem, 'pass' => $passAll]);
    }

    public function final() {
        file_put_contents(storage_path('installed'), "Ecom successfully installed on ".date("Y/m/d h:i:sa"));
        return view('installer.final');
    }

    private function writeHttps(){

$https = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews
    </IfModule>

    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} -d [OR]
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ ^$1 [N]

    RewriteCond %{REQUEST_URI} (\.\w+$) [NC]
    RewriteRule ^(.*)$ public/$1 

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ server.php
    RewriteCond %{HTTPS} !=on
    RewriteRule .* https://%{HTTP_HOST}%{REQUEST_URI} [R,L]
</IfModule>';

        $update = fopen(base_path('.htaccess'), 'w');
        fwrite($update, $https);
    }


    public function migration(Request $request){
        $first_name = $request->get('first_name');
        $email = $request->get('email');
        $username = $request->get('username');
        $password = $request->get('password');
        try {
            Artisan::call('migrate', ["--force" => true]);
            Artisan::call('db:seed', ['--force' => true]);
            $this->log('Database migrated and seeded');
        }catch(\Exception $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
            $this->log($e->getMessage());
        }

        # Create admin account
        try {
            $new_user           = new User;
            $new_user->name     = ['first_name' => $first_name, 'last_name' => ''];
            $new_user->email    = $email;
            $new_user->username = $username;
            $new_user->role     = 1;
            $new_user->password = Hash::make($password);
            $new_user->save();
            Auth::login($new_user);
            $this->log('Admin user created :'. $username);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
            $this->log($e->getMessage());
        }
        $this->log('"========================== END INSTALLATION ========================"');
        $new_array = [
            "APP_ENV"       => "production",
            "APP_DEBUG"     => "false",
            "APP_MIGRATION" => "no"
        ];
        $env_update = $this->changeEnv($new_array);

        Artisan::call('key:generate', ["--force"=> true]);

        return redirect()->route('InstallFinal');
    }

    public function appDetailSubmit(Request $request) {
        # Validate requests
        $request->validate([
            'app_name'          => ['required'],
            'first_name'              => ['required', 'string', 'max:191'],
            'email'             => ['required', 'string', 'email', 'max:191'],
            'username'          => ['required', 'string', 'max:16'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'db_host'           => ['required'],
            'db_port'           => ['required'],
            'db_database'       => ['required'],
            'db_username'       => ['required'],
        ]);

        $new_array = [];

        # Update env with site details
        $requests = $request->all();
        unset($requests['_token'], $requests['force_https'], $requests['first_name'], $requests['email'], $requests['username'], $requests['password'], $requests['password_confirmation']);
        $requests['APP_URL']     = url('/');
        foreach($requests as $key => $value) {
            $new_array[strtoupper($key)] = $value;
        }
        $env_update = $this->changeEnv($new_array);
        if($env_update):
          $this->log('Updated env with new details');
        endif;

        # Force https
        if($request->force_https):
          $this->writeHttps();
          $this->log('Forced https');
        endif;
        $requests = [
            'first_name'      => $request->first_name,
            'email'     => $request->email,
            'username'  => $request->username,
            'password'  => $request->password,
        ];
        return redirect()->route('InstallDatabase', $requests);
    }

    protected function changeEnv($data = array()){
        if(count($data) > 0){
            $env = file_get_contents(base_path() . '/.env');
            $env = explode("\n", $env);
            foreach((array)$data as $key => $value) {
                if($key == "_token") {
                    continue;
                }
                $notfound = true;
                foreach($env as $env_key => $env_value) {
                    $entry = explode("=", $env_value, 2);
                    if($entry[0] == $key){
                        $env[$env_key] = $key . "=\"" . $value."\"";
                        $notfound = false;
                    } else {
                        $env[$env_key] = $env_value;
                    }
                }
                if($notfound) {
                    $env[$env_key + 1] = "\n".$key . "=\"" . $value."\"";
                }
            }
            $env = implode("\n", $env);
            file_put_contents(base_path('.env'), $env);
            return true;
        } else {
            return false;
        }
    }
}
