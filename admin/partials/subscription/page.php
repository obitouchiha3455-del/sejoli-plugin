<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data Langganan', 'sejoli'); ?>
    </h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-subscription box">
                <select class="update-subscription-select" name="update-subscription-select">
                    <option value=""><?php _e('Pilihan aksi pada langganan yang dipilih', 'sejoli'); ?></option>
                    <option value="pending"><?php _e('Ubah status langganan menjadi Belum Aktif', 'sejoli'); ?></option>
                    <option value="inactive"><?php _e('Ubah status langganan menjadi Tidak Aktif', 'sejoli'); ?></option>
                    <option value="active"><?php _e('Ubah status langganan menjadi Aktif', 'sejoli'); ?></option>
                </select>
                <button type="button" name="button" class='update-subscription button button-primary'><?php _e('Update Langganan', 'sejoli'); ?></button>
            </div>

            <div class="sejoli-form-filter box" style='float:right;'>

                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="ID" value="" placeholder="<?php _e('Pencarian order ID', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="user_id"></select>
                    <select class="autosuggest filter" name="product_id"></select>
                    <select class="autosuggest filter" name="status">
                        <option value=""><?php _e('Semua status langganan', 'sejoli'); ?></option>
                        <option value="pending"><?php _e('Belum Aktif', 'sejoli'); ?></option>
                        <option value="inactive"><?php _e('Tidak Aktif', 'sejoli'); ?></option>
                        <option value="active"><?php _e('Aktif', 'sejoli'); ?></option>
                    </select>
                    <select class="autosuggest filter" name="type">
                        <option value=""><?php _e('Semua tipe langganan', 'sejoli'); ?></option>
                        <option value="tryout"><?php _e("Berlangganan - Tryout", 'sejoli'); ?></option>
                        <option value="signup"><?php _e("Berlangganan - Awal", 'sejoli'); ?></option>
                        <option value="regular"><?php _e("Berlangganan - Regular", 'sejoli'); ?></option>
                    </select>
                    <?php wp_nonce_field('search-subscription', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>

            <!-- EXPORT CSV -->
            <div class="sejoli-form-filter box" style='float:right;'>

                <button type="button" name="button" class='button toggle-export'><?php _e('Export Data', 'sejoli'); ?></button>

                <div class="sejoli-form-export-holder sejoli-form-float">

                    <p><?php _e('Export data subscription yang sudah expired dan belum melakukan perpanjangan.', 'sejoli'); ?></p>

                    <input type="text" class='filter' name="max_renewal_day" value="" placeholder="<?php _e('Lama hari expired. Default 0 hari', 'sejoli'); ?>">

                    <?php wp_nonce_field('export-subscription', 'sejoli-nonce'); ?>

                    <button type="button" name="button" class='button button-primary do-export'><?php _e('Export ke CSV', 'sejoli'); ?></button>
                </div>
            </div>
            <!-- END|EXPORT CSV -->

        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-subscription" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Detil',       'sejoli'); ?></th>
                        <th><?php _e('Pembeli',     'sejoli'); ?></th>
                        <th><?php _e('Akhir Aktif', 'sejoli'); ?></th>
                        <th><?php _e('Status',      'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Detil',       'sejoli'); ?></th>
                        <th><?php _e('Pembeli',     'sejoli'); ?></th>
                        <th><?php _e('Akhir Aktif', 'sejoli'); ?></th>
                        <th><?php _e('Status',      'sejoli'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-subscription box">
                <select class="update-subscription-select" name="update-subscription-select">
                    <option value=""><?php _e('Pilihan aksi pada langganan yang dipilih', 'sejoli'); ?></option>
                    <option value="pending"><?php _e('Ubah status langganan menjadi Belum Aktif', 'sejoli'); ?></option>
                    <option value="inactive"><?php _e('Ubah status langganan menjadi Tidak Aktif', 'sejoli'); ?></option>
                    <option value="active"><?php _e('Ubah status langganan menjadi Aktif', 'sejoli'); ?></option>
                </select>
                <button type="button" name="button" class='update-subscription button button-primary'><?php _e('Update Langganan', 'sejoli'); ?></button>
            </div>
        </div>
    </div>
</div>
<div class="order-modal-holder ui modal"></div>

<script type="text/javascript">
let sejoli_table;

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

        $("select[name='status'], select[name='type']").select2({
            width : '100%'
        });

        sejoli.helper.filterData();

        sejoli_table = $('#sejoli-subscription').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.subscription.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-subscription-table';
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
                    targets: [0, 2, 3, 4 ],
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
                            id : full.order_id,
                            product : full.product_name,
                            parent : full.order_parent_id,
                            date : ("0000-00-00 00:00:00" !== full.updated_at) ? sejoli.helper.convertdate(full.updated_at) : sejoli.helper.convertdate(full.created_at),
                            type : sejoli_admin.subscription.type[full.order_type]
                        })
                    }
                },{
                    targets: 2,
                    width: '15%',
                    data: 'user_name'
                },{
                    targets: 3,
                    width: '15%',
                    data : 'end_date',
                    render : function(data, type, full) {
                        return sejoli.helper.convertdate(data) + '<br /> (' + full.day_left + ' hari ) '
                    }
                },{
                    targets: 4,
                    width : '100px',
                    data : 'status',
                    render : function(data, type, full) {
                        let tmpl = $.templates('#order-status');
                        return tmpl.render({
                            label : sejoli_admin.text.status[full.status],
                            color : sejoli_admin.color[full.status]
                        });
                    }
                }
            ]
        });

        sejoli_table.on('preXhr',function(){
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(){
            sejoli.helper.unblockUI('.sejoli-table-holder');
        });

        $(document).on('click', '.toggle-search', function(){
            $('.sejoli-form-float').hide();
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

        $(document).on('click', '.update-subscription', function(){
            let proceed  = true;
            let subscription_id = [];
            let status   = $(this).parent().find('select[name=update-subscription-select]').val();

            if('' === status) {
                alert('<?php _e('Anda belum memilih aksi'); ?>');
                return;
            }

            if('delete' !== status) {
                proceed = confirm('<?php _e('Anda yakin akan mengupdate subscription yang dipilih', 'sejoli'); ?>');
            }

            if(proceed) {
                $("tbody input[type=checkbox]:checked").each(function(i, el){
                    subscription_id.push($(el).data('id'));
                });

                if(0 < subscription_id.length) {
                    $.ajax({
                        url : sejoli_admin.subscription.update.ajaxurl,
                        type : 'POST',
                        data : {
                            subscriptions : subscription_id,
                            status : status,
                            nonce : sejoli_admin.subscription.update.nonce
                        },
                        beforeSend : function() {
                            sejoli.helper.blockUI('.sejoli-table-holder');
                        },success : function(response) {
                            sejoli.helper.unblockUI('.sejoli-table-holder');
                            sejoli_table.ajax.reload();
                        }
                    });
                } else {
                    alert('<?php _e('Anda belum memilih subscription'); ?>');
                    return;
                }
            }
        });

        /**
         * @since   1.5.3
         */
        $(document).on('click', '.toggle-export', function(){
            $('.sejoli-form-float').hide();
            $('.sejoli-form-export-holder').toggle();
        });

        $(document).on('click', '.do-export', function(){

            sejoli.helper.filterData('.sejoli-form-export-holder');
            $('.sejoli-form-export-holder').hide();

            $.ajax({
                url : sejoli_admin.subscription.export.ajaxurl,
                type : 'POST',
                dataType: 'json',
                data : {
                    nonce : sejoli_admin.subscription.export.nonce,
                    data : sejoli.var.search,
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
<strong>
    {{:product}}
</strong>
<div style='line-height:220%'>
    <span class="ui olive label">INV {{:id}}</span>
    <span class="ui teal label"><i class="calendar outline icon"></i>{{:date}}</span>

    {{if parent }}
    <span class="ui pink label" style='text-transform:uppercase;'><i class="redo icon"></i>INV {{:parent}}</span>
    {{/if}}

    {{if type }}
    <span class="ui brown label" style='text-transform:uppercase;'><i class="clock icon"></i>{{:type}}</span>
    {{/if}}
</div>
</script>

<script id='order-status' type="text/x-jsrender">
<div class="ui horizontal label boxed" style="background-color:{{:color}};">{{:label}}</div>
</script>

<?php require 'order-modal-content.php'; ?>
