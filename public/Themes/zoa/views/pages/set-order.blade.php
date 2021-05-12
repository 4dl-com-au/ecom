@extends('layouts.app')
@section('content')
<div class="container pt-7 mb-5">
  <div class="nk-block-head-content mb-4 text-center">
    <h3 class="nk-block-title page-title">{{ __('View Products ordered') }}</h3></div>
    <h5 class="text-center">{{ __('Enter order id') }}</h5>
    <form method="get" class="row">
     <div class="form__group col-md-8">
       <label class="">{{ __('ID') }}
       </label>
       <input type="text" class="form-control" name="order_id" required="">
     </div>
     <div class="col-md-4">
	     <div class="h-100 mt-5 mt-md-3 w-100 d-flex align-center">
	       <button class="btn btn-primary btn-lg btn-block">{{ __('Submit') }}</button>
	     </div>
     </div>
    </form>
  </div>
@endsection
