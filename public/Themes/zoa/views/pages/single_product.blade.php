@extends('layouts.app')
 @section('content')
 <div class="border-top bg-color--white">
          <div class="row gutter-0">
            <div class="col-prod w-100">
              <div class="flex-col single_product_main_image flex flex-center-y flex-center-x">
                <div class="product-gallery">
                  <figure class="main-product-wrapper">
                      <div class="owl-carousel product-carousel owl-dots-overlay-right w-100">
                        @php
                          $i = 1;
                        @endphp
                        @foreach ($product->media as $image)
                        @php
                          $i++
                        @endphp
                        <div class="product-gallery__image ">
                            <img src="{{ getStorage('media/user/products/', $image) }}" class="img-single-product w-100" alt="" width="1196">
                        </div>
                        @endforeach
                      </div>

                  </figure>

                </div>

              </div>
            </div>
            <div class="col-prod col-main-data">
              <div class="entry-summary mt-7 pt-0">
                <h1 class="entry-title">{{$product->title}}</h1>
                <p class="prod-price"><span class="currency">{!! Currency::symbol($user->gateway['currency'] ?? '') !!}</span>{{ nf(!empty($product->salePrice) ? $product->salePrice : $product->price) }}
                </p>

                <div class="entry-summary__shortdescription product-description">
                  {!! clean($product->description) !!}
                </div>
                <hr>


                <p>{{ __('Stock') }} - {{ nf($product->stock, 0) }}</p>

                @if (!empty($product->sku))
                  <p>{{ __('Sku') }} - {{ $product->sku }}</p>
                @endif

                 @if (!empty($product->extra->shipping))
                   <p class="d-block">{{ __('Shipping locations') }} : {{ str_replace(',', ' , ', $product->extra->shipping ?? '') }}</p>
                 @endif

                 @if ($product->stock_status == 1)

                    @if (!empty($product->external_url))
                      <a href="{{ url("$product->external_url") }}" style="{{ store_colors($uid, 'css') }}" class="rounded-pill btn mb-3 btn-primary btn-block w-100"><em class="fz-20px ni ni-globe mr-2"></em> {{ __('Get it on') }} {{ $product->external_url_name }}</a>
                    @else


                    <form class="product-quantity margin-top-30" id="add-to-cart" data-qty="1" data-route="{{ route('add-to-cart', ['user_id' => $user->id, 'product' => $product->id]) }}" data-product-prices="{{ route('user-get-product-prices') }}" data-id="{{$product->id}}">
                      {!! product_options_html($uid, $product->id) !!}

                    <div class="card-shadow p-3 my-4 product-option-prices d-none">
                      <h4>{{ __('Total') }} - {!! Currency::symbol($user->gateway['currency'] ?? '') !!}<span></span></h4>
                    </div>


                      <div class="d-flex between-center align-center mt-5">
                        <div class="qnt">
                          <input type="number" id="quantity" name="quantity" min="1" max="10" value="1">
                          <a class="dec minus nt-button" href="#">-</a>
                          <a class="inc plus qnt-button" href="#">+</a>
                        </div>
                        <button class="button button-md btn-block button-dark ajax_add_to_cart w-250px d-flex align-center" style="{{ store_colors($uid, 'css') }}" type="submit"><p class="mr-2">{{ __('Add to Cart') }}</p>
                          <i class="fz-20px ni ni-cart zero"></i>
                          <i class="fz-20px ni ni-plus-circle first"></i>
                          <i class="fz-20px ni ni-check-circle second"></i>
                        </button>
                      </div>
                    </form>
                    
                    @endif
                    @else

                    <h5>{{ __('Out of stock') }}</h5>
                 @endif

                 <a href="{{ route('user-profile-product-review', ['profile' => $user->username, 'id' => $product->id]) }}" class="btn btn_sm_primary fs-14px mt-3 mb-3" style="{{ store_colors($uid)  }}">{{ __('REVIEWS') }} ({{$reviews}})</a>
                <div class="product-meta">
                  <span>{{ __('Category') }}: </span> @foreach ($product->categories as $item)
                            @php
                              if($category = \App\Model\Product_Category::where('slug', $item)->first()):
                                echo '<a href="#">'.$category->title.'</a>' . ', ';
                              endif;
                            @endphp
                            @endforeach

                  <div class="single-social justify-content-start mt-4">
                    @foreach (['facebook', 'twitter', 'pinterest', 'whatsapp', 'linkedin'] as $value)
                      <a href="{{ url(share_to_media($value, $product->title)) }}" class="{{ $value }}"><i class="ni ni-{{ ($value == 'facebook') ? 'facebook-f' : $value }}"></i></a>
                    @endforeach
                  </div>
                </div>

              </div>

            </div>

          </div>


      </div>
@stop