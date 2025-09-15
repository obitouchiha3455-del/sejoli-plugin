<?php
include 'header-confirm.php';
include 'header-logo.php';
$user_data = array(
    'name'  => ''
);

if(is_user_logged_in()) :
    $user      = wp_get_current_user();
    $user_data = array(
        'name'  => $user->display_name
    );
endif;

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
?>

<div class="ui text container">
    <div class="confirm confirm-holder">
        <h2><?php _e('Konfirmasi Pembayaran', 'sejoli'); ?></h2>
        <form class="ui form" method="post">
            <div class="required field">
                <p><strong><?php _e('Silahkan masukkan NOMOR INVOICE Anda terlebih dahulu', 'sejoli'); ?></strong></p>
                <p><?php _e('Jika nomor invoice yang anda masukkan benar, Anda bisa melakukan proses konfirmasi selanjutnya.', 'sejoli'); ?></p>
                <div class="ui right icon input">
                    <input id='invoice_id_dummy' type="text" value="<?php echo $order_id; ?>" name="invoice_id_dummy" placeholder="<?php _e('Masukan NOMOR INVOICE Anda', 'sejoli'); ?>">
                    <input id='invoice_id' type="hidden" value="<?php echo $order_id; ?>" name="invoice_id" />
                    <i class="search icon"></i>
                </div>
            </div>
            <div class="required field hide">
                <label><?php _e('Produk yang anda beli', 'sejoli'); ?></label>
                <input type="text" name="product_name" value="" readonly>
                <input type="hidden" name="product" value="" />
            </div>
            <div class="required field hide">
                <label><?php _e('Nama Pengirim', 'sejoli'); ?></label>
                <input type="text" name="nama_pengirim" placeholder="<?php _e('Masukan nama pengirim', 'sejoli'); ?>" value='<?php echo $user_data['name']; ?>'>
            </div>
            <div class="required field hide">
                <label><?php _e('No Rekening Anda', 'sejoli'); ?></label>
                <input type="text" name="no_rekening_anda" placeholder="<?php _e('Masukan nomor rekening anda', 'sejoli'); ?>">
            </div>
            <div class="required field hide">
                <label><?php _e('Bank Asal Transfer', 'sejoli'); ?></label>
                <input type="text" name="bank_asal_transfer" placeholder="<?php _e('Bank asal transfer', 'sejoli'); ?>">
            </div>
            <div class="required field hide">
                <label><?php _e('Jumlah Nominal', 'sejoli'); ?></label>
                <input type="number" name="jumlah_nominal" placeholder="<?php _e('Jumlah nominal yang anda kirimkan', 'sejoli'); ?>">
            </div>
            <div class="required field hide">
                <label><?php _e('Bank Tujuan Transfer', 'sejoli'); ?></label>
                <input type="text" name="bank_transfer" placeholder="<?php _e('Bank tujuan transfer', 'sejoli'); ?>">
            </div>
            <div class="field hide">
                <label><?php _e('Keterangan', 'sejoli'); ?></label>
                <p><?php _e('Diisi jika ada keterangan yang ingin disampaikan', 'sejoli'); ?></p>
                <textarea name="keterangan" placeholder="<?php _e('Untuk mempercepat proses verifikasi harap cantumkan nomor invoice supaya tidak tertukar dengan pembeli lain', 'sejoli'); ?>"></textarea>
            </div>
            <div class="required field hide">
                <label><?php _e('Bukti Transfer', 'sejoli'); ?></label>
                <input type="file" name="bukti_transfer">
            </div>
            <div class="confirm-info field hide">
                <p><?php _e('Pastikan konfirmasi pembayaran hanya dilakukan setelah pembayaran dilakukan.', 'sejoli'); ?></p>
            </div>
            <button type="submit" class="submit-button hide massive ui green button"><?php _e('KONFIRMASI PEMBAYARAN', 'sejoli'); ?></button>
        </form>
        <div class="alert-holder confirm-alert-holder"></div>
    </div>
    <div class="sejoli-complete-confirm ui success message hide">
        <h3 class="ui header">
            <?php _e('Konfirmasi pembayaran telah dikirimkan', 'sejoli'); ?>
        </h3>
        <p><?php _e('Terima kasih', 'sejoli'); ?></p>
        <p>
            <?php _e('Konfirmasi pembayaran anda sudah dikirimkan oleh sistem kepada admin. <br />Invoice pesanan anda akan kami proses setelah kami melakukan pengecekan.', 'sejoli'); ?>
        </p>
    </div>
    <div class="sejoli-invoice-check ui error message hide">
        <h3 class="ui header"></h3>
        <p></p>
    </div>
</div>
<script id="alert-template" type="text/x-jsrender">
    <div class="ui {{:type}} message">
        <i class="close icon"></i>
        <div class="header">
            {{:type}}
        </div>
        {{if messages}}
            <ul class="list">
                {{props messages}}
                    <li>{{>prop}}</li>
                {{/props}}
            </ul>
        {{/if}}
    </div>
</script>
<script type="text/javascript">

let delay = 0;

(function( $ ) {
    'use strict';

    let ajaxprocess, order_id;

    order_id = $('#invoice_id_dummy').val();

    if( order_id ) {

        let holder = $('.confirm-holder').parent();

        $(window).load(function() {
            
            if( typeof ajaxprocess != 'undefined' ) {
                ajaxprocess.abort();
            }

            ajaxprocess = $.ajax({
                url : '<?php echo site_url('/sejoli-ajax/check-order-for-confirmation'); ?>',
                type: 'GET',
                dataType: 'json',
                data : {
                    order_id : order_id,
                    sejoli_ajax_nonce : '<?php echo wp_create_nonce('sejoli-check-order-for-confirmation'); ?>',
                },beforeSend : function() {

                    holder.addClass('loading');
                    $('.sejoli-invoice-check.ui.error').addClass('hide').hide();
                    $('form .hide').addClass('hide').hide();

                }, success : function(response) {

                    holder.removeClass('loading');

                    if(true === response.valid) {

                        $('form .hide').removeClass('hide').show();
                        $("input[name='invoice_id']").val(response.order.invoice_id);
                        $("input[name='product_name']").val(response.order.product);
                        $("input[name='product']").val(response.order.product_id);
                        $("input[name='jumlah_nominal']").val(response.order.total);

                    } else {

                        $('.sejoli-invoice-check .header').html('<?php _e('Konfirmasi tidak bisa dilakukan', 'sejoli'); ?>');
                        $('.sejoli-invoice-check p').html(response.message);
                        $('.sejoli-invoice-check.ui.error').removeClass('hide').show();

                    }
                }
            })

        });

    }

    $('.hide').hide();

    $(document).on('keyup', '#invoice_id_dummy', function(){
        let value = $(this).val(),
            holder = $(this).parent();

        clearTimeout(delay);

        delay = setTimeout(function(){

            if( typeof ajaxprocess != 'undefined' ) {
                ajaxprocess.abort();
            }

            ajaxprocess = $.ajax({
                url : '<?php echo site_url('/sejoli-ajax/check-order-for-confirmation'); ?>',
                type: 'GET',
                dataType: 'json',
                data : {
                    order_id : value,
                    sejoli_ajax_nonce : '<?php echo wp_create_nonce('sejoli-check-order-for-confirmation'); ?>',
                },beforeSend : function() {

                    holder.addClass('loading');
                    $('.sejoli-invoice-check.ui.error').addClass('hide').hide();
                    $('form .hide').addClass('hide').hide();

                }, success : function(response) {

                    holder.removeClass('loading');

                    if(true === response.valid) {

                        $('form .hide').removeClass('hide').show();
                        $("input[name='invoice_id']").val(response.order.invoice_id);
                        $("input[name='product_name']").val(response.order.product);
                        $("input[name='product']").val(response.order.product_id);
                        $("input[name='jumlah_nominal']").val(response.order.total);

                    } else {

                        $('.sejoli-invoice-check .header').html('<?php _e('Konfirmasi tidak bisa dilakukan', 'sejoli'); ?>');
                        $('.sejoli-invoice-check p').html(response.message);
                        $('.sejoli-invoice-check.ui.error').removeClass('hide').show();

                    }
                }
            })

        }, 800);
    });

    $(document).on('submit','.confirm form', function(e){

        e.preventDefault();

        var formData = new FormData(this);

        formData.append('sejoli_ajax_nonce', sejoli_checkout.ajax_nonce.confirm);

        $.ajax({
            url: sejoli_checkout.ajax_url,
            type: 'post',
            data:formData,
            cache:false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                sejoliSaBlockUI('', '.confirm-holder');
            },
            success: function( response ) {
                // console.log( response );
                sejoliSaUnblockUI('.confirm-holder');

                var alert = {};

                if ( response.valid ) {

                    alert.type = 'success';

                } else {

                    alert.type = 'error';

                }

                alert.messages = response.messages;

                var template = $.templates("#alert-template");
                var htmlOutput = template.render(alert);
                $(".confirm-alert-holder").html(htmlOutput);

                if(true === response.valid) {
                    setTimeout(function(){
                        $(".ui.text.container .confirm").hide();
                        $(".sejoli-complete-confirm").removeClass('hide').show();
                    },1500);
                }
            }
        });

    });
}(jQuery));
</script>
<?php
include 'footer-secure.php';
include 'footer.php';
