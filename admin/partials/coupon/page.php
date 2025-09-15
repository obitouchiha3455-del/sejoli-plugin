<?php
    $edit_coupon_url = wp_nonce_url(admin_url('/'), 'sejoli-edit-coupon');
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data Kupon', 'sejoli'); ?>
	</h1>
    <a href="<?php echo admin_url('post-new.php?post_type=sejoli-coupon'); ?>" class="page-title-action"><?php _e('Tambah Kupon', 'sejoli'); ?></a>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-coupon box">
                <select class="update-coupon-select" name="update-coupon-select">
                    <option value=""><?php _e('Pilihan aksi pada kupon yang dipilih', 'sejoli'); ?></option>
                    <option value="pending"><?php _e('Ubah status kupon ke Tidak Aktif', 'sejoli'); ?></option>
                    <option value="active"><?php _e('Ubah status kupon ke Aktif', 'sejoli'); ?></option>
                    <option value='delete'><?php _e('Hapus kupon', 'sejoli'); ?></option>
                </select>
                <button type="button" name="button" class='update-coupon button button-primary'><?php _e('Update Kupon', 'sejoli'); ?></button>
            </div>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="code" value="" placeholder="<?php _e('Pencarian Kupon', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="user_id"></select>
                    <select class="autosuggest filter" name="status">
                        <option value=""><?php _e('Status Kupon', 'sejoli'); ?></option>
                        <option value="pending"><?php _e('Tidak aktif', 'sejoli'); ?></option>
                        <option value="active"><?php _e('Aktif', 'sejoli'); ?></option>
                        <option value="need-approve"><?php _e('Butuh persetujuan', 'sejoli'); ?></option>
                    </select>
                    <?php wp_nonce_field('search-coupon', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-coupon" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Detil',       'sejoli'); ?></th>
                        <th><?php _e('Pemilik',     'sejoli'); ?></th>
                        <th><?php _e('Discount',    'sejoli'); ?></th>
                        <th><?php _e('Penggunaan',  'sejoli'); ?></th>
                        <th><?php _e('Status',      'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Detil',       'sejoli'); ?></th>
                        <th><?php _e('Pemilik',     'sejoli'); ?></th>
                        <th><?php _e('Discount',    'sejoli'); ?></th>
                        <th><?php _e('Penggunaan',  'sejoli'); ?></th>
                        <th><?php _e('Status',      'sejoli'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-coupon box">
                <select class="update-coupon-select" name="update-coupon-select">
                    <option value=""><?php _e('Pilihan aksi pada kupon yang dipilih', 'sejoli'); ?></option>
                    <option value="pending"><?php _e('Ubah status kupon ke Tidak Aktif', 'sejoli'); ?></option>
                    <option value="active"><?php _e('Ubah status kupon ke Aktif', 'sejoli'); ?></option>
                    <option value='delete'><?php _e('Hapus kupon', 'sejoli'); ?></option>
                </select>
                <button type="button" name="button" class='update-coupon button button-primary'><?php _e('Update Kupon', 'sejoli'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function( $ ) {
	'use strict';
    $(document).ready(function() {

        sejoli.helper.select_2(
            "select[name='user_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.user.placeholder
        );

        sejoli.helper.daterangepicker("input[name='date-range']");

        $("select[name='status']").select2({
            width : '100%'
        });

        sejoli.helper.filterData();

        let tmpl = {
            edit : $.templates("#sejoli-edit-coupon-tmpl")
        }

        let sejoli_table = $('#sejoli-coupon').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.coupon.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-coupon-table';
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
                    targets: [0, 2, 3 ],
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
                    render: function(data, type, full) {
                        return tmpl.edit.render({
                            id : full.ID,
                            parent : full.parent_code,
                            code : full.code,
                            limit_date : sejoli.helper.convertdate(full.limit.date),
                            limit_use : (0 === parseInt(full.limit.use)) ? null : full.limit.use,
                            free_shipping : full.free_shipping,
                            renewal_coupon : full.renewal_coupon
                         });
                    }
                },{
                    targets: 2,
                    width: '15%',
                    data: 'username'
                },{
                    targets: 3,
                    width: '15%',
                    data : 'discount',
                    className : 'price'
                },{
                    targets: 4,
                    width: '8%',
                    data : 'usage',
                    className : 'price'

                },{
                    targets: 5,
                    data : 'status',
                    width : '100px',
                    render : function(data, type, full) {
                        let tmpl = $.templates('#coupon-status');
                        return tmpl.render({
                            label : sejoli_admin.text.status[data],
                            color : sejoli_admin.color[data]
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

        $(document).on('click', '.update-coupon', function(){
            let proceed  = true;
            let coupon_id = [];
            let status   = $(this).parent().find('select[name=update-coupon-select]').val();

            if('' === status) {
                alert('<?php _e('Anda belum memilih aksi'); ?>');
                return;
            }

            if('delete' === status) {
                proceed = confirm('<?php _e('Anda yakin akan menghapus kupon yang dipilih', 'sejoli'); ?>');
            }

            $("tbody input[type=checkbox]:checked").each(function(i, el){
                coupon_id.push($(el).data('id'));
            });

            if(proceed && 'delete' !== status) {

                if(0 < coupon_id.length) {
                    $.ajax({
                        url : sejoli_admin.coupon.update.ajaxurl,
                        type : 'POST',
                        data : {
                            coupons : coupon_id,
                            status : status,
                            nonce : sejoli_admin.coupon.update.nonce
                        },
                        beforeSend : function() {
                            sejoli.helper.blockUI('.sejoli-table-holder');
                        },success : function(response) {
                            sejoli.helper.unblockUI('.sejoli-table-holder');
                            sejoli_table.ajax.reload();
                        }
                    });
                } else {
                    alert('<?php _e('Anda belum memilih kupon'); ?>');
                    return;
                }
            } else if(proceed && 'delete' === status) {

                if(0 < coupon_id.length) {

                    $.ajax({
                        url : sejoli_admin.coupon.delete.ajaxurl,
                        type : 'POST',
                        data : {
                            coupons : coupon_id,
                            status : status,
                            nonce : sejoli_admin.coupon.delete.nonce
                        },
                        beforeSend : function() {
                            sejoli.helper.blockUI('.sejoli-table-holder');
                        },success : function(response) {
                            sejoli.helper.unblockUI('.sejoli-table-holder');
                            sejoli_table.ajax.reload();
                        }
                    });
                } else {
                    alert('<?php _e('Anda belum memilih kupon'); ?>');
                    return;
                }

            }
        });
    });
})(jQuery);
</script>
<script id="sejoli-edit-coupon-tmpl" type="text/x-jsrender">
<div class="coupon-action">
{{if !parent}}
    <a class="ui mini button" href="<?php echo $edit_coupon_url; ?>&code={{:code}}">EDIT</a>
{{/if}}
{{:code}}
    <div style='line-height:220%'>
    {{if parent}}
    <span class="ui teal label"><i class="tag icon"></i>{{:parent}}</span>
    {{/if}}

    {{if limit_date}}
    <span class="ui red label"><i class="calendar outline icon"></i>{{:limit_date}}</span>
    {{/if}}

    {{if limit_use}}
    <span class="ui red label"><i class="redo icon"></i>{{:limit_use}}</span>
    {{/if}}
    
    {{if renewal_coupon}}
    <span class="ui green label"><i class="refresh icon"></i><?php _e( 'RENEWAL COUPON', 'sejoli' ); ?></span>
    {{/if}}
    </div>

    {{if free_shipping}}
    <span class="ui green label"><i class="truck icon"></i>FREE SHIPPING</span>
    {{/if}}
</div>
</script>
<script id='coupon-status' type="text/x-jsrender">
    <div class="ui horizontal label boxed" style="background-color:{{:color}};">{{:label}}</div>
</script>
