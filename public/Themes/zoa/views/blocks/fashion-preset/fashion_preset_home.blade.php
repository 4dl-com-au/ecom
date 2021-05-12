@section('customCSS')

     <link href="{{ themes('css/fashion-preset-1.css?v=' . env('APP_VERSION')) }}" rel="stylesheet">
@stop

<div class="fashion-preset-1">
                <!-- BEGIN FIRST SCREEN -->
            <div class="first-screen">
                <div class="first-screen__center">
                    <div class="main-slider">
                        <div class="main-slider__list-wrap">
                            <div class="main-slider__list js-main-slider loaded">
                                @foreach (store_products($uid, 1) as $items)
                                <div class="main-slider__item active">
                                    <div class="main-slider__max">
                                        <div class="main-slider__row">
                                            <div class="main-slider__cell">
                                                <div class="main-slider__content">
                                                    <h2 class="main-slider__title mb-0">{{ $items->title }}</h2>
                                                    <a class="btn btn_sm_primary" style="{{ store_colors($uid) }}" href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $items->id]), ['ref' => $user->username]) }}">
                                                        <span>{{ __('Shop now') }}</span>
                                                    </a>
                                                </div>
                                                <div class="main-slider__image-wrap">
                                                    <div class="main-slider__image" data-bg-src="{{ getfirstproductimg($items->id) }}"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (!empty(user_top_products($uid, 0, 3)))
            <!-- FIRST SCREEN END -->
            <section class="collection-block container">
                <div class="collection-block__content">
                    <div class="collections">

                        <div class="collections__top">
                            <div class="collections__max">
                                <h3 class="collection-title">
                                    <span class="collection-title__count">{{ count(store_products($uid)) }}</span>
                                    <span class="collection-title__plus">+</span>
                                    <span class="collection-title__text">{{ __('Products for you') }}</span>
                                </h3>
                            </div>
                        </div>
                        @php
                            $i=1;
                        @endphp

                    @foreach (user_top_products($uid, 0, 3) as $items)
                        <article class="collection collection_{{$i++}}">
                            <div class="collection__all">
                                <div class="collection__mob-image">
                                    <a class="collection__image" data-bg-src="{{ getfirstproductimg($items->id) }}" href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $items->id]), ['ref' => $user->username]) }}"></a>
                                </div>
                                <div class="collection__row">
                                    <div class="collection__cell">
                                        <div class="collection__content">
                                            <span class="collection__subtitle category-subtitle"></span>
                                            <h4 class="collection__title">{{ $items->title }}</h4>
                                            <a class="collection__more read-more" href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $items->id]), ['ref' => $user->username]) }}">{{ __('Shop now') }}</a>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </article>
                    @endforeach
                    </div>                                 
                </div>
            </section>
            @endif
        <section class="container section pt-md-0"> 
            <div class="row align-items-end mb-4 px-4 px-md-0">
                <div class="col-12 col-md-12 col-lg-8">
                    <span class="title title-overhead">{{ __('categories') }}</span>
                    <h1 class="title title--h1 js-lines">{{ __('Categories') }}</h1>
                </div>
                <div class="col-12 col-md-12 col-lg-4 text-lg-right d-none d-md-block">
                    <a class="btn" style="{{ store_colors($uid, 'css') }}" href="{{ route('user-profile-categories', ['profile' => $user->username]) }}">{{ __('View all categories') }}</a>
                </div>
            </div>
            <!-- Product Categories -->
            <div class="banner container mb-5">
                <div class="row col-spacing-10">
                    @foreach (store_categories($uid, 3) as $items)
                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="banner-img">
                            <a href="{{ route('user-profile-products', ['profile' => $user->username, 'category' => $items->slug]) }}" class="effect-img3 plus-zoom">
                                <img src="{{ getcategoryImage($items->id) }}" alt="">
                            </a>
                            <div class="box-center content3">
                                <a href="" hidden>{{ p_category($uid, 'count', $items->slug) . __(' Products') }}</a>
                                <a href="{{ route('user-profile-products', ['profile' => $user->username, 'category' => $items->slug]) }}">{{$items->title}}</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div><!-- end row -->
            </div><!-- end container-fluid -->      
        </section>

            <div class="product-tab d-block js-tabs-content container-fluid">
                <div class="container">
                 <div class="row align-items-end mb-4">
                        <div class="col-12 col-md-12 col-lg-8">
                            <span class="title title-overhead">{{ __('products') }}</span>
                            <h1 class="title title--h1 js-lines">{{ __('All Products') }}</h1>
                        </div>
                        <div class="col-12 col-md-12 col-lg-4 text-lg-right d-none d-md-block">
                            <a class="btn" style="{{ store_colors($uid, 'css') }}" href="{{ route('user-profile-products', ['profile' => $user->username]) }}">{{ __('View all Products') }}</a>
                        </div>
                    </div>
                </div>
                    <div class="main-catalog">
                        <div class="row">
                          @foreach (store_products($uid, 3) as $key => $product)
                            <article class="short-item col-md-3">
                                <div class="short-item__all">
                                    <a class="short-item__image-bg" href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $product->id]), ['ref' => $user->username]) }}">
                                        <img class="short-item__image" src="{{ getfirstproductimg($product->id) }}" alt="">
                                    </a>
                                    <h4 class="short-item__title mb-0">
                                        <a class="short-item__link" href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $product->id]), ['ref' => $user->username]) }}">{{ $product->title }}</a>
                                    </h4>
                                    <span class="short-item__price">{!! Currency::symbol($user->gateway['currency'] ?? '') !!}{{ nf(!empty($product->salePrice) ? $product->salePrice : $product->price) }}</span>
                                </div>
                            </article>
                            @endforeach                 
                        </div>
                    </div>
                </div>
        <!-- Section About Us -->
        <section id="about" class="container section mt-4">
            <div class="row">
                <div class="col-12 col-lg-5">
                    <span class="title title--overhead js-lines">{{ __('Our story') }}</span>
                    <h1 class="title title--h1 js-lines">{{ $about_title }}</h1>
                </div>
                <div class="col-12 col-lg-6 offset-lg-1 offset-top">
                    <p class="paragraph js-scroll-show">{{ $short_about }}</p>
                </div>
            </div>
        </section>
</div>