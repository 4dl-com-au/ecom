<?php

namespace App\Http\Controllers\Dashboard;

use File, Str;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Model\Products;
use App\Model\Product_Orders;
use App\Model\Product_Reviews;
use App\Model\Product_Category;
use App\Model\Option;
use App\Model\Customers;
use App\Mail\GeneralMail;
use App\Model\OptionValues;

class ProductController extends Controller{
    public $user;
    function __construct(){
      $this->middleware(function ($request, $next) {
         $this->user = Auth::user();
         return $next($request);
      });
    }

    public function remove_option(Request $request){
      $id = $request->id;
      Option::where('id', $id)->delete();
    }

    public function remove_option_value(Request $request){
      $id = $request->id;
      OptionValues::where('id', $id)->delete();
    }

    public function new_product(){
      $categories = Product_Category::where('user', $this->user->id)->get();
      return view('dashboard.products.add', ['categories' => $categories]);
    }

    public function edit_product($id){
      $user = Auth::user();
      if (!$product = Products::find($id)) {
        abort(404);
      }

      $reviews = Product_Reviews::where('product_id', $id)->where('storeuser', $user->id)->orderBy('id', 'desc')->get();
      $categories = Product_Category::where('user', $this->user->id)->get();
      return view('dashboard.products.edit', ['product' => $product, 'categories' => $categories, 'reviews' => $reviews]);
    }
    public function single_orders($id, Request $request){
      $order = Product_Orders::where('storeuser', user('id'))->where('id', $id)->first();

      if (!$order) {
        abort(404);
      }

      $products = [];

      $customer = '';

      if ($customer = Customers::where('id', $order->customer)->first()) {
        $customer = $customer;
      }


      foreach ($order->products as $key => $value) {
       $product = Products::where('id', $key)->first();
       if (!array_key_exists($key, $products)) {
          $products[$key] = [
           'qty'  => 0,
           'name'  => '',
           'price'  => '',
           'media'  => '',
           'options' => ''
          ];
       }
       $price = $value['price'];

       /**
       *
       * Use Product price
       * $price = !empty($product->salePrice) ? $product->salePrice : $product->price;
       *
       **/

       $products[$key]['qty'] = $value['qty'];
       $products[$key]['options'] = $value['options'] ?? '';
       $products[$key]['name'] = $product->title ?? $value['name'] ?? '';
       $products[$key]['price'] = $price;
       $products[$key]['media'] = $product->media ?? '';
      }

      if ($request->get('type') == 'invoice') {

        return view('dashboard.orders.invoice', ['order' => $order, 'customer' => $customer, 'products' => $products]);
      }

      return view('dashboard.orders.single-order', ['order' => $order, 'customer' => $customer, 'products' => $products]);
    }


    public function order_status(Request $request){
      if ($request->action == 'send_custom_order_mail') {
        if ($order = Product_Orders::find($request->id)) {
          $email = $order->details->email ?? '';
          $mail = (object) ['subject' => $request->mail_subject, 'message' => $request->mail_message];

           try {
            Mail::to($email)->send(new GeneralMail($mail));
           } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
          }


          return back()->with('success', __('Mail sent!'));

        }

        return back()->with('error', __('Could not find order'));
      }


      if ($request->action == 'update_order') {
        $id = $request->id;
        if ($order = Product_Orders::find($id)) {
          $order->send_email = $request->send_email;
          $order->order_status = $request->order_status;

          $order->save();


          if ($request->send_email == 1) {
            $email = $order->details->email ?? '';

            if ($request->order_status == 1) {

               $mail = (object) ['subject' => __('Order Delivered'), 'message' => __('Thank you for your patronage. This is to confirm that your order from <a href="'. route('user-profile', ['profile' => user('username', $order->storeuser)]) .'">'. full_name($order->storeuser) .'</a> has been delivered.

                <br>
                <br>

                If you\'re a customer on this store kindly use the link below to login and view the invoice of this order.') . '<br><br>' . '<td style="text-align:center;"><a href="'. route('user-profile-login', ['profile' => user('username', $order->storeuser)]) .'" style="background-color:#6576ff;border-radius:4px;color:#ffffff;display:inline-block;font-size:13px;font-weight:600;line-height:44px;text-align:center;text-decoration:none;text-transform: uppercase; text-align:center; padding: 0 25px">'. __('Login') .'</a>
         <p style="margin-top: 5px; margin-bottom: 10px;">'.__('Or').'</p>
         '. route('user-profile-login', ['profile' => user('username', $order->storeuser)]) .'</td>'];

               try {
                Mail::to($email)->send(new GeneralMail($mail));
               } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
              }

            }elseif ($request->order_status == 2) {
              $mail = (object) ['subject' => __('Order Pending'), 'message' => __('This it to let you know that your order on <a href="'. route('user-profile', ['profile' => user('username', $order->storeuser)]) .'">'. full_name($order->storeuser) .'</a> has been set to pending.')];
               try {
                Mail::to($email)->send(new GeneralMail($mail));
               } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
              }

            }elseif ($request->order_status == 3) {
              $mail = (object) ['subject' => __('Order Canceled'), 'message' => __('This it to let you know that your order on <a href="'. route('user-profile', ['profile' => user('username', $order->storeuser)]) .'">'. full_name($order->storeuser) .'</a> has been Canceled.')];

               try {
                Mail::to($email)->send(new GeneralMail($mail));
               } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
              }

            }

          }

        }

        return back()->with('success', __('Order Saved'));
      }

      if ($request->action == 'update_all') {
        if ($request->type == 'mark_as_delivered') {
          foreach ($request->action_select as $key => $value) {
            if ($product = Product_Orders::find($value)) {
              $product->order_status = 1;
              $product->save();
            }
          }
        }elseif ($request->type == 'remove') {
          foreach ($request->action_select as $key => $value) {
            if ($product = Product_Orders::find($value)) {
              $product->delete();
            }
          }
        }
        return ['response' => 'success'];
      }elseif($request->action == 'mark_as_delivered'){
        if ($product = Product_Orders::find($request->id)) {
          $product->order_status = 1;
          $product->save();
        }
        return back()->with('success', __('Order set as completed'));
      }elseif ($request->action == 'remove') {
        if ($product = Product_Orders::find($request->id)) {
          $product->delete();
        }

        return back()->with('success', __('Order removed successfully'));
      }
    }

    public function orders(){
      $orders = Product_Orders::where('storeuser', user('id'))->orderBy('id', 'DESC')->paginate(13);
      return view('dashboard.orders.orders', ['orders' => $orders]);
    }

    public function post_product(Request $request, $type, Products $products){
      $user = Auth::user();
      if (!in_array($type, ['new', 'edit', 'delete', 'remove_single_image', 'remove_single_file'])) {
        abort(403);
      }
      $options = $request->options;
      $images = [];
      $categories = [];
      $max_size = settings('user.products_image_size') ?? '1';
      $max_size = $max_size.'000';

      if(!empty($request->product_categories)):
        foreach ($request->product_categories as $value) {
          $categories[] = $value;
        }
      endif;


      if (in_array($type, ['edit', 'new'])) {
        $files = $request->media;
        $downloadable_files = $request->downloadables;

        $request->validate([
           'product_name' => 'required|string',
           'product_price' => 'required|numeric',
        ]);
        if ($request->hasFile('media')) {
          $request->validate([
             'media'   => 'required',
             'media.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:'.$max_size,
          ]);
        }
        $slug = $maybe_slug = slugify($request->product_name);
        $next = '_';
      }


      if ($type == 'new') {
        $products = new Products;
        $products->user = $user->id;

        # Package limits

        if(package('settings.products_limit') != -1 && count(Products::where('user', user('id'))->get()) > package('settings.products_limit')) {
          return back()->with('error', 'Package limit reached');
        }

        # Product Images

        if ($request->hasFile('media')) {
          if (count($files) > settings('user.products_image_limit') ?? '3') {
            return back()->with('error', settings('user.products_image_limit') .' '. __('Images max'));
          }
          foreach ($files as $file) {
            $image_name = putStorage('media/user/products', $file);
            $images[] = $image_name;
          }
        }


        # Downloadable Product Files
        if (!empty($downloadable_files)) {
            #$request->validate([
            #    'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            #]);
            $file_name = $slug .'_'.Str::random(13).'.'.$downloadable_files->extension();
            $downloadable_files->move(media_path('user/downloadables'), $file_name);
            $products->files = $file_name;
        }

        $extra = ['shipping' => $request->product_shipping];
      }elseif ($type == 'edit') {


        while (Products::where('slug', '=', $slug)->where('id', '!=', $request->id)->first()) {
          $slug = "{$maybe_slug}{$next}";
          $next = $next . '_';
        }
        $products = Products::find($request->id);

        if ($request->hasFile('media')) {
          if (count($files) > settings('user.products_image_limit') ?? '3') {
            return back()->with('error', settings('user.products_image_limit') .' '. __('Images max'));
          }
          if (!empty($products->media)) {
            foreach ($products->media as $img) {
              if (mediaExists('media/user/products', $img)) {
                storageDelete('media/user/products', $img);
              }
            }
          }
          foreach ($files as $file) {
            $image_name = putStorage('media/user/products', $file);
            $images[] = $image_name;
          }
        }else{
          $images = $products->media;
        }



        if (!empty($downloadable_files)) {
            #$request->validate([
            #    'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            #]);
            if (!empty($products->files) && file_exists(media_path('user/downloadables/' . $products->files))) {
              unlink(media_path('user/downloadables/' . $products->files));
            }
            $file_name = $slug .'_'.Str::random(13).'.'.$downloadable_files->extension();
            $downloadable_files->move(media_path('user/downloadables'), $file_name);
            $products->files = $file_name;
        }

        
        $extra = ['shipping' => $request->product_shipping];
      }elseif ($type == 'delete') {
        $products = Products::find($request->id);
        if (!empty($products->media)) {
            $media = $products->media;
            foreach ($media as $key => $value) {
                if (mediaExists('media/user/products', $value)) {
                  storageDelete('media/user/products', $value);
                }
            }
        }
        $products->delete();

        return back()->with('success', __('Product deleted successfully'));
      }elseif ($type == 'remove_single_image') {

        if (mediaExists('media/user/products', $request->image)) {
          storageDelete('media/user/products', $request->image);
        }
        if ($product = Products::find($request->id)) {
          $media = [];
          foreach ($product->media as $key => $item) {
            if ($item !== $request->image) {
              $media[] = $item;
            }
          }
          $product->media = $media;
          $product->save();
        }
        return ['response' => 'success'];
      }elseif ($type == 'remove_single_file') {
        if (file_exists(media_path('user/downloadables/'.$request->image))) {
          unlink(media_path('user/downloadables/'.$request->image));
        }
        if ($product = Products::find($request->id)) {
          $product->files = null;
          $product->save();
        }
        return ['response' => 'success'];
      }
      $products->title = $request->product_name;
      $products->slug  = $slug;
      $products->price = $request->product_price;
      $products->salePrice  = $request->product_salePrice;
      $products->stock = $request->product_stock;
      $products->stock_management = $request->manage_stock;
      $products->stock_status = $request->stock_status;
      $products->sku = $request->product_sku;
      $products->product_condition = $request->product_condition;
      $products->external_url = $request->external_url;
      $products->external_url_name = $request->external_url_name;
      $products->description = $request->product_description;
      $products->categories = $categories;
      $products->media = $images;
      $products->extra = $extra;
      $products->save();
      if (!empty($options)) {
        foreach ($options as $key => $value) {
          $op = Option::UpdateOrCreate(['id' => $value['id'], 'user' => $user->id], [
            'user' => $user->id,
            'product' => $products->id,
            'name' => $value['name'],
            'type' => $value['type'],
            'is_required' => $value['required'],
            'is_global' => 0,
          ]);
          if (!empty($value['values'])) {
            foreach ($value['values'] as $key => $item) {
              OptionValues::UpdateOrInsert(['id' => $item['id']], [
               'user' => $user->id,
               'option_id' => $op->id,
               'price' => $item['price'],
               'label' => $item['label'],
              ]);
            }
          }
        }
      }
      return back()->with('success', 'Saved Successfully');
    }

    public function my_product(){
      $user = Auth::user();
      $products = Products::where('user', $user->id)->orderBy('position', 'ASC')->orderBy('id', 'DESC')->get();
      return view('dashboard.products.all', ['products' => $products]);
    }

    public function products_sortable(Request $request){
     foreach($request->data as $key) {
        $key['id'] = (int) $key['id'];
        $key['position'] = (int) $key['position'];
        $update = Products::find($key['id']);
        $update->position = $key['position'];
        $update->save();
     }
    }

    public function product_price(Request $request){
      $total = \App\Cart::getOptionsAttr($request->options, 'total_price');

      return ['status' => 'success', 'total' => number_format($total)];
    }

    public function products_sortable_images($id, Request $request){
     $images = [];
     foreach($request->data as $key) {
        $images[] = $key['id'];
     }
     $update = Products::find($id);
     $update->media = $images;
     $update->save();
    }

    public function category_view(){
      $user = Auth::user();
      $categories = Product_Category::where('user', $user->id)->get();
      return view('dashboard.category.view', ['categories' => $categories]);
    }

    public function category_post(Request $request, $type){
      $user = Auth::user();
      $images = null;
      if (!in_array($type, ['new', 'edit', 'delete'])) {
        abort(403);
      }
      $slug = $maybe_slug = slugify($request->title ?? 'null');
      $next = '_';
      if ($type == 'new') {
        while (Product_Category::where('slug', '=', $slug)->first()) {
          $slug = "{$maybe_slug}{$next}";
          $next = $next . '_';
        }
        $category = new Product_Category;
        if (!empty($request->media)) {
            $request->validate([
                'media' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            ]);
            if (!empty($category->media)) {
                if(mediaExists('media/user/categories', $category->media)){
                    storageDelete('media/user/categories', $category->media);
                }
            }
            $imageName = putStorage('media/user/categories', $request->media);
            $images = $imageName;
        }
      }elseif($type == 'edit'){
        $category = Product_Category::find($request->id);
        while (Product_Category::where('slug', '=', $slug)->where('id', '!=', $request->id)->first()) {
          $slug = "{$maybe_slug}{$next}";
          $next = $next . '_';
        }
        if (!empty($request->media)) {
            $request->validate([
                'media' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            ]);
            if (!empty($category->media)) {
                if(mediaExists('media/user/categories', $category->media)){
                    storageDelete('media/user/categories', $category->media);
                }
            }

            $image = putStorage('media/user/categories', $request->media);

            $images = $image;
        }else{
          $images = $category->media;
        }
      }elseif ($type == 'delete') {
        $id = $request->id;
        $category = Product_Category::find($id);
        if (!empty($category->media) && mediaExists('media/user/categories', $category->media)) {
          storageDelete('media/user/categories', $category->media);
        }
        $category->delete();
        return back()->with('success', __('Category Deleted'));
      }
      $category->user  = $user->id;
      $category->title = $request->title;
      $category->slug = $slug;
      $category->status = $request->status;
      $category->description = $request->description;
      $category->media = $images;
      $category->save();
      return back()->with('success', 'Saved Successfully');
    }

    private function getBase64Size($file){
      try{
        $size = (int) (strlen(rtrim($file, '=')) * 3 / 4);
        $size    = round($size / 1024);
      }catch(\Exception $e){

      }
      return $size;
    }
}