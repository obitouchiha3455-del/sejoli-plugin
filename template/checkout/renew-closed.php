<?php

global $sejolisa;
extract( (array) $sejolisa['subscription']);
$product_link = get_permalink($product_id);
$product = get_post( $product_id );

include 'header.php';
include 'header-logo.php';

?>
<div class="ui text closed container">
    <div class="ui segment">
        <h3 class='ui header'><?php _e('Perpanjangan order tidak bisa dilakukan', 'sejoli'); ?></h3>
        <p>
            <?php _e('Maaf, perpanjangan order ini sudah melewati batas maksimal hari perpanjangan.', 'sejoli'); ?><br />
            <?php _e('Silahkan anda order lagi melalui tombol di bawah ini.', 'sejoli'); ?>
        </p>

        <form id='sejoli-checkout-code-access' class="ui form" method="POST" action=''>
            <a href='<?php echo $product_link; ?>' class="ui fluid blue button" type="submit">
                <?php printf( __('Ke halaman checkout %s', 'sejoli'), $product->post_title ); ?>
            </a>
        </form>
    </div>
</div>
<?php
include 'footer.php';
