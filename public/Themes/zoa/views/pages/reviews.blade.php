@extends('layouts.app')

@section('content')
<div class="container pt-8 mb-5">
	<div class="row">
		<div class="col-md-9 mx-auto d-flex align-items-center flex-column">
			<div class="d-flex">
				<h3 class="ml-2">{{$product->title}}</h3>
			</div>
			<hr>
			<div class="mx-auto">
                  @php
                    $rating = number_format($reviews->avg('rating'), 1);
                  @endphp
                  @foreach (range(1,5) as $i)
                  <span class="fa-stack m-0" style="width: 1em;">
                    <i class="far fa-star fa-stack-1x"></i>
                    @if ($rating > 0)
                      @if ($rating > 0.5)
                      <i class="fas fa-star fa-stack-1x"></i>
                      @else
                      <i class="fas fa-star-half fa-stack-1x"></i>
                      @endif
                    @endif
                    @php
                      $rating--;
                    @endphp
                  </span>
                  @endforeach
			</div>

  <div class="container mt-4">
    <div class="card border-0 radius-5 card-shadow p-4">
                            <form action="{{ route('user-profile-product-review-post', ['profile' => $user->username, 'id' => $product->id]) }}" method="post" class="comment-form">
                               @csrf
                               <div class="row mb-3">
                                    <div class="rating left-0 ml-3">
                                      <label class="text-muted d-block"><h5 class="text-muted">{{ __('Your rating') }}</h5></label>
                                        <span><input type="radio" name="rating" id="str5" value="5"><label for="str5"><i class="fad fa-star"></i></label></span>
                                        <span><input type="radio" name="rating" id="str4" value="4"><label for="str4"><i class="fad fa-star"></i></label></span>
                                        <span><input type="radio" name="rating" id="str3" value="3"><label for="str3"><i class="fad fa-star"></i></label></span>
                                        <span><input type="radio" name="rating" id="str2" value="2"><label for="str2"><i class="fad fa-star"></i></label></span>
                                        <span>
                                          <input type="radio" name="rating" id="str1" value="1"><label for="str1"><i class="fad fa-star"></i></label>
                                        </span>
                                    </div>
                               </div>
                               <div class="form__group">
                                   <textarea class="form-control" name="review" placeholder="{{ __('Write a description') }}" style="border-color: {{ store_colors($uid, 'background')  }}"></textarea>
                               </div>
                               <div class="row">
                                  <div class="col-6">
                                     <div class="form__group">
                                        <input class="form-control" placeholder="{{ __('Name') }}" name="name" type="text" style="border-color: {{ store_colors($uid, 'background')  }}">
                                    </div>
                                  </div>
                                  <div class="col-6">
                                    <div class="form__group">
                                        <input class="form-control" placeholder="{{ __('Email') }}" name="email" type="text" style="border-color: {{ store_colors($uid, 'background')  }}">
                                    </div>
                                  </div>
                               </div>
                            <button name="submit" type="submit" id="submit" class="button primary mt-4 btn-block w-100 text-white" style="{{ store_colors($uid, 'css') }}" />{{ __('Add Review') }}</button>
                            </form>
    </div>
  </div>
		</div>
          <div class="col-12 col-md-9 mx-auto form__content mt-5">
             <div class="container-fluid m-auto">
               <ul class="table-reviews row">
                @if (empty($reviews))
                <div class="h-100">
                  <h3>{{ __('Nothing here') }}</h3>
                </div>
                @endif
                @foreach($reviews as $review)
                <div class="col-md-12">
                 <div class="p-4 bdrs-20 card-shadow">
                   <li class="mb-3">
                      <img src="{{ productReviewImage($review->id) }}" class="product_page-img">
                   </li>
                     <span class="position bold text-left title">{{$review->review->name ?? ''}}</span>
                     <br><hr>
                     <span class="position bold text-left card-rating" data-rating="{{$review->rating}}"> </span>
                     <br>
                     <span class="position bold text-left date">{{Carbon\Carbon::parse($review->created_at)->diffForHumans()}}</span>
                     <hr>
                   <p>{{$review->review->review ?? ''}}</p>
                  </div>
                </div>
                  @endforeach
               </ul>
             </div>
          </div>
	</div>
</div>
@endsection
