(function($){
  "use strict";
    const nr = (number, decimals = 0, digits = 1) => {
          var si = [
            { value: 1, symbol: "" },
            { value: 1E3, symbol: "kB" },
            { value: 1E6, symbol: "MB" },
            { value: 1E9, symbol: "GB" },
            { value: 1E12, symbol: "TB" },
            { value: 1E15, symbol: "P" },
            { value: 1E18, symbol: "E" }
          ];
          var rx = /\.0+$|(\.[0-9]*[1-9])0+$/;
          var i;
          for (i = si.length - 1; i > 0; i--) {
            if (number >= si[i].value) {
              break;
            }
          }
          return (number / si[i].value).toFixed(digits).replace(rx, "$1") + si[i].symbol;
    };
    if ($('#chat-body').length) {
      setInterval(function() {
        $.ajax({
            type: "GET",
            url: $('#chat-body').data('route'),
            dataType: 'html',
            data: '',
            success: function (data) {
              if($('#chat-body').find('.simplebar-content')){
                  $('#chat-body .simplebar-content').html(data);
              }else{
                  $('#chat-body').html(data);
              }
              //
            }
        });
      }, 5000);
    }

    var previousTarget = null;

    $(document).on('click', '.show-hide-chat', function(){
      var $value = $(this).data('value');
      $('.show-hide-chat').removeClass('bg-light border-1 card-shadow');


      if(this === previousTarget) {
        $(this).removeClass('bg-light border-1 card-shadow');
        $('[name="type"]').val('text');
        $('.show-open').hide();
        $('#open-text').show();
        
        previousTarget = null;
        return true;
      }

      $('[name="type"]').val($value);
      $(this).addClass('bg-light border-1 card-shadow');

      $('.show-open').hide();
      $('#open-' + $value).show();
      previousTarget = this;
    });















    $(document).ready(function() {
    if (window.File && window.FileList && window.FileReader) {
      $(".uploader_input").on("change", function(e) {
        $(this).closest('.custom-input-uploader').find('.list-files').empty();
        const $this = $(this);
        var files = e.target.files, filesLength = files.length;
        for (var i = 0; i < filesLength; i++) {
          var f = files[i]
          var fileReader = new FileReader();
          fileReader.onload = (function(e) {
            var file = f;
            var item_is_image = $('<div class="p-file-upload-preview "><header class="p-file-upload-p-header"><div class="p-file-upload-p-header-content"><span class="p-file-upload-title">'+file.name+'</span><span class="p-file-upload-size">'+ nr(file.size, 0, 1) +'</span></div></header><main class="p-file-upload-p-main"><div class="p-file-upload-image"><img alt="agency-minimal-2-about-desktop.jpg" src="'+e.target.result+'" class=""></div></main></div>').hide();


            var item_is_file = $("<li class='file-item'><div class='file-item-body'><p>"+file.name+"</p><span>"+ nr(file.size, 0, 1) +"</span></div><div class='file-item-status remove-pre' hidden><span class='text-muted'><i class='icon ni ni-cross'></i></span></div></li>").hide();

            if (file.type.match('image.*')) {
              $this.closest('.custom-input-uploader').find('.list-files').append(item_is_image);
              item_is_image.slideDown('normal'); 
            }else{
              $this.closest('.custom-input-uploader').find('.list-files').append(item_is_file);
              item_is_file.slideDown('normal'); 
            }      
          });
          fileReader.readAsDataURL(f);
        }
      });
    }

    $(document).on('click', '.remove-pre', function(){
     var $this = $(this);
     var url = $(this).data('route');
     var image = $(this).data('image');
     var id = $(this).data('product');
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      $.ajax({
          type: "POST",
          url: url,
          data: {image:image, id:id},
          dataType: "json",
          success: function (data) {
            if (data.response == 'success') {
              $this.closest(".p-file-upload-preview").remove();
            }
          }
      });
    });

    $(document).on('click', '.select_all', function(){
      var $that = $(this).find('input');
      $(':checkbox').each(function() {
          this.checked = $that.is(':checked');
      });
    });
    $(document).on('click', '.update_all', function(){
     var url = $(this).data('route');
     var action = "update_all";
     var type = $(this).data('type');
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      var actions = $('input[name="action_select[]"]').map(function(){
        if(this.checked){
          return this.value;
        }
      }).get();
      $.ajax({
          type: "POST",
          url: url,
          data: {action:action, 'action_select[]':actions, type:type },
          dataType: "json",
          success: function (data) {
            if(data.response == 'success'){
              location.reload();
            }
          }
      });
    });
  });
})(jQuery);