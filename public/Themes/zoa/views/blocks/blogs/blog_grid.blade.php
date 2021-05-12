
<div class="blog-list">
    <div class="container">
        <div class="portfolio-title-outside row">
         @php
             $numofBlogs = (int) $number_of_blogs;
         @endphp
         @foreach (store_blogs($uid, $numofBlogs) as $item)
         <div class="col-md-4">
            <div class="portfolio-item card-shadow p-2 bdrs-15">
              <div class="portfolio-img bdrs-15">
                <a href="{{ Linker::url(route('user-profile-single-blog', ['profile' => $user->username, 'id' => $item->id]), ['ref' => $user->username]) }}">
                  <img src="{{ (!empty($item->image) && file_exists(public_path('media/user/blog/' . $item->image)) ? url('media/user/blog/' . $item->image) : $item->settings->media_url ?? '') }}" alt=" ">
                </a>
              </div>
              <div class="portfolio-title px-3">
                <h6 class="font-weight-medium margin-0 mb-2"><a href="#">{{ Str::limit($item->name, $limit = 35, $end = '...') }}</a></h6>
                <p>{!! str_replace("{{title}}", $item->name, Str::limit(clean($item->note, 'clean_all'), $limit = 100, $end = '...')) !!}</p>
                 <a href="{{ Linker::url(route('user-profile-single-blog', ['profile' => $user->username, 'id' => $item->id]), ['ref' => $user->username]) }}" class="mt-3 theme-color">{{ __('Read more') }} <em class="ni ni-arrow-right-circle ml-2"></em></a>
              </div>
            </div>
            </div>
          @endforeach
        </div>
    </div>
</div>