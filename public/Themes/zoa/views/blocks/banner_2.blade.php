
      <div class="section-2xl pb-0 bg-image banner" data-bg-src="{{ $banner }}">
        <div class="{{ !empty($banner) ? 'bg-dark-05' : '' }}">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-12 col-lg-3">
              <div class="d-inline-block margin-lg-right-40">
                  <img class="avatar-2xl theme-border" src="{{ avatar($uid) }}" alt="">
                </div>
            </div>
            <div class="col-12 col-lg-9">
              <h4 class="theme-name py-2 px-5 fs-17px" style="{{ store_colors($uid) }}">{{full_name($uid)}}</h4>
              <h4 class="font-weight-bold line-height-140 margin-0 mb-2 tagline mt-1">{{ $tagline }}</h4>
              <p class="header-about">{{ Str::limit(clean($short_about, 'clean_all'), $limit = 250, $end = '...') }}</p>
              <a style="{{ store_colors($uid) }}" class="button smoothscroll button-lg mt-3 align-items-center theme-btn d-flex w-250px">{{ __('Start shopping') }} <em class="ni ni-arrow-right-circle fs-17px ml-1"></em></a>
            </div>
          </div><!-- end row -->
        </div><!-- end container -->
        </div>
      </div>