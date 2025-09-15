
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Konfirmasi Pembayaran', 'sejoli'); ?>
	</h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="order_id" value="" placeholder="<?php _e('Pencarian Invoice', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="user_id"></select>
                    <select class="autosuggest filter" name="product_id"></select>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-confirmation" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('Tanggal', 'sejoli'); ?></th>
                        <th><?php _e('Invoice', 'sejoli'); ?></th>
                        <th><?php _e('Total',  'sejoli'); ?></th>
                        <th><?php _e('Detail',  'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Tanggal', 'sejoli'); ?></th>
                        <th><?php _e('Invoice', 'sejoli'); ?></th>
                        <th><?php _e('Total',  'sejoli'); ?></th>
                        <th><?php _e('Detail',  'sejoli'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<div class="order-modal-holder ui modal"></div>
<script type="text/javascript">
(function( $ ) {
	'use strict';
    $(document).ready(function() {

        sejoli.helper.select_2(
            "select[name='user_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.user.placeholder
        );

        sejoli.helper.select_2(
            "select[name='product_id']",
            sejoli_admin.product.select.ajaxurl,
            sejoli_admin.product.placeholder
        );

        sejoli.helper.filterData();

        let tmpl = {
            invoice : $.templates("#sejoli-invoice-tmpl"),
            button : $.templates("#sejoli-button-tmpl"),
        }

        let sejoli_table = $('#sejoli-confirmation').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.confirmation.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-confirmation-table';
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
                [ 0, "desc" ]
            ],
            columnDefs: [
                {
                    targets: [1, 2, 3 ],
                    orderable: false
                },{
                    targets: 0,
                    width: '100px',
                    data : 'id',
                    render: function(data, type, full) {
                        return full.created_at;
                    }
                },{
                    targets: 1,
                    render: function(data, type, full) {

                        return tmpl.invoice.render({
                            id : full.ID,
                            order : full.order_id,
                            product : full.product,
                         });
                    }
                },{
                    targets: 2,
                    width: '20%',
                    data: 'total'
                },{
                    targets: 3,
                    width: '100px',
                    className: 'center',
                    render: function(data, type, full) {
                        return tmpl.button.render({
                            id : full.ID
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

        $('input.parent-checkbox').change(function(){
            var checked = $(this).is(':checked');
            $("tbody input[type='checkbox']").attr('checked', checked);
            $('input.parent-checkbox').attr('checked', checked);
        });
    });
})(jQuery);
</script>
<script id="sejoli-invoice-tmpl" type="text/x-jsrender">
    <span class="ui olive label">INV {{:order}}</span>
    <span class="ui violet label"><i class="box icon"></i>{{:product}}</span>
</script>
<script id='sejoli-button-tmpl' type="text/x-jsrender">
    <button type='button' class='order-detail-trigger ui mini button' data-id='{{:id}}'>DETAIL</button>
</script>
<?php require_once 'modal-content.php';
