<?php require_once( plugin_dir_path( __FILE__ ) ) . 'popup.php'; ?>
<script type="text/javascript">
(function() {

    'use strict';

    let cssSProof  = document.createElement('link'),
        ssp_popup = document.getElementById('social-proof-holder'),
        ssp_start,
        ssp_show,
        ssp_delay,
        ssp_fade_in,
        ssp_fade_out,
        ssp_xhr = new XMLHttpRequest(),
        ssp_loaddata,
        ssp_formdata,
        ssp_fade_in_c = 'fadeInUp',
        ssp_fade_out_c = 'fadeOutDown',
        ssp_avatar = ssp_popup.getElementsByClassName('avatar')[0];

    ssp_loaddata = function() {

        ssp_formdata = new FormData();

        ssp_formdata.append('product_id', sejoli_social_proof.product_id);
        ssp_formdata.append('orders', document.getElementById('social-proof-orders').value );

        ssp_xhr.open('POST', sejoli_social_proof.ajax_url );

        ssp_xhr.onload = function() {

            if( ssp_xhr.status === 200 ) {

                let response = JSON.parse( ssp_xhr.responseText ) ;

                if( response.success ) {

                    document.getElementById('social-proof-orders').value = response.data.orders;
                    if(typeof ssp_avatar !== "undefined") {
                        ssp_popup.getElementsByClassName('avatar')[0].src = response.data.avatar;
                        ssp_popup.getElementsByClassName('avatar')[0].srcset = response.data.avatar;
                    }
                    ssp_popup.getElementsByClassName('buyer-name')[0].innerHTML = response.data.name;
                    ssp_popup.getElementsByClassName('product-name')[0].innerHTML = response.data.product;
                    ssp_popup.getElementsByClassName('order-text')[0].innerHTML = response.data.time;

                    setTimeout(function(){
                        ssp_fade_in();
                    }, 800);

                } else {
                    console.log('error');
                }

            }

        }

        ssp_xhr.send( ssp_formdata );
        
    }

    ssp_fade_in = function() {

        ssp_popup.classList.remove(ssp_fade_out_c);
        ssp_popup.classList.add(ssp_fade_in_c);

        ssp_show = setTimeout(function(){
            ssp_fade_out();
        }, sejoli_social_proof.show_time);
    }

    ssp_fade_out = function() {

        ssp_popup.classList.remove(ssp_fade_in_c);
        ssp_popup.classList.add(ssp_fade_out_c);

        setTimeout(function(){
            ssp_loaddata();
        }, sejoli_social_proof.delay_time )

    }

    cssSProof.href = sejoli_social_proof.main_css;
    cssSProof.rel  = 'stylesheet';
    cssSProof.type = 'text/css';
    document.getElementsByTagName('head')[0].appendChild(cssSProof);

    ssp_fade_in_c = sejoli_social_proof.position.includes('bottom') ? 'fadeInUp' : 'fadeInDown';
    ssp_fade_out_c = sejoli_social_proof.position.includes('bottom') ? 'fadeOutDown' : 'fadeOutUp';

    cssSProof.onload = function(){
        ssp_start = setTimeout(function(){
            ssp_loaddata();
        }, sejoli_social_proof.first_time);
    };
})();
</script>
