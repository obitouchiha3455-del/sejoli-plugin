(function( $ ) {
    'use strict';

    $(document).ready(function($){

        // $(window).scroll(function() {
        //   var stickyPoint = 200; // Misalnya 200px
        //   var scroll = $(window).scrollTop();
          
        //   if (scroll >= stickyPoint) {
        //     $('.floating-side').addClass('fixed');
        //     $('.produk-dibeli').hide();
        //     $('.floating-side .produk-dibeli').show();
        //   } else {
        //     $('.floating-side').removeClass('fixed');
        //     $('.produk-dibeli').show();
        //     $('.floating-side .produk-dibeli').hide();
        //   }
        // });
    
        function incrementValue(e) {
            e.preventDefault();
            var fieldName = $(e.target).data('field');
            var parent = $(e.target).closest('div');
            var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

            if (!isNaN(currentVal)) {
                parent.find('input[name=' + fieldName + ']').val(currentVal + 1);
            } else {
                parent.find('input[name=' + fieldName + ']').val(0);
            }
        }

        function decrementValue(e) {
            e.preventDefault();
            var fieldName = $(e.target).data('field');
            var parent = $(e.target).closest('div');
            var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

            if (!isNaN(currentVal) && currentVal > 0) {
                parent.find('input[name=' + fieldName + ']').val(currentVal - 1);
            } else {
                parent.find('input[name=' + fieldName + ']').val(0);
            }
        }

        $(document).on('click', '.button-plus', function(e) {
            incrementValue(e);
        });

        $(document).on('click', '.button-minus', function(e) {
            decrementValue(e);
        });

        $('.pesanan-anda').on('click', 'a.detail-pesanan-link', function(e) {
            e.preventDefault();

            var $t = $('.pesanan-anda table');

            if ($t.is(':visible')) {
                $t.slideUp();
                $('a.detail-pesanan i').removeClass('angle up icon');
                $('a.detail-pesanan i').addClass('angle down icon');
            } else {
                $t.slideDown();
                $('a.detail-pesanan i').removeClass('angle down icon');
                $('a.detail-pesanan i').addClass('angle up icon');
            }
        });

        $('.ui.radio.checkbox').checkbox();

        $('.ui.dropdown').dropdown();

        $(document).on('click','.kode-diskon-form-toggle a',function(e){
            e.preventDefault();

            $(this).parent().hide();
            $('.kode-diskon-form').show();
        });

        $(document).on('click','.login-form-toggle p a',function(e){
            e.preventDefault();

            $('.login-form-toggle').hide();
            $('.login-form').show();
        });

        if ( $('.countdown-payment-run').length > 0 ) {
            var datetime = $('.countdown-payment-run').data('datetime');
            var time_left = 0;
            var time_start = new Date();
            var time_end = new Date(datetime);

            if ( time_end > time_start ) {
                time_left = ( time_end.getTime() - time_start.getTime() ) * 0.001;
            }

            var countdown = $('.countdown-payment-run').FlipClock(time_left, {
                countdown: true,
                onStart: function() {
                    // console.log('countdown start');
                },
                onStop: function() {
                    // console.log('countdown stop');
                },
            });

            $('.hours   .flip-clock-label').html(sejoli_checkout.countdown_text.jam);
            $('.minutes .flip-clock-label').html(sejoli_checkout.countdown_text.menit);
            $('.seconds .flip-clock-label').html(sejoli_checkout.countdown_text.detik);
        }

        $(document).on('click',".close.icon",function(){
            $(this).parent().hide();
        });

        $(document).on('click', '.copy-btn', function(e) {
            e.preventDefault();

            var targetSelector = $(this).data('target');
            var targetElement = document.querySelector(targetSelector);

            var textToCopy = targetElement ? targetElement.innerText.trim() : '';

            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(textToCopy).select();
            document.execCommand("copy");
            $temp.remove();

            $(this).popup({
                title: 'Copied!',
                on: 'manual',
                exclusive: true
            }).popup('show');

            var button = $(this);
            setTimeout(function () {
                button.popup('hide');
            }, 3000);
        });

        $('.select2-filled').select2({
        });

        if(sejoli_checkout.g_recaptcha_sitekey && sejoli_checkout.g_recaptcha_enabled){

            grecaptcha.ready(() => {
                grecaptcha.execute(sejoli_checkout.g_recaptcha_sitekey, { action: 'checkout' }).then(token => {
                    document.querySelector('#recaptchaResponse').value = token;
                });
            });

        }

    });

})( jQuery );

var $                = jQuery,
    timeout_redirect = 500;

function sejoliSaBlockUI( message = sejoli_checkout.please_wait, target = '') {
    message = ('' === message) ? sejoli_checkout.please_wait : message;
    target  = ('' === target) ? 'body' : target;

    jQuery(target).block({
        message: '<p class="sejolisa-block-ui"><i class="spinner loading icon"></i>'+message+'</p>',
        css: {
            border: 0,
            backgroundColor: 'transparent',
        }
    });
}

function sejoliSaUnblockUI(target = '') {
    target = ('' === target) ? 'body' : target;
    jQuery(target).unblock();

}

function sejoliSaAjaxReturnError(jqXHRstatus = '', textStatus = '', jqXHRResponseText = '') {

    var errMessage = '';

    switch (jqXHRstatus) {
        case 0:
            errMessage = ['Not connect.\n Verify Network. Please contact website owner for assistance.'];
            break;
        case 404:
            errMessage = ['Requested page not found. [404]. Please contact website owner for assistance.'];
            break;
         case 500:
            errMessage = ['Internal Server Error [500]. Please contact website owner for assistance.'];
            break;
        default:
            errMessage = ['Uncaught Error.\n' + jqXHRResponseText + '. Please contact website owner for assistance.'];
            break;
    }

    switch (textStatus) {
        case 'parsererror':
            errMessage = ['Requested JSON parse failed. Please contact website owner for assistance.'];
            break;
        case 'timeout':
            errMessage = ['Time out error. Please contact website owner for assistance.'];
            break;
         case 'abort':
            errMessage = ['Ajax request aborted. Please contact website owner for assistance.'];
            break;
        default:
            errMessage = ['Uncaught Error.\n' + jqXHRResponseText + '. Please contact website owner for assistance.'];
            break;
    }

    return errMessage;

}

let sejoliSaCheckout = {
    init: function() {
        this.checkoutQuantity();
        this.checkoutBumpProduct();
        this.getCalculate();
        this.getPaymentGateway();
        this.applyCoupon();
        this.submitCheckout();
        this.getCurrentUser();
        this.submitLogin();
        this.deleteCoupon();
        this.changePaymentGateway();
    },

    vars : {
        delay : 0,
        ajax: ''
    },

    func : {
        setData: function(process_type, nonce) {
            return {
                'process-action'   : process_type,
                coupon             : $('#apply_coupon').val(),
                product_id         : sejoli_checkout.product_id,
                payment_gateway    : $('input[name="payment_gateway"]:checked').val(),
                quantity           : $('#qty').val(),
                sejoli_ajax_nonce  : nonce,
                price              : $('#price').val(),
                wallet             : $('#use-wallet').is(':checked'),
                recaptcha_response : $('#recaptchaResponse').val(),
            }
        },
        applyCoupon: function() {

            var data = sejoliSaCheckout.func.setData('apply-coupon', sejoli_checkout.ajax_nonce.apply_coupon);
            var bump_ids = document.getElementsByName('bump_product');

            for (var i = 0, length = bump_ids.length; i < length; i++) {
                if (bump_ids[i].checked) {
                    data.product_id = bump_ids[i].value;
                    data.main_product_id = sejoli_checkout.product_id;
                    
                    break;
                }
            }

            sejoliSaCheckout.vars.ajax = $.ajax({
                url: sejoli_checkout.ajax_url,
                type: 'post',
                data: data,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                    sejoliSaBlockUI('', '.kode-diskon');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');
                        sejoliSaUnblockUI('.kode-diskon');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".coupon-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');
                    sejoliSaUnblockUI('.kode-diskon');

                    var alert = {};

                    var today    = new Date();
                    var date     = today.getFullYear()+'-'+("0" + (today.getMonth() + 1)).slice(-2)+'-'+("0" + today.getDate()).slice(-2);
                    var time     = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                    var dateTime = date+' '+time;

                    if ( 
                        (response.valid && response.data.coupon.limit_use > 0 && response.data.coupon.limit_date != null && response.data.coupon.limit_use != response.data.coupon.usage && new Date(dateTime) < new Date(response.data.coupon.limit_date) && response.data.coupon.status == 'active') ||
                        (response.valid && response.data.coupon.limit_use > 0 && response.data.coupon.limit_date == null && response.data.coupon.limit_use != response.data.coupon.usage && response.data.coupon.status == 'active') ||
                        (response.valid && response.data.coupon.limit_use == 0 && response.data.coupon.limit_date != null && new Date(dateTime) < new Date(response.data.coupon.limit_date) && response.data.coupon.status == 'active') ||
                        (response.valid && response.data.coupon.limit_use == 0 && response.data.coupon.limit_date == null && response.data.coupon.status == 'active')
                    ) {

                        alert.type = 'success';

                        if(0 === parseInt(response.data.raw_total)) {
                            $('.metode-pembayaran').hide();
                            $('input[name="payment_gateway"]').prop('checked', false);
                        } else {
                            $('.metode-pembayaran').show();
                            sejoliSaCheckout.func.changePaymentGateway();
                        }

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.data);

                        $(".produk-dibeli tbody").html(htmlOutput);
                        $(".total-holder").html( response.data.total );

                        var substr = response.data.product.bump_sales;
                        $.each(substr , function(index, val) { 
                          $(".bump-total-holder-" + val.ID).html(val.price);
                        });

                        if(response.data.affiliate) {
                            $(".affiliate-name").html(sejoli_checkout.affiliasi_oleh + ' ' + response.data.affiliate);
                        }

                        if(0 === parseInt(response.data.raw_total)) {

                            $('.metode-pembayaran').hide();
                            $('input[name="payment_gateway"]').prop('checked', false);
                            setTimeout(() => {
                                $('.beli-sekarang .submit-button').removeAttr('disabled','disabled');
                            }, 500)

                        } else {

                            $('.metode-pembayaran').show();
                            sejoliSaCheckout.func.changePaymentGateway();

                        }

                    } else {

                        alert.type = 'error';
  
                    }
                    
                    alert.messages = response.messages;
                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);

                    $(".coupon-alert-holder").html(htmlOutput);
                    
                    $(document).trigger('sejoli:calculate');

                    setTimeout(function(){
                        $('.free-shipping-price').css("text-decoration", "line-through");
                        
                        var tdElement = $('.hapus-kupon').closest('tr').find('td:eq(1)');

                        if (!tdElement.text().trim().startsWith('-')) {
                            tdElement.prepend('- ');
                        }
                    }, 500);

                }
            });
        },
        deleteCoupon: function() {

            var data = sejoliSaCheckout.func.setData('delete-coupon', sejoli_checkout.ajax_nonce.delete_coupon);
            var bump_ids = document.getElementsByName('bump_product');

            for (var i = 0, length = bump_ids.length; i < length; i++) {
                if (bump_ids[i].checked) {
                    data.product_id = bump_ids[i].value;
                    data.main_product_id = sejoli_checkout.product_id;
                    
                    break;
                }
            }

            $.ajax({
                url: sejoli_checkout.ajax_url,
                type: 'post',
                data: data,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".coupon-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    var alert = {};

                    if ( response.valid ) {
                        alert.type = 'success';

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.data);
                        $(".produk-dibeli tbody").html(htmlOutput);
                        $(".total-holder").html( response.data.total );
                        
                        var substr = response.data.product.bump_sales;
                        $.each(substr , function(index, val) { 
                          $(".bump-total-holder-" + val.ID).html(val.price);
                        });

                        $("#apply_coupon").val("");
                    } else {
                        alert.type = 'error';
                    }

                    alert.messages = response.messages;

                    if(0 === parseInt(response.data.raw_total)) {
                        $('.metode-pembayaran').hide();
                        $('input[name="payment_gateway"]').prop('checked', false);
                        setTimeout(() => {
                            $('.beli-sekarang .submit-button').removeAttr('disabled','disabled');
                        }, 500)
                    } else {
                        $('.metode-pembayaran').show();
                        sejoliSaCheckout.func.changePaymentGateway();
                    }

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);

                    $(".coupon-alert-holder").html(htmlOutput);

                    $(document).trigger('sejoli:calculate');

                }
            });

        },
        submitCheckout: function(button) {

            var fb_pixel_event = button.data('fb-pixel-event');

            var data = sejoliSaCheckout.func.setData('checkout', sejoli_checkout.ajax_nonce.submit_checkout);

            var qty = $('#qty').val();
            if ( typeof qty !== 'undefined' ) {
                data.quantity = qty;
            }

            var product_id = $('#product_id').val();
            if ( typeof product_id !== 'undefined' ) {
                data.product_id = product_id;
            }

            var coupon = $('#coupon').val();
            if ( typeof coupon !== 'undefined' ) {
                data.coupon = coupon;
            }
            var user_name = $('#user_name').val();
            if ( typeof user_name !== 'undefined' ) {
                data.user_name = user_name;
            }
            var user_email = $('#user_email').val();
            if ( typeof user_email !== 'undefined' ) {
                data.user_email = user_email;
            }
            var user_password = $('#user_password').val();
            if ( typeof user_password !== 'undefined' ) {
                data.user_password = user_password;
            }

            var user_phone = $('#user_phone').val();
            if ( typeof user_phone !== 'undefined' ) {
                data.user_phone = user_phone;
            }

            $.ajax({
                url : sejoli_checkout.ajax_url,
                type: 'post',
                data: data,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                    button.attr('disabled', true);
                },
                complete: function(jqXHR, textStatus) {

                    button.attr('disabled', false);

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    var alert = {};

                    button.attr('disabled', false);

                    if ( response.valid && textStatus === 'success' ) {

                        if(typeof sejoli_fb_pixel !== 'undefined') {

                            fbq('init', sejoli_fb_pixel.id);
                            fbq('track', sejoli_fb_pixel.event.submit, {
                                content_ids : sejoli_fb_pixel.product_id,
                                content_type: sejoli_fb_pixel.content_type,
                                currency    : sejoli_fb_pixel.currency,
                                value       : response.data.order.grand_total
                            });

                            if (true === sejoli_fb_pixel.affiliate_active && typeof sejoli_fb_pixel.affiliate_id !== 'undefined') {

                                fbq('init', sejoli_fb_pixel.affiliate_id);
                                fbq('track', sejoli_fb_pixel.event.submit, {
                                    content_ids : sejoli_fb_pixel.product_id,
                                    content_type: sejoli_fb_pixel.content_type,
                                    currency    : sejoli_fb_pixel.currency,
                                    value       : response.data.order.grand_total
                                });

                            }
                        }

                        alert.type = 'success';

                        setTimeout(function(){
                            window.location.replace(response.redirect_link);
                        }, timeout_redirect);

                    } else {

                        alert.type = 'error';
                        sejoliSaUnblockUI('.element-blockable');
                    }

                    alert.messages = response.messages;

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                }
            });

        },
        changePaymentGateway: function(element) {

            var data = sejoliSaCheckout.func.setData('change-payment', sejoli_checkout.ajax_nonce.get_calculate);
            var bump_ids = document.getElementsByName('bump_product');

            for (var i = 0, length = bump_ids.length; i < length; i++) {
                if (bump_ids[i].checked) {
                    data.product_id = bump_ids[i].value;
                    
                    break;
                }
            }

            $.ajax({
                url: sejoli_checkout.ajax_url,
                type: 'post',
                data: data,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);
                        $(".produk-dibeli tbody").html(htmlOutput);
                        $(".total-holder").html(response.calculate.total);

                        $(document).trigger('sejoli:calculate');

                    }

                }
            });
        }
    },

    checkoutQuantity: function() {

        $(document).on('change','input.change-calculate-affect-qty',function() {

            var data = sejoliSaCheckout.func.setData('change-calculate', sejoli_checkout.ajax_nonce.get_calculate);
            var bump_ids = document.getElementsByName('bump_product');

            for (var i = 0, length = bump_ids.length; i < length; i++) {
                if (bump_ids[i].checked) {
                    data.product_id = bump_ids[i].value;
                    
                    break;
                }
            }

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : data,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {
                    sejoliSaUnblockUI('.element-blockable');

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);

                        $(".produk-dibeli tbody").html( htmlOutput );

                        $(".total-holder").html( response.calculate.total );

                        $(document).trigger('sejoli:calculate');
                    }
                }
            });

        });

        $(document).on('click','.button-plus',function() {

            var data = sejoliSaCheckout.func.setData('change-calculate', sejoli_checkout.ajax_nonce.get_calculate);
            var bump_ids = document.getElementsByName('bump_product');

            for (var i = 0, length = bump_ids.length; i < length; i++) {
                if (bump_ids[i].checked) {
                    data.product_id = bump_ids[i].value;
                    
                    break;
                }
            }

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : data,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
        
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);

                        $(".produk-dibeli tbody").html( htmlOutput );

                        $(".total-holder").html( response.calculate.total );

                        $(document).trigger('sejoli:calculate');
                    }

                }
            });

        });

        $(document).on('click','.button-minus',function() {

            var data = sejoliSaCheckout.func.setData('change-calculate', sejoli_checkout.ajax_nonce.get_calculate);
            var bump_ids = document.getElementsByName('bump_product');

            for (var i = 0, length = bump_ids.length; i < length; i++) {
                if (bump_ids[i].checked) {
                    data.product_id = bump_ids[i].value;
                    
                    break;
                }
            }

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : data,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
                    
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);

                        $(".produk-dibeli tbody").html( htmlOutput );

                        $(".total-holder").html( response.calculate.total );

                        $(document).trigger('sejoli:calculate');
                    }

                }
            });

        });

    },

    checkoutBumpProduct: function() {

        $(document).on('click','.cancel-add-product-bump',function(event) {
            event.preventDefault();

            var data = sejoliSaCheckout.func.setData('change-calculate', sejoli_checkout.ajax_nonce.get_calculate);

            var bump_ids = document.getElementsByName('bump_product');

            for (var i = 0, length = bump_ids.length; i < length; i++) {
                bump_ids[i].checked = false;
                bump_ids[i].previous = bump_ids[i].checked;

                data.product_id = sejoli_checkout.product_id;
                // $('#bump-total-holder-' + bump_ids[i].value).show();
            }

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : data,
                beforeSend : function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
                    
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    $('.cancel-add-product-bump').hide();

                    var productID = $('input#product_id').val(data.product_id);

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#bump-produk-template");
                        var htmlOutput = template.render(response.calculate.product.bump_sales);

                        $(".bump-produk tbody").html(htmlOutput);

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);

                        $(".produk-dibeli tbody").html( htmlOutput );
                        $(".total-holder").html( response.calculate.total );

                        $(document).trigger('sejoli:calculate');

                    }

                }
            });

        });

        $(document).on('change','input.bump-product',function() {

            var data = sejoliSaCheckout.func.setData('change-calculate', sejoli_checkout.ajax_nonce.get_calculate);
            var bump_ids = document.getElementsByName('bump_product');

            $('.cancel-add-product-bump').hide();
            $('.coupon-alert-holder').hide();

            for (var i = 0, length = bump_ids.length; i < length; i++) {
                data.product_id = bump_ids[i].value;
                if (bump_ids[i].checked) {
                    $('#cancel-' + $(this).attr('id')).show();
                    $('#bump-total-holder-' + data.product_id).hide();
                    
                    break;
                } else {
                    $('#cancel-' + $(this).attr('id')).hide();
                    $('#bump-total-holder-' + data.product_id).show();
                }
            }

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : data,
                beforeSend : function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    var productID = $('input#product_id').val(bump_ids[i].value);

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);

                        $(".produk-dibeli tbody").html( htmlOutput );
                        // 
                        $(".total-holder").html( response.calculate.total );

                        $(document).trigger('sejoli:calculate');
                    }

                }
            });

        });

    },

    getCalculate: function() {

        var data = sejoliSaCheckout.func.setData('calculate', sejoli_checkout.ajax_nonce.get_calculate);
        setTimeout(function(){ 
            $('.metode-pembayaran').hide();
            $('input[name="payment_gateway"]').prop('checked', false);
        }, 0);

        setTimeout(function(){ 
            var payment_gateway = $('input[name="payment_gateway"]:checked').val();
            data.payment_gateway = payment_gateway;

            $.ajax({
                url : sejoli_checkout.ajax_url,
                type: 'post',
                data: data,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
                    
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);

                        $(".produk-dibeli tbody").html(htmlOutput);

                        var template   = $.templates("#bump-produk-template");
                        var htmlOutput = template.render(response.calculate.product.bump_sales);

                        $(".bump-produk tbody").html(htmlOutput);

                        var template   = $.templates("#apply-coupon-template");
                        var htmlOutput = template.render();
                        $(".kode-diskon .data-holder").html(htmlOutput);

                        var template   = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);

                        if(0 === parseInt(response.calculate.raw_total)) {
                            $('.metode-pembayaran').hide();
                            $('input[name="payment_gateway"]').prop('checked', false);
                        } else {
                            $('.metode-pembayaran').show();
                            sejoliSaCheckout.func.changePaymentGateway();
                        }

                        if(response.calculate.affiliate) {
                            $(".affiliate-name").html(sejoli_checkout.affiliasi_oleh + ' ' + response.calculate.affiliate);
                        }

                        $(".total-holder").html( response.calculate.total );

                    }

                }
            })

         }, 3000);

    },

    getCalculateAfterUseWallet: function() {

        var data = sejoliSaCheckout.func.setData('change-wallet', sejoli_checkout.ajax_nonce.get_calculate);
        var payment_gateway = $('input[name="payment_gateway"]:checked').val();
        var coupon = $('#apply_coupon').val();
        data.payment_gateway = payment_gateway;
        data.coupon = coupon;

        $.ajax({
            url : sejoli_checkout.ajax_url,
            type: 'post',
            data: data,
            beforeSend: function() {
                sejoliSaBlockUI('', '.element-blockable');
            },
            complete: function(jqXHR, textStatus) {

                if( textStatus === "success" ) {

                    return true;

                } else {

                    sejoliSaUnblockUI('.element-blockable');

                }

            },
            error: function(jqXHR, textStatus, errorThrown) {

                var alert  = {};

                alert.type = 'error';

                alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                var template   = $.templates("#alert-template");
                var htmlOutput = template.render(alert);
                $(".checkout-alert-holder").html(htmlOutput);

            },
            success: function(response, textStatus, errorThrown) {

                sejoliSaUnblockUI('.element-blockable');

                if ( typeof response.calculate !== 'undefined' ) {

                    if(coupon) {
                        sejoliSaCheckout.func.applyCoupon();
                        setTimeout(function(){
                            var tdElement = $('.hapus-kupon').closest('tr').find('td:eq(1)');

                            if (!tdElement.text().trim().startsWith('-')) {
                                tdElement.prepend('- ');
                            }
                        }, 1000);
                    } else {
                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);

                        $(".produk-dibeli tbody").html(htmlOutput);

                        var template   = $.templates("#apply-coupon-template");
                        var htmlOutput = template.render();
                        $(".kode-diskon .data-holder").html(htmlOutput);

                        var template   = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);

                        if(response.calculate.affiliate) {
                            $(".affiliate-name").html(sejoli_checkout.affiliasi_oleh + ' ' + response.calculate.affiliate);
                        }

                        $(".total-holder").html( response.calculate.total );
                    }

                }

            }
        })

    },

    getPaymentGateway: function() {

        $.ajax({
            url: sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                product_id       : sejoli_checkout.product_id,
                sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.get_payment_gateway,
            },
            complete: function(jqXHR, textStatus) {

                if( textStatus === "success" ) {

                    return true;

                } else {

                    sejoliSaUnblockUI('.element-blockable');

                }

            },
            error: function(jqXHR, textStatus, errorThrown) {

                var alert  = {};
               
                alert.type = 'error';

                alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                var template   = $.templates("#alert-template");
                var htmlOutput = template.render(alert);
                $(".checkout-alert-holder").html(htmlOutput);

            },
            success: function(response, textStatus, errorThrown) {

                if ( typeof response.payment_gateway !== 'undefined' ) {

                    var template   = $.templates("#metode-pembayaran-template");
                    var htmlOutput = template.render(response);
                    
                    $(".metode-pembayaran .data-holder").html(htmlOutput);

                    $('.ui.radio.checkbox').checkbox();

                }

            }
        });

    },

    applyCoupon: function() {

        sejoliSaCheckout.vars.delay = 0;

        $(document).on('submit','.kode-diskon-form',function(e){
            e.preventDefault();
            sejoliSaCheckout.func.applyCoupon();
        });

        $(document).on('click','.submit-coupon',function(e){
            e.preventDefault();
            sejoliSaCheckout.func.applyCoupon();
        });

        $(document).on('keyup', '#apply_coupon', function(){

            if(typeof sejoliSaCheckout.vars.ajax.abort === 'function')
            { sejoliSaCheckout.vars.ajax.abort(); }

            clearTimeout(sejoliSaCheckout.vars.delay);

            sejoliSaCheckout.vars.delay = setTimeout(function(){
                sejoliSaCheckout.func.applyCoupon();
            },1000)

        })
    },

    submitCheckout: function() {

        $(document).on('click','.beli-sekarang .submit-button',function(e){
            e.preventDefault();
            sejoliSaCheckout.func.submitCheckout($(this));
        });

        $(document).on('click', '.order-detail-trigger', function(e){
            e.preventDefault();
            $('.order-modal-holder').modal('show');
        });

        $(document).on('click', '.close-popup', function(e){
            e.preventDefault();
            $('.order-modal-holder').modal('hide');
        });
    },

    submitLogin: function() {

        $(document).on('click','.submit-login',function(e){
            e.preventDefault();

            var login_email    = $('#login_email').val();
            var login_password = $('#login_password').val();

            $.ajax({
                url : sejoli_checkout.ajax_url,
                type: 'post',
                data: {
                    login_email      : login_email,
                    login_password   : login_password,
                    sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.submit_login
                },
                beforeSend: function() {
                    sejoliSaBlockUI('', '.login-form');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.login-form');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
                    
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".login-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.login-form');

                    var alert = {};

                    if ( response.success ) {

                        alert.type = 'success';

                        setTimeout(function(){
                            location.reload(true);
                        }, 1000);

                    } else {

                        alert.type = 'error';

                    }

                    alert.messages = response.data;

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".login-alert-holder").html(htmlOutput);
                }

            })

        });

    },

    getCurrentUser: function() {

        $.ajax({
            url : sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.get_current_user,
            },
            complete: function(jqXHR, textStatus) {

                if( textStatus === "success" ) {

                    return true;

                } else {

                    sejoliSaUnblockUI('.element-blockable');

                }

            },
            error: function(jqXHR, textStatus, errorThrown) {

                var alert  = {};
               
                alert.type = 'error';

                alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                var template   = $.templates("#alert-template");
                var htmlOutput = template.render(alert);
                $(".login-alert-holder").html(htmlOutput);

            },
            success: function(response, textStatus, errorThrown) {

                if ( typeof response.current_user.id === 'undefined' ) {

                    var template   = $.templates("#informasi-pribadi-template");
                    var htmlOutput = template.render();
                    $(".informasi-pribadi .data-holder").html(htmlOutput);
                    $(".informasi-pribadi").show();

                }

                var template   = $.templates("#login-template");
                var htmlOutput = template.render(response);
                $(".login .data-holder").html(htmlOutput);

            }
        });

        $(document).on('change','#user_email',function(e){

            var val = $(this).val();

            $.ajax({
                url : sejoli_checkout.ajax_url,
                type: 'post',
                data: {
                    sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.check_user_email,
                    email: val,
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    var alert = {};

                    if ( response.success ) {

                        $('.user-email-alert-holder').html('');

                    } else {

                        alert.type     = 'error';
                        alert.messages = response.data;

                        var template   = $.templates("#alert-template");
                        var htmlOutput = template.render(alert);
                        $(".user-email-alert-holder").html(htmlOutput);

                    }

                }
            })

        });

        $(document).on('change','#user_phone',function(e){

            var val = $(this).val();

            $.ajax({
                url : sejoli_checkout.ajax_url,
                type: 'post',
                data: {
                    sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.check_user_phone,
                    phone            : val,
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
                    
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    var alert = {};

                    if ( response.success ) {

                        $('.user-phone-alert-holder').html('');

                    } else {

                        alert.type     = 'error';
                        alert.messages = response.data;

                        var template   = $.templates("#alert-template");
                        var htmlOutput = template.render(alert);
                        $(".user-phone-alert-holder").html(htmlOutput);

                    }

                }
            })

        });

    },

    deleteCoupon: function() {
        $(document).on('click','.hapus-kupon',function(e){
            e.preventDefault();
            sejoliSaCheckout.func.deleteCoupon();
        })
    },

    loading : function() {

        var order_id = $('#order_id').val();

        $.ajax({
            url : sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                order_id         : order_id,
                sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.loading,
            },
            complete: function(jqXHR, textStatus) {

                if( textStatus === "success" ) {

                    return true;

                } else {

                    sejoliSaUnblockUI('.element-blockable');

                }

            },
            error: function(jqXHR, textStatus, errorThrown) {

                var alert  = {};

                alert.type = 'error';

                alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                var template   = $.templates("#alert-template");
                var htmlOutput = template.render(alert);
                $(".checkout-alert-holder").html(htmlOutput);

            },
            success: function(response, textStatus, errorThrown) {

                if ( response.valid ) {
                    window.location.replace(response.redirect_link);
                } else {

                }

            }
        });

    },

    changePaymentGateway: function() {

        $(document).on('change','input[type=radio][name=payment_gateway]',function() {
            sejoliSaCheckout.func.changePaymentGateway($(this));
        });

    }
}

let sejoliSaCheckoutFisik = {

    init: function(){
        this.getCalculate();
        this.changeCalculate();
        this.changeCalculateAffectShipping();
        this.getPaymentGateway();
        this.getSubdistrictSelect2();
        this.applyCoupon();
        this.deleteCoupon();
        this.submitCheckout();
        this.getCurrentUser();
        this.submitLogin();
    },

    vars : {
        delay : 0,
    },

    func : {
        applyCoupon : function() {

            var formData = new FormData();

            formData.append('process-action', 'apply-coupon');
            formData.append('coupon', $('#apply_coupon').val());
            formData.append('shipment', $('#shipping_method').val());
            formData.append('shipping_own_value', $('#shipping_own_value').val());
            formData.append('product_id', sejoli_checkout.product_id);
            formData.append('district_id', $('#kecamatan').val());
            formData.append('district_name', $('#kecamatan').find(":selected").text());
            formData.append('quantity', $('#qty').val());
            formData.append('payment_gateway', $("input[name='payment_gateway']:checked").val());
            formData.append('sejoli_ajax_nonce', sejoli_checkout.ajax_nonce.apply_coupon);
            formData.append('wallet', $('#use-wallet').is(':checked'));
            formData.append('recaptcha_response', $('#recaptchaResponse').val());

            $('.variations-select2').each(function(index,element){
                formData.append(''+$(element).attr('name')+'', $(element).val());
            });

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.kode-diskon');
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');
                        sejoliSaUnblockUI('.kode-diskon');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
            
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".coupon-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');
                    sejoliSaUnblockUI('.kode-diskon');

                    var alert = {};

                    var today    = new Date();
                    var date     = today.getFullYear()+'-'+("0" + (today.getMonth() + 1)).slice(-2)+'-'+("0" + today.getDate()).slice(-2);
                    var time     = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                    var dateTime = date+' '+time;
                    
                    if ( 
                        (response.valid && response.data.coupon.limit_use > 0 && response.data.coupon.limit_date != null && response.data.coupon.limit_use != response.data.coupon.usage && new Date(dateTime) < new Date(response.data.coupon.limit_date) && response.data.coupon.status == 'active') ||
                        (response.valid && response.data.coupon.limit_use > 0 && response.data.coupon.limit_date == null && response.data.coupon.limit_use != response.data.coupon.usage && response.data.coupon.status == 'active') ||
                        (response.valid && response.data.coupon.limit_use == 0 && response.data.coupon.limit_date != null && new Date(dateTime) < new Date(response.data.coupon.limit_date) && response.data.coupon.status == 'active') ||
                        (response.valid && response.data.coupon.limit_use == 0 && response.data.coupon.limit_date == null && response.data.coupon.status == 'active')
                    ) {

                        if( response.data.coupon.free_shipping === true ) {

                            setTimeout(function(){
                                $('.free-shipping-price').css("text-decoration", "line-through");
                            }, 500);

                        }

                        alert.type     = 'success';
                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.data);
                        $(".rincian-pesanan tbody").html(htmlOutput);
                        $(".produk-dibeli tbody").html(htmlOutput);
                        var template   = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);
                        $(".total-holder").html( response.data.total );

                        if(response.data.affiliate) {
                            $(".affiliate-name").html(sejoli_checkout.affiliasi_oleh + ' ' + response.data.affiliate);
                        }

                        if(response.data.coupon.type == "percentage" && response.data.coupon.nominal == 100){
                            $('table').find('tr.biaya-transaksi').remove();
                        }

                    } else {

                        alert.type = 'error';

                    }

                    alert.messages = response.messages;

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);

                    $(".coupon-alert-holder").html(htmlOutput);

                    $(document).trigger('sejoli:calculate');

                    setTimeout(function(){
                        $('.free-shipping-price').css("text-decoration", "line-through");
                        
                        var tdElement = $('.hapus-kupon').closest('tr').find('td:eq(1)');

                        if (!tdElement.text().trim().startsWith('-')) {
                            tdElement.prepend('- ');
                        }
                    }, 1000);

                }
            });
        },
        deleteCoupon : function() {

            var formData = new FormData();

            formData.append('process-action', 'delete-coupon');
            formData.append('coupon', $('#coupon').val());
            formData.append('shipment', $('#shipping_method').val());
            formData.append('shipping_own_value', $('#shipping_own_value').val());
            formData.append('product_id', sejoli_checkout.product_id);
            formData.append('district_id', $('#kecamatan').val());
            formData.append('district_name', $('#kecamatan').find(":selected").text());
            formData.append('quantity', $('#qty').val());
            formData.append('payment_gateway', $("input[name='payment_gateway']:checked").val());
            formData.append('sejoli_ajax_nonce', sejoli_checkout.ajax_nonce.delete_coupon);
            formData.append('wallet', $('#use-wallet').is(':checked'));

            $('.variations-select2').each(function(index,element){
                formData.append(''+$(element).attr('name')+'', $(element).val());
            });

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : formData,
                processData: false,
                contentType: false,
                beforeSend : function() {
                    sejoliSaBlockUI();
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI();

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
                    
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".coupon-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {
                    
                    sejoliSaUnblockUI();

                    var alert = {};

                    if ( response.valid ) {

                        alert.type     = 'success';
                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.data);
                        $(".rincian-pesanan tbody").html(htmlOutput);
                        $(".produk-dibeli tbody").html(htmlOutput);
                        var template   = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);
                        $(".total-holder").html( response.data.total );
                        $("#apply_coupon").val("");

                    } else {

                        alert.type = 'error';

                    }

                    alert.messages = response.messages;
                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);

                    $(".coupon-alert-holder").html(htmlOutput);

                    $(document).trigger('sejoli:calculate');

                }
            });
        },
        submitCheckout : function(button) {
            var fb_pixel_event = button.data('fb-pixel-event'),
                formElement    = document.getElementById('sejoli-checkout-fisik'),
                formData       = new FormData(formElement);

            formData.append('process-action', 'checkout');
            formData.append('product_id', sejoli_checkout.product_id);
            formData.append('sejoli_ajax_nonce', sejoli_checkout.ajax_nonce.submit_checkout);

            var qty = $('#qty').val();
            if ( typeof qty !== 'undefined' ) {
                // data.quantity = qty;
                formData.append('quantity', qty);
            }

            var coupon = $('#coupon').val();
            if ( typeof coupon !== 'undefined' ) {
                // data.coupon = coupon;
                formData.append('coupon', coupon);
            }

            var nama_penerima = $('#nama_penerima').val();
            if ( typeof nama_penerima !== 'undefined' ) {
                // data.user_name = nama_penerima;
                formData.append('user_name', nama_penerima);
            }

            var alamat_lengkap = $('#alamat_lengkap').val();
            if ( typeof alamat_lengkap !== 'undefined' ) {
                // data.address = alamat_lengkap;
                formData.append('address', alamat_lengkap);
            }

            var alamat_email = $('#alamat_email').val();
            if ( typeof alamat_email !== 'undefined' ) {
                formData.append('user_email', alamat_email);
            }

            var kode_pos = $('#kode_pos').val();
            if ( typeof kode_pos !== 'undefined' ) {
                // data.postal_code = kode_pos;
                formData.append('postal_code', kode_pos);
            }

            var nomor_telepon = $('#nomor_telepon').val();
            if ( typeof nomor_telepon !== 'undefined' ) {
                // data.user_phone = nomor_telepon;
                formData.append('user_phone', nomor_telepon);
            }

            var kecamatan = $('#kecamatan').val();
            if ( typeof kecamatan !== 'undefined' ) {
                // data.district_id = kecamatan;
                formData.append('district_id', kecamatan);
                formData.append('district_name', $('#kecamatan').find(":selected").text());
            }

            var shipping_method = $('#shipping_method').val();
            if ( typeof shipping_method !== 'undefined' ) {
                // data.shipment = shipping_method;
                formData.append('shipment', shipping_method);
            }

            var order_note = $('#order-note').val();
            if( typeof order_note !== 'undefined') {
                formData.append('meta_data[note]', order_note);
            }

            $('.variations-select2').each(function(index,element){
                formData.append(''+$(element).attr('name')+'', $(element).val());
            });

            formData.append('wallet', $('#use-wallet').is(':checked'));

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : formData,
                processData: false,
                contentType: false,
                beforeSend : function() {
                    button.attr('disabled', true);
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    button.attr('disabled', false);

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    button.attr('disabled', false);

                    var alert = {};

                    if ( response.valid && textStatus === 'success' ) {

                        if(typeof sejoli_fb_pixel !== 'undefined') {

                            fbq('init', sejoli_fb_pixel.id);
                            fbq('track', sejoli_fb_pixel.event.submit, {
                                content_ids: sejoli_fb_pixel.product_id,
                                content_type: sejoli_fb_pixel.content_type,
                                currency: sejoli_fb_pixel.currency,
                                value: response.data.order.grand_total
                            });

                            if (true === sejoli_fb_pixel.affiliate_active && typeof sejoli_fb_pixel.affiliate_id !== 'undefined') {

                                fbq('init', sejoli_fb_pixel.affiliate_id);
                                fbq('track', sejoli_fb_pixel.event.submit, {
                                    content_ids: sejoli_fb_pixel.product_id,
                                    content_type: sejoli_fb_pixel.content_type,
                                    currency: sejoli_fb_pixel.currency,
                                    value: response.data.order.grand_total
                                });

                            }
                        }

                        alert.type = 'success';

                        setTimeout(function(){
                            window.location.replace(response.redirect_link);
                        }, timeout_redirect);

                    } else {

                        alert.type = 'error';
                        sejoliSaUnblockUI('.element-blockable');
                    }

                    alert.messages = response.messages;
                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                }
            });
        },
        changeCalculate: function() {

            var formData = new FormData();
            formData.append('process-action', 'change-calculate');
            formData.append('coupon', $('#coupon').val());
            formData.append('shipment', $('#shipping_method').val());
            formData.append('shipping_own_value', $('#shipping_own_value').val());
            formData.append('product_id', sejoli_checkout.product_id);
            formData.append('district_id', $('#kecamatan').val());
            formData.append('district_name', $('#kecamatan').find(":selected").text());
            formData.append('quantity', $('#qty').val());
            formData.append('payment_gateway', $("input[name='payment_gateway']:checked").val());
            formData.append('sejoli_ajax_nonce', sejoli_checkout.ajax_nonce.get_calculate);
            formData.append('wallet', $('#use-wallet').is(':checked'));

            $('.variations-select2').each(function(index,element){
                formData.append(''+$(element).attr('name')+'', $(element).val());
            });

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : formData,
                processData: false,
                contentType: false,
                beforeSend : function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);
                        $(".rincian-pesanan tbody").html(htmlOutput);
                        $(".produk-dibeli tbody").html(htmlOutput);
                        var template   = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);
                        $(".total-holder").html( response.calculate.total );

                        $(document).trigger('sejoli:calculate');

                        if(typeof response.calculate.wallet !== 'undefined') {
                            $('#use-wallet').attr('checked', true);
                        }
                    }

                }
            });
        },

        getCalculateAfterUseWallet: function() {

            var formData = new FormData();
            formData.append('process-action', 'change-wallet');
            formData.append('coupon', $('#coupon').val());
            formData.append('shipment', $('#shipping_method').val());
            formData.append('shipping_own_value', $('#shipping_own_value').val());
            formData.append('product_id', sejoli_checkout.product_id);
            formData.append('district_id', $('#kecamatan').val());
            formData.append('district_name', $('#kecamatan').find(":selected").text());
            formData.append('quantity', $('#qty').val());
            formData.append('payment_gateway', $("input[name='payment_gateway']:checked").val());
            formData.append('sejoli_ajax_nonce', sejoli_checkout.ajax_nonce.get_calculate);
            formData.append('wallet', $('#use-wallet').is(':checked'));

            var coupon = $('#apply_coupon').val();

            $('.variations-select2').each(function(index,element){
                formData.append(''+$(element).attr('name')+'', $(element).val());
            });

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : formData,
                processData: false,
                contentType: false,
                beforeSend : function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);
                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    if ( typeof response.calculate !== 'undefined' ) {
                        if(coupon) {
                            sejoliSaCheckoutFisik.func.applyCoupon();
                            setTimeout(function(){
                                $('.free-shipping-price').css("text-decoration", "line-through");
                                
                                var tdElement = $('.hapus-kupon').closest('tr').find('td:eq(1)');

                                if (!tdElement.text().trim().startsWith('-')) {
                                    tdElement.prepend('- ');
                                }
                            }, 1000);
                        } else {
                            var template   = $.templates("#produk-dibeli-template");
                            var htmlOutput = template.render(response.calculate);
                            $(".rincian-pesanan tbody").html(htmlOutput);
                            $(".produk-dibeli tbody").html(htmlOutput);
                            var template   = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);
                            $(".total-holder").html( response.calculate.total );

                            $(document).trigger('sejoli:calculate');

                            if(typeof response.calculate.wallet !== 'undefined') {
                                $('#use-wallet').attr('checked', true);
                            }
                        }
                    }

                }
            });
        }
    },

    getCalculate: function() {

        $.ajax({
            url : sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                type             : 'calculate',
                coupon           : $('#apply_coupon').val(),
                product_id       : sejoli_checkout.product_id,
                wallet           : $('#use-wallet').is(':checked'),
                sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.get_calculate,
            },
            complete: function(jqXHR, textStatus) {

                if( textStatus === "success" ) {

                    return true;

                } else {

                    sejoliSaUnblockUI('.element-blockable');

                }

            },
            error: function(jqXHR, textStatus, errorThrown) {

                var alert  = {};

                alert.type = 'error';

                alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                var template   = $.templates("#alert-template");
                var htmlOutput = template.render(alert);
                $(".checkout-alert-holder").html(htmlOutput);

            },
            success: function(response, textStatus, errorThrown) {

                if ( typeof response.calculate !== 'undefined' ) {

                    var template   = $.templates("#produk-dibeli-template");
                    var htmlOutput = template.render(response.calculate);
                    $(".rincian-pesanan tbody").html(htmlOutput);
                    $(".produk-dibeli tbody").html(htmlOutput);
                    var template   = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);

                    var template   = $.templates("#produk-dibeli-template");
                    var htmlOutput = template.render(response.calculate);

                    $(".produk-dibeli tbody").html(htmlOutput);

                    var template   = $.templates("#detail-pesanan-template");
                    var htmlOutput = template.render(response.calculate);
                    $(".detail-pesanan .data-holder").html(htmlOutput);

                    var template   = $.templates("#apply-coupon-template");
                    var htmlOutput = template.render();
                    $(".kode-diskon .data-holder").html(htmlOutput);

                    $(".total-holder").html( response.calculate.total );

                    $(".beli-sekarang .submit-button").attr('disabled',false);

                    if(response.calculate.affiliate) {
                        $(".affiliate-name").html(sejoli_checkout.affiliasi_oleh + ' ' + response.calculate.affiliate);
                    }

                    if(typeof response.calculate.wallet !== 'undefined') {
                        $('#use-wallet').attr('checked', true);
                    }

                    $('.select2-filled').select2({
                    });
                }

            }
        })
    },

    getPaymentGateway: function() {

        $.ajax({
            url : sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                type             : 'get-payment-gateway',
                product_id       : sejoli_checkout.product_id,
                sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.get_payment_gateway,
            },
            complete: function(jqXHR, textStatus) {

                if( textStatus === "success" ) {

                    return true;

                } else {

                    sejoliSaUnblockUI('.element-blockable');

                }

            },
            error: function(jqXHR, textStatus, errorThrown) {

                var alert  = {};
                
                alert.type = 'error';

                alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                var template   = $.templates("#alert-template");
                var htmlOutput = template.render(alert);
                $(".checkout-alert-holder").html(htmlOutput);

            },
            success: function(response, textStatus, errorThrown) {

                if ( typeof response.payment_gateway !== 'undefined' ) {

                    var template   = $.templates("#metode-pembayaran-template");
                    var htmlOutput = template.render(response);
                    $(".metode-pembayaran .data-holder").html(htmlOutput);

                    $('.ui.radio.checkbox').checkbox();

                }

            }
        });

    },

    getSubdistrictSelect2: function(){

        $('#kecamatan').select2({
            allowClear        : true,
            placeholder       : sejoli_checkout.district_select,
            minimumInputLength: 1,
            ajax: {
                url : sejoli_checkout.ajax_url,
                type: 'post',
                // dataType: 'json',
                data: function (params) {
                    return {
                        term             : params.term,
                        sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.get_subdistrict,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.results,
                    };
                },
            }
        });

        $('#kecamatan').on('change', function() {

            $('#shipping_method').val('').trigger('change');

            var formData = new FormData();
            formData.append('process-action', 'get-subdistrict');
            formData.append('coupon', $('#apply_coupon').val());
            formData.append('product_id', sejoli_checkout.product_id);
            formData.append('district_id', $('#kecamatan').val());
            formData.append('district_name', $('#kecamatan').find(":selected").text());
            formData.append('quantity', $('#qty').val());
            formData.append('sejoli_ajax_nonce', sejoli_checkout.ajax_nonce.get_shipping_methods);

            $('.variations-select2').each(function(index,element){
                formData.append(''+$(element).attr('name')+'', $(element).val());
            });

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : formData,
                processData: false,
                contentType: false,
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
                    
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    if ( typeof response.shipping_methods !== 'undefined' ) {

                        var template   = $.templates("#metode-pengiriman-template");
                        var htmlOutput = template.render(response);

                        $(".metode-pengiriman .data-holder").html(htmlOutput);

                        $('.select2-filled').select2({
                        });

                    }

                }
            });

        });

    },

    applyCoupon: function() {

        sejoliSaCheckoutFisik.vars.delay = 0;

        $(document).on('click','.submit-coupon',function(e){
            e.preventDefault();
            sejoliSaCheckoutFisik.func.applyCoupon();
        });

        $(document).on('keyup', '#apply_coupon', function(){
            clearTimeout(sejoliSaCheckoutFisik.vars.delay);

            sejoliSaCheckoutFisik.vars.delay = setTimeout(function(){
                sejoliSaCheckoutFisik.func.applyCoupon();
            },500)
        })
    },

    deleteCoupon: function() {
        $(document).on('click','.hapus-kupon',function(e){
            e.preventDefault();
            sejoliSaCheckoutFisik.func.deleteCoupon();
        });
    },

    submitCheckout: function() {
        $(document).on('submit','#sejoli-checkout-fisik',function(e){
            e.preventDefault();
            sejoliSaCheckoutFisik.func.submitCheckout($(this));
        });
    },

    getCurrentUser: function() {

        $.ajax({
            url : sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                type             : 'get-user',
                sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.get_current_user,
            },
            complete: function(jqXHR, textStatus) {

                if( textStatus === "success" ) {

                    return true;

                } else {

                    sejoliSaUnblockUI('.element-blockable');

                }

            },
            error: function(jqXHR, textStatus, errorThrown) {

                var alert  = {};
                
                alert.type = 'error';

                alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                var template   = $.templates("#alert-template");
                var htmlOutput = template.render(alert);
                $(".checkout-alert-holder").html(htmlOutput);

            },
            success: function(response, textStatus, errorThrown) {

                var template   = $.templates("#login-template");
                var htmlOutput = template.render(response);
                $(".login .data-holder").html(htmlOutput);

                if ( typeof response.current_user.name !== 'undefined' ) {
                    $('#nama_penerima').val(response.current_user.name);
                }
                if ( typeof response.current_user.address !== 'undefined' ) {
                    $('#alamat_lengkap').val(response.current_user.address);
                }
                if ( typeof response.current_user.email !== 'undefined' ) {
                    $('#alamat_email').val(response.current_user.email);
                }
                if ( typeof response.current_user.phone !== 'undefined' ) {
                    $('#nomor_telepon').val(response.current_user.phone);
                }
                if ( typeof response.current_user.postal_code !== 'undefined' ) {
                    $('#kode_pos').val(response.current_user.postal_code);
                }
                if ( typeof response.current_user.subdistrict !== 'undefined' ) {
                    var option      = new Option(response.current_user.subdistrict.text, response.current_user.subdistrict.id);
                    option.selected = true;
                    $("#kecamatan").append(option);
                    $("#kecamatan").trigger("change");
                }

                if ( response.current_user.length === 0 ) {

                    $(document).on('change','#alamat_email',function(e){

                        var val = $(this).val();

                        $.ajax({
                            url : sejoli_checkout.ajax_url,
                            type: 'post',
                            data: {
                                sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.check_user_email,
                                email            : val,
                            },
                            complete: function(jqXHR, textStatus) {

                                if( textStatus === "success" ) {

                                    return true;

                                } else {

                                    sejoliSaUnblockUI('.element-blockable');

                                }

                            },
                            error: function(jqXHR, textStatus, errorThrown) {

                                var alert  = {};

                                alert.type = 'error';

                                alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                                var template   = $.templates("#alert-template");
                                var htmlOutput = template.render(alert);
                                $(".checkout-alert-holder").html(htmlOutput);

                            },
                            success: function(response, textStatus, errorThrown) {

                                var alert = {};

                                if ( response.success ) {

                                    $('.user-email-alert-holder').html('');

                                } else {

                                    alert.type     = 'error';
                                    alert.messages = response.data;
                                    var template   = $.templates("#alert-template");
                                    var htmlOutput = template.render(alert);
                                    $(".user-email-alert-holder").html(htmlOutput);

                                }

                            }
                        })

                    });

                    // $(document).on('change','#nomor_telepon',function(e){

                    //     var val = $(this).val();

                    //     $.ajax({
                    //         url : sejoli_checkout.ajax_url,
                    //         type: 'post',
                    //         data: {
                    //             sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.check_user_phone,
                    //             phone            : val,
                    //         },
                    //         success: function( response ) {

                    //             var alert = {};

                    //             if ( response.success ) {

                    //                 $('.user-phone-alert-holder').html('');

                    //             } else {

                    //                 alert.type     = 'error';
                    //                 alert.messages = response.data;
                    //                 var template   = $.templates("#alert-template");
                    //                 var htmlOutput = template.render(alert);
                    //                 $(".user-phone-alert-holder").html(htmlOutput);

                    //             }

                    //         }
                    //     })

                    // });

                }
            }
        });

    },

    submitLogin: function() {

        $(document).on('click','.submit-login',function(e){

            e.preventDefault();

            var login_email    = $('#login_email').val();
            var login_password = $('#login_password').val();

            $.ajax({
                url : sejoli_checkout.ajax_url,
                type: 'post',
                data: {
                    login_email      :login_email,
                    login_password   :login_password,
                    sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.submit_login
                },
                beforeSend: function() {
                    sejoliSaBlockUI('', '.login-form');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.login-form');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};

                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".login-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.login-form');

                    var alert = {};

                    if ( response.success ) {

                        alert.type = 'success';

                        setTimeout(function(){
                            location.reload(true);
                        }, 1000);

                    } else {

                        alert.type = 'error';

                    }

                    alert.messages = response.data;
                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".login-alert-holder").html(htmlOutput);

                }
            })

        });

    },

    changeCalculateAffectShipping: function() {

        var timeout = null;

        $(document).on('change','input.change-calculate-affect-shipping',function() {

            clearTimeout(timeout);

            timeout = setTimeout(function () {

                $('#kecamatan').trigger('change');

                sejoliSaCheckoutFisik.func.changeCalculate();

            }, 800);

        });
        
        $(document).on('click','.button-plus',function() {

            clearTimeout(timeout);

            timeout = setTimeout(function () {

                $('#kecamatan').trigger('change');

                sejoliSaCheckoutFisik.func.changeCalculate();

            }, 800);

        });

        $(document).on('click','.button-minus',function() {

            clearTimeout(timeout);

            timeout = setTimeout(function () {

                $('#kecamatan').trigger('change');

                sejoliSaCheckoutFisik.func.changeCalculate();

            }, 800);

        });

        $(document).on('change','select.change-calculate-affect-shipping',function() {

            $('#kecamatan').trigger('change');

            var formData = new FormData();
            formData.append('process-action', 'change-shipping');
            formData.append('coupon', $('#coupon').val());
            formData.append('shipment', $('#shipping_method').val());
            formData.append('product_id', sejoli_checkout.product_id);
            formData.append('district_id', $('#kecamatan').val());
            formData.append('district_name', $('#kecamatan').find(":selected").text());
            formData.append('quantity', $('#qty').val());
            formData.append('payment_gateway', $("input[name='payment_gateway']:checked").val());
            formData.append('sejoli_ajax_nonce', sejoli_checkout.ajax_nonce.get_calculate);
            formData.append('wallet', $('#use-wallet').is(':checked'));

            $('.variations-select2').each(function(index,element){
                formData.append(''+$(element).attr('name')+'', $(element).val());
            });

            $.ajax({
                url        : sejoli_checkout.ajax_url,
                type       : 'post',
                data       : formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    sejoliSaBlockUI('', '.element-blockable');
                },
                complete: function(jqXHR, textStatus) {

                    if( textStatus === "success" ) {

                        return true;

                    } else {

                        sejoliSaUnblockUI('.element-blockable');

                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {

                    var alert  = {};
                    
                    alert.type = 'error';

                    alert.messages = sejoliSaAjaxReturnError(jqXHR.status, textStatus, jqXHR.responseText);

                    var template   = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                },
                success: function(response, textStatus, errorThrown) {

                    sejoliSaUnblockUI('.element-blockable');

                    if ( typeof response.calculate !== 'undefined' ) {

                        var template   = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);
                        $(".rincian-pesanan tbody").html(htmlOutput);
                        $(".produk-dibeli tbody").html(htmlOutput);
                        var template   = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);
                        $(".total-holder").html( response.calculate.total );

                    }
                    
                }
            });

        });

    },

    changeCalculate: function() {

        $(document).on('change','.change-calculate',function() {
            sejoliSaCheckoutFisik.func.changeCalculate();
        });
    }
}
