(function($){
  "use strict";
    function sleep(time) {
      return new Promise((resolve) => setTimeout(resolve, time));
    }
    $(document).on("click", "#migrate-update", function () {
        var $route = $(this).attr('route');
        $.get($route, {steps: 1}, function(data){
            $('#update-stats').append('<h6 class="text-info d-block mt-2">Calling Backend And Trying Database migration</h6>');
            $.get($route, {steps: 2}, function(data){
                if (data == "SUCCESS") {
                 $('#update-stats').append('<h6 class="text-success d-block mt-2">Database updated</h6>');
                }else{
                 $('#update-stats').append('<h6 class="text-danger d-block mt-2">'+data+'</h6>');
                }
            });
        });
    });

    $(document).on("click", "#update-cloud", function () {
        var $route = $(this).attr('route');
        $.get($route, {steps: 1}, function(data){
            $('#update-stats').append('<h6 class="text-'+data.status+' d-block mt-2">'+data.response+'</h6>');
            $.get($route, {steps: 2}, function(data){
              $('#update-stats').append('<h6 class="text-success d-block mt-2">Feching files</h6>');
               sleep(2000).then(() => {
                $('#update-stats').append('<h6 class="text-'+data.status+' d-block mt-2">'+data.response+'</h6>');
                  $('#update-stats').append('<h6 class="text-success d-block mt-2">Unzipping files</h6>');
                $.get($route, {steps: 3}, function(data){
                   sleep(2000).then(() => {
                    $('#update-stats').append('<h6 class="text-'+data.status+' d-block mt-2">'+data.response+'</h6>');
                    $.get($route, {steps: 4}, function(data){
                      $('#update-stats').append('<h6 class="text-success d-block mt-2">Updating database</h6>');
                       sleep(2000).then(() => {
                        $('#update-stats').append('<h6 class="text-'+data.status+' d-block mt-2">'+data.response+'</h6>');
                          $.get($route, {steps: 5}, function(data){
                           $('#update-stats').append('<h6 class="text-'+data.status+' d-block mt-2">'+data.response+'</h6>');
                            sleep(2000).then(() => {
                              $('#update-stats').append('<h6 class="text-success d-block mt-2">Success</h6>');
                            });
                          });
                       });
                    });
                   });
                });
               });
            });
        });
    });
})(jQuery);