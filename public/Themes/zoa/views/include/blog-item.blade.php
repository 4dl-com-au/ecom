
                    <div class="blog-list-item row p-4">
                        <div class="col-md-5 col-12">
                            <div class="blog-list-info">
                                <div class="blog-tag">
                                   <div class="blog-date text-uppercase">
                                        {{ \Carbon\Carbon::parse($item->created_at)->toFormattedDateString() }}
                                    </div>
                                </div>
                                <h3 class="blog-title">
                                    <a href="{{ Linker::url(route('user-profile-single-blog', ['profile' => $user->username, 'id' => $item->id]), ['ref' => $user->username]) }}">{{ Str::limit($item->name, $limit = 35, $end = '...') }}</a>
                                </h3>
                                <p class="blog-desc">{!! str_replace("{{title}}", $item->name, Str::limit(clean($item->note, 'clean_all'), $limit = 100, $end = '...')) !!}</p>
                                <a href="{{ Linker::url(route('user-profile-single-blog', ['profile' => $user->username, 'id' => $item->id]), ['ref' => $user->username]) }}" class="read-more">{{ __('Read more') }}</a>
                            </div>
                        </div>
                        <div class="col-md-7 col-12">
                            <div class="blog-img">
                                <a href="" class="effect-img3 plus-zoom">
                                    <img src="{{ (!empty($item->image) && file_exists(public_path('media/user/blog/' . $item->image)) ? url('media/user/blog/' . $item->image) : $item->settings->media_url ?? '') }}" alt="" class="img-reponsive">

                                </a>
                            </div>
                        </div>
                    </div>