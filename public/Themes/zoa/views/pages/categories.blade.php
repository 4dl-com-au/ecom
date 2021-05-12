@extends('layouts.app')
@section('content')

<div class="shop-heading text-center d-flex flex-column justify-center mb-5">
    <h1>{{ __('Categories') }}</h1>
</div>
@if (count(store_categories($uid, 3)) > 0)
	<!-- Product Categories -->
	<div class="banner container mb-5">
		<div class="row col-spacing-10">
			@foreach (store_categories($uid, 5, true) as $items)
             <div class="col-md-4 col-sm-4 col-xs-12">
                 <div class="banner-img">
                     <a href="{{ route('user-profile-products', ['profile' => $user->username, 'category' => $items->slug]) }}" class="effect-img3 plus-zoom">
                         <img src="{{ url('media/user/categories/'.$items->media) }}" alt="">
                     </a>
                     <div class="box-center content3">
                         <a href="{{ route('user-profile-products', ['profile' => $user->username, 'category' => $items->slug]) }}">{{$items->title}}</a>
                        <p class="text-white mt-3">{{ p_category($uid, 'count', $items->slug) . __(' Products') }}</p>
                     </div>
                 </div>
             </div>
			@endforeach
		</div><!-- end row -->
        {{ store_categories($uid, 5, true)->links() }}
	</div><!-- end container-fluid -->
@endif
@endsection
