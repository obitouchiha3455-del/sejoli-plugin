<form method='POST' action='' id='confirmation-confirmed-modal' enctype='multipart/form-data' class="order-shipping-modal-holder ui modal">
    <i class="close icon"></i>
    <div class="header">
        <?php _e('Resi Pengiriman', 'sejoli'); ?>
    </div>
    <div class="content">
        <div class="ui divided selection list">
        </div>
    </div>
    <div class="actions">
        <button type="submit" class="sejolisa-confirm-order-shipping ui button"><?php _e('Update status order ke DIKIRIM', 'sejoli'); ?></button>
    </div>
</form>

<script type="text/javascript">

let sejoli_render_shipping_content;
let courier;

(function( $ ) {
	'use strict';
    sejoli_render_shipping_content = function(response) {
        if(false === response.valid) {
            alert('<?php _e('Terjadi kesalahan. Cek error log', 'sejoli'); ?>');
        } else {
            let template     = $.templates('#order-shipping-modal-content'),
                variant_tmpl = $.templates('#order-variant-data'),
                content      = '';
            $.each(response.orders, function(i, value){
                let variants = null;

                if(null != value.meta_data && value.meta_data.hasOwnProperty('variants')) {
                    variants = variant_tmpl.render(value.meta_data.variants)
                }

                content += template.render({
                    order_id     : value.order_id,
                    product_name : value.product_name,
                    shipping_data: value.shipping_data,
                    shipping     : ( 'physical' === value.product_type ) ? true : false,
                    variants     : variants
                });

                courier = value.shipping_data.courier;
            });
            
            $('.order-shipping-modal-holder .content .ui.divided').html(content);
            $('.order-shipping-modal-holder').modal('show');
            sejoli_table.ajax.reload();
        }
    }

    $(document).ready(function(){
        $('.order-shipping-modal-holder').submit(function(){
            let data = $(this).serializeControls();
            $.ajax({
                url : sejoli_admin.order.input_resi.ajaxurl,
                type: 'POST',
                data: {
                    data : data,
                    nonce: sejoli_admin.order.input_resi.nonce
                },
                beforeSend : function() {
                    sejoli.helper.blockUI('.order-shipping-modal-holder');
                },success  : function(response) {
                    sejoli.helper.unblockUI('.order-shipping-modal-holder');
                    $('.order-shipping-modal-holder').modal('hide');
                    sejoli_table.ajax.reload();
                }
            });
            return false;
        });
    });
})(jQuery);

</script>

<script id='order-shipping-modal-content' type="text/x-jsrender">
<div class="item sejoli-shipping-detail">
    <input type="hidden" name="order_id" value="{{:order_id}}">
    <span class='order_id'><span class="ui olive label">INV {{:order_id}}</span></span>
    <span class='product_name' style="padding-top: 2px;">
        <?php _e('Produk ', 'sejoli'); ?> : {{:product_name}} <br />
        {{:variants}}
        {{if shipping }}
            <br />
            <?php _e('Nama Pembeli ', 'sejoli'); ?> : {{:shipping_data.receiver}} <br />
            <?php _e('Kontak ', 'sejoli'); ?> : {{:shipping_data.phone}} <br />
            <?php _e('Alamat Pengiriman ', 'sejoli'); ?> : {{:shipping_data.address}}
        {{/if}}
    </span>
    {{if shipping}}
    <span class='input'>
        <label>
            <span class='ui blue label'><?php _e('Kurir ', 'sejoli'); ?></span> {{:shipping_data.courier}} - {{:shipping_data.service}}
        </label>
        {{if shipping_data.resi_number}}
            <?php 
            echo '<style>
                    .sejolisa-confirm-order-shipping { display: none !important; }
                    .sejolisa-confirm-order-pickup { display: none !important; }
                </style>';
            ?>
            <label>
                <span class='ui blue label'><?php _e('No Resi', 'sejoli'); ?> : </span> {{:shipping_data.resi_number}}
            </label>
        {{else}}
            {{if shipping_data.courier == 'COD'}}
                <?php 
                echo '<style>
                        .sejolisa-confirm-order-shipping { display: none !important; }
                    </style>';
                ?>
                <span class='ui blue label label-resi' style="display: none;"><?php _e('No Resi', 'sejoli'); ?> : </span> <span class="no-resi" style="margin: 10px 0 0 0;"></span>
                <input type='hidden' class='noresi' readonly name='order_resi[{{:order_id}}]' value='' placeholder='<?php _e('No Resi', 'sejoli'); ?>' />
            {{else}}
                <?php 
                echo '<style>
                        .sejolisa-confirm-order-pickup { display: none !important; }
                    </style>';
                ?>
                <input type='text' name='order_resi[{{:order_id}}]' value='' placeholder='<?php _e('No Resi', 'sejoli'); ?>' />
            {{/if}}
        {{/if}}
    </span>
    {{else}}
    <span class='no-need'>
        -
    </span>
    {{/if}}
</div>
</script>
