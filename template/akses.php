<?php sejoli_header(); ?>
    <h2 class="ui header"><?php _e('Akses', 'sejoli'); ?></h2>
    <div class="ui three column doubling stackable cards item-holder masonry grid">
    </div>
    <div class="bonus-modal-holder ui modal"></div>
    <script id="item-template" type="text/x-js-render">
        <?php include 'akses-item-tmpl.php'; ?>
    </script>
    <script id='bonus-template' type="text/x-js-render">
        <?php include 'jsrender/tmpl-bonus-access.php'; ?>
    </script>
    <script>
        jQuery(document).ready(function($){
            sejoli.akses.init();

            jQuery('body').on('click', '.open-bonus', function(){
                let affiliate_id = jQuery(this).data('affiliate'),
                    product_id =  jQuery(this).data('product');

                jQuery.ajax({
                    type     : 'get',
                    url      : sejoli_member_area.bonus.ajaxurl,
                    dataType : 'json',
                    data     : {
                        nonce : sejoli_member_area.bonus.nonce,
                        affiliate : affiliate_id,
                        product : product_id
                    },
                    beforeSend : function() {
                        sejoli.block('.sejolisa-memberarea-content');
                    },
                    success : function(response) {
                        sejoli.unblock('.sejolisa-memberarea-content');
                        if('' !== response.content) {
                            let tmpl = $.templates('#bonus-template'),
                                html = tmpl.render(response);

                            $('.bonus-modal-holder').html(html).modal('show');
                        }
                    }
                })
                return false;
            });

            jQuery('body').on('click', '.click-access', function(){

                let product_id  = jQuery(this).data('product'),
                    access_link = jQuery(this).data('access'),
                    access_id   = jQuery(this).data('accessid');

                jQuery.ajax({
                    type     : 'get',
                    url      : sejoli_fb_tiktok_conversion.fb_tiktok_access_pixel_conversion.ajaxurl,
                    dataType : 'json',
                    data     : {
                        nonce : sejoli_fb_tiktok_conversion.fb_tiktok_access_pixel_conversion.nonce,
                        product : product_id,
                        access_link : access_link,
                        access : access_id
                    },
                    beforeSend : function() {
                        window.location = access_link;
                    },
                    success : function(response) {
                        console.log("Successfuly");
                    }
                });

                return false;

            });
        });
    </script>
<?php sejoli_footer(); ?>
