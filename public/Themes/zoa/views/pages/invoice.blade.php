@extends('layouts.app')
@section('content')
                    <div class="invoice-wrapper bg-light mt-8">
                            <div class="invoice-header p-3">
                                <div class="left">
                                    <h3>{{ __('New Invoice') }}</h3>
                                </div>
                                <div class="right">
                                    <div class="controls">
                                        <a href="Javascript::void" class="action" onclick="window.print()">
                                            <i class="tio print fs-19px"></i>
                                        </a>


                                        <a href="Javascript::void" class="action html-to-canvas-download" data-html=".invoice-body" data-name="store-invoice-{{ user('username', $uid) }}">
                                            <i class="tio download_from_cloud fs-19px"></i>
                                        </a>

                                        <a href="{{ route('user-profile', ['profile' => $user->username]) }}" class="action">
                                            <i class="tio arrow_backward fs-19px"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="invoice-body bg-light card border-0 radius-5">
                                <div class="invoice-card">
                                    <div class="invoice-section is-flex is-bordered">
                                        <div class="h-avatar is-large">
                                            <img class="ob-cover w-68px h-68px radius-100" src="{{ avatar($uid) }}" alt=" " data-user-popover="6">
                                        </div>
                                        <div class="meta">
                                            <h3 class="m-0">{{ full_name($uid) }}</h3>
                                            <span>{{ user('email', $uid) }}</span>
                                            <span>{{ user('address', $uid) }}</span>
                                        </div>
                                        <div class="end">
                                            <h3>{{ __('New invoice') }}</h3>
                                            <span>{{ __('Issued on') }}: {{ Carbon\Carbon::now()->toFormattedDateString() }}</span>
                                            <span>{{ __('By') }}: {{ $details->{'first_name'} ?? '' }} {{ $details->last_name ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="invoice-section is-flex is-bordered">
                                      @if (!empty($customer))
                                        <div class="h-avatar is-customer is-large">
                                            <img class="ob-cover w-68px h-68px radius-100" src="{{c_avatar($customer->id)}}" alt=" ">
                                        </div>
                                        @endif
                                        <div class="meta">
                                            <h3 class="mb-2">{{ $details->first_name ?? '' }} {{ $details->last_name ?? '' }}</h3>

                                            <span>{{ $details->email ?? '' }}</span>
                                            <span>{{ $details->country ?? '' }} - {{ $details->address ?? '' }}</span>
                                        </div>
                                        <div class="end is-left">
                                            <h3 class="mb-2">{{ __('Status') }}</h3>
                                            <p>{{ __('Unpaid') }}</p>


                                            <form action="{{ route('user-profile-checkout', ['profile' => $user->username, 'proceed' => true]) }}" method="post">
                                                @csrf

                                                @foreach ($details as $key => $value)
                                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">

                                                @endforeach

                                                <button class="btn bg-blue mt-3 effect-letter">{{ __('Proceed') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="invoice-section">
                                             <div class="flex-table">
                                                 <!--Table header-->
                                                 <div class="flex-table-header">
                                                     <span>{{ __('Product') }}</span>
                                                     <span>{{ __('Quantity') }}</span>
                                                     <span>{{ __('Price') }}</span>
                                                     <span>{{ __('Options') }}</span>
                                                     <span>{{ __('Total') }}</span>
                                                 </div>
                                                 @php
                                                   $prices = 0;
                                                   $qty = 0;
                                                   $total_price = 0;
                                                 @endphp
                                                  @foreach ($cart as $key => $item)

                                                  @php
                                                    $price = !empty($item->salePrice) ? ($item->quantity * $item->associatedModel->salePrice) : ($item->quantity * $item->price);
                                                    $qty = $qty + $item->quantity;
                                                    $prices = $prices + $price;
                                                    $total_price = ($total_price + $price * $item->quantity); 
                                                  @endphp
                                                     <div class="flex-table-item">
                                                         <div class="flex-table-cell is-media" data-th="">
                                                             <div class="h-avatar is-medium">
                                                                 <img class="avatar is-squared h-40px object-cover" src="{{ getfirstproductimg($item->associatedModel->id) }}" alt=" ">
                                                             </div>
                                                             <div>
                                                                 <span class="item-name dark-inverted fs-11px">{{$item->associatedModel->title}}</span>
                                                             </div>
                                                         </div>
                                                         <div class="flex-table-cell" data-th="{{ __('Quantity') }}">
                                                             <span class="light-text fs-11px">{{ $item->quantity }}</span>
                                                         </div>
                                                         <div class="flex-table-cell" data-th="{{ __('Price') }}">
                                                             <span class="tag fs-11px">{{ nf($price) }}</span>
                                                         </div>
                                                         <div class="flex-table-cell" data-th="{{ __('Options') }}">
                                                             <span class="tag fs-11px">{!! \App\Cart::getOptionsAttr($item->attributes->options) !!}</span>
                                                         </div>
                                                         <div class="flex-table-cell" data-th="{{ __('Total') }}">
                                                             <span class="tag fs-11px">{{ nf($price * $item->quantity, 2) }}</span>
                                                         </div>
                                                     </div>
                                              @endforeach
                                          </div>
                                        <div class="flex-table sub-table">
                                            <!--Table item-->
                                            <div class="flex-table-item shadow-none">
                                                <div class="flex-table-cell is-grow is-vhidden" data-th=""></div>
                                                <div class="flex-table-cell cell-end is-vhidden" data-th=""></div>
                                                <div class="flex-table-cell is-vhidden" data-th=""></div>


                                                <div class="flex-table-cell" data-th="">
                                                    <span class="table-label">{{ __('Quantity') }}</span>
                                                </div>
                                                <div class="flex-table-cell has-text-right" data-th="">
                                                    <span class="table-total dark-inverted">{{ number_format($qty) }}</span>
                                                </div>
                                            </div>

                                            <!--Table item-->
                                            <div class="flex-table-item shadow-none">
                                                <div class="flex-table-cell is-grow is-vhidden" data-th=""></div>
                                                <div class="flex-table-cell cell-end is-vhidden" data-th=""></div>
                                                <div class="flex-table-cell is-vhidden" data-th=""></div>


                                                <div class="flex-table-cell" data-th="">
                                                    <span class="table-label">Subtotal</span>
                                                </div>
                                                <div class="flex-table-cell has-text-right" data-th="">
                                                    <span class="table-total dark-inverted">{!! Currency::symbol(user('gateway.currency', $uid)) !!}{{ nf($prices) }}</span>
                                                </div>
                                            </div>
                                            <!--Table item-->
                                            <div class="flex-table-item shadow-none">
                                                <div class="flex-table-cell is-grow is-vhidden" data-th=""></div>
                                                <div class="flex-table-cell cell-end is-vhidden" data-th=""></div>
                                                <div class="flex-table-cell is-vhidden" data-th=""></div>


                                                <div class="flex-table-cell" data-th="">
                                                    <span class="table-label">Total</span>
                                                </div>
                                                <div class="flex-table-cell has-text-right" data-th="">
                                                    <span class="table-total is-bigger dark-inverted">{!! Currency::symbol(user('gateway.currency', $uid)) !!}{{ nf($total_price) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                </div>
                            </div>
                        </div>
@endsection
