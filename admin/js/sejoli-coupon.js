let sejoli_coupon;

(function( $ ) {
	'use strict';

	sejoli_coupon = {
        code : '',
        codeValid : false,
        button : '',
		codeWrapper : '',
		interval : false,
        init : function() {
            this.button = $("input[name='publish']");
			this.codeWrapper = $('#titlewrap');
            this.checkCouponCode();
        },
		clearMessage : function() {
			this.codeWrapper.find('em').remove();
		},
        checkCouponCode : function() {
            this.code = $("input[name='post_title']").val();
            if('' === this.code ) {
				this.button.attr('disabled', false);
            } else {
				$.ajax({
					url : sejoli_admin.coupon.check.ajaxurl,
					data : {
						nonce : sejoli_admin.coupon.check.nonce,
						code : this.code
					},
					dataType: 'json',
					beforeSend : function() {
						sejoli_coupon.button.attr('disabled', true);
						sejoli_coupon.clearMessage();
						sejoli_coupon.codeWrapper.append('<em>' + sejoli_admin.coupon.text.checking + '</em>');
					},
					success : function(response) {
						sejoli_coupon.clearMessage();
						if(false === response.valid) {
							sejoli_coupon.button.attr('disabled', false);
							sejoli_coupon.codeWrapper.append('<em>' + sejoli_admin.coupon.text.coupon_not_exists + '</em>');
						} else {
							sejoli_coupon.button.attr('disabled', true);
							sejoli_coupon.codeWrapper.append('<em>' + sejoli_admin.coupon.text.coupon_exists + '</em>');
						}
					}
				});
            }
        }
    };

    $(document).ready(function(){
        sejoli_coupon.init();
    });

    $("input[name='post_title']").on('change keyup' , function(){

		clearInterval(sejoli_coupon.interval);

		sejoli_coupon.interval = setTimeout(function(){
			console.log('test');
	        sejoli_coupon.checkCouponCode();
		},500);

    })

})(jQuery);
