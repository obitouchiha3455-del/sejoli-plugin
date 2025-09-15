<?php
    $date = date('Y-m-d',strtotime('-30day')) . ' - ' . date('Y-m-d');
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data Komisi', 'sejoli'); ?>
    </h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-chart-data box" style='float:right;'>
                <select name="chart-display">
                    <option value="total-order" selected="selected"><?php _e('Berdasarkan total order', 'sejoli'); ?></option>
                    <option value="total-paid"><?php _e('Berdasarkan total komisi', 'sejoli'); ?></option>
                </select>
            </div>
        </div>
        <div class="sejoli-chart-holder">
            <canvas id="chart-canvas" width="100%" height="100%" style="display:block; max-height: 526px !important"></canvas>
        </div>
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-commission box">
                <select class="update-commission-select" name="update-commission-select">
                    <option value=""><?php _e('Pilihan aksi pada komisi yang dipilih', 'sejoli'); ?></option>
                    <option value=1><?php _e('Konfirmasi pembayaran komisi', 'sejoli'); ?></option>
                    <!-- <option value=0><?php //_e('Ubah status komisi ke Belum Dibayar', 'sejoli'); ?></option> -->
                </select>
                <button type="button" name="button" class='update-commission button button-primary'><?php _e('Update Komisi', 'sejoli'); ?></button>
            </div>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="order_id" value="" placeholder="<?php _e('Pencarian order ID', 'sejoli'); ?>">
                    <input type="text" class='filter' name="date-range" value="<?php echo $date; ?>" placeholder="<?php _e('Pencarian berdasarkan tanggal', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="affiliate_id"></select>
                    <select class="autosuggest filter" name="product_id"></select>
                    <select class="autosuggest filter" name="status">
                        <option value=""><?php _e('Status Order', 'sejoli'); ?></option>
                        <option value="pending"><?php _e('Order belum selesai', 'sejoli'); ?></option>
                        <option value="added" selected='selected'><?php _e('Order sudah selesai', 'sejoli'); ?></option>
                        <option value="cancelled"><?php _e('Order dibatalkan', 'sejoli'); ?></option>
                    </select>
                    <select class="update-commission-select filter" name="paid_status">
                        <option value="" selected='selected'><?php _e('Status Pembayaran', 'sejoli'); ?></option>
                        <option value=1><?php _e('Sudah Dibayar', 'sejoli'); ?></option>
                        <option value=0><?php _e('Belum Dibayar', 'sejoli'); ?></option>
                    </select>
                    <?php wp_nonce_field('search-commission', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-commission" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Detil',       'sejoli'); ?></th>
                        <th><?php _e('Affiliasi',   'sejoli'); ?></th>
                        <th><?php _e('Tier',        'sejoli'); ?></th>
                        <th><?php _e('Total',       'sejoli'); ?></th>
                        <th><?php _e('Status',      'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Detil',       'sejoli'); ?></th>
                        <th><?php _e('Affiliasi',   'sejoli'); ?></th>
                        <th><?php _e('Tier',        'sejoli'); ?></th>
                        <th><?php _e('Total',       'sejoli'); ?></th>
                        <th><?php _e('Status',      'sejoli'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-commission box">
                <select class="update-commission-select" name="update-commission-select">
                    <option value=""><?php _e('Pilihan aksi pada komisi yang dipilih', 'sejoli'); ?></option>
                    <option value=1><?php _e('Konfirmasi pembayaran komisi', 'sejoli'); ?></option>
                    <!-- <option value=0><?php //_e('Ubah status komisi ke Belum Dibayar', 'sejoli'); ?></option> -->
                </select>
                <button type="button" name="button" class='update-commission button button-primary'><?php _e('Update Komisi', 'sejoli'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

let sejoli_table;

(function( $ ) {
    'use strict';

    $(document).ready(function() {

        function update_chart() {
            $.ajax({
                url : sejoli_admin.commission.chart.ajaxurl,
                type : 'GET',
                dataType: 'json',
                data : {
                    action : 'sejoli-commission-chart',
                    data : sejoli.var.search,
                    type : chart_display.val()
                },
                beforeSend : function() {
                    sejoli.helper.blockUI('.sejoli-chart-holder');
                },
                success : function(response) {
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

        $("select[name='status'], select[name='paid_status']").select2({
            width : '100%'
        });

        sejoli.helper.filterData();

        sejoli_table = $('#sejoli-commission').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.commission.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-commission-table';
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
                    targets: [0, 2, 5],
                    orderable: false
                },{
                    targets: 0,
                    width: '18px',
                    className: 'center',
                    data: 'checkbox',
                    render: function ( data, type, full) {
                      return '<input type="checkbox" value="' + full.ID + '" data-id="' + full.ID +'" />';
                    },
                },{
                    targets: 1,
                    data : 'ID',
                    render : function(data, type, full) {
                        let tmpl = $.templates('#order-detail'),
                            subsctype = null;

                        return tmpl.render({
                            id : full.ID,
                            order_id : full.order_id,
                            product : full.product_name,
                            date : ("0000-00-00 00:00:00" !== full.updated_at) ? sejoli.helper.convertdate(full.updated_at) : sejoli.helper.convertdate(full.created_at)
                        })
                    }
                },{
                    targets: 2,
                    width: '15%',
                    data: 'affiliate_id',
                    render : function(data, type, full) {
                        return full.affiliate_name;
                    }
                },{
                    targets: 3,
                    width: '32px',
                    data : 'tier',
                    className : 'center',
                },{
                    targets: 4,
                    width: '180px',
                    data : 'commission',
                    className : 'price',
                    render : function(data ,type, full) {
                        return sejoli_admin.text.currency + sejoli.helper.formatPrice(data);
                    }
                },{
                    targets: 5,
                    width : '100px',
                    data : 'status',
                    render : function( data, type, full) {
                        let tmpl = $.templates('#order-status'),
                            status = full.status;

                        if(1 === parseInt(full.paid_status)) {
                            status = 'paid';
                        }

                        return tmpl.render({
                            status : status,
                            label : sejoli_admin.commission.status[status],
                            color : sejoli_admin.color[status]
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
        })

        $('input.parent-checkbox').change(function(){
            var checked = $(this).is(':checked');
            $("tbody input[type='checkbox']").attr('checked', checked);
            $('input.parent-checkbox').attr('checked', checked);
        });

        $(document).on('click', '.update-commission', function(){
            let proceed  = true;
            let commission_id = [];
            let status   = $(this).parent().find('select[name=update-commission-select]').val();

            if('' === status) {
                alert('<?php _e('Anda belum memilih aksi'); ?>');
                return;
            }

            proceed = confirm('<?php _e('Anda yakin akan melakukan aksi pada komisi yang dipilih?', 'sejoli'); ?>');

            if(proceed) {

                $("tbody input[type=checkbox]:checked").each(function(i, el){
                    commission_id.push($(el).data('id'));
                });

                if(0 < commission_id.length) {

                    if(1 === parseInt(status)) {
                        $.ajax({
                           url : sejoli_admin.commission.confirm.ajaxurl,
                           type : 'POST',
                           data : {
                               commissions : commission_id,
                               status : status,
                               nonce : sejoli_admin.commission.confirm.nonce
                           },
                           beforeSend : function() {
                               sejoli.helper.blockUI('.sejoli-table-holder');
                           },success : function(response) {
                               sejoli.helper.unblockUI('.sejoli-table-holder');
                               sejoli_render_confirmation(response);
                           }
                       });
                    }

                    // $.ajax({
                    //     url : sejoli_admin.commission.update.ajaxurl,
                    //     type : 'POST',
                    //     data : {
                    //         commissions : commission_id,
                    //         status : status,
                    //         nonce : sejoli_admin.commission.nonce
                    //     },
                    //     beforeSend : function() {
                    //         sejoli.helper.blockUI('.sejoli-table-holder');
                    //     },success : function(response) {
                    //         sejoli.helper.unblockUI('.sejoli-table-holder');
                    //         sejoli_table.ajax.reload();
                    //     }
                    // });
                } else {
                    alert('<?php _e('Anda belum memilih komisi', 'sejoli'); ?>');
                    return;
                }
            }
        });
    });
})(jQuery);
</script>

<script id='order-detail' type="text/x-jsrender">
<button type='button' class='order-detail-trigger ui mini button' data-id='{{:order_id}}' data-commission='{{:id}}'>DETAIL</button>
<strong>
    {{:product}}
</strong>
<div style='line-height:220%'>
    <span class="ui olive label">INV {{:order_id}}</span>
    <span class="ui teal label"><i class="calendar outline icon"></i>{{:date}}</span>
</div>
</script>

<script id='order-status' type="text/x-jsrender">
<div class="ui horizontal label boxed {{:status}}" style="background-color:{{:color}};">{{:label}}</div>
</script>

<?php require 'order-modal-content.php'; ?>
<?php require 'confirm-modal-content.php'; ?>
