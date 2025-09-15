<?php
include 'header.php';
include 'header-logo.php';
$code_access = sejolisa_carbon_get_post_meta(get_the_ID(), 'coupon_access_checkout');
?>
<div class="ui text closed container">
    <div class="ui segment">
        <h3 class='ui header'><?php _e('Penjualan sudah ditutup', 'sejoli'); ?></h3>
        <p><?php _e('Maaf, penjualan produk ini sudah ditutup.', 'sejoli'); ?></p>
        <?php if(!empty($code_access)) : ?>
        <p>
            <?php _e('Jika anda memiliki kode akses untuk halaman ini, silahkan masukkan untuk mengakses halaman checkout.', 'sejoli'); ?><br />
            <?php _e('Kode akses hanya diberikan kepada pengunjung tertentu.', 'sejoli'); ?>
        </p>

        <form id='sejoli-checkout-code-access' class="ui form" method="POST" action=''>
            <div class="field ">
                <label><?php _e('Kode Akses', 'sejoli'); ?></label>
                <input type="text" name="code_access" placeholder="<?php _e('Kode akses untuk halaman checkout', 'sejoli'); ?>">
            </div>
            <input type="hidden" name="product_id" value="<?php the_ID(); ?>">
            <?php wp_nonce_field('sejoli-checkout-code-access', 'sejoli_ajax_nonce'); ?>
            <button class="ui fluid blue button" type="submit"><?php _e('Cek Kode Akses', 'sejoli'); ?></button>
            <div style='display:none;' class="ui error message">Red</div>
        </form>
        <?php endif; ?>
    </div>
</div>
<script type="text/javascript">
(function( $ ) {
    'use strict';
    $(document).ready(function(){

        $('#sejoli-checkout-code-access').submit(function(e){
            let data = $(this).serializeArray();
            e.preventDefault();
            $.ajax({
                url : '<?php echo site_url('sejoli-ajax/check-checkout-code-access/'); ?>',
                type: 'POST',
                data: data,
                dataType: 'json',
                beforeSend: function() {

                    $('#sejoli-checkout-code-access .ui.message').removeClass('error').hide();

                    sejoliSaBlockUI( '<?php _e('Mengecek kode akses', 'sejoli'); ?>', '.ui.closed.container');
                }, success: function(response) {
                    sejoliSaUnblockUI( '.ui.closed.container' );

                    if(true === response.valid) {
                        $('#sejoli-checkout-code-access .ui.message').addClass('success').html(response.message).show();
                        setTimeout(function(){
                            location.reload();
                        },1500);
                    } else {
                        $('#sejoli-checkout-code-access .ui.message').addClass('error').html(response.message).show();
                    }
                }
            })

            return false;
        });

    });
})( jQuery );
</script>
<?php
include 'footer.php';
