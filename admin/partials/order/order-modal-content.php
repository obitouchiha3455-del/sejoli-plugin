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
                url  : sejoli_admin.order.detail.ajaxurl,
                type : 'GET',
                data : {
                    order_id : order_id,
                    nonce    : sejoli_admin.order.detail.nonce
                },
                beforeSend: function() {
                    sejoli.helper.blockUI('.sejoli-table-holder');
                },
                success : function(response) {
                    sejoli.helper.unblockUI('.sejoli-table-holder');
                    let tmpl            = $.templates('#order-modal-content');
                    let affiliate_name  = null,
                        affiliate_phone = null,
                        affiliate_email = null;
                    let buyer_phone = null,
                        buyer_email = null;

                    if (0 != response.affiliate_id && response.affiliate !== null) {
                        if (response.affiliate.data && response.affiliate.data.display_name) {
                            affiliate_name  = response.affiliate.data.display_name;
                        }
                        if (response.affiliate.data && response.affiliate.data.meta && response.affiliate.data.meta.phone) {
                            affiliate_phone = response.affiliate.data.meta.phone;
                        }
                        if (response.affiliate.data && response.affiliate.data.user_email) {
                            affiliate_email = response.affiliate.data.user_email;
                        }
                    }

                    if(response.user !== null) {
                        buyer_phone = response.user.data.meta.phone;
                        buyer_email = response.user.data.user_email;
                    }

                    let subscription = sejoli_admin.subscription.type[response.type];
                    let variants     = null;
                    let variant_tmpl = $.templates('#order-variant-data');

                    if(response.meta_data.variants) {
                        variants = variant_tmpl.render(response.meta_data.variants)
                    }

                    let content = tmpl.render({
                        id              : order_id,
                        date            : sejoli.helper.convertdate(response.created_at),
                        buyer_name      : response.user.data.display_name,
                        buyer_email     : buyer_email,
                        buyer_phone     : buyer_phone,
                        variants        : variants,
                        product_name    : response.product.post_title,
                        quantity        : response.quantity,
                        total           : sejoli_admin.text.currency + sejoli.helper.formatPrice(response.grand_total),
                        status          : sejoli_admin.order.status[response.status],
                        color           : sejoli_admin.color[response.status],
                        subscription    : (null != subscription) ? subscription.toUpperCase() : null,
                        courier         : response.courier,
                        address         : response.address,
                        parent_order    : (response.order_parent_id > 0) ? response.order_parent_id : null,
                        affiliate_name  : affiliate_name,
                        affiliate_phone : affiliate_phone,
                        affiliate_email : affiliate_email,
                        meta_data       : response.meta_data,
                        markup_price    : sejoli_admin.text.currency + sejoli.helper.formatPrice(response.meta_data.markup_price),
                        confirm_date    : sejoli.helper.convertdate(response.confirm_date),
                        confirm_detail  : response.confirm_detail,
                        status_log      : response.status_log,
                        payment_gateway : response.payment_gateway.toUpperCase(),
                        payment_channel : (null != response.payment_data) ? response.payment_data.payment_channel : null,
                        payment_fee     : (null != response.payment_data) ? sejoli_admin.text.currency + sejoli.helper.formatPrice(response.payment_data.payment_fee) : null
                    });

                    $('.order-modal-holder').html(content).modal('show');
                }
            })
        });

        let sejoli_check_shipping = function(order_id) {
            $.ajax({
                url  : sejoli_admin.order.shipping.ajaxurl,
                type : 'POST',
                data : {
                    orders : order_id,
                    nonce  : sejoli_admin.order.shipping.nonce
                },
                beforeSend : function() {
                    sejoli.helper.blockUI('.order-modal-holder');
                },success  : function(response) {
                    sejoli.helper.unblockUI('.order-modal-holder');
                    sejoli_render_shipping_content(response);
                }
            });
        }

        $(document).on('click', '.update-order-popup', function(){

            let order_id = $(this).data('id');
            let action   = $('.action-order-select-popup').val();

            if('' !== action) {

                let confirmed = confirm('<?php _e('Apakah anda yakin akan mengupdate order ', 'sejoli'); ?>' + order_id + '?');

                if(confirmed) {

                    if('shipping' === action) {
                        sejoli_check_shipping(order_id);
                    } else {
                        $.ajax({
                            url  : sejoli_admin.order.update.ajaxurl,
                            type : 'POST',
                            data : {
                                orders : [order_id],
                                status : action,
                                nonce  : sejoli_admin.order.update.nonce
                            },
                            beforeSend : function() {
                                sejoli.helper.blockUI('.sejoli-table-holder');
                            },success  : function(response) {
                                sejoli.helper.unblockUI('.sejoli-table-holder');
                                sejoli_table.ajax.reload();
                                $('.order-modal-holder').modal('hide');
                            }
                        });
                    }
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
            <span class="ui large main blue horizontal label" style='float:left;'><?php _e('Produk', 'sejoli'); ?></span>
            <span class="order-product-detail">
                {{:product_name}} X{{:quantity}} <br />
                {{:variants}}
            </span>
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
        {{if payment_fee && payment_channel}}
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Channel Pembayaran', 'sejoli'); ?></span>
                {{:payment_gateway}} - {{:payment_channel}}
                (<?php _e('Biaya layanan:', 'sejoli'); ?> {{:payment_fee}})
            </div>    
        {{/if}}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Total', 'sejoli'); ?></span>
            {{:total}}
        </div>
        {{if courier}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Kurir', 'sejoli'); ?></span>
                {{:courier}}
            </div>
        {{/if}}
        {{if markup_price !== 'Rp. NaN'}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Biaya COD', 'sejoli'); ?></span>
                {{:markup_price}}
            </div>
        {{/if}}
        {{if meta_data.shipping_data }}
            {{if meta_data.shipping_data.resi_number}}
                <div class='item'>
                    <span class="ui large main blue horizontal label"><?php _e('Nomor Resi', 'sejoli'); ?></span>
                    {{:meta_data.shipping_data.resi_number}}
                </div>
            {{/if}}
        {{/if}}
        {{if address}}
            <div class='item'>
                <span class="ui large main blue horizontal label" style='float:left;'><?php _e('Alamat Pengiriman', 'sejoli'); ?></span>
    			<span class='order-product-detail'>{{:address}}</span>
                {{if meta_data.shipping_data }}
                    {{if meta_data.shipping_data.postal_code}}
                        , {{:meta_data.shipping_data.postal_code}}
                    {{/if}}    
                {{/if}}
            </div>
        {{/if}}
		{{if meta_data.note }}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Catatan Pemesanan', 'sejoli'); ?></span>
    			{{:meta_data.note}}
            </div>
        {{/if}}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Status', 'sejoli'); ?></span>
            <span class="ui large horizontal label" style="background-color:{{:color}};color:white;">{{:status}}</span>
        </div>
        {{if status_log}}
        <div class="item">
            <span class="ui large main blue horizontal label"><?php _e('Log Status', 'sejoli'); ?></span>
            <table style="display: inline-flex;height: 100px;overflow-y: scroll;width: 80%;">
                {{for status_log}}
                <tr>
                    <td><?php echo __('- Order status changed from ', 'sejoli'); ?><b>{{:old_status}}</b> <?php echo __('to ', 'sejoli'); ?><b>{{:new_status}}</b>. <span style="font-size: 12px; color: #999;">~ {{:update_date}} 
                        {{if updated_by}}
                        <?php echo __('by ', 'sejoli'); ?> {{:updated_by}}</span></td>
                        {{/if}}
                </tr>
                {{/for}}
            </table>
        </div>
        {{/if}}
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
        {{if confirm_date }}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Tanggal Konfirmasi', 'sejoli'); ?></span>
                {{:confirm_date}}
            </div>
        {{/if}}
        {{if confirm_detail}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Bukti Konfirmasi', 'sejoli'); ?></span>
                <a href='{{:confirm_detail.proof}}' target='_blank'>lihat bukti konfirmasi</a>
            </div>
        {{/if}}
    </div>
</div>
<div class="shipment-tracking"></div>
<div class="actions">
    <select class="action-order-select-popup">
        <option value=""><?php _e('Pilihan aksi', 'sejoli'); ?></option>
        <?php foreach($order_status as $key => $label) : ?>
            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
        <?php endforeach; ?>
        <option value="resend"><?php _e('Kirim ulang notifikasi', 'sejoli'); ?></option>
    </select>
    <a href='#' class='update-order-popup ui button' data-id='{{:id}}'><?php _e('Update Order', 'sejoli'); ?></a>
</div>
</script>
<script id='order-variant-data' type="text/x-jsrender">
<span style='text-transform:capitalize;'>{{:type}}</span> : {{:label}} <br />
</script>
