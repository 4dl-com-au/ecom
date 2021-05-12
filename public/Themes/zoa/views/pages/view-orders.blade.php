@extends('layouts.app')

@section('content')
<div class="container mb-5 pt-8">
  <div class="nk-block-head-content mb-3">
    <h5>{{ __('Hey') }}, {{ $order->details->first_name ?? '' }}</h5>
    <hr>
    <p>{{ __('Order ref') }} - {{ $order->ref }} <hr> {{ __('Ordered at') }} - {{Carbon\Carbon::parse($order->created_at)->toFormattedDateString()}} <hr> {{ __('Price spent') }} - {{ nf($order->price) }} <hr> {{ count($products) }} {{ __('Products ordered') }} <hr> {{ __('Order status') }} - {{ $order->delivered == 1 ? 
    __('Completed') : __('Pending') }}</p>
    <hr>
    <h3 class="nk-block-title page-title">{{ __('Products ordered') }}</small></h3>
  </div>
  <div class="row">
    @foreach ($products as $key => $items)
    <div class="col-md-4">
        <div class="product-box">
          <div class="product-img">
              <a class="h-100">
                <img src="{{ getfirstproductimg($key) }}" alt=" " class="h-100 w-100">
              </a>
                <div class="product-badge-right">
              <span class="font-small uppercase font-family-secondary font-weight-medium">QTY - {{$items['qty']}}</span>
            </div>
          </div>
          <div class="product-title">
            <h6 class="font-weight-medium"><a>{{$items['name']}}</a></h6>
            <p>{{ nf($items['price']) }}</p>
            <p>{{ $items['options'] ?? '' }}</p>
            @if ($items['downloadables'])
              <a class="btn btn-link mt-3 pl-0" href="{{ url('media/user/downloadables/'.$items['downloadables']) }}" download>{{ __('Download File') }}</a>
            @endif
          </div>
        </div>
    </div>
    @endforeach
  </div>
</div>
@endsection
