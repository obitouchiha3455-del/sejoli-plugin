<?php
    $order_status = apply_filters('sejoli/order/status', []);
    $date         = date('Y-m-d',strtotime('-30day')) . ' - ' . date('Y-m-d');
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data Penjualan', 'sejoli'); ?>
	</h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-chart-data box" style='float:right;'>
                <select name="chart-display">
                    <option value="total-order" selected="selected"><?php _e('Berdasarkan total order', 'sejoli'); ?></option>
                    <option value="total-paid"><?php _e('Berdasarkan total pembayaran', 'sejoli'); ?></option>
                    <option value="total-quantity"><?php _e('Berdasarkan total kuantitas barang', 'sejoli'); ?></option>
                </select>
            </div>
        </div>
        <div class="sejoli-chart-holder">
            <canvas id="chart-canvas" width="100%" height="100%" style="display:block; max-height: 526px !important"></canvas>
        </div>
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-order box">
                <select class="update-order-select" name="update-order-select">
                    <option value=""><?php _e('Pilihan aksi pada order yang dipilih', 'sejoli'); ?></option>
                    <?php foreach($order_status as $key => $label) : ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                    <option value="resend"><?php _e('Kirim ulang notifikasi', 'sejoli'); ?></option>
                </select>
                <button type="button" name="button" class='update-order button button-primary'><?php _e('Update Order', 'sejoli'); ?></button>
            </div>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='export-csv button'><?php _e('Export CSV', 'sejoli'); ?></button>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="ID" value="" placeholder="<?php _e('Pencarian order ID. Gunakan koma jika lebih dari satu', 'sejoli'); ?>">
                    <input type="text" class='filter' name="date-range" value="<?php echo $date; ?>" placeholder="<?php _e('Pencarian berdasarkan tanggal', 'sejoli'); ?>">
                    <input type="text" class='filter' name="grand_Total" value="" placeholder="<?php _e('Pencarian berdasarkan nilai invoice', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="user_id"></select>
                    <select class="autosuggest filter" name="affiliate_id"></select>
                    <select class="autosuggest filter" name="product_id"></select>
                    <select class="autosuggest filter" name="status">
                        <option value=""><?php _e('Semua status order', 'sejoli'); ?></option>
                        <?php foreach($order_status as $key => $label) : ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="autosuggest filter" name="type">
                        <option value=""><?php _e('Semua tipe order', 'sejoli'); ?></option>
                        <option value="regular"><?php _e('Pembelian sekali waktu', 'sejoli'); ?></option>
                        <option value="subscription-tryout"><?php _e("Berlangganan - Tryout", 'sejoli'); ?></option>
                        <option value="subscription-signup"><?php _e("Berlangganan - Awal", 'sejoli'); ?></option>
                        <option value="subscription-regular"><?php _e("Berlangganan - Regular", 'sejoli'); ?></option>
                    </select>
                    <?php wp_nonce_field('search-order', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-order" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Detil',     'sejoli'); ?></th>
                        <th><?php _e('Pembeli',   'sejoli'); ?></th>
                        <th><?php _e('Affiliasi', 'sejoli'); ?></th>
                        <th><?php _e('Total',     'sejoli'); ?></th>
                        <th><?php _e('Status',    'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Detil',      'sejoli'); ?></th>
                        <th><?php _e('Pembeli',    'sejoli'); ?></th>
                        <th><?php _e('Affiliasi',  'sejoli'); ?></th>
                        <th><?php _e('Total',      'sejoli'); ?></th>
                        <th><?php _e('Status',     'sejoli'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-order box">
                <select class="update-order-select" name="update-order-select">
                    <option value=""><?php _e('Pilihan aksi pada order yang dipilih', 'sejoli'); ?></option>
                    <?php foreach($order_status as $key => $label) : ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" name="button" class='update-order button button-primary'><?php _e('Update Order', 'sejoli'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="order-modal-holder ui modal"></div>

<script type="text/javascript">

let sejoli_table,
    sejoli_order_action = null;

(function( $ ) {
	'use strict';

    let sejoli_direct_update = function(order_id, status) {
        $.ajax({
            url  : sejoli_admin.order.update.ajaxurl,
            type : 'POST',
            data : {
                orders : order_id,
                status : status,
                nonce  : sejoli_admin.order.update.nonce
            },
            beforeSend : function() {
                sejoli.helper.blockUI('.sejoli-table-holder');
            },success  : function(response) {
                sejoli.helper.unblockUI('.sejoli-table-holder');
                sejoli_table.ajax.reload();
            }
        });
    }

    let sejoli_check_shipping = function(order_id) {
        $.ajax({
            url  : sejoli_admin.order.shipping.ajaxurl,
            type : 'POST',
            data : {
                orders : order_id,
                nonce  : sejoli_admin.order.shipping.nonce
            },
            beforeSend : function() {
                sejoli.helper.blockUI('.sejoli-table-holder');
            },success  : function(response) {
                sejoli.helper.unblockUI('.sejoli-table-holder');
                sejoli_render_shipping_content(response);
            }
        });
    }

    $(document).ready(function() {

        function update_chart() {
            $.ajax({
                url  : sejoli_admin.order.chart.ajaxurl,
                type : 'GET',
                dataType: 'json',
                data : {
                    action : 'sejoli-order-chart',
                    data   : sejoli.var.search,
                    type   : chart_display.val()
                },
                beforeSend : function() {
                    sejoli.helper.blockUI('.sejoli-chart-holder');
                },
                success    : function(response) {
                    sejoli.helper.unblockUI('.sejoli-chart-holder');
                    let format = ('total-paid' == chart_display.val()) ? 'currency' : ''
                    sejoli.helper.chartJS('chart-canvas', response.datasets, response.labels, format);
                }
            });
        }

        sejoli.helper.select_2(
            "select[name='user_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.user.placeholder
        );

        sejoli.helper.select_2(
            "select[name='affiliate_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.affiliate.placeholder
        );

        sejoli.helper.select_2(
            "select[name='product_id']",
            sejoli_admin.product.select.ajaxurl,
            sejoli_admin.product.placeholder
        );

        let chart_display = $("select[name='chart-display']").select2({
            placeholder : '<?php _e('Pengaturan data chart', 'sejoli'); ?>',
            width : '100%'
        });

        sejoli.helper.daterangepicker("input[name='date-range']");

        $("select[name='status'], select[name='type']").select2({
            width : '100%'
        });

        sejoli.helper.filterData();

        sejoli_table = $('#sejoli-order').DataTable({
            language   : dataTableTranslation,
            searching  : false,
            processing : false,
            serverSide : true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.order.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-order-table';
                    data.security = sejoli.ajax_nonce;
                    data.backend  = true;
                }
            },
            pageLength : 50,
            lengthMenu : [
                [10, 50, 100, 200, -1],
                [10, 50, 100, 200, dataTableTranslation.all],
            ],
            order: [
                [ 1, "desc" ]
            ],
            columnDefs: [
                {
                    targets: [0, 2, 3, 4, 5],
                    orderable: false
                },{
                    targets   : 0,
                    width     : '18px',
                    className : 'center',
                    data      : 'checkbox',
                    render: function ( data, type, full) {
                      return '<input type="checkbox" value="' + full.ID + '" data-id="' + full.ID +'" />';
                    },
                },{
                    targets: 1,
                    data : 'ID',
                    render : function( data, type, full) {
                        let tmpl      = $.templates('#order-detail'),
                            subsctype = null,
                            quantity  = null;

                        if(1 < parseInt(full.quantity)) {
                            quantity = full.quantity;
                        }

                        return tmpl.render({
                            id       : full.ID,
                            product  : full.product_name,
                            coupon   : full.coupon_code,
                            parent   : (0 === parseInt(full.order_parent_id)) ? false : full.order_parent_id,
                            date     : ("0000-00-00 00:00:00" !== full.updated_at) ? sejoli.helper.convertdate(full.updated_at) : sejoli.helper.convertdate(full.created_at),
                            type     : sejoli_admin.subscription.type[full.type],
                            menu     : Hooks.apply_filters('sejoli_order_action', '', full),
                            quantity : quantity,
                        })
                    }
                },{
                    targets : 2,
                    width   : '15%',
                    data    : 'user_name'
                },{
                    targets : 3,
                    width   : '15%',
                    data    : 'affiliate_name'
                },{
                    targets   : 4,
                    width     : '10%',
                    data      : 'grand_total',
                    className : 'price',
                    render : function(data, type, full) {
                        return sejoli_admin.text.currency + sejoli.helper.formatPrice(data)
                    }
                },{
                    targets : 5,
                    data    : 'status',
                    width   : '100px',
                    render : function( data, type, full ) {
                        let tmpl = $.templates('#order-status');
                        return tmpl.render({
                            label : sejoli_admin.order.status[full.status],
                            color : sejoli_admin.color[full.status]
                        });
                    }
                }
            ]
        });

        sejoli_table.on('preXhr',function(){
            console.log('load');
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(){
            console.log('loaded');
            update_chart();
            sejoli.helper.unblockUI('.sejoli-table-holder');
        });

        sejoli_table.on('draw',function(){
            // console.log( $('.order-action').dropdown() );
        });

        $(document).on('click', '.toggle-search', function(){
            $('.sejoli-form-filter-holder').toggle();
        });

        $(document).on('click', '.do-search', function(){
            sejoli.helper.filterData();
            sejoli_table.ajax.reload();
            $('.sejoli-form-filter-holder').hide();
        });

        $(document).on('click', '.reset-search', function(){
            sejoli.helper.clearFilter();
            sejoli_table.ajax.reload();
        });

        chart_display.on('change', function(){
            update_chart();
        });

        $('input.parent-checkbox').change(function(){
            var checked = $(this).is(':checked');
            $("tbody input[type='checkbox']").attr('checked', checked);
            $('input.parent-checkbox').attr('checked', checked);
        });

        /**
         * Update bulk order
         */
        $(document).on('click', '.update-order', function(){
            let proceed  = true;
            let order_id = [];
            let status   = $(this).parent().find('select[name=update-order-select]').val();
                
            if('' === status) {
                alert('<?php _e('Anda belum memilih aksi', 'sejoli'); ?>');
                return;
            }

            if('shipping' != status) {
                proceed = confirm('<?php _e('Anda yakin akan melakukan update pada order yang dipilih?', 'sejoli'); ?>');
            }

            if(proceed) {

                $("tbody input[type=checkbox]:checked").each(function(i, el){
                    order_id.push($(el).data('id'));
                });

                if(0 < order_id.length) {
                    if('shipping' === status) {
                        sejoli_check_shipping(order_id);
                    } else {
                        sejoli_direct_update(order_id, status);
                    }
                } else {
                    alert('<?php _e('Anda belum memilih order', 'sejoli'); ?>');
                    return;
                }
            }
        });

        /**
         * Do export csv
         */
        $(document).on('click', '.export-csv', function(){
            sejoli.helper.filterData();
            $.ajax({
                url      : sejoli_admin.order.export_prepare.ajaxurl,
                type     : 'POST',
                dataType : 'json',
                data     : {
                    action  : 'sejoli-order-export-prepare',
                    nonce   : sejoli_admin.order.export_prepare.nonce,
                    data    : sejoli.var.search,
                    backend : 1
                },
                beforeSend : function() {
                    sejoli.helper.blockUI('.sejoli-table-holder');
                },
                success : function(response) {
                    sejoli.helper.unblockUI('.sejoli-table-holder');
                    window.location.href = response.url.replace(/&amp;/g, '&');
                }
            });
            return false;
        });
    });
})(jQuery);

</script>

<script id='order-detail' type="text/x-jsrender">
<button type='button' class='order-detail-trigger ui mini button' data-id='{{:id}}'>DETAIL</button>
{{if menu}}
<div class='order-action ui simple dropdown'>
    <div class='text'>
        <strong>
            {{:product}}
            {{if quantity}}
                <span class='ui label red'>x{{:quantity}}</span>
            {{/if}}
        </strong>
    </div>
    <i class="dropdown icon"></i>
    <div class='menu'>
        {{:menu}}
    </div>
</div>
{{else}}
<strong>
    {{:product}}
    {{if quantity}}
        <span class='ui label red'>x{{:quantity}}</span>
    {{/if}}
</strong>
{{/if}}
<div style='line-height:220%'>
    <span class="ui olive label">INV {{:id}}</span>
    <span class="ui teal label"><i class="calendar outline icon"></i>{{:date}}</span>
    {{if parent }}
        <span class="ui pink label" style='text-transform:uppercase;'><i class="redo icon"></i>INV {{:parent}}</span>
    {{/if}}
    {{if type }}
        <span class="ui brown label" style='text-transform:uppercase;'><i class="clock icon"></i>{{:type}}</span>
    {{/if}}
    {{if coupon }}
        <span class="ui purple label" style='text-transform:uppercase;'><i class="cut icon"></i>{{:coupon}}</span>
    {{/if}}
</div>
</script>

<script id='order-status' type="text/x-jsrender">
<div class="ui horizontal label boxed" style="background-color:{{:color}};">{{:label}}</div>
</script>

<?php require 'order-followup.php'; ?>
<?php require 'order-modal-content.php'; ?>
<?php require 'order-shipping-modal-content.php'; ?>