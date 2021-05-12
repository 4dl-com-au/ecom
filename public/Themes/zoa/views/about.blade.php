@extends('layouts.app')

@section('content')
<div class="section-body pt-0 px-3">
    <div class="row pt-8">
      <div class="col-lg-10 mx-auto text-center wow fadeInDown" data-wow-delay="0.1s">
        <h2>{{ __('About') }}</h2>
      </div>
    </div>
 <div class="container">
  <div class="section">
        <div class="container">
          <div class="row align-items-center col-spacing-50">
            <div class="col-12">
              {!! clean(user('extra.about', $uid)) !!}
            </div>
          </div><!-- end row -->
        </div><!-- end container -->
      </div>
   </div>
</div>
@endsection
