<script type="text/javascript">
(function( $ ) {
	'use strict';
    $(document).ready(function(){
        /**
         * Open popup
         */
        $(document).on('click', '.order-detail-trigger', function(){
            let order_id = $(this).data('id');

            $.ajax({
                url : sejoli_admin.order.detail.ajaxurl,
                type : 'GET',
                data : {
                    order_id : order_id,
                    nonce : sejoli_admin.order.detail.nonce
                },
                beforeSend: function() {
                    sejoli.helper.blockUI('.sejoli-table-holder');
                },
                success : function(response) {

                    sejoli.helper.unblockUI('.sejoli-table-holder');
                    let tmpl = $.templates('#order-modal-content');
                    let affiliate_name  = null,
                        affiliate_phone = null,
                        affiliate_email = null;
                    let buyer_phone = null,
                        buyer_email = null;

                    if(0 != response.affiliate_id && response.affiliate !== null || response.affiliate !== null) {
                        affiliate_name  = response.affiliate.data.display_name;
                        affiliate_phone = response.affiliate.data.meta.phone;
                        affiliate_email = response.affiliate.data.user_email;
                    }

                    if(response.user !== null) {
                        buyer_phone = response.user.data.meta.phone;
                        buyer_email = response.user.data.user_email;
                    }
                    let subscription = sejoli_admin.subscription.type[response.type];
                    let content = tmpl.render({
                        id : order_id,
                        date : sejoli.helper.convertdate(response.created_at),
                        buyer_name : response.user.data.display_name,
                        buyer_email     : buyer_email,
                        buyer_phone     : buyer_phone,
                        product_name : response.product.post_title,
                        total : sejoli_admin.text.currency + sejoli.helper.formatPrice(response.grand_total),
                        status : sejoli_admin.order.status[response.status],
                        color : sejoli_admin.color[response.status],
                        subscription : (null != subscription) ? subscription.toUpperCase() : null,
                        parent_order : (response.order_parent_id > 0) ? response.order_parent_id : null,
                        affiliate_name : affiliate_name,
                        affiliate_phone : affiliate_phone,
                        affiliate_email : affiliate_email,
                        meta_data       : response.meta_data,
                    });

                    $('.order-modal-holder').html(content).modal('show');
                }
            })
        });

        $(document).on('click', '.update-order-popup', function(){

            let order_id = $(this).data('id');
            let action = $('.action-order-select-popup').val();

            if('' !== action) {

                let confirmed = confirm('<?php _e('Apakah anda yakin akan mengupdate order ', 'sejoli'); ?>' + order_id + '?');

                if(confirmed) {
                    $.ajax({
                        url : sejoli_admin.order.update.ajaxurl,
                        type : 'POST',
                        data : {
                            orders : [order_id],
                            status : action,
                            nonce : sejoli_admin.order.update.nonce
                        },
                        beforeSend : function() {
                            sejoli.helper.blockUI('.sejoli-table-holder');
                        },success : function(response) {
                            sejoli.helper.unblockUI('.sejoli-table-holder');
                            sejoli_table.ajax.reload();
                            $('.order-modal-holder').modal('hide');
                        }
                    });
                }
            }
        });
    });
})(jQuery);
</script>
<script id='order-modal-content' type="text/x-jsrender">
<i class="close icon"></i>
<div class="header">
    <?php _e('Detil Order {{:id}}', 'sejoli'); ?>
</div>
<div class="content">
    <div class="ui divided selection list">
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Tanggal', 'sejoli'); ?></span>
            {{:date}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Nama Pembeli', 'sejoli'); ?></span>
            {{:buyer_name}}
        </div>
        {{if buyer_email}}
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Kontak', 'sejoli'); ?></span>
                <span class='ui grey label'><i class="phone icon"></i>{{:buyer_phone}}</span>
                <span class='ui grey label'><i class="envelope icon"></i>{{:buyer_email}}</span>
            </div>
        {{/if}}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Produk', 'sejoli'); ?></span>
            {{:product_name}}
        </div>
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Total', 'sejoli'); ?></span>
            {{:total}}
        </div>
        {{if meta_data.ppn}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('PPN', 'sejoli'); ?> ({{:meta_data.ppn}}%)</span>
                {{:meta_data.ppn_total}}
            </div>
        {{/if}}
        {{if meta_data.unique_code}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Biaya Transaksi', 'sejoli'); ?></span>
                {{:meta_data.unique_code}}
            </div>
        {{/if}}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Status', 'sejoli'); ?></span>
            <span class="ui large horizontal label" style="background-color:{{:color}};color:white;">{{:status}}</span>
        </div>

        {{if subscription }}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Tipe Langganan', 'sejoli'); ?></span>
            <span class="ui brown label" style='text-transform:uppercase;'><i class="clock icon"></i>{{:subscription}}</span>
        </div>
        {{/if}}

        {{if parent_order}}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Invoice Asal', 'sejoli'); ?></span>
            <span class="ui pink label" style='text-transform:uppercase;'><i class="redo icon"></i>INV {{:parent_order}}</span>
        </div>
        {{/if}}

        {{if affiliate_name}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Affiliasi', 'sejoli'); ?></span>
                {{:affiliate_name}}
                <span class='ui grey label'><i class="phone icon"></i>{{:affiliate_phone}}</span>
                <span class='ui grey label'><i class="envelope icon"></i>{{:affiliate_email}}</span>
            </div>
        {{/if}}
    </div>
</div>
<div class="actions">
    <select class="action-order-select-popup">
        <option value=""><?php _e('Pilihan aksi', 'sejoli'); ?></option>
        <option value="on-hold"><?php _e('Ubah status langganan menjadi Belum Aktif', 'sejoli'); ?></option>
        <option value="cancelled"><?php _e('Ubah status langganan menjadi Tidak Aktif', 'sejoli'); ?></option>
        <option value="completed"><?php _e('Ubah status langganan menjadi Aktif', 'sejoli'); ?></option>
    </select>
    <a href='#' class='update-order-popup ui button' data-id='{{:id}}'><?php _e('Update Order', 'sejoli'); ?></a>
</div>
</script>
