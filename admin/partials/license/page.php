
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data Lisensi', 'sejoli'); ?>
	</h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-license box">
                <select class="update-license-select" name="update-license-select">
                    <option value=""><?php _e('Pilihan aksi pada lisensi yang dipilih', 'sejoli'); ?></option>
                    <option value="pending"><?php _e('Ubah status lisensi ke Tidak Aktif', 'sejoli'); ?></option>
                    <option value="active"><?php _e('Ubah status lisensi ke Aktif', 'sejoli'); ?></option>
                    <option value="delete"><?php _e('Hapus penanda lisensi', 'sejoli'); ?></option>
                </select>
                <button type="button" name="button" class='update-license button button-primary'><?php _e('Update Lisensi', 'sejoli'); ?></button>
            </div>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="code" value="" placeholder="<?php _e('Pencarian Lisensi', 'sejoli'); ?>">
                    <input type="text" class='filter' name="string" value="" placeholder="<?php _e('Pencarian Penanda', 'sejoli'); ?>">
                    <input type="text" class='filter' name="order_id" value="" placeholder="<?php _e('Pencarian Invoice', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="user_id"></select>
                    <select class="autosuggest filter" name="product_id"></select>
                    <select class="autosuggest filter" name="status">
                        <option value=""><?php _e('Status Lisensi', 'sejoli'); ?></option>
                        <option value="pending"><?php _e('Tidak aktif', 'sejoli'); ?></option>
                        <option value="active"><?php _e('Aktif', 'sejoli'); ?></option>
                    </select>
                    <?php wp_nonce_field('search-license', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-license" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Lisensi',       'sejoli'); ?></th>
                        <th><?php _e('Pemilik',     'sejoli'); ?></th>
                        <th><?php _e('Penanda',    'sejoli'); ?></th>
                        <th><?php _e('Status',      'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Lisensi',       'sejoli'); ?></th>
                        <th><?php _e('Pemilik',     'sejoli'); ?></th>
                        <th><?php _e('Penanda',    'sejoli'); ?></th>
                        <th><?php _e('Status',      'sejoli'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-license box">
                <select class="update-license-select" name="update-license-select">
                    <option value=""><?php _e('Pilihan aksi pada lisensi yang dipilih', 'sejoli'); ?></option>
                    <option value="pending"><?php _e('Ubah status lisensi ke Tidak Aktif', 'sejoli'); ?></option>
                    <option value="active"><?php _e('Ubah status lisensi ke Aktif', 'sejoli'); ?></option>
                    <option value="delete"><?php _e('Hapus penanda lisensi', 'sejoli'); ?></option>
                </select>
                <button type="button" name="button" class='update-license button button-primary'><?php _e('Update Lisensi', 'sejoli'); ?></button>
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

        sejoli.helper.select_2(
            "select[name='product_id']",
            sejoli_admin.product.select.ajaxurl,
            sejoli_admin.product.placeholder
        );

        $("select[name='status']").select2({
            width : '100%'
        });

        sejoli.helper.filterData();

        let tmpl = {
            edit : $.templates("#sejoli-edit-license-tmpl")
        }

        let sejoli_table = $('#sejoli-license').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.license.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-license-table';
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
                            code : full.code,
                            order : full.order_id,
                            product : full.product_name,
                         });
                    }
                },{
                    targets: 2,
                    width: '20%',
                    data: 'owner_name'
                },{
                    targets: 3,
                    width: '20%',
                    data : 'string',
                },{
                    targets: 4,
                    data : 'status',
                    width : '100px',
                    render : function(data, type, full) {
                        let tmpl = $.templates('#license-status');
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

        $(document).on('click', '.update-license', function(){
            let proceed  = true;
            let license_id = [];
            let status   = $(this).parent().find('select[name=update-license-select]').val();

            if('' === status) {
                alert('<?php _e('Anda belum memilih aksi'); ?>');
                return;
            }

            if('delete' === status) {
                proceed = confirm('<?php _e('Anda yakin akan menghapus penanda lisensi yang dipilih', 'sejoli'); ?>');
            }

            if(proceed) {
                $("tbody input[type=checkbox]:checked").each(function(i, el){
                    license_id.push($(el).data('id'));
                });

                if(0 < license_id.length) {
                    $.ajax({
                        url : sejoli_admin.license.update.ajaxurl,
                        type : 'POST',
                        data : {
                            licenses : license_id,
                            status : status,
                            nonce : sejoli_admin.license.update.nonce
                        },
                        beforeSend : function() {
                            sejoli.helper.blockUI('.sejoli-table-holder');
                        },success : function(response) {
                            sejoli.helper.unblockUI('.sejoli-table-holder');
                            sejoli_table.ajax.reload();
                        }
                    });
                } else {
                    alert('<?php _e('Anda belum memilih lisensi'); ?>');
                    return;
                }
            }
        });
    });
})(jQuery);
</script>
<script id="sejoli-edit-license-tmpl" type="text/x-jsrender">
<strong>
    {{:code}}
</strong>
<div style='line-height:220%'>
    <span class="ui olive label">INV {{:order}}</span>
    <span class="ui violet label"><i class="box icon"></i>{{:product}}</span>
</div>
</script>
<script id='license-status' type="text/x-jsrender">
    <div class="ui horizontal label boxed" style="background-color:{{:color}};">{{:label}}</div>
</script>
