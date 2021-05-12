      <div class="slide v6">
                @php
                    $numofProducts = $number_of_products !== 'all' ? $number_of_products : 3;
                @endphp
            <div class="px-lg-6">
            <div class="js-slider-v4">
              @foreach (store_products($uid, $numofProducts) as $key => $item)
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