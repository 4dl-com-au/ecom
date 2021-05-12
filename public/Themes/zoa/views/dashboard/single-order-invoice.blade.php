@extends('layouts.app')
 @section('content')
 <div class="container">
  <div class="row mt-7">
    <div class="col-md-3">
        @include('dashboard.dashboard-head')
    </div>
    <div class="col-md-9">
                        <div class="invoice-wrapper">
                            <div class="invoice-header px-3">
                                <div class="left">
                                    <h3>{{ __('Invoice') }} {{ $order->ref }}</h3>
                                </div>
                                <div class="right">
                                    <div class="controls">
                                        <a href="Javascript::void" class="action" onclick="window.print()">
                                            <i class="tio print fs-19px"></i>
                                        </a>


                                        <a href="Javascript::void" class="action html-to-canvas-download" data-html=".invoice-body" data-name="invoice-{{ $order->ref }}">
                                            <i class="tio download_from_cloud fs-19px"></i>
                                        </a>

                                        <a href="{{ route('user-single-order', ['id' => $order->id]) }}" class="action">
                                            <i class="tio arrow_backward fs-19px"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="invoice-body card border-0 radius-5">
                                <div class="invoice-card">
                                    <div class="invoice-section is-flex is-bordered">
                                        <div class="h-avatar is-large">
                                            <img class="ob-cover w-68px h-68px radius-100" src="{{ avatar($order->storeuser) }}" alt=" " data-user-popover="6">
                                        </div>
                                        <div class="meta">
                                            <h3 class="mb-2">{{ full_name($user->id) }}</h3>
                                            <span>{{ user('email', $uid) }}</span>
                                            <span>{{ user('address', $uid) }}</span>
                                        </div>
                                        <div class="end">
                                            <h3 class="mb-2">{{ __('Invoice') }} {{ $order->ref }}</h3>
                                            <span>{{ __('Issued on') }}: {{ \Carbon\Carbon::parse($order->created_at)->toFormattedDateString() }}</span>
                                            <span>{{ __('By') }}: {{ $order->details->{'first_name'} ?? '' }} {{ $order->details->last_name ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="invoice-section is-flex is-bordered">
                                      @if (!empty($customer))
                                        <a href="{{ route('user-customer', ['id' => $customer->id]) }}" class="h-avatar is-customer is-large">
                                            <img class="ob-cover w-68px h-68px radius-100" src="{{c_avatar($customer->id)}}" alt=" ">
                                        </a>
                                        @endif
                                        <div class="meta">
                                            <h3 class="mb-2">{{ $order->details->{'first_name'} ?? '' }} {{ $order->details->last_name ?? '' }}</h3>

                                            <span>{{ $order->details->email ?? '' }}</span>
                                            <span>{{ $order->details->country ?? '' }} - {{ $order->details->address ?? '' }}</span>
                                        </div>
                                        <div class="end is-left">
                                            <h3 class="mb-2">{{ __('Status') }}</h3>
                                            <p>{{ $order->order_status == 1 ? __('Delivered') : '' }} {{ $order->order_status == 2 ? __('Pending') : '' }} {{ $order->order_status == 3 ? __('Canceled') : '' }}</p>
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
                                                  @foreach ($products as $key => $items)

                                                  @php
                                                    $qty = $qty + $items['qty'];
                                                    $prices = $prices + $items['price'];
                                                    $total_price = ($total_price + $items['price'] * $items['qty']); 
                                                  @endphp
                                                     <div class="flex-table-item">
                                                         <div class="flex-table-cell is-media" data-th="">
                                                             <div class="h-avatar is-medium">
                                                                 <img class="avatar is-squared h-40px object-cover" src="{{ getfirstproductimg($key) }}" alt=" ">
                                                             </div>
                                                             <div>
                                                                 <span class="item-name dark-inverted fs-11px">{{$items['name']}}</span>
                                                             </div>
                                                         </div>
                                                         <div class="flex-table-cell" data-th="{{ __('Quantity') }}">
                                                             <span class="light-text fs-11px">{{$items['qty']}}</span>
                                                         </div>
                                                         <div class="flex-table-cell" data-th="{{ __('Price') }}">
                                                             <span class="tag fs-11px">{{nf($items['price'])}}</span>
                                                         </div>
                                                         <div class="flex-table-cell" data-th="{{ __('Options') }}">
                                                             <span class="tag fs-11px">{{$items['options']}}</span>
                                                         </div>
                                                         <div class="flex-table-cell" data-th="{{ __('Total') }}">
                                                             <span class="tag fs-11px">{{ nf(($items['price'] * $items['qty'])) }}</span>
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
                                                    <span class="table-label">{{ __('Subtotal') }}</span>
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
                                                    <span class="table-label">{{ __('Total') }}</span>
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
                    </div>
                </div>
            </div>
  @stop