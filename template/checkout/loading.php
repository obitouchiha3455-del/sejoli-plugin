<?php
include 'header-loading.php';
?>
<div class="loading holder">
    <h2><?php _e('Mohon Tunggu', 'sejoli'); ?></h2>
    <p><?php _e('Pembelian sedang kami proses...', 'sejoli'); ?></p>
    <p style='text-align:center'>
        <img src="<?php echo SEJOLISA_URL; ?>public/img/creditcard.png" alt="loading">
    </p>
    <input type="hidden" id="order_id" value="<?php echo $order_id; ?>">
</div>
<script>
    jQuery(document).ready(function($){
        setTimeout(function(){
            sejoliSaCheckout.loading();
        }, 500);
    });
</script>
<?php
include 'footer.php';
