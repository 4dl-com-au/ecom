@extends('layouts.app')
 @section('content')
 <div class="container">
  <div class="row mt-7">
    <div class="col-md-3">
        @include('dashboard.dashboard-head')
    </div>
    <div class="col-md-9">
          <div class="flex-table">
            <!--Table header-->
            <div class="flex-table-header">
                <span>{{ __('Ref') }}</span>
                <span>{{ __('Gateway') }}</span>
                <span>{{ __('Date') }}</span>
                <span>{{ __('Status') }}</span>
                <span>{{ __('Name') }}</span>
                <span>{{ __('Purchased') }}</span>
                <span class="cell-end">
                    
                </span>
            </div>

            @foreach($all_orders as $order)
            <!--Table item-->
            <div class="flex-table-item">
                <div class="flex-table-cell" data-th="{{ __('Reference') }}">
                    <span class="light-text">{{ $order->ref }}</span>
                </div>
                <div class="flex-table-cell" data-th="{{ __('Gateway') }}">
                    <span class="light-text">{{ $order->gateway }}</span>
                </div>
                <div class="flex-table-cell" data-th="{{ __('Date') }}">
                    <span class="dark-inverted is-weight-600">{{ \Carbon\Carbon::parse($order->created_at)->toFormattedDateString() }}</span>
                </div>
                <div class="flex-table-cell" data-th="{{ __('Status') }}">
                    <span class="tag is-green is-rounded">{{ $order->order_status == 1 ? __('Delivered') : '' }} {{ $order->order_status == 2 ? __('Pending') : '' }} {{ $order->order_status == 3 ? __('Canceled') : '' }}</span>
                </div>
                <div class="flex-table-cell" data-th="{{ __('Name') }}">
                    <a class="action-link is-pushed-mobile">{{ $order->details->first_name . ' ' . $order->details->last_name ?? ''  }}</a>
                </div>
                <div class="flex-table-cell" data-th="{{ __('Purchased') }}">
                    <a class="action-link is-pushed-mobile">{{ count($order->products) .' @ '. clean(Currency::symbol(user('gateway.currency', $uid)), 'titles') .' '. number_format($order->price) }}</a>
                </div>
                <div class="flex-table-cell cell-end" data-th="Actions">
                  <a href="{{ route('user-customer-single-order', ['profile' => $user->username, 'id' => $order->id]) }}" class="btn btn_sm_primary bg-blue effect-letter c-white"><em class="icon ni ni-eye"></em><span>{{ __('Order Details') }}</span></a>
                </div>
            </div>
            @endforeach
         </div>
    </div>
  </div>
 </div>
@stop