@section('customCSS')

     <link href="{{ themes('css/enhanced-preset.css?v=' . env('APP_VERSION')) }}" rel="stylesheet">
@stop
<div class="enhanced">
        <section class="container pt-8 pt-lg-6">
            @php
                $banner_base = basename($banner);
                $banner_exists = mediaExists('media/user/pages', $banner_base);
            @endphp
            <div class="enhanced-banner p-4 px-5 radius-25 h-350px bg-image" data-bg-src="{{ $banner_exists ? $banner : url('media/misc/enhanced-banner.png') }}">
                <div class="row h-100">
                  <div class="col-md-7 d-flex align-items-end">
                     <div class="enhanced-banner-texts">
                        <h1 class="text-white mb-0 mb-2">{{ full_name($uid) }}</h1>
                        <p class="text-white">{!! $banner_subtitle !!}</p>
                        <a href="{{ route('user-profile-products', ['profile' => $user->username]) }}" class="btn btn_sm_primary bg-white fs-14px mt-3">{{ __('SEE PRODUCTS') }}</a>
                    </div>
                  </div>
                </div>
            </div>
        </section>

        <section class="container py-4"> 
            <div class="row align-items-end mb-4">
                <div class="col-12 col-md-12 col-lg-8">
                    <h1 class="title title--h1 fs-30px ml-3">{{ __('Categories') }}</h1>
                </div>
                <div class="col-12 col-md-12 col-lg-4 text-lg-right d-none d-md-block">
                    <a class="btn" style="{{ store_colors($uid, 'css') }}" href="{{ route('user-profile-categories', ['profile' => $user->username]) }}">{{ __('View all categories') }}</a>
                </div>
            </div>
            <!-- Product Categories -->
            <div class="banner container mb-5 radius-25 p-3 bg-white">
                <div class="row col-spacing-10">
                    @foreach (store_categories($uid, 3) as $items)
                    <div class="col-12 col-sm-6 col-md-3">
                        <a href="{{ route('user-profile-products', ['profile' => $user->username, 'category' => $items->slug]) }}" class="ob-cover fancy-box-c bg-white">
                            <img src="{{ getcategoryImage($items->id) }}" alt="">
                            <div class="content">
                                <h5 class="font-weight-normal title">{{$items->title}}</h5>
                                <p class="subtitle">{{ p_category($uid, 'count', $items->slug) . __(' Products') }}</p>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div><!-- end row -->
            </div><!-- end container-fluid -->      
        </section>


        <section class="container pt-4">
            <div class="row align-items-end mb-1">
                <div class="col-12 col-md-12 col-lg-8">
                    <h1 class="title title--h1 fs-30px ml-3">{{ __('Our Products') }}</h1>
                </div>
                <div class="col-12 col-md-12 col-lg-4 text-lg-right d-none d-md-block">
                    <a class="btn" style="{{ store_colors($uid, 'css') }}" href="{{ route('user-profile-products', ['profile' => $user->username]) }}">{{ __('View all Products') }}</a>
                </div>
            </div>
            <div class="row">
              @foreach (store_products($uid, 8) as $key => $product)
               <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="product-box">
                  <div class="product-img">
                    <a href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $product->id]), ['ref' => $user->username]) }}" class="h-100">
                      <img src="{{ getfirstproductimg($product->id) }}" alt="" class="h-100 w-100">
                    </a>
                        @if (!empty($product->salePrice))
                      <div class="product-badge-left">
                        <span class="font-small uppercase font-family-secondary font-weight-medium">{{__('On Sale')}}</span>
                      </div>
                        @endif
                  </div>
                  <div class="product-card-middle">
                      
                  <div class="product-title bg-white mt-0">
                    <h6 class="font-weight-medium"><a href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $product->id]), ['ref' => $user->username]) }}">{{ $product->title }}</a></h6>
                  <div class="add-to-cart d-flex justify-content-between">
                    <h4 class="fs-23px theme-color">{!! Currency::symbol($user->gateway['currency'] ?? '') !!}{{ nf(!empty($product->salePrice) ? $product->salePrice : $product->price) }}</h4>
                    @if (!empty($product->external_url))
                      <a target="_blank" href="{{ url("$product->external_url") }}" class="theme-color button-text-1"><em class="fz-20px ni ni-globe"></em></a>
                      @else
                      <form class="product-quantity" id="add-to-cart" data-qty="1" data-route="{{ route('add-to-cart', ['user_id' => $user->id, 'product' => $product->id]) }}" data-id="{{$product->id}}">
                          <input type="hidden" id="quantity" name="quantity" min="1" max="10" value="1">
                          <button class="button-text-1 theme-color ajax_add_to_cart d-flex align-center" type="submit">
                            <i class="fz-20px tio shopping_basket_outlined zero"></i>
                            <i class="fz-20px ni ni-plus-circle first"></i>
                            <i class="fz-20px ni ni-check-circle second"></i>
                          </button>
                        </form>
                    @endif
                   </div>
                  </div>
                  </div>
                </div>
               </div>
              @endforeach
            </div>
            
        </section>
        <!-- Section About Us -->
        <section id="about" class="container pt-5 pb-5">
            <h1 class="title mb-3 d-block d-lg-none">{{ __('About') }}</h1>
            <div class="row">
                <div class="col-12 col-lg-5">
                    <div class="image">
                        <img src="{{ $about_banner }}" class="h-400px ob-cover radius-20 w-100" alt="">
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="">
                      <h1 class="title mb-3 d-lg-block d-none">{{ __('About') }}</h1>
                      <p class="paragraph js-scroll-show">{!! $short_about !!}</p>
                    </div>
                </div>
            </div>
        </section>

        @if (count(store_blogs($uid)) > 0)
        <section class="container pt-4">
            <div class="row align-items-end mb-1">
                <div class="col-12 col-md-12 col-lg-8">
                    <h1 class="title title--h1 fs-30px ml-3">{{ __('Blog Post') }}</h1>
                </div>
            </div>
            @include('blocks.blogs.blog_grid', ['number_of_blogs' => 6])

        </section>
        @endif
        @if (!empty(user_top_products($uid, 0, 8)))
        <section class="container pt-4">
            <div class="row align-items-end mb-1">
                <div class="col-12 col-md-12 col-lg-8">
                    <h1 class="title title--h1 fs-30px ml-3">{{ __('Top Products') }}</h1>
                </div>
            </div>
            <div class="row">
              @foreach (user_top_products($uid, 0, 8) as $key => $product)
               <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="product-box">
                  <div class="product-img">
                    <a href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $product->id]), ['ref' => $user->username]) }}" class="h-100">
                      <img src="{{ getfirstproductimg($product->id) }}" alt="" class="h-100 w-100">
                    </a>
                        @if (!empty($product->salePrice))
                      <div class="product-badge-left">
                        <span class="font-small uppercase font-family-secondary font-weight-medium">{{__('On Sale')}}</span>
                      </div>
                        @endif
                  </div>
                  <div class="product-card-middle">
                      
                  <div class="product-title bg-white mt-0">
                    <h6 class="font-weight-medium"><a href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $product->id]), ['ref' => $user->username]) }}">{{ $product->title }}</a></h6>
                  <div class="add-to-cart d-flex justify-content-between">
                    <h4 class="fs-23px theme-color">{!! Currency::symbol($user->gateway['currency'] ?? '') !!}{{ nf(!empty($product->salePrice) ? $product->salePrice : $product->price) }}</h4>
                    @if (!empty($product->external_url))
                      <a target="_blank" href="{{ url("$product->external_url") }}" class="theme-color button-text-1"><em class="fz-20px ni ni-globe"></em></a>
                      @else
                      <form class="product-quantity" id="add-to-cart" data-qty="1" data-route="{{ route('add-to-cart', ['user_id' => $user->id, 'product' => $product->id]) }}" data-id="{{$product->id}}">
                          <input type="hidden" id="quantity" name="quantity" min="1" max="10" value="1">
                          <button class="button-text-1 theme-color ajax_add_to_cart d-flex align-center" type="submit">
                            <i class="fz-20px tio shopping_basket_outlined zero"></i>
                            <i class="fz-20px ni ni-plus-circle first"></i>
                            <i class="fz-20px ni ni-check-circle second"></i>
                          </button>
                        </form>
                    @endif
                   </div>
                  </div>
                  </div>
                </div>
               </div>
              @endforeach
            </div>
        </section>
        @endif
</div>  