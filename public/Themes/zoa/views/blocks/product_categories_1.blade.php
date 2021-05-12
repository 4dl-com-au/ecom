        <!-- Section Rooms -->
        <section class="container mt-7"> 
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
                @php
                    $numofProducts = (int) $number_of_products;
                @endphp
                    @foreach (store_categories($uid, $numofProducts) as $items)
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