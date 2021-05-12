<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js one-page-layout" data-click-ripple-animation="yes">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- CSRF Token -->
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>{{'@'.$user->username}}{{ !package('settings.custom_branding', $uid) ? ' - ' . env('APP_NAME') : '' }}</title>
      @if(!empty(settings('favicon')))
      <link href="{!! package('settings.custom_branding', $uid) ? user_favicon($uid) : favicon() !!}" rel="shortcut icon" type="image/png" />
      @endif
      <!-- Fonts -->
      <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;700&family=Unna:ital,wght@0,400;1,700&display=swap" rel="stylesheet">
      <link href="{{ asset('css/bootstrap.css?v=' . env('APP_VERSION')) }}" rel="stylesheet">
      @foreach(['animate.css', 'swiper.min.css', 'icons.css', 'aos.css', 'main.css', 'normalize.css', 'ecom.css'] as $file)
      <link href="{{ asset('assets/css/' . $file . '?v=' . env('APP_VERSION')) }}" rel="stylesheet">
      @endforeach
      <!-- Styles -->
      @foreach(['plugins/magnific-popup/magnific-popup.min', 'plugins/owl-carousel/owl.carousel.min', 'plugins/owl-carousel/owl.theme.default.min', 'plugins/justified-gallery/justified-gallery.min', 'plugins/sal/sal.min', 'css/main', 'css/custom', 'plugins/themify/themify-icons.min', 'plugins/simple-line-icons/css/simple-line-icons'] as $file)
      <link href="{{ themes($file . '.css?v=') . env('APP_VERSION') }}" rel="stylesheet">
      @endforeach

      <link href="{{ asset('font/css/all.css?v=' . env('APP_VERSION')) }}" rel="stylesheet">

      @foreach(['bundle'] as $file)
      <script src="{{ asset('js/' . $file . '.js?v=' . env('APP_VERSION')) }}" type="text/javascript"></script>
      @endforeach

      @yield('customCSS')

      {!! profile_analytics($user->id) !!}

      @if (package('settings.add_to_head', $uid))
       {!! clean(user('extra.headScript', $uid), 'allowonlyscript') !!}
      @endif

   </head>
   {!! custom_code() !!}
   <body data-preloader="4" class="{{ profile_body_classes($user->id) }}">
      {!! ($user->background_type == "color") ? "
      <style> $background_color </style>
      " : "" !!}
      <div class="if-is-mobile"></div>
      <div class="sidebar-overlay"></div>
      @php
      $cart = new \App\Cart;
      $cart = $cart->getAll($uid);
      @endphp

      <div class="search-form-wrapper header-search-form" id="search-product">
         <div class="container">
            <div class="search-results-wrapper">
               <div class="btn-search-close data-box" data-target="#search-product">
                  <i class="ni ni-cross fs-20px"></i>
               </div>
            </div>
            <form method="get" action="{{ route('user-profile-products', ['profile' => $user->username]) }}" role="search" class="mt-8">
               @if (!empty(request()->get('page')))
               <input type="hidden" name="page" value="{{request()->get('page')}}">
               @endif
               <div class="col-md-12 mb-0">
                  <div class="form-group">
                     <input type="text" class="search-input" name="query" value="{{ request()->get('query') }}" placeholder="{{ __('Search') }}">
                  </div>
               </div>
               <div class="col-md-12 row">
                  <div class="form-group col-6">
                     <label class="m-3">{{ __('Min Price') }}</label>
                     <input type="text" class="search-input" name="min-price" value="{{ $min_price }}" placeholder="{{ __('Min Price') }}">
                  </div>
                  <div class="form-group col-6">
                     <label class="m-3">{{ __('Max Price') }}</label>
                     <input type="text" class="search-input" name="max-price" value="{{ $max_price }}" placeholder="{{ __('Max Price') }}">
                  </div>
               </div>
               <div class="col-md-12">
                  <label class="m-3">{{ __('Category') }}</label>
                  <select class="custom-select w-100" name="category">
                     <option value="">{{ __('None') }}</option>
                     @foreach($categories as $category)
                     <option value="{{$category->slug}}" {{ request()->get('category') == $category->slug ? 'selected' : '' }}>{{$category->title}}</option>
                     @endforeach
                  </select>
               </div>
               <div class="col-12">
                  <button class="button smoothscroll justify-center button-lg mt-0 align-items-center theme-btn d-flex w-100">{{ __('Search') }}</button>
               </div>
            </form>
         </div>
      </div>
      <div class="wrapper pt-lg-4">
         <header class="header-nav-center active-blue d-flex align-items-center" id="OverallHeader">
            <div class="px-sm-5 px-md-8 w-100">
               <!-- navbar -->
               <nav class="navbar navbar-expand-lg navbar-light px-sm-0">
                  <a class="navbar-brand h-50px" href="{{ route('user-profile', ['profile' => $user->username]) }}">
                  <img class="w-80px h-100 ob-contain" src="{{ avatar($uid) }}" alt="logo" />
                  </a>
                  <div class="d-flex">
                     <div class="d-flex d-lg-none">
                        <a href="#" class="search-toggle data-box c-black d-flex align-items-center" data-target="#search-product">
                        <i class="tio search fs-25px"></i>
                        </a>
                        <a href="{{ route('user-profile-login', ['profile' => $user->username]) }}" class="ml-3 c-black d-flex align-items-center">
                        <i class="tio user_outlined fs-25px"></i>
                        </a>
                        <div class="element element-cart d-flex align-items-center">
                           <a href="{{ route('user-profile-checkout', ['profile' => $user->username]) }}" class="icon-cart c-black">
                           <i class="tio shopping_cart_outlined fs-25px"></i>
                           <span class="count cart-count cart-total">{{count($cart)}}</span>
                           </a>
                        </div>
                     </div>
                     <button class="navbar-toggler menu ripplemenu ml-3" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                        aria-label="Toggle navigation" type="button">
                        <svg viewBox="0 0 64 48">
                           <path d="M19,15 L45,15 C70,15 58,-2 49.0177126,7 L19,37"></path>
                           <path d="M19,24 L45,24 C61.2371586,24 57,49 41,33 L32,24"></path>
                           <path d="M45,33 L19,33 C-8,33 6,-2 22,14 L45,37"></path>
                        </svg>
                     </button>
                  </div>
                  <div class="collapse navbar-collapse" id="navbarSupportedContent">
                     {!! store_menu($uid, 5, 'html', ['ul' => 'nav ml-4 navbar-nav mx-auto']) !!}
                     <div class="nav_account d-none d-lg-flex">

                      <form method="get" action="{{ route('user-profile-products', ['profile' => $user->username]) }}" class="form-group --password mb-0 mr-4">
                         @if (!empty(request()->get('page')))
                         <input type="hidden" name="page" value="{{request()->get('page')}}">
                         @endif
                        <div class="input-group">
                          <input type="text" name="query" class="form-control h-50px border-0 bg-light" placeholder="{{ __('Search') }}" />
                          <div class="input-group-prepend hide_show">
                            <button><span class="input-group-text tio search"></span></button>
                          </div>
                        </div>
                      </form>

                        @if (!auth_user($uid, 'check'))
                        <a href="{{ route('user-profile-login', ['profile' => $user->username]) }}" class="ml-3 c-black mr-4 d-flex align-items-center">
                        <i class="tio user_outlined fs-25px"></i>
                        </a>
                        @endif
                        <div class="element element-cart mr-4 d-flex align-items-center">
                           <a href="{{ route('user-profile-checkout', ['profile' => $user->username]) }}" class="icon-cart c-black">
                           <i class="tio shopping_cart_outlined fs-25px"></i>
                           <span class="count cart-count cart-total">{{count($cart)}}</span>
                           </a>
                        </div>
                        @if (auth_user($uid, 'check'))
                          <a href="{{ route('user-profile-login', ['profile' => $user->username]) }}" class="p-0 opacity-1 sweep_letter scale sweep_top user-avatar-text bg-blue radius-100" style="{{ store_colors($uid) }}">
                            <img class="h-100 ob-cover" src="{{ c_avatar(auth_user($uid, 'get')->id) }}" alt="">
                          </a>
                        @endif
                     </div>
                  </div>
               </nav>
               <!-- End Navbar -->
            </div>
            <!-- end container -->
         </header>
         <!-- Scroll to Top -->
         <div class="scrolltotop">
            <a class="button-circle button-circle-sm button-circle-black" href="#"><i class="ti-arrow-up"></i></a>
         </div>
         <!-- end Scroll to Top -->
         @if (!package('settings.ads', $uid) && settings('ads.enabled'))
         {!! settings('ads.store_header') !!}
         @endif
         @yield('content')
         @if (!package('settings.ads', $uid) && settings('ads.enabled'))
         {!! settings('ads.store_footer') !!}
         @endif
         <footer class="footer_short position-relative bg-white z-index-3 mt-5">
            <div class="container">
               <div class="row justify-content-md-center text-center">
                  <div class="col-md-8">
                     <a class="logo c-dark">
                     <img class="h-30px w-100px ob-contain" src="{{ avatar($uid) }}" alt="">
                     </a>
                     <div class="social--media">
                        @if (package('settings.social', $uid))
                        @foreach ($options->socials as $key => $items)
                        @if (!empty($user->socials[$key]))
                        <a href="{{(!empty($user->socials[$key]) ? Linker::url(sprintf($items['address'], $user->socials[$key]), ['ref' => $user->username]) : "")}}" target="_blank" class="btn so-link">
                        <i class="ni ni-{{$items['icon']}}"></i>
                        </a>
                        @endif
                        @endforeach
                        @endif
                     </div>
                     {!! store_menu($uid, 5, 'html', ['ul' => 'other--links d-flex justify-content-center mb-3']) !!}
                     <div class="copyright">
                        @if (!package('settings.custom_branding', $uid))
                        <p class="c-black">{{'© ' . date('Y')}} <a href="{{ url('/') }}" class="c-black" target="_blank">{{ ucfirst(config('app.name')) }}</a> {{ __('All Right
                           Reseved') }}
                        </p>
                        @else
                        @if (package('settings.custom_branding', $uid))
                        @if (!empty(user('extra.custom_branding', $uid)))
                        <p>{{'© ' . date('Y') .' '. (!empty(user('extra.custom_branding', $uid)) ? user('extra.custom_branding', $uid) : "")}}</p>
                        @endif
                        @endif
                        @endif
                     </div>
                  </div>
               </div>
            </div>
         </footer>
      </div>
      <!-- end wrapper -->
      @if (!empty(Session::get('error')))
      <script>
         Swal.fire({
           title: 'Error!',
           text: '{{Session::get('error')}}',
           icon: 'error',
           confirmButtonText: 'OK'
         });
      </script>
      @endif
      @if (!empty(Session::get('success')))
      <script>
         Swal.fire({
           title: '{{Session::get('success')}}',
           icon: 'success',
           confirmButtonText: 'OK'
         });
      </script>
      @endif
      @if (!empty(Session::get('info')))
      <script>
         Swal.fire({
           title: '{{Session::get('info')}}',
           icon: 'info',
           confirmButtonText: 'OK'
         });
      </script>
      @endif
      @if(!$errors->isEmpty())
      @foreach ($errors->all() as $error)
      <script>
         Swal.fire({
           title: '{{ $error }}',
           icon: 'error',
           confirmButtonText: 'OK'
         });
      </script>
      @endforeach
      @endif
      @yield('footerJS')
      <script src="{{ asset('slick/slick.min.js') }}"></script>
      @foreach(['plugins/plugins', 'js/functions.min', 'js/script'] as $file)
      <script src="{{ themes($file . '.js?v=' . env('APP_VERSION')) }}" type="text/javascript"></script>
      @endforeach
      <script src="{{ asset('js/scripts.js') }}"></script>
   </body>
</html>

