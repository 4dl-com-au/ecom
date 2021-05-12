@extends('layouts.app')
@section('content')
<div class="pt-8 px-3 mb-4 container">

  <!-- Hero section -->
  <div class="section-3xl bg-image parallax-bg bdrs-20" data-bg-src="{{ (!empty($blog->image) && file_exists(public_path('media/user/blog/' . $blog->image)) ? url('media/user/blog/' . $blog->image) : $blog->settings->media_url ?? '') }}">
    <div class="bg-dark-02 bdrs-20">
      <div class="container py-8 text-center">
        <div class="d-inline-block bg-dark-09 padding-x-50 padding-y-30">
          <h4 class="uppercase letter-spacing-3 margin-0">{!!clean($blog_title)!!}</h4>
        </div>
      </div>
    </div>
  </div>
  <!-- end Hero section -->
  <div class="section pt-2">
    <div class="container">
      <div class="margin-top-40">
        <div class="col-12">
          {!! clean($blog_note) !!}
        </div>
      </div><!-- end row -->
    </div><!-- end container -->
   </div>
</div>
@endsection
