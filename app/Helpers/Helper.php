<?php

use Carbon\Carbon;
use App\Model\Settings;
use Ausi\SlugGenerator\SlugGenerator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Herbert\EnvatoClient;
use Herbert\Envato\Auth\Token as EnvatoToken;

if (!function_exists('hello')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function hello(){

        echo "Hello";
    }
}

if (!function_exists('p_category')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function p_category($user, $type, $slug){
      if ($type == 'count') {
        $products = \App\Model\Products::where('user', $user)->get();
        $count = [];
        foreach ($products as $value) {
          if (in_array($slug, $value->categories)) {
            $count[] = $value;
          }
        }


        return count($count);
      }
    }
}


if (!function_exists('store_count_stats')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function store_count_stats($type){
      $thisMonth = \Carbon\Carbon::now()->startOfMonth()->toDateString();
      $model = new \App\User;

      if ($type == 'count') {
         $return = $model->where('active', 1)->count();
         $return = number_format($return);
         return $return;
      }

      if ($type == 'getActiveUsersAvatar') {
        $active = $model->where('active', 1)->where('last_activity', '>=', $thisMonth)->limit(10)->get();

        $html = '';

        foreach ($active as $value) {
          $html .= '<img src="'. avatar($value->id) .'">';
        }

        return $html;

      }
    }
}


if (!function_exists('addHttps')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function addHttps($url, $scheme = 'https://'){
      return parse_url($url, PHP_URL_SCHEME) === null ?
        $scheme . $url : $url;
    }
}

if (!function_exists('getHost')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getHost($address) { 
       if (parse_url($address, PHP_URL_SCHEME) === null) {
         $address = 'https://' . $address;
       }

       $parseUrl = parse_url(trim($address)); 


       $parseUrl = !empty($parseUrl['host']) ? $parseUrl['host'] : $parseUrl['path'];
       $parseUrl = trim($parseUrl);


      $parseUrl = preg_replace('/^www\./', '', $parseUrl);

      return $parseUrl;
    }
}


if (!function_exists('generatePages')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function generatePages($user){
      if (Theme::has(user('extra.template', $user)) && file_exists(public_path('Themes/'.Theme::get(user('extra.template', $user))['name'].'/blocks/blocks.php'))):
         
         $blocksphp = require(public_path('Themes/'.Theme::get(user('extra.template', $user))['name'].'/blocks/blocks.php'));
         $blocksphp = $blocksphp['pages'];


         foreach ($blocksphp as $key => $value) {
            $userPages = \App\Model\UserPages::where('user', $user)->where('slug', $key)->first();

            if (!$userPages) {
              $page = new \App\Model\UserPages;
              $page->user = $user;
              $page->name = $value['name'] ?? '';
              $page->slug = $key;
              $page->is_home = $value['active'] ?? 0;
              $page->save();



              if (is_array($value['blocks'])) {
                foreach ($value['blocks'] as $block_key => $block_value) {
                  $section = new \App\Model\PagesSections;
                  $section->user = $user;
                  $section->page_id = $page->id;
                  $section->theme = user('extra.template', $user);
                  $section->status = 1;
                  $section->block_slug = $block_key;

                  $section->data = $block_value;
                  $section->save();
                }
              }
            }
         }
      endif;
    }
}

if (!function_exists('logo')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function logo(){
      $logo = settings('logo');

      $logo = getStorage('media/logo', $logo);

      return $logo;
    }
}

if (!function_exists('favicon')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function favicon(){
      $favicon = settings('favicon');

      $favicon = getStorage('media/favicon', $favicon);

      return $favicon;
    }
}

if (!function_exists('storageFileSize')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function storageFileSize($directory, $file){
      $filesystem = env('FILESYSTEM');

      $location = $directory .'/'. $file;

      if (Storage::disk($filesystem)->exists($location)) {

          return Storage::disk($filesystem)->size($location);
      }

      return 0;
    }
}

if (!function_exists('storageDelete')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function storageDelete($directory, $file){
      $filesystem = env('FILESYSTEM');

      $location = $directory .'/'. $file;

      if (Storage::disk($filesystem)->exists($location)) {

          Storage::disk($filesystem)->delete($location);

          return true;
      }

      return false;
    }
}

if (!function_exists('mediaExists')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function mediaExists($directory, $file){
      $filesystem = env('FILESYSTEM');

      $location = $directory .'/'. $file;

      if (Storage::disk($filesystem)->exists($location)) {
          return true;
      }

      return false;
    }
}

if (!function_exists('getStorage')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getStorage($directory, $file){
      $filesystem = env('FILESYSTEM');

      $location = $directory .'/'. $file;

      $get = Storage::disk($filesystem)->url($location);

      return $get;
    }
}

if (!function_exists('putStorage')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function putStorage($directory, $file){
      $filesystem = env('FILESYSTEM');


      $put = \Storage::disk($filesystem)->put($directory, $file);

      \Storage::disk($filesystem)->setVisibility($put, 'public');


      return basename($put);
    }
}

if (!function_exists('store_colors')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function store_colors($store, $type = 'css'){
      if (!$user = \App\User::find($store)) {
        return false;
      }

      $value = '';
      $default_colors = ['background' => '#000', 'color' => '#fff'];

      $background = user('extra.background_color', $user->id) ?? $default_colors['color'];

      $color = user('extra.background_text_color', $user->id) ?? $default_colors['background'];

      if ($type == 'css') {
        $value = 'background: ' . $background . ' !important; color: ' . $color . ' !important; ';
      }

      if ($type == 'color') {
        $value = $color;
      }

      if ($type == 'background') {
        $value = $background;
      }

      if ($type == 'cssColor') {
        $value = 'color: ' . $color . ' !important';
      }

      if ($type == 'cssBackgrounds') {
        $value = 'background: ' . $background . ' !important';
      }


      return $value;

    }
}

if (!function_exists('store_menu')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function store_menu($user, $limit = null, $type = 'results', $class = ['ul' => 'nav', 'li' => 'nav__item', 'a' => 'nav__link']){
      $user = \App\User::find($user);

      $model = new \App\Model\UserPages;

      $menu = $model->where('user', $user->id);

      if ($limit !== null) {
        #$menu = $menu->limit($limit);
      }

      $menu = $menu->get();

      if ($type == 'results') {
        return $menu;
      }

      $ul_class = $class['ul'] ?? '';
      $li_class = $class['li'] ?? '';
      $a_class = $class['a'] ?? '';

      #$menu[] = (object) ['name' => __('Products'), 'slug' => 'products', 'id' => 999];

      $html = '<ul class="'.$ul_class.'">';

      foreach ($menu as $item) {
        if (empty($item->parent)) {
          if (count($item->childs ) > 0) {
            $html .= '<li class="'.$li_class.' dropdown">';
            $html .= '<a class="nav-link dropdown-toggle dropdown_menu py-0 my-0 mx-2 px-0" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span>'.$item->name.'</span> </a>';
          }else{
            $html .= '<li class="'.$li_class.' ">';
            $html .= '<a class="'.$a_class.' " href="'. route('user-store-page', ['profile' => $user->username, 'page' => $item->slug]) .'"><span>'.$item->name.'</span> </a>';
          }


          if (count($item->childs) > 0) {
            $html .= '<div class="dropdown-menu sm_dropdown" aria-labelledby="navbarDropdown"><ul class="dropdown_menu_nav">';
            $html .= '<li class="'.$li_class.' ">';
            $html .= '<a class="'.$a_class.' dropdown-item" href="'. route('user-store-page', ['profile' => $user->username, 'page' => $item->slug]) .'"><span>'.$item->name.'</span> </a>';

            $html .= '<div class="divider my-2" ></div>';

            foreach ($item->childs as $vPalue) {
              $html .= '<li class="'.$li_class.' "> <a class="dropdown-item" href="'. route('user-store-page', ['profile' => $user->username, 'page' => $vPalue->slug]) .'"><span>'.$vPalue->name.'</span> </a></li>'; 
            }
            $html .= '</ul></div>';
          }

          $html .= '</li>';
        }
      }
      $html .= '</ul>';

      if ($type == 'html') {
        return $html;
      }

    }
}

if (!function_exists('media')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function media($path){
      return url('media/' . $path);
    }
}

if (!function_exists('get_blocks_inputs_html')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function get_blocks_inputs_html($inputs, $type = 'new', $data = []){
      $html = '<div class="form-group">';
      if ($type == 'new') {
        foreach ($inputs as $key => $input) {
          if ($input['type'] == 'text') {
            $html .= '<label class="muted-deep fw-normal m-2">'. $input['name'] .'</label><input type="text" name="data['. $key .'][text]" class="form-control">';
          }

          if ($input['type'] == 'link') {
            $html .= '<label class="muted-deep fw-normal m-2">'. $input['name'] .'</label><input type="text" name="data['. $key .'][link]" class="form-control">';
          }


          if ($input['type'] == 'textarea') {
            $html .= '<label class="muted-deep fw-normal m-2">'. $input['name'] .'</label><textarea name="data['. $key .'][textarea]" class="form-control editor"></textarea>';
          }

          if ($input['type'] == 'select') {
            $html .= '<label class="muted-deep fw-normal m-2">'. $input['name'] .'</label>';
            $html .= '<select class="form-select custom-select mb-3" name="data['. $key .'][select]">';
            foreach ($input['options'] as $option_key => $option_label) {
              $html .= '<option value="'.$option_key.'">'.$option_label.'</option>';
            }
            $html .= '</select>';
          }


          if ($input['type'] == 'image') {
            $html .= '<div class="card border-0 mt-4 card-shadow custom-input-uploader">
                              <div class="card-body py-3 px-3">
                                 <div class="card-title py-3">'. $input['name'] .'</div>
                                 <div class="file-upload mt-0">
                                    <input name="data['. $key .'][image]" type="hidden" value="">
                                    <input class="file-input uploader_input" name="data['. $key .'][image]" type="file">
                                    <img src="'. url('media/misc/upload-image-placeholder.svg') .'" class="mb-3 h-73px" alt="">
                                  <div class="card-subtitle">'. __('Drag n Drop your file here').'</div>
                                    <div class="list-files w-100 mt-4">
                                    </div>
                                 </div>
                              </div>
                           </div>';
          }
        }
      }

      if ($type == 'edit') {
        $data = (array) $data;
        foreach ($inputs as $key => $input) {
          if (array_key_exists($key, $data)) {
              if ($input['type'] == 'text') {
                $html .= '<label class="muted-deep fw-normal m-2">'. $input['name'] .'</label>';
                $html .= '<input type="text" name="data['. $key .'][text]" value="'. $data[$key]->value .'" class="form-control">';
              }

              if ($input['type'] == 'link') {
                $html .= '<label class="muted-deep fw-normal m-2">'. $input['name'] .'</label>';
                $html .= '<input type="text" name="data['. $key .'][link]" value="'. $data[$key]->value .'" class="form-control">';
              }


              if ($input['type'] == 'textarea') {
                $html .= '<label class="muted-deep fw-normal m-2">'. $input['name'] .'</label>';
                $html .= '<textarea name="data['. $key .'][textarea]" class="form-control editor">'. $data[$key]->value .'</textarea>';
              }


              if ($input['type'] == 'select') {
                $html .= '<label class="muted-deep fw-normal m-2">'. $input['name'] .'</label>';
                $html .= '<select class="form-select custom-select mb-3" name="data['. $key .'][select]">';
                foreach ($input['options'] as $option_key => $option_label) {
                  $html .= '<option value="'.$option_key.'"';
                    if ($data[$key]->value == $option_key) {
                      $html .= 'selected';
                    }
                  $html .= '>' . $option_label . '</option>';
                }
                $html .= '</select>';
              }


            if ($input['type'] == 'image') {
              $html .= '<div class="card border-0 mt-4 card-shadow custom-input-uploader">
                 <div class="card-body py-3 px-3">
                    <div class="card-title py-3">'. $input['name'] .'</div>
                    <div class="file-upload mt-0">
                       <input name="data['. $key .'][image]" type="hidden" value="">
                       <input class="file-input uploader_input" name="data['. $key .'][image]" type="file">
                       <img src="'. url('media/misc/upload-image-placeholder.svg') .'" class="mb-3 h-73px" alt="">
                     <div class="card-subtitle">'. __('Drag n Drop your file here').'</div>
                       <div class="list-files w-100 mt-4">';
                       if (mediaExists('media/user/pages', $data[$key]->value)) {
                        $html .= '<div class="p-file-upload-preview">
                                        <header class="p-file-upload-p-header">
                                          <div class="p-file-upload-p-header-content">
                                            <span class="p-file-upload-title">'. $data[$key]->value .'</span>
                                            <span class="p-file-upload-size">'. nr(storageFileSize('media/user/pages', $data[$key]->value)) .' <br></span>
                                          </div>
                                        </header>
                                        <main class="p-file-upload-p-main">
                                          <div class="p-file-upload-image">
                                            <img alt="" src="'. getStorage('media/user/pages', $data[$key]->value) .'" class="">
                                          </div>
                                        </main>
                                      </div>';
                       }
                       $html .='</div>
                    </div>
                 </div>
              </div>';
            }
          }
        }
      }


      $html .= '</div>';

      return $html;
    }
}

if (!function_exists('convo_messages_html')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function convo_messages_html($convo_id, $looking_from = 'store'){
      $messages = \App\Model\Messages::where('conversation_id', $convo_id)->get()->groupBy(function($date) {
          return $date->created_at->format('Y-m-d');
      });

      $convo = \App\Model\Conversations::where('id', $convo_id)->first();


      #print_r($messages);

      #return ;

      $html = '';
      if (count($messages) < 1) {
          $html .= '<li class="no-messages"><img class="light-image" src="assets/img/illustrations/placeholders/search-4.svg" alt=""><div class="text"><h3>'. __('No messages yet') . '</h3><p>'.__('Start the conversation by typing a message').'</p></div></li>';
      }

      foreach ($messages as $key => $items) {
        $date = $key;

        if (Carbon::parse($date)->isToday()) {
          $date = 'Today';
        }elseif (Carbon::parse($date)->isYesterday()) {
          $date = 'Yesterday';
        }else{
          $date = Carbon::parse($key)->diffForHumans();
        }

        $html .= '<li class="divider-container"><div class="divider"><span>' . $date . '</span></div></li>';

        foreach ($items as $item) {
          $message_from = $item->from == 'store' ? 'self' : 'other';

          if ($looking_from == 'customer') {
            $message_from = $item->from == 'store' ? 'other' : 'self';
          }
          $avatar = avatar($convo->user);

          if ($item->from == 'customer') {
            $avatar = c_avatar($convo->customer);
          }

          if ($item->type == 'link') {
            $html .= '<li class="'.$message_from.'"><div class="avatar"></div><div class="msg is-link mt-3">
                        <div class="icon-wrapper">
                          <i class="tio link text-white"></i>
                        </div>
                        <p class="link-meta">
                          <span>'. __('Shared a link') .'</span>
                          <a href="'. $item->data .'" target="_blank">'.__('Open Link').'</a>
                        </p>
                    </div></li>';
          }

          if ($item->type == 'file') {
            $file = getStorage('media/user/chat/files', $item->data);
            $html .= '<li class="'.$message_from.'"><div class="avatar"></div><div class="msg is-link bg-dark mt-3">
                        <div class="icon-wrapper">
                          <i class="tio download_from_cloud text-white"></i>
                        </div>
                        <p class="link-meta c-white">
                          <span class="c-white">'. __('Shared a File') .'</span>
                          <a class="c-white" download="' . $item->data . '" href="'. $file .'" target="_blank">'.__('Download').'</a>
                        </p>
                    </div></li>';
          }

          if ($item->type == 'images') {
            $image = getStorage('media/user/chat/images', $item->data);
            $html .= '<li class="'.$message_from.'"><div class="avatar"><img src="'. $avatar .'" draggable="false"></div><div class="msg is-image">
                        <div class="image-container">
                            <img src="'. $image .'" class="w-100">
                            <div class="image-overlay"></div>
                            <div class="image-actions">
                                <div class="actions-inner">
                                     <a href="'. $image .'" download="'. $item->data .'" class="action">
                                     <i class="tio download_from_cloud"></i>
                                    </a>
                                    <a href="'. $image .'" target="_blank" class="action messaging-popup">
                                      <i class="tio link"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div></li>';
          }


          if ($item->type == 'text') {
            $html .= '<li class="'.$message_from.'"><div class="avatar"><img src="'. $avatar .'" draggable="false"></div><div class="msg"><div class="msg-inner"><p>'. $item->data .'</p></div><time>'. Carbon::parse($item->created_at)->format('g:i A') .'</time></div></li>';
          }
        }
      }

      return $html;
    }
}

if (!function_exists('get_single_theme_blocks')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function get_single_theme_blocks($block, $user){
      if (Theme::has(user('extra.template', $user)) && file_exists(public_path('Themes/'.Theme::get(user('extra.template', $user))['name'].'/blocks/blocks.php'))):
         
         $blocksphp = require(public_path('Themes/'.Theme::get(user('extra.template', $user))['name'].'/blocks/blocks.php'));
         $blocksphp = $blocksphp['blocks'];

         if (array_key_exists($block, $blocksphp)) {
           return $blocksphp[$block];
         }

      endif;
    }
}

if (!function_exists('pages_sections_values')) {
  function pages_sections_values ($user, $page_id, $type = 'data') {
     $theme = user('extra.template', $user);
     $page = \App\Model\UserPages::where('id', $page_id)->first();
     $sections = \App\Model\PagesSections::where('page_id', $page->id)->where('theme', $theme)->where('status', 1)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

     $theme_name = Theme::get(user('extra.template', $user))['name'];

     $blocks_path = public_path('Themes/'.$theme_name.'/blocks/blocks.php');

     if(!file_exists($blocks_path)):
      return false;
     endif;

     $blocks_file = require $blocks_path;
     $blocks_file = $blocks_file['blocks'];
     $newData = [];
     $block_include = [];

     foreach ($sections as $items) {
        $data = $items->data;
        if (array_key_exists($items->block_slug, $blocks_file)) {
          $section = $blocks_file[$items->block_slug];

          if (array_key_exists('products', $section)) {
            $products = \App\Model\Products::where('user', $user);

            if (array_key_exists('limit', $section['products'])) {
              $products = $products->limit($section['products']['limit']);
            }

            $products = $products->get();

            $newData[$items->id]['products'] = $products;
          }


          $inputs = $section['inputs'];

          $block_include[$items->block_slug] = ['include' => $section['include']];

          foreach ($inputs as $key => $value) {

            $inputdata = $data->{$key} ?? '';
            $inputval = $inputdata->value ?? '';
            if (!empty($inputdata->type) && $inputdata->type == 'image') {
              $inputval = getStorage('media/user/pages', $inputval);
            }

            $newData[$items->id][$key] = clean($inputval ?? '', 'titles');
          }
        }
     }

     if ($type == 'data') {
      return $newData;
     }

     if ($type == 'other_data') {
       return $block_include;
     }


  }
}
if (!function_exists('get_theme_blocks_sections')) {
    function get_theme_blocks_sections($user, $page_id){
      $theme = user('extra.template', $user);
      $page = \App\Model\UserPages::where('id', $page_id)->first();
      $sections = \App\Model\PagesSections::where('page_id', $page->id)->where('theme', $theme)->where('status', 1)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

      $returnHtml = [];

      if (Theme::has(user('extra.template', $user)) && file_exists(public_path('Themes/'.Theme::get(user('extra.template', $user))['name'].'/blocks/blocks.php'))):
         $themePath = public_path('Themes/'.Theme::get(user('extra.template', $user))['name']);
         
         $blocksphp =  require(public_path('Themes/'.Theme::get(user('extra.template', $user))['name'].'/blocks/blocks.php'));

        foreach ($sections as $value) {
          $block_type = $blocksphp['blocks'][$value->block_slug]['type'];

          $sections_data = [];
          $sections_data = $value->data;
          $inputKey = [];
          $inputVal = [];

          if ($block_type == 'products_wrapper') {
            $product_item_html = [];
            foreach ($sections_data as $key => $section_data_val) {
              $inputKey[] = '{' . $key . '}';
              $inputVal[] = $section_data_val->value;
            }

            foreach (\App\Model\Products::where('user', $user)->get() as $product) {
              $products_item = $blocksphp['blocks']['products_item'];
              if (!empty($products_item)) {
                $find = ['{name}', '{price}', '{saleprice}', '{description}'];
                $replace = [$product->title, $product->price, $product->salePrice, $product->description];
                $products_item['html'] = str_replace($inputKey, $inputVal, $products_item['html']);

                $product_item_html[] = str_replace($find, $replace, $products_item['html']);

              }
            }

            $blocksphp['blocks'][$value->block_slug]['html'] = str_replace($inputKey, $inputVal, $blocksphp['blocks'][$value->block_slug]['html']);

            $returnHtml[$value->block_slug] = str_replace('{products_item}', implode('', $product_item_html), $blocksphp['blocks'][$value->block_slug]['html']);

          }elseif($block_type !== 'products_wrapper'){

            foreach ($sections_data as $key => $section_data_val) {
              $inputKey[] = '{' . $key . '}';
              if ($section_data_val->type == 'image') {
                $inputVal[] = url('media/user/pages/'. $section_data_val->value);
              }else{
                $inputVal[] = $section_data_val->value;
              }
            }

            $html = $blocksphp['blocks'][$value->block_slug]['html'];
            $returnHtml[$value->block_slug] = str_replace($inputKey, $inputVal, $html);


            #${'jeff'} = 'Hey Man';

            #include $themePath . '/blocks/' . $blocksphp['blocks'][$value->block_slug]['include'];
            echo $blocksphp['blocks'][$value->block_slug]['include'];

            #echo implode('', $returnHtml);
          }



        }

      endif;
    }
}

if (!function_exists('get_theme_blocks')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function get_theme_blocks($user, $theme){
      if (Theme::has($theme) && file_exists(public_path('Themes/'.Theme::get($theme)['name'].'/blocks/blocks.php'))):
         $require = require(public_path('Themes/'.Theme::get($theme)['name'].'/blocks/blocks.php'));

         return $require['blocks'] ?? [];

      endif;
    }
}

if (!function_exists('auth_user')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function auth_user($store, $type = 'get'){
      $session = session();
      $auth = $session->get('auth-customer-' . $store);

      if ($type == 'logout') {
        $session->forget('auth-customer-' . $store);

        return true;
      }

      if ($type == 'check' && !empty($auth)) {
        if ($auth['store'] == $store) {
          return true;
        }
      }

      if ($type == 'get') {
        if (auth_user($store, 'check')) {
          $customer = App\Model\Customers::where('id', $auth['customer'])->first();
          if (!$customer) {
            $session->forget('auth-customer-' . $store);
          }
          return $customer;
        }
      }


      return false;
    }
}

if (!function_exists('subdomain_wildcard_creation')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function subdomain_wildcard_creation($user_id){
        if (!$user = \App\User::find($user_id)) {
           return false;
        }
        if (!env('APP_USER_WILDCARD')) {
            return false;
        }

        $app_url = !empty(env('APP_USER_WILDCARD_DOMAIN')) ? env('APP_USER_WILDCARD_DOMAIN') : env('APP_URL');

        $app_url = addHttps($app_url);

        $app_url = parse_url($app_url);
        $slugGenerator = new SlugGenerator;
        $username = $maybe_slug = slugify($user->username);
        $domain = $username .'.'. $app_url['host'];
        $next = '_';
        if (\App\Model\Domains::where('host', '=', $domain)->where('user', '!=', $user->id)->exists()) {
            $username = "{$maybe_slug}{$next}";
            $next = $next . '_';
        }
        $new_domain = $username .'.'. $app_url['host'];
        if (!$domain = \App\Model\Domains::where('user', $user->id)->where('wildcard', 1)->first()) {
            $new = new \App\Model\Domains;
            $new->user = $user->id;
            $new->scheme = $app_url['scheme'].'://';
            $new->wildcard = 1;
            $new->status = 1;
            $new->host = $new_domain;
            $new->save();

            $user->domain = $new->id;
            $user->save();
        }else{
          if ($domain = \App\Model\Domains::where('user', $user->id)->where('wildcard', 1)->first()) {
              $update = \App\Model\Domains::find($domain->id);
              $update->scheme = $app_url['scheme'].'://';
              $update->host = $new_domain;
              $update->save();
          }
        }

        return true;
    }
}
if (!function_exists('product_options_html')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function product_options_html($uid, $product_id){
        $html = '';
        foreach (product_options($uid, $product_id) as $item){
            $html .= '<div class="card my-4"><div class="card-header radius-10"><h5>'. $item->name .'</h5></div></div>';

            if ($item->type == 'dropdown') {
                $html .= '<select class="form-control dy-product-options form-select"';
                if ($item->is_required) {
                    $html .= ' required=""';
                }
                $html .= ' data-search="off" data-ui="lg" name="options['.$item->id.']">';
                foreach (product_options_values($item->id) as $val):
                    $html .= '<option value="'. $val->id .'">'. $val->label .' - '. Currency::symbol(user('gateway.currency', $uid)) . number_format($val->price) .'</option>';
                endforeach;
                $html .= '</select>';
            }elseif ($item->type == 'checkbox') {
                foreach (product_options_values($item->id) as $val):
                    $html .= '<div class="custom-control custom-control-alternative mr-3 custom-checkbox"><input class="custom-control-input dy-product-options"';
                    if ($item->is_required) {
                        $html .= ' required=""';
                    }
                    $html .= ' type="checkbox" name="options['.$item->id.'][]" value="'. $val->id .'" id="checkbox-'. $val->id .'"><label class="custom-control-label" for="checkbox-'.$val->id.'"><span class="text-muted">'. $val->label .' - '. Currency::symbol(user('gateway.currency', $uid)) . number_format($val->price) .'</span></label></div>';
                endforeach;
            }elseif ($item->type == 'radio') {
                foreach (product_options_values($item->id) as $val):
                    $html .= '<div class="custom-control custom-control-lg custom-radio mr-3"><input type="radio" id="radio-'.$val->id.'" name="options['.$item->id.'][]" value="'. $val->id .'" class="custom-control-input dy-product-options"';

                    if ($item->is_required) {
                        $html .= ' required=""';
                    }

                    $html .= '><label class="custom-control-label" for="radio-'.$val->id.'">'.$val->label.' -  '. Currency::symbol(user('gateway.currency', $uid)) . number_format($val->price) .'</label></div>';
                endforeach;
            }elseif ($item->type == 'multiple_select') {
                $html .= '<select class="form-control form-select dy-product-options"';
                if ($item->is_required) {
                    $html .= ' required=""';
                }
                $html .= ' data-search="off" data-ui="lg" multiple="" name="options['.$item->id.'][]">';
                foreach (product_options_values($item->id) as $val):
                    $html .= '<option value="'. $val->id .'">'. $val->label .' - '. Currency::symbol(user('gateway.currency', $uid)) . number_format($val->price) .'</option>';
                endforeach;
                $html .= '</select>';
            }elseif ($item->type == 'color') {
                $html .= '<div class="d-flex">';
                  foreach (product_options_values($item->id) as $val):
                      $html .= '<label class="color-select">
                      <input type="radio" name="options['.$item->id.'][]" value="'. $val->id .'" class="custom-control-input dy-product-options"';

                    if ($item->is_required) {
                        $html .= ' required=""';
                    }

                      $html .= ' ><div style="--color-select-val: '.$val->label.'" class="color-select-inner"></div><span class="d-block"> '. Currency::symbol(user('gateway.currency', $uid)) . number_format($val->price) .'</span></label>';
                  endforeach;
                $html .= '</div>';


            }
        }


        return $html;
    }
}

if (!function_exists('user_top_products')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function user_top_products($user, $offset = 0, $limit = 1){
        $orders = \App\Model\Product_Orders::where('storeuser', $user)->get();
        $topproducts = [];
        foreach ($orders as $order) {
           foreach ($order->products as $key => $value) {
             if ($product = \App\Model\Products::where('id', $key)->first()) {
                 $topproducts[$key] = $product;
             }
           }
        }
        $topproducts = array_slice($topproducts, $offset, $limit);

        return $topproducts;
    }
}

if (!function_exists('user_first_top_products')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function user_first_top_products($user){
        $orders = \App\Model\Product_Orders::where('storeuser', $user)->get();
        $topproducts = [];
        foreach ($orders as $order) {
           foreach ($order->products as $key => $value) {
            if ($product = \App\Model\Products::where('id', $key)->first()) {
             $topproducts[$key] = $product;
            }
           }
        }
        $topproducts = array_slice($topproducts, 0, 1);

        return $topproducts;
    }
}

if (!function_exists('product_options')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function product_options($user, $product_id){
      $options = \App\Model\Option::where('product', $product_id)->where('user', $user)->get();
      return $options;
    }
}

if (!function_exists('product_options_values')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function product_options_values($option_id){
      $values = \App\Model\OptionValues::where('option_id', $option_id)->get();
      return $values;
    }
}

if (!function_exists('share_to_media')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function share_to_media($type, $name){
        $url = url()->current();

        if ($type == 'facebook') {
            $query = urldecode(http_build_query([
                'app_id' => env('FACEBOOK_APP_ID'),
                'href' => $url,
                'display' => 'page',
                'title' => urlencode($name)
            ]));

            return 'https://www.facebook.com/dialog/share?' . $query;
        }

        if ($type == 'twitter') {
            $query = urldecode(http_build_query([
                'url' => $url,
                'text' => urlencode(\Str::limit($name, 120))
            ]));

            return 'https://twitter.com/intent/tweet?' . $query;
        }

        if ($type == 'whatsapp') {
            $query = urldecode(http_build_query([
                'text' => urlencode($name . ' ' . $url)
            ]));

            return 'https://wa.me/?' . $query;
        }

        if ($type == 'linkedin') {
            $query = urldecode(http_build_query([
                'url' => $url,
                'summary' => urlencode($name)
            ]));

            return 'https://www.linkedin.com/shareArticle?mini=true&' . $query;
        }

        if ($type == 'pinterest') {
            $query = urldecode(http_build_query([
                'url' => $url,
                'description' => urlencode($name)
            ]));

            return 'https://pinterest.com/pin/create/button/?media=&' . $query;
        }

        if ($type == 'google') {
            $query = urldecode(http_build_query([
                'url' => $url,
            ]));

            return 'https://plus.google.com/share?' . $query;
        }
    }
}

if (!function_exists('countries')) {

    function countries(){
      $countries = ["Worldwide", "Afghanistan","Albania","Algeria","Andorra","Angola","Anguilla","Antigua &amp; Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bosnia &amp; Herzegovina","Botswana","Brazil","British Virgin Islands","Brunei","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Cape Verde","Cayman Islands","Chad","Chile","China","Colombia","Congo","Cook Islands","Costa Rica","Cote D Ivoire","Croatia","Cruise Ship","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Estonia","Ethiopia","Falkland Islands","Faroe Islands","Fiji","Finland","France","French Polynesia","French West Indies","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guam","Guatemala","Guernsey","Guinea","Guinea Bissau","Guyana","Haiti","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Jersey","Jordan","Kazakhstan","Kenya","Kuwait","Kyrgyz Republic","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Mauritania","Mauritius","Mexico","Moldova","Monaco","Mongolia","Montenegro","Montserrat","Morocco","Mozambique","Namibia","Nepal","Netherlands","Netherlands Antilles","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria","Norway","Oman","Pakistan","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Puerto Rico","Qatar","Reunion","Romania","Russia","Rwanda","Saint Pierre &amp; Miquelon","Samoa","San Marino","Satellite","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","South Africa","South Korea","Spain","Sri Lanka","St Kitts &amp; Nevis","St Lucia","St Vincent","St. Lucia","Sudan","Suriname","Swaziland","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor L'Este","Togo","Tonga","Trinidad &amp; Tobago","Tunisia","Turkey","Turkmenistan","Turks &amp; Caicos","Uganda","Ukraine","United Arab Emirates","United Kingdom","Uruguay","Uzbekistan","Venezuela","Vietnam","Virgin Islands (US)","Yemen","Zambia","Zimbabwe"];

      return $countries;
    }
}

if (!function_exists('install_log')) {
    function install_log(){
        if (file_exists(storage_path('logs/install.log'))) {
            $logfile = storage_path('logs/install.log');
        }else{
            file_put_contents(storage_path('logs/install.log'), '"========================== INSTALLATION START ========================"' . PHP_EOL, FILE_APPEND);
            return false;
        }
        $args = func_get_args();
        $message = array_shift($args);

        if (is_array($message)) $message = implode(PHP_EOL, $message);

        $message = "[" . date("Y/m/d h:i:s", time()) . "] " . vsprintf($message, $args) . PHP_EOL;
        file_put_contents($logfile, $message, FILE_APPEND);
    }
}
if (!function_exists('varexport')) {
    function varexport($expression, $return=FALSE) {
        $export = var_export($expression, TRUE);
        $patterns = [
            "/array \(/" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/" => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
        if ((bool)$return) return $export; else echo $export;
    }
}
if (!function_exists('ecom_variable_update')) {
    function ecom_variable_update($data = array()) {
        $content = require base_path('config/ecom.php');
        $update_ecom = [];

        foreach ($data as $key => $value) {
            $content[$key] = $value;
        }
        $insert = "<?php \n return " . varexport($content, true) . ';';
        file_put_contents(base_path('config/ecom.php'), $insert);
    }
}
if (!function_exists('env_update')) {
    function env_update($data = array()){
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
if (!function_exists('verify_license')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function verify_license($code, $clean = false){
      $token = new EnvatoToken('c4ScI4Zd0AxPqjkQq74xPigzfboMVDkt');
      $client = new EnvatoClient($token);
      $purchase_code = $code;
      $response = $client->user->sale(['code' => $purchase_code]);
      if (!$response->error && is_array($response->results)) {
          $results = $response->results;
          if (file_exists(storage_path('app/.code'))) {
              unlink(storage_path('app/.code'));
          }
          $env = [];
          $env["LICENSE_KEY"]  = $code;
          $env["LICENSE_NAME"] = $results['buyer'];
          $env["LICENSE_TYPE"] = $results['license'];
          env_update($env);
          unset($results['item'], $results['amount']);
          \Storage::put('.code', \Crypt::encryptString(json_encode($results)));
          ($clean ? install_log('License check success. Name on license "'.$results['buyer'].'". License type "'.$results['license'].'"') : '');

          return (object) ['status' => true, 'response' => 'Done'];
      }
      else {
        return (object) ['status' => false, 'response' => "The code produced an error:\n"];
      }

      return $results;
    }
}

if (!function_exists('license')) {
    function license($key = null){
        $code = null;
        if (file_exists(storage_path('app/.code'))) {
           try {
               $code = json_decode(Crypt::decryptString(Storage::get('.code')), true);
           } catch (\Exception $e) {
            $code = null;
           }
        }else{
            return false;
        }
        app('config')->set('license', $code);
        $key = !empty($key) ? '.'.$key : null;
        return app('config')->get('license'.$key);
    }
}

if (!function_exists('banner')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function banner($user = null){
        if ($user) {
            $user = \App\User::where('id', $user)->first();
        }elseif (!$user && Auth::check()) {
            $user = Auth::user();
        }else{
            return false;
        }
        
        return (!empty(user('media.banner', $user->id)) && file_exists(public_path('media/user/banner/' . user('media.banner', $user->id))) ? url('media/user/banner/' . user('media.banner', $user->id)) : user('extra.banner_url', $user->id));
    }
}


if (!function_exists('profile')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function profile($id){
      $user = \App\User::where('id', $id)->first();
      $domain = $user->domain;
      if (Schema::hasTable('domains')) {
          if ($domain == 'main') {
            $domain = env('APP_URL');
          }elseif ($domain = App\Model\Domains::where('id', $user->domain)->first()) {
            $domain = $domain->scheme.$domain->host;
          }else{
            $domain = env('APP_URL');
          }
      }
      $profile_url = $domain.'/'.$user->username;
      return $profile_url;
    }
}

if (!function_exists('productReviewImage')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function productReviewImage($id){
        $review = \App\Model\Product_Reviews::where('id', $id)->first();
        $avatar = $review->review->avatar ?? '';
        $default = url('media/default_avatar.png');
        $check = media_path('avatars/') . $avatar;
        $path = url('media/avatars/' . $avatar);
        return (file_exists($check)) ? $path : $default;
    }
}

if (!function_exists('has_gateway')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function has_gateway($gateway, $user){
        $gateways = getOtherResourceFile('store_gateways');
        $packageGateways = json_decode(package('gateways', $user)) ?? [];
        if (array_key_exists($gateway, $gateways)) {
            foreach ($packageGateways as $key => $value) {
                if ($gateway == $value) {
                   return true;
                }
            }

            return false;
        }else{
            return false;
        }
    }
}

if (!function_exists('package')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function package($key = null, $user = null){
        if ($user) {
            $user = \App\User::where('id', $user)->first();
        }
        if(Auth::check()){
            $user = Auth::user();
        }
        if ($user->package == 'free') {
            $package = settings('package_free');
        }elseif($user->package == 'trial'){
            $package = settings('package_trial');
        }else{
            if (!$package = \App\Model\Packages::where('id', $user->package)->first()->toArray()) {
                 $package = config('settings.package_free');
            }
        }
        $settings = !is_array($package['settings']) ? (array) $package['settings'] : $package['settings'];
        $price = !is_array($package['price']) ? (array) $package['price'] : $package['price'];
        app('config')->set('package', $package);
        app('config')->set('package.price', $price);
        app('config')->set('package.settings', $settings);
        $key = !empty($key) ? '.'.$key : null;
        return app('config')->get('package'.$key);
    }
}
if (!function_exists('nf')) {
    function nf($numbers, $decimal = 2){
        $return = number_format($numbers, $decimal);
        return $return;
    }
}

if (!function_exists('nr')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function nr($n, $precision = 1) {
        if ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = '';
        } else if ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n / 1000, $precision);
            $suffix = 'K';
        } else if ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n / 1000000, $precision);
            $suffix = 'M';
        } else if ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n / 1000000000, $precision);
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format($n / 1000000000000, $precision);
            $suffix = 'T';
        }

      // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
      // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ( $precision > 0 ) {
            $dotzero = '.' . str_repeat( '0', $precision );
            $n_format = str_replace( $dotzero, '', $n_format );
        }

        return $n_format . $suffix;
    }
}

if (!function_exists('get_chart_data')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function get_chart_data(Array $main_array){
            $results = [];
            foreach($main_array as $date_label => $data) {
                foreach($data as $label_key => $label_value) {
                    if(!isset($results[$label_key])) {
                        $results[$label_key] = [];
                    }
                    $results[$label_key][] = $label_value;
                }
            }
            foreach($results as $key => $value) {
                $results[$key] = '["' . implode('", "', $value) . '"]';
            }
            $results['labels'] = '["' . implode('", "', array_keys($main_array)) . '"]';
            return $results;
    }
}

if (!function_exists('slugify')) {
    function slugify($string, $delimiter = '_'){
        $slug = new SlugGenerator();
        return $slug->generate($string, ['delimiter' => $delimiter]);
    }
}

if (!function_exists('user')) {
    function user($key = null, $user = null){
        if ($user) {
            $user = \App\User::where('id', $user)->first();
        }
        if (Auth::check() && !$user) {
            $user = Auth::user();
        }elseif (!Auth::check() && !$user) {
            return redirect()->route('login');
        }
        app('config')->set('user', $user);
        $key = !empty($key) ? '.'.$key : null;
        return app('config')->get('user'.$key);
    }
}

if (!function_exists('customer')) {
    function customer($key = null, $user = null){
        if ($user) {
            $user = \App\Model\Customers::find($user);
        }
        app('config')->set('customer', $user);
        $key = !empty($key) ? '.'.$key : null;
        return app('config')->get('customer'.$key);
    }
}

if (!function_exists('full_name')) {
    function full_name($user = null){
        if ($user) {
            $user = \App\User::where('id', $user)->first();
        }
        if (Auth::check() && !$user) {
            $user = Auth::user();
        }
        $first_name = $user->name['first_name'] ?? '';
        $last_name = $user->name['last_name'] ?? '';
        $name = $first_name . ' ' . $last_name;
        return $name;
    }
}

if (!function_exists('settings')) {
    function settings($key = null){
       $getsettings = \App\Model\Settings::all()
       ->keyBy('key')
       ->transform(function ($setting) {
             $value = json_decode($setting->value, true);
             $value = (json_last_error() === JSON_ERROR_NONE) ? $value : $setting->value;
             return $value;
        })->toArray();
       app('config')->set('settings', $getsettings);
       $key = !empty($key) ? '.'.$key : null;
       return app('config')->get('settings'.$key);
    }
}

if (!function_exists('getOtherResourceFile')) {
    function getOtherResourceFile($file){
        return require base_path('resources') . "/others/" . $file . '.php';
    }
}

if (!function_exists('media_path')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function media_path($parm = ''){
    	if (!empty($parm)) {
    		$parm = '/'.$parm;
    	}
    	return public_path('media'.$parm);
    }
}

if (!function_exists('getfirstproductimg')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getfirstproductimg($id){
        if (!$product = \App\Model\Products::where('id', $id)->first()) {
            return url('media/empty-dark.png');
        }
        $media = implode(',', $product->media);
        $media = explode(',', $media);
        $default = url('media/empty-dark.png');
        $check = mediaExists('media/user/products', $media[0]);
        $path = getStorage('media/user/products', $media[0]);
        return (!empty($media[0]) && $check) ? $path : $default;
    }
}

if (!function_exists('avatar')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function avatar($user = null){
        if ($user) {
            $user = \App\User::where('id', $user)->first();
        }elseif (!$user && Auth::check()) {
            $user = Auth::user();
        }else{
            return false;
        }
        $avatar = user('media.avatar', $user->id);
        $default = url('media/default_avatar.png');
        $check = mediaExists('media/user/avatar', $avatar);
        $path = getStorage('media/user/avatar', $avatar);
        return (!empty($avatar) && $check) ? $path : $default;
    }
}

if (!function_exists('user_favicon')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function user_favicon($user = null){
        if ($user) {
            $user = \App\User::find($user);
        }elseif (!$user && Auth::check()) {
            $user = Auth::user();
        }else{
            return false;
        }

        $favicon = user('media.favicon', $user->id);
        $default = favicon();
        $check = mediaExists('media/user/favicon', $favicon);
        $path = getStorage('media/user/favicon', $favicon);
        return (!empty($favicon) && $check) ? $path : $default;
    }
}

if (!function_exists('c_avatar')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function c_avatar($user){
        if (!$user = \App\Model\Customers::find($user)) {
            return false;
        }
        $avatar = glob(media_path('avatars/').'*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
        $avatar = $avatar[array_rand($avatar)];
        $avatar = basename($avatar);

        $u_avatar = $user->avatar;
        $check = media_path('user/customers/avatar/') . $u_avatar;
        $path = media('user/customers/avatar/' . $u_avatar);

        if (!empty($u_avatar) && file_exists($check)) {
          return $path;
        }elseif (!empty($u_avatar) && file_exists(media_path('avatars/') . $u_avatar)) {
          return media('avatars/'. $u_avatar);
        }else{
          $user->avatar = $avatar;
          $user->save();
          return media('avatars/'. $avatar);
        }
    }
}

if (!function_exists('getcategoryImage')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getcategoryImage($id){
    	$category = \App\Model\Product_Category::where('id', $id)->first();
	    $default = url('img/default_avatar.png');
      $check = mediaExists('media/user/categories', $category->media);
      $path = getStorage('media/user/categories', $category->media);
	    return (!empty($category->media) && $check) ? $path : $default;
    }
}

if (!function_exists('profile_analytics')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function profile_analytics($user = null){
        if ($user) {
            $user = \App\User::where('id', $user)->first();
        }else{
            return false;
        }
        $return = [];
        if (package('settings.google_analytics', $user->id) && !empty($user->settings->google_analytics)):
        $return[] = '<!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id='. $user->settings->google_analytics .'"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag("js", new Date());

            gtag("config", "'. $user->settings->google_analytics .'");
        </script>';
       endif;
       if (package('settings.facebook_pixel', $user->id) && !empty($user->settings->facebook_pixel)):
        $return[] = "<!-- Facebook Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', ". $user->settings->facebook_pixel .");
            fbq('track', 'PageView');
        </script>
        <noscript><img height=\"1\" width=\"1\" style='display:none' src='https://www.facebook.com/tr?id=".$user->settings->facebook_pixel."&ev=PageView&noscript=1\"/></noscript>
        <!-- End Facebook Pixel Code -->";
       endif;

       return implode(' ', $return);
    }
}



if (!function_exists('profile_body_class')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function profile_body_classes($user = null){
        if ($user) {
            $user = \App\User::where('id', $user)->first();
        }else{
            return false;
        }
        $return = [];
        if (!empty($user->settings->showbuttombar) && $user->settings->showbuttombar) {
            $return[] = 'has-bottom-bar';
        }
        $return[] = 'profile';
        if (package('settings.custom_background', $user->id)) {
            if ($user->background_type == 'gradient') {
                $return[] = $user->background;
            }elseif ($user->background_type == 'default') {
                $return[] = 'default';
            }
        }else{
            $return[] = 'default';
        }

        if (!empty($user->settings->default_color) && $user->settings->default_color == 'dark') {
            $return[] = 'background-dark';
        }
       return implode(' ', $return);
    }
}

if (!function_exists('custom_code')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function custom_code(){
        $code = [];
        if (settings('custom_code.enabled')):
         $code[] = '<style>
             '. settings('custom_code.head') .'
         </style>';
        endif;

        return implode(' ', $code);
    }
}

if (!function_exists('store_categories')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function store_categories($user, $limit = null, $paginate = false){
        $categories = App\Model\Product_Category::where('user', $user);
        if (!empty($limit)) {
            $categories->limit($limit);
        }
        if ($paginate) {
            $categories = $categories->paginate($limit);
        }else{
            $categories = $categories->get();
        }

        return $categories;
    }
}

if (!function_exists('store_blogs')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function store_blogs($user, $limit = null){
        $blogs = App\Model\Blog::where('user', $user)->orderBy('order', 'ASC')->orderBy('id', 'DESC');
        if (!empty($limit)) {
            $blogs->limit($limit);
        }
        $blogs = $blogs->get();

        return $blogs;
    }
}

if (!function_exists('store_products')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function store_products($user, $limit = null, $paginate = false){
        $products = App\Model\Products::where('user', $user)->orderBy('position', 'ASC')->orderBy('id', 'DESC');
        if (!empty($limit)) {
            $products->limit($limit);
        }
        if (!$paginate) {
            $products = $products->get();
        }else{
            $products = $products->paginate($limit);
        }

        return $products;
    }
}
