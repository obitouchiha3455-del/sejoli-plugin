(function(){

    'use strict';

    let ssp_id = <?php echo $product->ID; ?>,
        ssp_css  = document.createElement('link'),
        ssp_popup = document.createElement('div'),
        ssp_first_time = <?php echo $this->first_time; ?>,
        ssp_start,
        ssp_show_time = <?php echo $this->display_time; ?>,
        ssp_delay = <?php echo $this->delay_time; ?>,
        ssp_xhr = new XMLHttpRequest(),
        ssp_formdata,
        ssp_fade_in_c = 'sejolifadeInUp',
        ssp_fade_out_c = 'sejolifadeOutDown',
        ssp_show,
        ssp_position = '<?php echo $this->position; ?>';

    ssp_popup.setAttribute('id', 'social-proof-container-<?php echo $product->ID; ?>');
    ssp_popup.className = 'social-proof-container animated ' + ssp_position;

    ssp_popup.innerHTML = "<input type='hidden' id='social-proof-orders-" + ssp_id + "' class='social-proof-orders' name='social-proof-orders' value=''>" +
        "<section id='social-proof-holder' class='social-proof-holder'>" +
            "<figure class='buyer-photo'>" +
                "<img class='avatar' src='#' srcset='#' />" +
            "</figure>" +
            "<div class='buyer-detail'>" +
                "<div class='buyer-text'><?php echo $popup_text; ?></div>" +
                "<span class='order-text'>45 menit lalu</span>" +
            "</div>" +
        "</section>";

    document.body.appendChild( ssp_popup );


    let ssp_loaddata = function() {

        ssp_formdata = new FormData();

        ssp_formdata.append('product_id', ssp_id);
        ssp_formdata.append('orders', ssp_popup.getElementsByClassName('social-proof-orders')[0].value );

        ssp_xhr.open('POST', '<?php echo home_url('sejoli-social-proof-ajax/' . $product->ID ); ?>' );

        ssp_xhr.onload = function() {
            if( ssp_xhr.status === 200 ) {
                let response = JSON.parse( ssp_xhr.responseText ) ;

                if( response.success ) {

                    ssp_popup.getElementsByClassName('social-proof-orders')[0].value = response.data.orders;
                    ssp_popup.getElementsByClassName('avatar')[0].src = response.data.avatar;
                    ssp_popup.getElementsByClassName('avatar')[0].srcset = response.data.avatar;
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
        };

        ssp_xhr.send( ssp_formdata );
    };

    let ssp_fade_in = function() {

        ssp_popup.classList.remove(ssp_fade_out_c);
        ssp_popup.classList.add(ssp_fade_in_c);

        ssp_show = setTimeout(function(){
            ssp_fade_out();
        }, ssp_show_time);
    };

    let ssp_fade_out = function() {

        ssp_popup.classList.remove(ssp_fade_in_c);
        ssp_popup.classList.add(ssp_fade_out_c);

        setTimeout(function(){
            ssp_loaddata();
        }, ssp_show_time )

    };

    ssp_css.href = '<?php echo home_url('sejoli-social-proof-iframe/' . $product->ID . '/css'); ?>';
    ssp_css.rel  = 'stylesheet';
    ssp_css.type = 'text/css';

    document.getElementsByTagName('head')[0].appendChild(ssp_css);

    ssp_fade_in_c = ssp_position.includes('bottom') ? 'sejolifadeInUp' : 'sejolifadeInDown';
    ssp_fade_out_c = ssp_position.includes('bottom') ? 'sejolifadeOutDown' : 'sejolifadeOutUp';

    ssp_css.onload = function(){
        console.log('load css completed');
        ssp_start = setTimeout(function(){
            ssp_loaddata();
        }, ssp_first_time);
    };

})();
