      <div class="slide v6 pt-6">
            <div class="px-lg-6">
            <div class="js-slider-v4">
              @foreach (store_products($uid, 3) as $key => $item)
                <div class="slide-img">
                    <a href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $item->id]), ['ref' => $user->username]) }}">
                        <img src="{{ getfirstproductimg($item->id) }}" alt="" class="img-responsive">
                    </a>
                    <div class="slide-img-title">
                        <h4>{{ $item->title }}</h4>
                    </div>
                    @if (!empty($item->categories))
                        <div class="tags-label-right">
                        @foreach ($item->categories as $value)
                            @php
                              if($category = \App\Model\Product_Category::where('slug', $value)->first()):
                                echo '<a href="'.route('user-profile-products', ['profile' => $user->username, 'category' => $value]).'">'.$category->title.'</a> ';
                              endif;
                            @endphp
                        @endforeach
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
               <div class="custom">
                    <div class="pagingInfo"></div>
                </div>
            </div>
        </div>

        <!-- Section Rooms -->
        <section class="container mt-5"> 
            <div class="row align-items-end mb-4">
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
        <section class="container"> 
            <div class="row align-items-end mb-4">
                <div class="col-12 col-md-12 col-lg-8">
                    <span class="title title-overhead">{{ __('products') }}</span>
                    <h1 class="title title--h1 js-lines">{{ __('All Products') }}</h1>
                </div>
                <div class="col-12 col-md-12 col-lg-4 text-lg-right d-none d-md-block">
                    <a class="btn" style="{{ store_colors($uid, 'css') }}" href="{{ route('user-profile-products', ['profile' => $user->username]) }}">{{ __('View all Products') }}</a>
                </div>
            </div>

            <!-- Grid -->
            <div class="row">
                @foreach (store_products($uid, 6) as $item)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('include.product-item', ['product' => $item])
                </div>
                @endforeach
            </div>          
        </section>
        <!-- Section About Us -->
        <section id="about" class="container mt-4">
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