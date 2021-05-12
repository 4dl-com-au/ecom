@extends('layouts.app')
@section('content')
<div class="wrapper-success container mt-8">
        <div class="logo text-center">
            <a href="#">
              <img src="{{ url('media/flame-809.png') }}" alt="" class="img-reponsive h-200px w-200px">
            </a>
        </div>

        @php
          $store_order = \Illuminate\Support\Facades\DB::table('store_orders')->where('slug', request()->get('order_id'))->first();
        @endphp
        <div class="content pt-3 d-flex align-items-center flex-column">
            <div class="title-header">
                <h1>{{ __('Order successful') }}</h1>
            </div>
            <p>{{ __('Thanks for purchasing our prodcut(s). Kindly check your mail to view orders / access downloadable items or use the form below') }}</p>

            <form method="GET" action="{{ route('user-profile-orders', $user->username) }}" class="row mt-4 w-100">
              <div class="col-md-8">
                <p class="form__group">
                  <input type="text" placeholder="{{ __('Order Id') }}" class="form-control" name="order_id" required="" value="{{ request()->get('order_id') }}">
                </p>
              </div>
              <div class="col-md-4">
                @if (auth_user($uid, 'check') && !empty($store_order->order_id))
                <a href="{{ route('user-customer-single-order', ['profile' => $user->username, 'id' => $store_order->order_id]) }}" class="button button-lg text-center align-items-center btn-block d-flex" style="{{ store_colors($uid) }}">{{ __('View') }}</a>
                @else
                <button class="button button-lg text-center align-items-center btn-block d-flex" style="{{ store_colors($uid) }}">{{ __('View') }}</button>
                @endif
              </div>
            </form>

            <a href="{{ route('user-profile-orders', $user->username) }}" class="button button-lg mt-3 text-center align-items-center theme-btn d-flex w-250px">{{ __('Orders') }} <em class="ni ni-arrow-right-circle fs-17px ml-1"></em></a>
        </div>
    </div>
@endsection
