@extends('layouts.app')
 @section('content')
 <div class="container">
  <div class="row mt-7">
    <div class="col-md-3">
        @include('dashboard.dashboard-head')
    </div>
    <div class="col-md-9">

<div class="row mt-7">
  <div class="col-md-4">
    <div class="quick-stat bg-white card-shadow p-3 radius-10 text-white">
      <div class="media-flex-center">
            <div class="flex-meta">
                <div class="title">
                  <i class="tio dollar_outlined text-muted-2 fs-45px"></i>
                  <span class="text-muted-2 fs-18px">{{ __('Payment') }}</span>
                  <span class="d-block text-muted-2">{{ $order->gateway }}</span>
                </div>
            </div>

            <div class="flex-end">
              @if (user('extra.invoicing', $uid))
              <a href="{{ route('user-customer-single-order', ['profile' => $user->username, 'id' => $order->id, 'type' => 'invoice']) }}" class="fs-13px text-muted-2">{{ __('View invoice') }}</a>
              @endif
            </div>
        </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="quick-stat bg-white card-shadow p-3 radius-10 text-white">
      <div class="media-flex-center">
            <div class="flex-meta">
                <div class="title">
                  <i class="tio premium_outlined text-muted-2 fs-45px"></i>
                  <span class="text-muted-2 fs-18px">{{ __('Shipping') }}</span>
                  <span class="d-block c-black">{{ $order->details->{'country'} ?? '' }}</span>
                </div>
                <span class="fs-40px"></span>
            </div>

            <div class="flex-end">
              @if (user('extra.invoicing', $uid))
              <a href="{{ route('user-customer-single-order', ['profile' => $user->username, 'id' => $order->id, 'type' => 'invoice']) }}" class="fs-13px text-muted-2">{{ __('View invoice') }}</a>
              @endif
            </div>
        </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="quick-stat bg-white card-shadow p-3 radius-10 text-white">
      <div class="media-flex-center">
            <div class="flex-meta">
                <div class="title">
                  <i class="tio top_security_outlined text-muted-2 fs-45px"></i>
                  <span class="text-muted-2 fs-18px">{{ __('Status') }}</span>
                  <span class="d-block c-black">{{ $order->order_status == 1 ? __('Delivered') : '' }} {{ $order->order_status == 2 ? __('Pending') : '' }} {{ $order->order_status == 3 ? __('Canceled') : '' }}</span>
                </div>
                <span class="fs-40px"></span>
            </div>

            <div class="flex-end">
              @if (user('extra.invoicing', $uid))
              <a href="{{ route('user-customer-single-order', ['profile' => $user->username, 'id' => $order->id, 'type' => 'invoice']) }}" class="fs-13px text-muted-2">{{ __('View invoice') }}</a>
              @endif
            </div>
        </div>
    </div>
  </div>

  <div class="col-md-7">
        <div class="mt-5">
          <p class="m-0 fs-20px">{{ __('Products') }}</p>
        </div>
         <div class="flex-table mt-4">
             <!--Table header-->
             <div class="flex-table-header">
                 <span class="grow-4">{{ __('Product') }}</span>
                 <span>{{ __('Quantity') }}</span>
                 <span>{{ __('Price') }}</span>
                 <span>{{ __('Options') }}</span>
                 <span>{{ __('Total') }}</span>
             </div>
              @foreach ($products as $key => $items)
                 <div class="flex-table-item {{ $items['downloadables'] ? 'mb-0' : '' }}">
                     <div class="flex-table-cell is-media grow-4" data-th="">
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
                @if ($items['downloadables'])
                  <a class="text-sticker ml-auto fs-13px" style="{{ store_colors($uid) }}" href="{{ getStorage('media/user/downloadables/', $items['downloadables']) }}" download>{{ __('Download File') }}</a>
                  <div class="mb-4"></div>
                @endif
          @endforeach
      </div>
  </div>

  <div class="col-md-5">
    <div class="p-3 border-0 card-shadow card mt-5">
                <!-- Title -->
                <div class="card-header border-0">
                    <h3 class="fs-20px m-0">{{ __('Billing address') }}</h3>
                </div>
                <!-- Billing Address -->
                <div class="card-body">
                        <div class="row">
                         @foreach ($order->details as $key => $value)
                            <div class="info-block col-6 mb-4">
                                <span class="label-text d-block text-muted">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                <span class="label-value d-block">{{ $value ?? '' }}</span>
                            </div>
                          @endforeach
                        </div>

                        @if (user('extra.refund_request', $uid))
                          <div class="card-header border-0 mb-4 mt-4">
                              <h3 class="fs-20px m-0">{{ __('Refund') }}</h3>
                          </div>
                          @if (!empty($refund) && $refund->status == 0)
                            <h6>{{ __('Your refund request is in review') }}</h6>
                          @endif
                          @if (!empty($refund) && $refund->status == 2)
                            <h6>{{ __('Your refund request was canceled') }}</h6>
                          @endif
                          @if (!empty($refund) && $refund->status == 1)
                            <h6>{{ __('Your refund has been given') }}</h6>
                          @endif

                          @if (empty($refund))
                          <div class="order-details-header pt-0">
                            <div class="left">
                              <form action="{{ route('user-request-refund', ['profile' => $user->username]) }}" method="post">
                                @csrf

                                <input type="hidden" value="{{ $order->id }}" name="order_id">
                                
                                <div class="order-details-header">
                                  <div class="left">
                                    <button class="btn btn_sm_primary" style="{{ store_colors($uid)  }}">{{ __('Request Refund') }}</button>
                                  </div>
                                </div>
                              </form>
                            </div>
                          </div>
                          @endif
                        @endif
                </div>
                <!-- /Address Form -->
            </div>
  </div>
</div>
</div>
</div>
</div>
  @stop