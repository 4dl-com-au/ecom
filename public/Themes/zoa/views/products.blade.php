@extends('layouts.app')
@section('content')
  @if (!empty(request()->get('category')) && $ca_banner = \App\Model\Product_Category::where('slug', request()->get('category'))->first())
    <div class="shop-heading text-center bg-image mt-7" data-bg-src="{{ url('media/user/categories/'.$ca_banner->media) }}">
      <div class="bg-dark-02 d-flex flex-column justify-center w-100 h-100">
        <h1>{{ $ca_banner->title }}</h1>
        <p class="font-weight-semi-bold">{{ $ca_banner->description }}</p>
      </div>
    </div>
  @else
     <div class="container pt-8">
       <div class="row">
        <div class="col-6">
          <div>
            <h2>{{ __('Products') }}</h2>
          </div>
        </div>
        <div class="col-6 d-flex justify-end align-center">
          <div class="justify-right">
            <a href="#" data-target="#search-product" class="button smoothscroll justify-center button-lg mt-0 align-items-center theme-btn d-flex w-lg-250px data-box" style="{{ store_colors($uid) }}"><p class="d-none d-lg-block">{{ __('Search') }}</p> <em class="icon ni ni-search d-block d-lg-none"></em></a>
          </div>
        </div>
       </div>
     </div>
  @endif

<div class="px-3 section-body">
	<div class="container">
	  <div class="content">
      <!-- Portfolio section -->
      <div class="section">
        <div class="row">
      		 @foreach($productPaginate as $product)
           <div class="col-md-4">
             @include('include.product-item', ['product' => $product])
           </div>
      		 @endforeach
        </div>
        <div class="d-flex align-center justify-center mt-4">
          @if ($productPaginate instanceof \Illuminate\Pagination\AbstractPaginator)
        	{{ $productPaginate->links() }}
          @endif
        </div>
      </div>
      <!-- end Portfolio section -->
	  </div>
	</div>
</div>
@endsection
