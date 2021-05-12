@extends('layouts.app')
@section('footerJS')
<script src="{{ url('assets/js/emoji-picker.js') }}"></script>
<script src="{{ url('js/others.js') }}"></script>
    <script>
    const button = document.querySelector('.add-emoji');

    const picker = new EmojiButton();

    button.addEventListener('click', () => {
      picker.togglePicker(button);
    });

    picker.on('emoji', emoji => {
       document.querySelector('#chat-input').value += emoji;
    });
</script>
@stop
 @section('content')
 <div class="container">
  <div class="row mt-7">
    <div class="col-md-3">
        @include('dashboard.dashboard-head')
    </div>
    <div class="col-md-9">
      <div id="huro-messaging" class="view-wrapper is-pushed-full ml-0">
            <div class="page-content-wrapper">
                <div class="page-content chat-content">
                    <!-- Chat Card -->
                    <div class="is-chat animated preFadeInUp fadeInUp">
                        <div class="chat-body-wrap">
                            <!-- Chat Body -->
                           <ol id="chat-body" class="chat-body get-chat" data-route="{{ route('get-chat-messages', ['id' => $convo->id, 'view' => 'customer']) }}" data-id="{{ $convo->id }}" data-simplebar>
                                {!! convo_messages_html($convo->id, 'customer') !!}
                            </ol>
                            <!-- Chat side -->
                        </div>
                        <div class="message-field-wrapper">
                            <form method="post" action="{{ route('user-chat-message', ['convo_id' => $convo->id]) }}" class="control" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" value="customer" name="from">
                                <input type="hidden" value="{{ $uid }}" name="user">
                                <div class="add-content">

                                    <div class="dropdown">
                                      <button class="button dropdown-toggle p-0" type="button" data-toggle="dropdown"><i class="tio add m-0"></i></button>
                                      <div class="dropdown-menu">
                                            <div class="dropdown-content">
                                                <a href="Javascript::void" class="dropdown-item show-hide-chat" data-value="images">
                                                    <i class="tio image"></i>
                                                    <div class="meta">
                                                        <span>{{ __('Images') }}</span>
                                                        <span>{{ __('Upload pictures') }}</span>
                                                    </div>
                                                </a>
                                                <a href="Javascript::void" class="dropdown-item show-hide-chat" data-value="link">
                                                    <i class="tio link"></i>
                                                    <div class="meta">
                                                        <span>{{ __('Link') }}</span>
                                                        <span>{{ __('Post a link') }}</span>
                                                    </div>
                                                </a>
                                                <hr class="dropdown-divider">
                                                <a href="Javascript::void" class="dropdown-item show-hide-chat" data-value="file">
                                                    <i class="tio file_outlined"></i>
                                                    <div class="meta">
                                                        <span>{{ __('File') }}</span>
                                                        <span>{{ __('Upload a file') }}</span>
                                                    </div>
                                                </a>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="add-emoji ml-1">
                                    <div class="button">
                                        <i class="tio slightly_smilling m-0"></i>
                                    </div>
                                </div>

                               <div class="card border-0 shadow-none custom-input-uploader hide show-open" id="open-file">
                                   <div class="card-body p-0 m-0">
                                       <div class="file-upload p-0 m-0">
                                           <input class="file-input uploader_input" name="data_file" type="file">
                                           <img src="{{ url('media/misc/upload-image-placeholder.svg') }}" class="h-25px my-3" alt=" ">
                                           <div class="card-subtitle">{{ __('Drop a file here') }}</div>
                                            <div class="list-files w-100 mt-4">
                                            </div>
                                       </div>
                                   </div>
                               </div>

                               <div class="card border-0 shadow-none custom-input-uploader hide show-open" id="open-images">
                                   <div class="card-body p-0 m-0">
                                       <div class="file-upload p-0 m-0">
                                           <input class="file-input uploader_input" name="data_image" type="file">
                                           <img src="{{ url('media/misc/upload-image-placeholder.svg') }}" class="h-25px my-3" alt=" ">
                                           <div class="card-subtitle">{{ __('Drag n Drop your image here') }}</div>
                                            <div class="list-files w-100 mt-4">
                                            </div>
                                       </div>
                                   </div>
                               </div>

                                <input type="hidden" name="type" value="text">

                                <input id="open-text" class="form-control show-open bg-white" type="text" placeholder="{{ __('Write a message ...') }}" name="data_text">

                                <input class="form-control hide show-open bg-white" id="open-link" type="text" placeholder="{{ __('Enter url') }}" name="data_link">

                                <div class="send-message">
                                    <button class="btn bg-blue c-white radius-10 effect-letter">
                                        {{ __('Send') }}
                                    </button>
                                </div>
                            </form>


                            <div class="typing-indicator">
                                <img src="assets/img/icons/typing.gif" alt="">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>
@endsection