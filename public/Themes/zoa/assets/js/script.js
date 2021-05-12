(function($){
  "use strict";

  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  var $document = $(document);

  const number_format = (number, decimals, dec_point = '.', thousands_point = ',') => {

      if (number == null || !isFinite(number)) {
          throw new TypeError('number is not valid');
      }

      if(!decimals) {
          let len = number.toString().split('.').length;
          decimals = len > 1 ? len : 0;
      }

      number = parseFloat(number).toFixed(decimals);

      number = number.replace('.', dec_point);

      let splitNum = number.split(dec_point);
      splitNum[0] = splitNum[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_point);
      number = splitNum.join(dec_point);

      return number;
  };

  const fade_out_redirect = ({ url = false, selector = 'body', wait_time = 70, full = false }) => {

      /* Get the base url */
      let base_url = 'http://eco.o/';

      /* Redirect link */
      let redirect_url = full ? url : `${base_url}${url}`;

      setTimeout(() => {
          $(selector).fadeOut(() => {
              $(selector).html('<div class="vw-100 vh-100 d-flex align-items-center"><div class="col-2 text-center mx-auto" style="width: 3rem; height: 3rem;"><h3 style="font-size: 30px;">Waiting...</h3></div></div>').show();
          });

          setTimeout(() => window.location.href = redirect_url, 100)
      }, wait_time)

  };
    var $status = $('.pagingInfo');
    var $slickElement = $('.js-slider-v4');
    $slickElement.on('init reInit afterChange', function(event, slick, currentSlide, nextSlide) {
        //currentSlide is undefined on init -- set it to 0 in this case (currentSlide is 0 based)
        var i = (currentSlide ? currentSlide : 0) + 1;
        
        $status.html(i  + '<span>' +slick.slideCount + '</span>');
      
        
    });
    $('.js-slider-v4').on('afterChange', function(event, slick, currentSlide) {
        $('.slick-active').append('<div class="pagingInfo"');
    });
  
    $('.js-slider-v4').slick({
      dots: true,
      arrows: false,
      slidesToShow: 1,
      slidesToScroll: 1      
    });
  if ($('.banner').length > 0) {
    if ($('.header').hasClass('absolute-dark')) {
      $('.header').removeClass('absolute-dark');
      $('.header').addClass('absolute-light');
    }
  }
  var userText = $("#copy-to-clipboard-input");
  var btnCopy = $("#btn-copy");

  // copy text on click
  btnCopy.on("click", function () {
    userText.select();
    document.execCommand("copy");
  });
  /* Custom links */
  $('dark-btn').on('click', event => {
      $(this).find('.os-toggler-w').addClass('on');
  });
  
  $('[href]').on('click', event => {
      //let url = $(event.currentTarget).attr('href');
      //fade_out_redirect({ url, full: true });
  });
  $document.ready(function() {
    $(".action-list-item").removeClass('active');
    $(".action-list-item").each(function() {
        if ($(this).attr('href') === window.location.href) {
            $(this).addClass("active");
        }
    });
    var is_mobile = false;
    if ($('.if-is-mobile').css('display') === 'none') {
      is_mobile = true;
    }

    if (is_mobile == true) {
      //$('body').addClass('toggle-sidebar');
    }

    var shipping_location = $('.checkout-country-selector').find(':selected').val();
    $('.shipping-locations').hide();
    $('[data-country="'+shipping_location+'"]').css('display', 'flex');
    if ($('[data-country="'+shipping_location+'"]').length) {
       $('.no-shipping').fadeOut();
       $('.no-shipping-val').removeAttr('checked');
    }else{
       $('.no-shipping').fadeIn();
       $('.no-shipping-val').attr('checked', true);
    }
  });

  $document.on('change', '[name=shipping_location]', function(){
    var shipping_price = parseFloat($(this).data('price'));
    var total_in_cart = parseFloat($('.total-in-cart').data('total'));
    $('.total-in-cart span').html(number_format(total_in_cart + shipping_price, 0));
  });

  $document.on('change', '.checkout-country-selector', function(){
    var country = $(this).val();
      $('.shipping-locations').hide();
      $('[data-country="'+country+'"]').css('display', 'flex');
      if ($('[data-country="'+country+'"]').length) {
        $('.no-shipping').fadeOut();
      }else{
        $('.no-shipping').fadeIn();
      }
  });
  $document.on('click', '.js-push-menu', function(){
    $('body').toggleClass('toggle-sidebar');
  });
  $document.on('click', '.sidebar-overlay', function(){
    $('body').removeClass('toggle-sidebar');
  });

  $document.on('click', '.data-box', function(){
    var target = $(this).attr('data-target');
    $(target).toggleClass('show');
  });

  $(document).on('change', '#add-to-cart', function(){
    var route = $(this).data('product-prices');
    var data = $(this).find('.dy-product-options').serialize();
     $.ajax({
         type: "POST",
         url: route,
         data: data,
         dataType: "json",
         success: function (data) {
          $('.product-option-prices h4 span').text(data.total);
          $('.product-option-prices').removeClass('d-none');
         }
     });
  });


  $('[data-bg-src]').each(function(){
    var e = $(this).attr('data-bg-src');
    $(this).css("background-image","url("+e+")");
  });

	$(document).on('submit', '#add-to-cart', function(e) {
    e.preventDefault();
    var url = $(this).data('route');
    var quantity = $(this).data('qty');
    var $this = $(this);
    var action = 'add';
    var data = $(this).serialize();
    var cart_item = $('.cart-total').text();
    $this.find('.ajax_add_to_cart').removeClass('added');
    $this.find('.ajax_add_to_cart').addClass('loading');
     $.ajaxSetup({
         headers: {
             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
     });
     $.ajax({
         type: "POST",
         url: url,
         data: data,
         dataType: "json",
         success: function (data) {
          if (data.status === 'success') {
            $('.cart-total').html(data.cart_count);
          }
          $this.find('.ajax_add_to_cart').removeClass('loading');
          $this.find('.ajax_add_to_cart').addClass('added');
         }
     });
	});


    $(document).on('change', '.cart_quantity', function(){
        var url = $(this).data('route');
        var product_id = $(this).data("id");
        var quantity = $(this).val();
        var action = "quantity_change";
  		  $.ajaxSetup({
  		      headers: {
  		          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  		      }
  		  });
          $.ajax({
               type: "POST",
               url: url,
               data:{product_id:product_id, quantity:quantity, action:action},
           	   dataType: "json",
               success:function(data){
                //console.log('Working');
               },
	            error: function (data) {
	            	console.log('Error:', data);
	            }
          });
     });
$(document).on('click', '[data-open="true"]', function(){
  var toggle = $(this).data('toggle');
  var parent = $(this).data('parent');
  $('.' + toggle).show();
  $('.' + parent).hide();
});
$(document).on('keyup', '#quantity', function(){
	$('.ajax_add_to_cart').data('qty', $(this).val());
});
  function filterSystem(minPrice, maxPrice) {
      $("#computers div.system").hide().filter(function () {
          var price = parseInt($(this).data("price"), 10);
          return price >= minPrice && price <= maxPrice;
      }).show();
  }
// Check Radio-box
$(".rating input:radio").attr("checked", false);

$('.rating input').click(function () {
    $(".rating span").removeClass('checked');
    $(this).parent().addClass('checked');
});
    //   Star Raiting ------------------
function cardRaining() {
    $.fn.duplicate = function (a, b) {
        var c = [];
        for (var d = 0; d < a; d++) $.merge(c, this.clone(b).get());
        return this.pushStack(c);
    };
    var cr = $(".card-rating");
    cr.each(function (cr) {
        var starcount = $(this).attr("data-rating");
        $("<i class='fad fa-star'></i>").duplicate(starcount).prependTo(this);
    });
}
cardRaining();

$(window).on('load', function(){
 setTimeout(function () {
   $('.shimmer').removeClass('shimmer');
   $('html').removeClass('pointer-event-0');
 }, 1500);
 var $input = $('.shop-number-inc').find('input');
 $('.ajax_add_to_cart').attr('data-qty', $input.val());
});
$('.minus').click(function () {
  var $input = $(this).parent().find('input');
  var count = parseFloat($input.val()) - 1;
  count = count < 1 ? 1 : count;
  $input.val(count);
  $input.change();
  $('.ajax_add_to_cart').attr('data-qty', $input.val());
  return false;
});
$('.plus').click(function () {
  var $input = $(this).parent().find('input');
  $input.val(parseFloat($input.val()) + 1);
  $input.change();
  $('.ajax_add_to_cart').attr('data-qty', $input.val());
  return false;
});
})(jQuery);