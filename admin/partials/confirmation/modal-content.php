<script type="text/javascript">
(function( $ ) {
	'use strict';
    $(document).ready(function(){
        /**
         * Open popup
         */
        $(document).on('click', '.order-detail-trigger', function(){
            let id = $(this).data('id');

            $.ajax({
                url : sejoli_admin.confirmation.detail.ajaxurl,
                type : 'GET',
                data : {
                    id : id,
                    nonce : sejoli_admin.confirmation.detail.nonce
                },
                beforeSend: function() {
                    sejoli.helper.blockUI('.sejoli-table-holder');
                },
                success : function(response) {
                    sejoli.helper.unblockUI('.sejoli-table-holder');
                    let tmpl = $.templates('#order-modal-content');

                    let content = tmpl.render({
                        order_id : response.order_id,
                        date : response.created_at,
                        product : response.product,
                        name: response.detail.sender,
                        bank_sender: response.detail.bank_sender,
                        bank_recipient: response.detail.bank_recipient,
                        account_number: response.detail.account_number,
                        proof: response.detail.proof,
                        note: response.detail.note
                    });

                    $('.order-modal-holder').html(content).modal('show');
                }
            })
        });
    });
})(jQuery);
</script>
<script id='order-modal-content' type="text/x-jsrender">
<i class="close icon"></i>
<div class="header">
    <?php _e('Detil Konfirmasi Pembayan INV{{:order_id}}', 'sejoli'); ?>
</div>
<div class="content">
    <div class="ui divided selection list">
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Tanggal', 'sejoli'); ?></span>
            {{:date}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Nomor Invoice', 'sejoli'); ?></span>
            INV{{:order_id}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Produk', 'sejoli'); ?></span>
            {{:product}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Nama pengkonfirmasi', 'sejoli'); ?></span>
            {{:name}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Bank asal transfer', 'sejoli'); ?></span>
            {{:bank_sender}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Nomor rekening pengirim', 'sejoli'); ?></span>
            {{:account_number}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Bank tujuan transfer', 'sejoli'); ?></span>
            {{:bank_recipient}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Bukti Transfer', 'sejoli'); ?></span>
            <a href='{{:proof}}' target='_blank'>lihat bukti transfer</a>
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Catatan', 'sejoli'); ?></span>
            {{:note}}
        </div>
    </div>
</div>
</script>
