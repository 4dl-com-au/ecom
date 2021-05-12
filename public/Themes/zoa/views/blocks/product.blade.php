        <!-- Section Rooms -->
        <section class="container mt-7"> 
            <div class="row align-items-end mb-4">
                <div class="col-12 col-md-12 col-lg-8">
                    <span class="title title-overhead">{{ __('products') }}</span>
                    <h1 class="title title--h1 js-lines">{{ __('All Products') }}</h1>
                </div>
                <div class="col-12 col-md-12 col-lg-4 text-lg-right d-none d-md-block">

                    @if ($show_search == 1)
                        <a href="#" data-target="#search-product" class="btn data-box" style="{{ store_colors($uid, 'css') }}"><p class="d-none d-lg-block">{{ __('Search') }}</p> <em class="icon ni ni-search d-block d-lg-none"></em></a>

                        @else
                    <a class="btn" style="{{ store_colors($uid, 'css') }}" href="{{ route('user-profile-products', ['profile' => $user->username]) }}">{{ __('View all Products') }}</a>
                    @endif
                </div>
            </div>

            <!-- Grid -->
            <div class="row">
                @php
                    $numofProducts = (int) $number_of_products;
                @endphp
                @foreach (store_products($uid, $numofProducts) as $item)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('include.product-item', ['product' => $item])
                </div>
                @endforeach
            </div>          
        </section>