<script type="text/javascript">
(function(){
    'use strict';

    let coupon_checkout = '<?php echo $user_coupon; ?>',
        coupon_toggler,
        coupon_form,
        coupon_field,
        observer = new MutationObserver( function(mutations){
            if(
                document.getElementById('sejoli-total-bayar') &&
                document.getElementById('kode-diskon-form-toggle') &&
                document.getElementById('kode-diskon-form') &&
                document.getElementById('apply_coupon')
            ) {

                coupon_toggler = document.getElementById('kode-diskon-form-toggle');
                coupon_form    = document.getElementById('kode-diskon-form');
                coupon_field   = document.getElementById('apply_coupon');

                if( '' !== coupon_checkout ) {
                    coupon_toggler.style.display = 'none';
                    coupon_form.style.display = 'block';
                    coupon_field.value = coupon_checkout;
                    document.getElementById('sejoli-submit-coupon').click();
                }

                observer.disconnect();
            } else if(
                document.getElementById('kode-diskon-form-toggle') &&
                document.getElementById('kode-diskon-form') &&
                document.getElementById('apply_coupon')
            ) {

                coupon_toggler = document.getElementById('kode-diskon-form-toggle');
                coupon_form    = document.getElementById('kode-diskon-form');
                coupon_field   = document.getElementById('apply_coupon');

                if( '' !== coupon_checkout ) {
                    coupon_toggler.style.display = 'none';
                    coupon_form.style.display = 'block';
                    coupon_field.value = coupon_checkout;
                    document.getElementById('sejoli-submit-coupon').click();
                }

                observer.disconnect();
            }
        });

    observer.observe(document, {attributes: false, childList: true, characterData: false, subtree:true});
})();
</script>
