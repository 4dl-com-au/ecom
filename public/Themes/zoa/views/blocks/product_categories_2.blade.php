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
                    <div class="col-12 col-sm-6 col-md-3">
                        <a href="{{ route('user-profile-products', ['profile' => $user->username, 'category' => $items->slug]) }}" class="ob-cover fancy-box-c">
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