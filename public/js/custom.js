(function($){
  "use strict";
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var pickr_options = {
    components: {

        // Main components
        preview: true,
        opacity: false,
        hue: true,
        comparison: false,

        // Input / output Options
        interaction: {
            hex: true,
            rgba: true,
            hsla: false,
            hsva: false,
            cmyk: false,
            input: true,
            clear: false,
            save: true
        }
    }
};

function copyclipboard(text) {
    var input = document.createElement('input');
    input.setAttribute('value', text);
    input.style.opacity = "0";
    document.body.appendChild(input);
    input.select();
    var result = document.execCommand('copy');
    document.body.removeChild(input);
    return result;
}

const getUrl = window.location;
const baseUrl = getUrl .protocol + "//" + getUrl.host + "/";


function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie (name) {   
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function cookieConsent() {
    if (!getCookie('allowCookies')) {
        $('#cookie-toast').toast('show');
    }
}


$('#cookie-deny-button').click(()=>{
    eraseCookie('allowCookies')
    $('#cookie-toast').toast('hide');
})

$('#cookie-accept-button').click(()=>{
    setCookie('allowCookies','1',7)
    $('#cookie-toast').toast('hide')
})

// load
cookieConsent()

$('.item_links').each(function(){
  if ($(this).find('.nav-link').length) {

  }else{
    $(this).closest('.parent-link').hide();
  }
});

$(document).on('click', '.html-to-canvas-download', function(){
  var $html = $(this).data('html');
  var $name = $(this).data('name');

  html2canvas(document.querySelector($html)).then(function(canvas) {
    var myImage = canvas.toDataURL("image/png");

    var link = document.createElement("a");
    link.download = $name;
    link.href = myImage;
    link.click();
});
});

$(document).on('click', '[data-clipboard]', function(){
  var $text = $(this).attr('data-clipboard');
  var $after_text = $(this).attr('data-clipboard-after-text');
  var $this_text  = $(this).html();
  var $this = $(this);

  copyclipboard($text);

  $(this).html($after_text);


   setTimeout(function () {
    $this.html($this_text);
   }, 1500);
});

$('[data-bg-src]').each(function(){
  var e = $(this).attr('data-bg-src');
  $(this).css("background-image","url("+e+")");
});

$(document).on('click', '.sign-in-modal', function(){
  $('.boxup-auth .box--signup').toggleClass('translat');
  $('.boxup-auth .overlay').show();
});

$(document).on('click', '.boxup-auth .overlay', function(){
  $('.boxup-auth .box--signup').addClass('translat');
  $('.boxup-auth .overlay').hide();
});


$('.clov-sidebar .clov-sidebar-inner .nav-link').each(function(){
  var $href = $(this).attr('href');
  $(this).removeClass('active');
  if (getUrl == $href) {
    $(this).addClass('active');;
  }
});


$('[pickr]').each(function(){
  var $pickr = $(this);
  var $el = $pickr.find('[pickr-div]').attr('id');
  var $color_input = $pickr.find('[pickr-input]');
  var $color_pickr = Pickr.create({
      el: "#"+$el,
      default: $color_input.val(),
      ...pickr_options
  });
  $color_pickr.off().on('change', hsva => {
      $color_input.val(hsva.toHEXA().toString()); 
  });
});

/*

Uncomment if nav menu doesnt click

or if you have mod_pagespeed_beacon cache on your server

*/

$('#sidebarMenu').each(function(){
  var $nav_html = $(this).find('.nav-pills');
  var $item__number = $(this).find('.item__number');

  if ($(this).find('.simplebar-content').find('.nav-pills').length) {

  }else{
    $(this).find('.simplebar-content').append($nav_html.prop('outerHTML'));
    $(this).find('.simplebar-content').append($item__number.prop('outerHTML'));

    $nav_html.remove();
    $item__number.remove();
  }
});


$(document).on('click', '.toggle-sidebar', function(){
  $('.toggle-sidebar').addClass('active');
  $('.sidebar-show').addClass('show');
  $('.overlay-sidebar').addClass('show');
});

$(document).on('click', '.overlay-sidebar', function(){
  $('.toggle-sidebar').removeClass('active');
  $(this).removeClass('show');
  $('.sidebar-show').removeClass('show');
});

$(document).on('click', '.toggle-chat-sidebar', function(){
  $('.sidebar-panel.is-messages').toggleClass('active');
  $('.is-messages-overlay').toggleClass('show');
  $('.toggle-chat-sidebar').addClass('active');
});

$(document).on('click', '.is-messages-overlay', function(){
  $('.toggle-chat-sidebar').removeClass('active');

  $('.sidebar-panel.is-messages').removeClass('active');
  $(this).removeClass('show');
});


$('body').on('click', '[data-confirm]', (event) => {
    let message = $(event.currentTarget).attr('data-confirm');

    if(!confirm(message)) return false;
});

$('img').each(function(){
    $(this).on('error', function() {
        $(this).css('display', 'none');
    });
});

$('.sortable-div').each(function(){
  var $this = $(this);
  var $handle = $this.data('handle');
  let sort = Sortable.create(this, {
      animation: 150,
      group: "sorting",
      handle: $handle,
      swapThreshold: 5,
      onUpdate: () => {
          let data = [];
          $this.find('.sortable-item').each((i, elm) => {
              let items = {
                  id: $(elm).data('id'),
                  position: i
              };
              data.push(items)
          });
          $.ajax({
              type: "POST",
              url: $this.data('route'),
              dataType: 'json',
              data: {
                  data: data
              }
          });
      }
  });
});
var submenu_animation_speed = 200,
        submenu_opacity_animation = true;
        
var select_sub_menus = $('.accordion-menu li:not(.open) .sub-menu'),
    active_page_sub_menu_link = $('.accordion-menu li.active-page > a');

// Hide all sub-menus
select_sub_menus.hide();


if(submenu_opacity_animation == false) {
    $('.sub-menu li').each(function(i){
        $(this).addClass('animation');
    });
};

// Accordion
$('.accordion-menu li a').on('click', function() {
    var sub_menu = $(this).next('.sub-menu'),
        parent_list_el = $(this).parent('li'),
        active_list_element = $('.accordion-menu > li.open'),
        show_sub_menu = function() {
            sub_menu.slideDown(submenu_animation_speed);
            parent_list_el.addClass('open');
            if(submenu_opacity_animation === true) {
                $('.open .sub-menu li').each(function(i){
                    var t = $(this);
                    setTimeout(function(){ t.addClass('animation'); }, (i+1) * 25);
                });
            };
        },
        hide_sub_menu = function() {
            if(submenu_opacity_animation === true) {
                $('.open .sub-menu li').each(function(i){
                    var t = $(this);
                    setTimeout(function(){ t.removeClass('animation'); }, (i+1) * 15);
                });
            };
            sub_menu.slideUp(submenu_animation_speed);
            parent_list_el.removeClass('open');
        },
        hide_active_menu = function() {
            $('.accordion-menu > li.open > .sub-menu').slideUp(submenu_animation_speed);
            active_list_element.removeClass('open');
        };
    
    if(sub_menu.length) {
        
        if(!parent_list_el.hasClass('open')) {
            if(active_list_element.length) {
                hide_active_menu();
            };
            show_sub_menu();
        } else {
            hide_sub_menu();
        };
        
        return false;
        
    };
});

if($('.active-page > .sub-menu').length) {
    active_page_sub_menu_link.click();
};

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
        $("<i class='ni ni-star'></i>").duplicate(starcount).prependTo(this);
    });
}
cardRaining();

$(document).ready(function() {
    var wrapper = $(".dy-wrap");
    var append = $(".dy-field-add");
    var add_button = $(".dy-add-field-button");
    $(add_button).click(function(e) {
        e.preventDefault();
        var $appended = $(wrapper).append(append.html()).find('.dy-item').addClass('append');
        reload_dy_ids();
    });

    $(wrapper).on("click", ".remove", function(e) {
        e.preventDefault();
        $(this).closest('.dy-item').remove();
        reload_dy_ids();
    });
    if ($('.dy-wrap').length ){
        reload_dy_ids();
    }

  $('.dy-item').each(function(){
    if ($(this).hasClass('append')) {
      $(this).find('select').select2();
    }
  });

  // Product Options

  const po_wrapper = $(".po-wrap");
  const po_append = $(".po-field-add");
  const po_add_button = $(".po-add-button");
  $(po_add_button).click(function(e){
    e.preventDefault();
    const $appended = $(po_wrapper).append(po_append.html()).find('.po-item').addClass('append');
    reload_po_ids();
  });
  $(po_wrapper).on("click", ".remove", function(e) {
      e.preventDefault();
      const route = $(this).data('route');
      const id = $(this).closest('.po-item').find('[data-po-name="id"]').val();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      $.ajax({
          type: "POST",
          url: route,
          data: {id:id},
          dataType: "json",
      });
      $(this).closest('.po-item').remove();
      reload_po_ids();
  });
  $(po_wrapper).on("click", ".remove", function(e) {
      e.preventDefault();
      $(this).closest('.po-item').remove();
      reload_po_ids();
  });

  if (po_wrapper.length) {
    reload_po_ids();
  }


  // Product options values

  const po_val_append = $(".po-val-field-add");
  $(document).on('click', '.po-val-add-button', function(e){
    e.preventDefault();
    const id = $(this).data('optionvalueid');
    const po_val_wrapper = $(this).closest('[data-id="'+id+'"]').find('.po-val-wrap');
    $(po_val_wrapper).append(po_val_append.html()).find('.po-val-item').addClass('append');
    reload_po_ids();
  });

  $(document).on("click", ".option-remove", function(e) {
      e.preventDefault();
      const route = $(this).data('route');
      const id = $(this).closest('.po-val-item').find('.option-values-id').val();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      $.ajax({
          type: "POST",
          url: route,
          data: {id:id},
          dataType: "json",
          success: function (data) {
            //console.log(data);
          }
      });
      $(this).closest('.po-val-item').remove();
      reload_po_ids();
  });
/*
  $(document).on('change', '.options-type', function(){
    if ($(this).val() === 'field') {
      $(this).closest('.po-item').find('.hide-if-text-textarea').hide();
      $(this).closest('.po-item').find('.po-val-item').nextAll().remove();
      $(this).closest('.po-item').find('.po-val-add-button').hide();
    }else if($(this).val() === 'textarea'){
      $(this).closest('.po-item').find('.hide-if-text-textarea').hide();
      $(this).closest('.po-item').find('.po-val-item').nextAll().remove();
      $(this).closest('.po-item').find('.po-val-add-button').hide();
    }else{
      $(this).closest('.po-item').find('.hide-if-text-textarea').show();
      $(this).closest('.po-item').find('.po-val-add-button').show();
    }
  });
*/
});
function reload_po_ids(){
  var x = 0;
  $('.po-item').each(function(){
    x++
    var $po_name = $(this).data('poname');
    $(this).data('id', x);
      if ($(this).hasClass('append')) {
        $(this).attr('data-id', x);
        $(this).find('[data-po-name]').each(function(){
           $(this).attr('name', $po_name + '[' + x + '][' + $(this).data('po-name') + ']');
        });
        $(this).find('.po-val-add-button').attr('data-optionvalueid', x);

        var ov = 0;
        if ($(this).find('.po-val-item').length) {

        }else{
          $(this).find('.po-val-add-button').click();
        }
        // Product option values
        $(this).find('.po-val-item').each(function(){
            ov++;
            const value_name = $(this).data('poval-name');
            $(this).find('[data-poval-name]').each(function(){
               $(this).attr('name', $po_name + '[' + x + '][' + value_name + ']['+ov+']['+ $(this).data('poval-name') +']');
            });
        });




        $(this).find('select').select2();
      }
  });
}

function reload_dy_ids(){
  var x = 0;
  $('.dy-item').each(function(){
    x++
    var $dy_name = $(this).data('dy-name');
      if ($(this).hasClass('append')) {
        $(this).attr('data-id', x);
        $(this).find('[data-dy-item-name]').each(function(){
           $(this).attr('name', $dy_name + '[' + x + '][' + $(this).data('dy-item-name') + ']');
        });
        $(this).find('select').select2();
      }
  });
}

$(window).scroll(function() {
    var scroll = $(window).scrollTop();

    if (scroll >= 50) {
        $(".sticky").addClass("nav-sticky");
        $("body").addClass("fixed-header");
    } else {
        $(".sticky").removeClass("nav-sticky");
        $("body").removeClass("fixed-header");
    }
});
$(document).on('click', '.dark-mode', function() {
  if ($('body').hasClass('background-dark')) {
      $(this).find('em').removeClass('ni-sun');
      $(this).find('em').addClass('ni-moon');
  } else {
      $(this).find('em').removeClass('ni-moon');
      $(this).find('em').addClass('ni-sun');
  }
  if ($('body').hasClass('background-dark')) {
    sessionStorage.setItem('background', 'light');
    $('body').removeClass('background-dark');
    $(this).removeClass('on');
    $('body').addClass('theme-background');
  }else{
    sessionStorage.setItem('background', 'dark');
    $('body').addClass('background-dark');
    $('body').removeClass('theme-background');
    $(this).addClass('on');
  }
  return false;
});
$(document).ready(function() {
    var fr = $('[name="payment_frequency"]:checked');
    $('.'+fr.attr('data-payment-pricing')).show();
});
var $plansHolders = $('.month, .annual, .quarter').hide();
$(document).on('change', '[name="payment_frequency"]', function(){
    var $this = $(this);
    $plansHolders.hide();
    $('.'+$this.attr('data-payment-pricing')).show();
});
$('[data-search]').on('keyup', function () {
  var searchVal = $(this).val();
  var filterItems = $('[data-filter-item]');
  if (searchVal != '') {
    filterItems.addClass('d-none');
    $('[data-filter-item][data-filter-name*="' + searchVal.toLowerCase() + '"]').removeClass('d-none');
  } else {
    filterItems.removeClass('d-none');
  }
});

$(document).on('click', '.redirect-href', function(){
  redirect($(this).attr('href'), true);
});

$('[role="iconpicker"]').on('change', event => {
    $(event.currentTarget).closest('.link-icon').find('input').attr('value', event.icon).trigger('change');
});
if (sessionStorage['background'] == 'dark') {
   document.getElementById("body").className += " background-dark";
   $('.dark-mode').addClass('on');
   $('.dark-mode').find('em').removeClass('ni-moon');
   $('.dark-mode').find('em').addClass('ni-sun');
}


$(document).on('change', '.on-change-ajax-send', function(){
    var data = $(this).serialize();
    var route = $(this).data('route');
    $.ajax({
         type: "POST",
         url: route,
         data: data,
         dataType: "json",
         success: function (data) {

         }
   });
});

$(document).on('change', 'select[name="loginasuser"]', function(){
    var $selected = $(this).find(':selected').val();
    $(this).closest('form').find('input[name="id"]').val($selected);
});

$(document).on('click', '.submit-closest', function() { var $this = $(this); var $form = $this.closest('#form-submit'); $form.submit(); });

    function ecommerceLineS1(selector, set_data) {
        var $selector = $(selector || ".ecommerce-line-chart-s1");
        $selector.each(function() {
            for (var $self = $(this), _self_id = $self.attr("id"), _get_data = void 0 === set_data ? eval(_self_id) : set_data, selectCanvas = document.getElementById(_self_id).getContext("2d"), chart_data = [], i = 0; i < _get_data.datasets.length; i++) chart_data.push({
                label: _get_data.datasets[i].label,
                tension: _get_data.lineTension,
                backgroundColor: _get_data.datasets[i].background,
                borderWidth: 2,
                borderColor: _get_data.datasets[i].color,
                pointBorderColor: "transparent",
                pointBackgroundColor: "transparent",
                pointHoverBackgroundColor: "#fff",
                pointHoverBorderColor: _get_data.datasets[i].color,
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 2,
                pointRadius: 4,
                pointHitRadius: 4,
                data: _get_data.datasets[i].data
            });
            var chart = new Chart(selectCanvas, {
                type: "line",
                data: {
                    labels: _get_data.labels,
                    datasets: chart_data
                },
                options: {
                    legend: {
                        display: !!_get_data.legend && _get_data.legend,
                        rtl: NioApp.State.isRTL,
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            fontColor: "#6783b8"
                        }
                    },
                    maintainAspectRatio: !1,
                    tooltips: {
                        enabled: !0,
                        rtl: NioApp.State.isRTL,
                        callbacks: {
                            title: function(a, t) {
                                return t.labels[a[0].index]
                            },
                            label: function(a, t) {
                                return t.datasets[a.datasetIndex].data[a.index] + " " + _get_data.dataUnit
                            }
                        },
                        backgroundColor: "#1c2b46",
                        titleFontSize: 10,
                        titleFontColor: "#fff",
                        titleMarginBottom: 4,
                        bodyFontColor: "#fff",
                        bodyFontSize: 10,
                        bodySpacing: 4,
                        yPadding: 6,
                        xPadding: 6,
                        footerMarginTop: 0,
                        displayColors: !1
                    },
                    scales: {
                        yAxes: [{
                            display: !1,
                            ticks: {
                                beginAtZero: !0,
                                fontSize: 12,
                                fontColor: "#9eaecf",
                                padding: 0
                            },
                            gridLines: {
                                color: "#e5ecf8",
                                tickMarkLength: 0,
                                zeroLineColor: "#e5ecf8"
                            }
                        }],
                        xAxes: [{
                            display: !1,
                            ticks: {
                                fontSize: 12,
                                fontColor: "#9eaecf",
                                source: "auto",
                                padding: 0,
                                reverse: NioApp.State.isRTL
                            },
                            gridLines: {
                                color: "transparent",
                                tickMarkLength: 0,
                                zeroLineColor: "#e5ecf8",
                                offsetGridLines: !0
                            }
                        }]
                    }
                }
            })
        })
    }
    ecommerceLineS1()

    function ecommerceLineS4(selector, set_data) {
        var $selector = $(selector || ".ecommerce-line-chart-s4");
        $selector.each(function() {
            for (var $self = $(this), _self_id = $self.attr("id"), _get_data = void 0 === set_data ? eval(_self_id) : set_data, selectCanvas = document.getElementById(_self_id).getContext("2d"), chart_data = [], i = 0; i < _get_data.datasets.length; i++) chart_data.push({
                label: _get_data.datasets[i].label,
                tension: _get_data.lineTension,
                backgroundColor: _get_data.datasets[i].background,
                borderWidth: 2,
                borderDash: _get_data.datasets[i].dash,
                borderColor: _get_data.datasets[i].color,
                pointBorderColor: "transparent",
                pointBackgroundColor: "transparent",
                pointHoverBackgroundColor: "#fff",
                pointHoverBorderColor: _get_data.datasets[i].color,
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 2,
                pointRadius: 4,
                pointHitRadius: 4,
                data: _get_data.datasets[i].data
            });
            var chart = new Chart(selectCanvas, {
                type: "line",
                data: {
                    labels: _get_data.labels,
                    datasets: chart_data
                },
                options: {
                    legend: {
                        display: !!_get_data.legend && _get_data.legend,
                        rtl: NioApp.State.isRTL,
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            fontColor: "#6783b8"
                        }
                    },
                    maintainAspectRatio: !1,
                    tooltips: {
                        enabled: !0,
                        rtl: NioApp.State.isRTL,
                        callbacks: {
                            title: function(a, t) {
                                return t.labels[a[0].index]
                            },
                            label: function(a, t) {
                                return t.datasets[a.datasetIndex].data[a.index]
                            }
                        },
                        backgroundColor: "#1c2b46",
                        titleFontSize: 13,
                        titleFontColor: "#fff",
                        titleMarginBottom: 6,
                        bodyFontColor: "#fff",
                        bodyFontSize: 12,
                        bodySpacing: 4,
                        yPadding: 10,
                        xPadding: 10,
                        footerMarginTop: 0,
                        displayColors: !1
                    },
                    scales: {
                        yAxes: [{
                            display: !0,
                            stacked: !!_get_data.stacked && _get_data.stacked,
                            position: NioApp.State.isRTL ? "right" : "left",
                            ticks: {
                                beginAtZero: !0,
                                fontSize: 11,
                                fontColor: "#9eaecf",
                                padding: 10,
                                min: 0,
                                stepSize: 3e3
                            },
                            gridLines: {
                                color: "#e5ecf8",
                                tickMarkLength: 0,
                                zeroLineColor: "#e5ecf8"
                            }
                        }],
                        xAxes: [{
                            display: !1,
                            stacked: !!_get_data.stacked && _get_data.stacked,
                            ticks: {
                                fontSize: 9,
                                fontColor: "#9eaecf",
                                source: "auto",
                                padding: 10,
                                reverse: NioApp.State.isRTL
                            },
                            gridLines: {
                                color: "transparent",
                                tickMarkLength: 0,
                                zeroLineColor: "transparent"
                            }
                        }]
                    }
                }
            })
        })
    }
    ecommerceLineS4()
    var trafficSources = {
            labels: ["Organic Search", "Social Media", "Referrals", "Others"],
            dataUnit: "People",
            legend: !1,
            datasets: [{
                borderColor: "#fff",
                background: ["#b695ff", "#b8acff", "#ffa9ce", "#f9db7b"],
                data: [4305, 859, 482, 138]
            }]
        },
        orderStatistics = {
            labels: ["Completed", "Processing", "Canclled"],
            dataUnit: "People",
            legend: !1,
            datasets: [{
                borderColor: "#fff",
                background: ["#816bff", "#13c9f2", "#ff82b7"],
                data: [4305, 859, 482]
            }]
        };

    function ecommerceDoughnutS1(selector, set_data) {
        var $selector = $(selector || ".ecommerce-doughnut-s1");
        $selector.each(function() {
            for (var $self = $(this), _self_id = $self.attr("id"), _get_data = void 0 === set_data ? eval(_self_id) : set_data, selectCanvas = document.getElementById(_self_id).getContext("2d"), chart_data = [], i = 0; i < _get_data.datasets.length; i++) chart_data.push({
                backgroundColor: _get_data.datasets[i].background,
                borderWidth: 2,
                borderColor: _get_data.datasets[i].borderColor,
                hoverBorderColor: _get_data.datasets[i].borderColor,
                data: _get_data.datasets[i].data
            });
            var chart = new Chart(selectCanvas, {
                type: "doughnut",
                data: {
                    labels: _get_data.labels,
                    datasets: chart_data
                },
                options: {
                    legend: {
                        display: !!_get_data.legend && _get_data.legend,
                        rtl: NioApp.State.isRTL,
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            fontColor: "#6783b8"
                        }
                    },
                    rotation: -1.5,
                    cutoutPercentage: 70,
                    maintainAspectRatio: !1,
                    tooltips: {
                        enabled: !0,
                        rtl: NioApp.State.isRTL,
                        callbacks: {
                            title: function(a, t) {
                                return t.labels[a[0].index]
                            },
                            label: function(a, t) {
                                return t.datasets[a.datasetIndex].data[a.index] + " " + _get_data.dataUnit
                            }
                        },
                        backgroundColor: "#1c2b46",
                        titleFontSize: 13,
                        titleFontColor: "#fff",
                        titleMarginBottom: 6,
                        bodyFontColor: "#fff",
                        bodyFontSize: 12,
                        bodySpacing: 4,
                        yPadding: 10,
                        xPadding: 10,
                        footerMarginTop: 0,
                        displayColors: !1
                    }
                }
            })
        })
    }
    ecommerceDoughnutS1()
    function analyticsDoughnut(selector, set_data) {
        var $selector = $(selector || ".analytics-doughnut");
        $selector.each(function() {
            for (var $self = $(this), _self_id = $self.attr("id"), _get_data = void 0 === set_data ? eval(_self_id) : set_data, selectCanvas = document.getElementById(_self_id).getContext("2d"), chart_data = [], i = 0; i < _get_data.datasets.length; i++) chart_data.push({
                backgroundColor: _get_data.datasets[i].background,
                borderWidth: 2,
                borderColor: _get_data.datasets[i].borderColor,
                hoverBorderColor: _get_data.datasets[i].borderColor,
                data: _get_data.datasets[i].data
            });
            var chart = new Chart(selectCanvas, {
                type: "doughnut",
                data: {
                    labels: _get_data.labels,
                    datasets: chart_data
                },
                options: {
                    legend: {
                        display: !!_get_data.legend && _get_data.legend,
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            fontColor: "#6783b8"
                        }
                    },
                    rotation: -1.5,
                    cutoutPercentage: 70,
                    maintainAspectRatio: !1,
                    tooltips: {
                        enabled: !0,
                        callbacks: {
                            title: function(a, e) {
                                return e.labels[a[0].index]
                            },
                            label: function(a, e) {
                                return e.datasets[a.datasetIndex].data[a.index] + " " + _get_data.dataUnit
                            }
                        },
                        backgroundColor: "#fff",
                        borderColor: "#eff6ff",
                        borderWidth: 2,
                        titleFontSize: 13,
                        titleFontColor: "#6783b8",
                        titleMarginBottom: 6,
                        bodyFontColor: "#9eaecf",
                        bodyFontSize: 12,
                        bodySpacing: 4,
                        yPadding: 10,
                        xPadding: 10,
                        footerMarginTop: 0,
                        displayColors: !1
                    }
                }
            })
        })
    }
    function orderOverviewChart(selector, set_data) {
        var $selector = $(selector || ".order-overview-chart");
        $selector.each(function () {
            for (
                var $self = $(this),
                    _self_id = $self.attr("id"),
                    _get_data = void 0 === set_data ? eval(_self_id) : set_data,
                    _d_legend = void 0 !== _get_data.legend && _get_data.legend,
                    selectCanvas = document.getElementById(_self_id).getContext("2d"),
                    chart_data = [],
                    i = 0;
                i < _get_data.datasets.length;
                i++
            )
                chart_data.push({
                    label: _get_data.datasets[i].label,
                    data: _get_data.datasets[i].data,
                    backgroundColor: _get_data.datasets[i].color,
                    borderWidth: 2,
                    borderColor: "transparent",
                    hoverBorderColor: "transparent",
                    borderSkipped: "bottom",
                    barPercentage: 0.8,
                    categoryPercentage: 0.6,
                });
            var chart = new Chart(selectCanvas, {
                type: "bar",
                data: { labels: _get_data.labels, datasets: chart_data },
                options: {
                    legend: { display: !!_get_data.legend && _get_data.legend, labels: { boxWidth: 30, padding: 20, fontColor: "#6783b8" } },
                    maintainAspectRatio: !1,
                    tooltips: {
                        enabled: !0,
                        callbacks: {
                            title: function (e, a) {
                                return a.datasets[e[0].datasetIndex].label;
                            },
                            label: function (e, a) {
                                return a.datasets[e.datasetIndex].data[e.index] + " " + _get_data.dataUnit;
                            },
                        },
                        backgroundColor: "#eff6ff",
                        titleFontSize: 13,
                        titleFontColor: "#6783b8",
                        titleMarginBottom: 6,
                        bodyFontColor: "#9eaecf",
                        bodyFontSize: 12,
                        bodySpacing: 4,
                        yPadding: 10,
                        xPadding: 10,
                        footerMarginTop: 0,
                        displayColors: !1,
                    },
                    scales: {
                        yAxes: [
                            {
                                display: !0,
                                stacked: !!_get_data.stacked && _get_data.stacked,
                                ticks: {
                                    beginAtZero: !0,
                                    fontSize: 11,
                                    fontColor: "#9eaecf",
                                    padding: 10,
                                },
                                gridLines: { color: "#e5ecf8", tickMarkLength: 0, zeroLineColor: "#e5ecf8" },
                            },
                        ],
                        xAxes: [
                            {
                                display: !0,
                                stacked: !!_get_data.stacked && _get_data.stacked,
                                ticks: { fontSize: 9, fontColor: "#9eaecf", source: "auto", padding: 10 },
                                gridLines: { color: "transparent", tickMarkLength: 0, zeroLineColor: "transparent" },
                            },
                        ],
                    },
                },
            });
        });
    }
    orderOverviewChart();
    function lineChart(selector, set_data) {
        var $selector = $(selector || ".line-chart");
        $selector.each(function() {
            for (var $self = $(this), _self_id = $self.attr("id"), _get_data = void 0 === set_data ? eval(_self_id) : set_data, selectCanvas = document.getElementById(_self_id).getContext("2d"), chart_data = [], i = 0; i < _get_data.datasets.length; i++) chart_data.push({
                label: _get_data.datasets[i].label,
                tension: _get_data.lineTension,
                backgroundColor: _get_data.datasets[i].background,
                borderWidth: 3,
                borderColor: _get_data.datasets[i].color,
                pointBorderColor: _get_data.datasets[i].color,
                pointBackgroundColor: "#fff",
                pointHoverBackgroundColor: "#fff",
                pointHoverBorderColor: _get_data.datasets[i].color,
                pointBorderWidth: 3,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 2,
                pointRadius: 4,
                pointHitRadius: 4,
                data: _get_data.datasets[i].data
            });
            var chart = new Chart(selectCanvas, {
                type: "line",
                data: {
                    labels: _get_data.labels,
                    datasets: chart_data
                },
                options: {
                    legend: {
                        display: !!_get_data.legend && _get_data.legend,
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            fontColor: "#6783b8"
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        enabled: !0,
                        callbacks: {
                            title: function(a, t) {
                                return t.labels[a[0].index]
                            },
                            label: function(a, t) {
                                return t.datasets[a.datasetIndex].data[a.index] + " " + _get_data.dataUnit
                            }
                        },
                        backgroundColor: "#eff6ff",
                        titleFontSize: 13,
                        titleFontColor: "#6783b8",
                        titleMarginBottom: 6,
                        bodyFontColor: "#9eaecf",
                        bodyFontSize: 12,
                        bodySpacing: 4,
                        yPadding: 10,
                        xPadding: 10,
                        footerMarginTop: 0,
                        displayColors: !1
                    },
                    scales: {
                        yAxes: [{
                            display: !0,
                            ticks: {
                                beginAtZero: !1,
                                fontSize: 12,
                                fontColor: "#9eaecf",
                                padding: 10
                            },
                            gridLines: {
                                color: "#e5ecf8",
                                tickMarkLength: 0,
                                zeroLineColor: "#e5ecf8"
                            }
                        }],
                        xAxes: [{
                            display: !0,
                            ticks: {
                                fontSize: 12,
                                fontColor: "#9eaecf",
                                source: "auto",
                                padding: 5
                            },
                            gridLines: {
                                color: "transparent",
                                tickMarkLength: 10,
                                zeroLineColor: "#e5ecf8",
                                offsetGridLines: !0
                            }
                        }]
                    }
                }
            })
        })
    }
    lineChart();
function doughnutChart(selector, set_data) {
    var $selector = $(selector || ".doughnut-chart");
    $selector.each(function() {
        for (var $self = $(this), _self_id = $self.attr("id"), _get_data = void 0 === set_data ? eval(_self_id) : set_data, selectCanvas = document.getElementById(_self_id).getContext("2d"), chart_data = [], i = 0; i < _get_data.datasets.length; i++) chart_data.push({
            backgroundColor: _get_data.datasets[i].background,
            borderWidth: 2,
            borderColor: _get_data.datasets[i].borderColor,
            hoverBorderColor: _get_data.datasets[i].borderColor,
            data: _get_data.datasets[i].data
        });
        var chart = new Chart(selectCanvas, {
            type: "doughnut",
            data: {
                labels: _get_data.labels,
                datasets: chart_data
            },
            options: {
                legend: {
                    display: !!_get_data.legend && _get_data.legend,
                    labels: {
                        boxWidth: 12,
                        padding: 20,
                        fontColor: "#6783b8"
                    }
                },
                rotation: 1,
                cutoutPercentage: 40,
                maintainAspectRatio: !1,
                tooltips: {
                    enabled: !0,
                    callbacks: {
                        title: function(a, t) {
                            return t.labels[a[0].index]
                        },
                        label: function(a, t) {
                            return t.datasets[a.datasetIndex].data[a.index]
                        }
                    },
                    backgroundColor: "#eff6ff",
                    titleFontSize: 13,
                    titleFontColor: "#6783b8",
                    titleMarginBottom: 6,
                    bodyFontColor: "#9eaecf",
                    bodyFontSize: 12,
                    bodySpacing: 4,
                    yPadding: 10,
                    xPadding: 10,
                    footerMarginTop: 0,
                    displayColors: !1
                }
            }
        })
    })
}
doughnutChart();
NioApp.BS.progress('[data-progress]')

!function (NioApp, $) {
  "use strict"; // DataTable Init

  function month_v(selector, set_data) {
    var $selector = selector ? $(selector) : $('.monthly-visits');
    $selector.each(function () {
      var $self = $(this),
          _self_id = $self.attr('id'),
          _get_data = typeof set_data === 'undefined' ? eval(_self_id) : set_data;

      var selectCanvas = document.getElementById(_self_id).getContext("2d");
      var chart_data = [];

      for (var i = 0; i < _get_data.datasets.length; i++) {
        chart_data.push({
          label: _get_data.datasets[i].label,
          tension: .4,
          backgroundColor: NioApp.hexRGB(_get_data.datasets[i].color, .3),
          borderWidth: 2,
          borderColor: _get_data.datasets[i].color,
          pointBorderColor: 'transparent',
          pointBackgroundColor: 'transparent',
          pointHoverBackgroundColor: "#fff",
          pointHoverBorderColor: _get_data.datasets[i].color,
          pointBorderWidth: 2,
          pointHoverRadius: 4,
          pointHoverBorderWidth: 2,
          pointRadius: 4,
          pointHitRadius: 4,
          data: _get_data.datasets[i].data
        });
      }

      var chart = new Chart(selectCanvas, {
        type: 'line',
        data: {
          labels: _get_data.labels,
          datasets: chart_data
        },
        options: {
          legend: {
            display: false
          },
          maintainAspectRatio: false,
          tooltips: {
            enabled: true,
            callbacks: {
              title: function title(tooltipItem, data) {
                return false;
              },
              label: function label(tooltipItem, data) {
                return data.datasets[tooltipItem.datasetIndex]['data'][tooltipItem['index']] + ' ' + _get_data.dataUnit;
              }
            },
            backgroundColor: '#fff',
            titleFontSize: 11,
            titleFontColor: '#6783b8',
            titleMarginBottom: 4,
            bodyFontColor: '#9eaecf',
            bodyFontSize: 10,
            bodySpacing: 3,
            yPadding: 8,
            xPadding: 8,
            footerMarginTop: 0,
            displayColors: false
          },
          scales: {
            yAxes: [{
              display: false,
              ticks: {
                beginAtZero: true
              }
            }],
            xAxes: [{
              display: false
            }]
          }
        }
      });
    });
  } // init investProfit


  NioApp.coms.docReady.push(function () {
    month_v();
  });
}(NioApp, jQuery);
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

$(".avatar_custom").change(function() {
  var input = this;
  var $this = $(this);
  var $parent = $this.closest('.profile-avatar');
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
        $parent.find('img').css('display', 'block');
        $parent.find('img').attr("src", ""+e.target.result+"");
        $parent.addClass('active');
        $parent.find('label').text('You' + "'ve" + ' selected an image');
    }
    reader.readAsDataURL(input.files[0]);
  }
});
$(".upload").change(function() {
  var input = this;
  var $this = $(this);
  var $parent = $this.closest('.image-upload');
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    
    reader.onload = function(e) {
        $parent.find('img').attr("src", ""+e.target.result+"");
        $parent.find('img').css('display', 'block');
        $parent.addClass('active');
        $parent.find('label').text('You' + "'ve" + ' selected an image');
    }
    reader.readAsDataURL(input.files[0]);
  }
});
$(".file-image-upload").change(function() {
    var $close = $(this).closest('.avatar-upload');
    var input = this;

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $close.find('.image-preview').css('background-image', 'url('+e.target.result +')');
            $close.find('.image-preview').hide();
            $close.find('.image-preview').fadeIn(650);
        }
        reader.readAsDataURL(input.files[0]);
    }
});
$('.ecom-toast').each(function(){
  $(this).toast({
    autohide: true,
    delay: 3000,
  });


  $(this).on('show.bs.toast', function () {
    $(this).closest('.toast-outer').removeClass('d-none');
    $(this).closest('.toast-outer').addClass('d-flex');
  });

  $(this).toast('show');

  $(this).on('hidden.bs.toast', function () {
    $(this).closest('.toast-outer').removeClass('d-flex');
    $(this).closest('.toast-outer').addClass('d-none');
  });
});
})(jQuery);