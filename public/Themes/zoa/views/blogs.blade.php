@extends('layouts.app')
@section('content')
<div class="shop-heading text-center d-flex flex-column mb-5 justify-center">
    <h1>{{ __('Blog') }}</h1>
</div>
<div class="blog-list">
    <div class="container">
        <div class="row">
         @foreach (store_blogs($uid, 3) as $item)
            @include('include.blog-item', ['item' => $item])
          @endforeach
        </div>
    </div>
</div>
@endsection
