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

let sejoliSaCheckoutRenew = {
    vars : {
        delay : 0,
        ajax: ''
    },

    func: {
        setData: function(process_type, nonce) {
            return {
                'process-action': process_type,
                order_id: sejoli_checkout_renew.order_id,
                coupon: $('#apply_coupon').val(),
                quantity: $('#qty').val(),
                product_id: sejoli_checkout.product_id,
                payment_gateway: $('input[name="payment_gateway"]:checked').val(),
                sejoli_ajax_nonce: nonce,
                price: $('#price').val(),
                wallet: $('#use-wallet').is(':checked'),
                renew_page: true,
                recaptcha_response : $('#recaptchaResponse').val(),
            }
        },

        applyCoupon : function() {
            var data = sejoliSaCheckoutRenew.func.setData('apply-coupon', sejoli_checkout_renew.ajax_nonce.apply_coupon);

            sejoliSaCheckoutRenew.vars.ajax = $.ajax({
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

                    if ( response.valid ) {

                        alert.type = 'success';

                        if(0 === parseInt(response.data.raw_total)) {
                            $('.metode-pembayaran').hide();
                        } else {
                            $('.metode-pembayaran').show();
                        }

                        var template = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.data);

                        $(".produk-dibeli tbody").html(htmlOutput);
                        $(".total-holder").html( response.data.total );

                        if(response.data.affiliate) {
                            $(".affiliate-name").html('Affiliasi oleh ' + response.data.affiliate);
                        }

                        if(0 === parseInt(response.data.raw_total)) {
                            $('.metode-pembayaran').hide();
                            setTimeout(() => {
                                $('.beli-sekarang .submit-button').removeAttr('disabled','disabled');
                            }, 500)
                        } else {
                            $('.metode-pembayaran').show();
                        }

                    } else {

                        alert.type = 'error';

                    }

                    alert.messages = response.messages;

                    var template = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".coupon-alert-holder").html(htmlOutput);

                }
            });
        }
    },

    init: function(){
        this.getCalculate();
        this.getPaymentGateway();
        this.applyCoupon();
        this.submitCheckout();
        this.getCurrentUser();
        this.submitLogin();
        this.deleteCoupon();
        this.changePaymentGateway();
    },

    getCalculate: function(){

        var data = sejoliSaCheckoutRenew.func.setData('calculate', sejoli_checkout_renew.ajax_nonce.get_calculate);
        setTimeout(function(){ 
            var payment_gateway = $('input[name="payment_gateway"]:checked').val();
            var quantity = $('#qty').val();

            $.ajax({
            url: sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                order_id : sejoli_checkout_renew.order_id,
                product_id: sejoli_checkout.product_id,
                payment_gateway: payment_gateway,
                quantity: quantity,
                renew_page: true,
                sejoli_ajax_nonce: sejoli_checkout_renew.ajax_nonce.get_calculate,
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

                    var template = $.templates("#produk-dibeli-template");
                    var htmlOutput = template.render(response.calculate);
                    $(".produk-dibeli tbody").html(htmlOutput);

                    var template = $.templates("#apply-coupon-template");
                    var htmlOutput = template.render();
                    $(".kode-diskon .data-holder").html(htmlOutput);

                    var template = $.templates("#beli-sekarang-template");
                    var htmlOutput = template.render();
                    $(".beli-sekarang .data-holder").html(htmlOutput);

                    if(response.calculate.affiliate) {
                        $(".affiliate-name").html('Affiliasi oleh ' + response.calculate.affiliate);
                    }

                    $(".total-holder").html( response.calculate.total );

                }

            }
        })

        }, 3000);

    },

    getCalculateAfterUseWallet: function(){

        var data = sejoliSaCheckoutRenew.func.setData('calculate', sejoli_checkout_renew.ajax_nonce.get_calculate);   
        var coupon = $('#apply_coupon').val();

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

                    if(coupon) {
                        sejoliSaCheckoutRenew.func.applyCoupon();
                    } else {
                        var template = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);
                        $(".produk-dibeli tbody").html(htmlOutput);

                        var template = $.templates("#apply-coupon-template");
                        var htmlOutput = template.render();
                        $(".kode-diskon .data-holder").html(htmlOutput);

                        var template = $.templates("#beli-sekarang-template");
                        var htmlOutput = template.render();
                        $(".beli-sekarang .data-holder").html(htmlOutput);

                        if(response.calculate.affiliate) {
                            $(".affiliate-name").html('Affiliasi oleh ' + response.calculate.affiliate);
                        }

                        $(".rincian-pesanan tbody").html(htmlOutput);
                        $(".total-holder").html( response.calculate.total );
                    }

                }

            }
        })

    },

    getPaymentGateway: function(){

        $.ajax({
            url: sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                product_id       : sejoli_checkout.product_id,
                renew_page       : true,
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

                    var template = $.templates("#metode-pembayaran-template");
                    var htmlOutput = template.render(response);
                    $(".metode-pembayaran .data-holder").html(htmlOutput);

                    $('.ui.radio.checkbox').checkbox();

                }

            }
        });

    },

    applyCoupon: function(){

        sejoliSaCheckoutRenew.vars.delay = 0;

        console.log('test');

        $(document).on('submit','.kode-diskon-form',function(e){
            e.preventDefault();
            sejoliSaCheckoutRenew.func.applyCoupon();
        });

        $(document).on('click','.submit-coupon',function(e){
            e.preventDefault();
            sejoliSaCheckoutRenew.func.applyCoupon();
        });

        $(document).on('keyup', '#apply_coupon', function(){

            if(typeof sejoliSaCheckoutRenew.vars.ajax.abort === 'function')
            { sejoliSaCheckoutRenew.vars.ajax.abort(); }

            clearTimeout(sejoliSaCheckoutRenew.vars.delay);

            sejoliSaCheckoutRenew.vars.delay = setTimeout(function(){
                sejoliSaCheckoutRenew.func.applyCoupon();
            },1000)
        });


    },

    submitCheckout: function(){

        $(document).on('click','.beli-sekarang .submit-button',function(e){

            e.preventDefault();

            var fb_pixel_event = $(this).data('fb-pixel-event');

            var data = {
                order_id           : sejoli_checkout_renew.order_id,
                renew_page         : true,
                payment_gateway    : $('input[name="payment_gateway"]:checked').val(),
                product_id         : sejoli_checkout.product_id,
                quantity           : $('#qty').val(),
                sejoli_ajax_nonce  : sejoli_checkout_renew.ajax_nonce.submit_checkout,
                recaptcha_response : $('#recaptchaResponse').val(),
            };

            var qty = $('#qty').val();
            if ( typeof qty !== 'undefined' ) {
                data.quantity = qty;
            }

            var coupon = $('#apply_coupon').val();
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

            let button = $(this);

            $.ajax({
                url: sejoli_checkout.ajax_url,
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

                    sejoliSaUnblockUI('.element-blockable');

                    var alert = {};

                    button.attr('disabled', false);

                    if ( response.valid ) {

                        if ( typeof fbq !== "undefined" && fb_pixel_event !== '' ) {
                            fbq('trackCustom', fb_pixel_event, {});
                        }

                        alert.type = 'success';

                        window.location.replace(response.redirect_link);

                    } else {

                        alert.type = 'error';

                    }

                    alert.messages = response.messages;

                    var template = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".checkout-alert-holder").html(htmlOutput);

                }
            });
        });
    },

    submitLogin: function(){

        $(document).on('click','.submit-login',function(e){

            e.preventDefault();

            var login_email = $('#login_email').val();
            var login_password = $('#login_password').val();

            $.ajax({
                url: sejoli_checkout.ajax_url,
                type: 'post',
                data: {
                    login_email:login_email,
                    login_password:login_password,
                    sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.submit_login
                },
                beforeSend: function() {
                    sejoliSaBlockUI();
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

                    sejoliSaUnblockUI();

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

                    var template = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".login-alert-holder").html(htmlOutput);

                }
            })

        });

    },

    getCurrentUser: function(){

        $.ajax({
            url: sejoli_checkout.ajax_url,
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

                    var template = $.templates("#informasi-pribadi-template");
                    var htmlOutput = template.render();
                    $(".informasi-pribadi .data-holder").html(htmlOutput);
                    $(".informasi-pribadi").show();

                }

                var template = $.templates("#login-template");
                var htmlOutput = template.render(response);
                $(".login .data-holder").html(htmlOutput);

            }
        });

        $(document).on('change','#user_email',function(e){

            var val = $(this).val();

            $.ajax({
                url: sejoli_checkout.ajax_url,
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

                        alert.type = 'error';
                        alert.messages = response.data;

                        var template = $.templates("#alert-template");
                        var htmlOutput = template.render(alert);
                        $(".user-email-alert-holder").html(htmlOutput);

                    }

                }
            })

        });

        $(document).on('change','#user_phone',function(e){

            var val = $(this).val();

            $.ajax({
                url: sejoli_checkout.ajax_url,
                type: 'post',
                data: {
                    sejoli_ajax_nonce: sejoli_checkout.ajax_nonce.check_user_phone,
                    phone: val,
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

                        alert.type = 'error';
                        alert.messages = response.data;

                        var template = $.templates("#alert-template");
                        var htmlOutput = template.render(alert);
                        $(".user-phone-alert-holder").html(htmlOutput);

                    }

                }
            })

        });

    },

    deleteCoupon: function(){

        $(document).on('click','.hapus-kupon',function(e){

            e.preventDefault();

            var data = sejoliSaCheckoutRenew.func.setData('delete-coupon', sejoli_checkout_renew.ajax_nonce.get_calculate);
            setTimeout(function(){ 
                var payment_gateway = $('input[name="payment_gateway"]:checked').val();
                var quantity = $('#qty').val();

                $.ajax({
                    url: sejoli_checkout.ajax_url,
                    type: 'post',
                    data: {
                        order_id : sejoli_checkout_renew.order_id,
                        product_id: sejoli_checkout.product_id,
                        payment_gateway: payment_gateway,
                        renew_page: true,
                        quantity: quantity,
                        sejoli_ajax_nonce: sejoli_checkout_renew.ajax_nonce.delete_coupon,
                    },
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

                        var template = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.data);
                        $(".produk-dibeli tbody").html(htmlOutput);
                        $(".total-holder").html( response.data.total );
                        $("#apply_coupon").val("");

                    } else {

                        alert.type = 'error';

                    }

                    alert.messages = response.messages;

                    if(0 === parseInt(response.data.raw_total)) {
                        $('.metode-pembayaran').hide();
                    } else {
                        $('.metode-pembayaran').show();
                    }

                    var template = $.templates("#alert-template");
                    var htmlOutput = template.render(alert);
                    $(".coupon-alert-holder").html(htmlOutput);

                }
            })

            }, 3000);

        })
    },

    loading: function(){

        var order_id = $('#order_id').val();

        $.ajax({
            url: sejoli_checkout.ajax_url,
            type: 'post',
            data: {
                order_id: order_id,
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

    changePaymentGateway: function(){

        $(document).on('change','input[type=radio][name=payment_gateway]',function() {

            var data = {
                coupon: $('#apply_coupon').val(),
                order_id : sejoli_checkout_renew.order_id,
                product_id: sejoli_checkout.product_id,
                quantity: $('#qty').val(),
                renew_page: true,
                payment_gateway: $(this).val(),
                sejoli_ajax_nonce: sejoli_checkout_renew.ajax_nonce.get_calculate,
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

                        var template = $.templates("#produk-dibeli-template");
                        var htmlOutput = template.render(response.calculate);
                        $(".produk-dibeli tbody").html(htmlOutput);
                        $(".total-holder").html(response.calculate.total);

                    }

                }
            })

        });

    }
}