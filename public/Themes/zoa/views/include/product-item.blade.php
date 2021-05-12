                            

                <div class="">
                    <!-- ItemRoom -->
                    <div class="zoa-products mb-0">
                        <figure class="item-wrap">
                            <a class="item-link" href="{{ Linker::url(route('user-profile-single-product', ['profile' => $user->username, 'id' => $product->id]), ['ref' => $user->username]) }}">
                                <img class="cover" src="{{ getfirstproductimg($product->id) }}" alt=" " />
                            </a>
                        </figure>
                        <div class="item-details">
                            <h4 class="item-name">{{ $product->title }}</h4>
                            <div class="item-price">{!! Currency::symbol($user->gateway['currency'] ?? '') !!}{{ nf(!empty($product->salePrice) ? $product->salePrice : $product->price) }}<span>
                                      <form class="product-quantity d-inline-flex mt-3 mt-lg-0" id="add-to-cart" data-qty="1" data-route="{{ route('add-to-cart', ['user_id' => $user->id, 'product' => $product->id]) }}" data-id="{{$product->id}}">
                                          <input type="hidden" id="quantity" name="quantity" min="1" max="10" value="1">
                                          <button style="border-color: {{ store_colors($uid, 'background')  }}; color: {{ store_colors($uid, 'background')  }}" class="button-producto ajax_add_to_cart d-flex align-center" type="submit">
                                            <i class="fz-20px tio shopping_cart_add zero"></i>
                                            <i class="fz-20px ni ni-plus-circle first"></i>
                                            <i class="fz-20px ni ni-check-circle second"></i>
                                          </button>
                                        </form>
                                      </span>
                                    </div>
                        </div>
                    </div>
                </div>