<?php
global $wp_roles;
$group_options = sejolisa_get_user_group_options();
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data User', 'sejoli'); ?>
    </h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-order-holder box">
                <button type="button" name="button" class='button toggle-update'><?php _e('Update User', 'sejoli'); ?></button>
                <div class="sejoli-update-order sejoli-form-float">
                    <select class="update-order-select" name="affiliate_id">
                        <option value=""><?php _e('Pilihan affiliasi', 'sejoli'); ?></option>
                    </select>
                    <select class="update-order-select" name="role">
                        <option value=""><?php _e('Pilihan role', 'sejoli'); ?></option>
                        <?php foreach($wp_roles->roles as $role => $detail) : ?>
                        <option value='<?php echo $role; ?>'><?php echo $detail['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="update-order-select" name="group">
                        <option value=""><?php _e('Pilihan grup', 'sejoli'); ?></option>
                        <?php foreach($group_options as $id => $label) : ?>
                        <option value='<?php echo $id; ?>'><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" name="button" class='do-update button button-primary'><?php _e('Update User', 'sejoli'); ?></button>
                </div>
            </div>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='export-csv button'><?php _e('Export CSV', 'sejoli'); ?></button>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="ID" value="" placeholder="<?php _e('Pencarian user ID. Gunakan koma jika lebih dari satu', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="user_id"></select>
                    <select class="autosuggest filter" name="affiliate_id"></select>
                    <select class="autosuggest filter" name="role">
                        <option value=""><?php _e('Semua role', 'sejoli'); ?></option>
                        <?php foreach($wp_roles->roles as $role => $detail) : ?>
                        <option value='<?php echo $role; ?>'><?php echo $detail['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="autosuggest filter" name="group">
                        <option value=""><?php _e('Semua grup', 'sejoli'); ?></option>
                        <?php foreach($group_options as $id => $label) : ?>
                        <option value='<?php echo $id; ?>'><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php wp_nonce_field('search-user', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-users" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('User', 'sejoli'); ?></th>
                        <th><?php _e('Email', 'sejoli'); ?></th>
                        <th><?php _e('Telpon', 'sejoli'); ?></th>
                        <th><?php _e('Affiliasi', 'sejoli'); ?></th>
                        <th><?php _e('Grup', 'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('User', 'sejoli'); ?></th>
                        <th><?php _e('Email', 'sejoli'); ?></th>
                        <th><?php _e('Telpon', 'sejoli'); ?></th>
                        <th><?php _e('Affiliasi', 'sejoli'); ?></th>
                        <th><?php _e('Grup', 'sejoli'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

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
            "select[name='affiliate_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.affiliate.placeholder
        );

        sejoli.helper.filterData();

        sejoli_table = $('#sejoli-users').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.userlist.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-user-table';
                    data.security = sejoli_admin.userlist.table.nonce
                    data.backend  = true;
                }
            },
            pageLength : 50,
            lengthMenu : [
                [10, 50, 100, 200],
                [10, 50, 100, 200],
            ],
            order: [
                [ 1, "asc" ]
            ],
            columnDefs: [
                {
                    targets: [0, 2, 3, 4, 5],
                    orderable: false
                },{
                    targets: 0,
                    data : 'ID',
                    width: '18px',
                    className: 'center',
                    render: function(data, type, full) {
                        return '<input type="checkbox" value="' + data + '" data-id="' + data +'" />';
                    }
                },{
                    targets: 1,
                    data : 'display_name',
                    render: function(data, type, full) {

                        let tmpl = $.templates('#user-detail');

                        return tmpl.render({
                            id : full.ID,
                            display_name : full.name,
                            roles : full.roles
                        })
                    }
                },{
                    targets: 2,
                    width: '180px',
                    data: 'email'
                },{
                    targets: 3,
                    width: '100px',
                    data : 'phone'
                },{
                    targets: 4,
                    width:  '160px',
                    data: 'affiliate'
                },{
                    targets: 5,
                    width: '160px',
                    data : 'group'
                }
            ],
            initComplete: function(settings, json) {
                // $('.sejoli-full-widget .orange .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.pending_commission));
                // $('.sejoli-full-widget .green .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.unpaid_commission));
                // $('.sejoli-full-widget .blue .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.paid_commission));
            }
        });

        sejoli_table.on('preXhr',function(){
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(){
            sejoli.helper.unblockUI('.sejoli-table-holder');
        });

        $(document).on('click', '.toggle-update', function(){
            $('.sejoli-update-order').toggle();
        });

        $(document).on('click', '.toggle-search', function(){
            $('.sejoli-form-filter-holder').toggle();
        });

        $(document).on('click', '.do-search', function(){
            sejoli.helper.filterData();
            console.log(sejoli.var.search);
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

        /**
         * Update bulk order
         */
        $(document).on('click', '.do-update', function(){
            let proceed = confirm('<?php _e('Anda yakin akan melakukan update pada user yang dipilih?', 'sejoli'); ?>'),
                user_id = [],
                role =    $(this).parent().find('select[name=role]').val(),
                group =   $(this).parent().find('select[name=group]').val(),
                affiliate_id =   $(this).parent().find('select[name=affiliate_id]').val();

            if('' === role && '' === group && '' === affiliate_id) {
                alert('<?php _e('Anda belum memilih perubahan role atau grup atau affiliasi'); ?>');
                return;
            }

            if(proceed) {

                $("tbody input[type=checkbox]:checked").each(function(i, el){
                    user_id.push($(el).data('id'));
                });

                if(0 < user_id.length) {

                    $.ajax({
                        url:      sejoli_admin.userlist.update.ajaxurl,
                        type:     'POST',
                        dataType: 'json',
                        data: {
                            nonce:  sejoli_admin.userlist.update.nonce,
                            ID:     user_id,
                            group:  group,
                            affiliate_id:  affiliate_id,
                            role:   role
                        },
                        beforeSend: function() {
                            sejoli.helper.blockUI('.sejoli-table-wrapper');
                        },success: function(response) {
                            sejoli.helper.unblockUI('.sejoli-table-wrapper');
                            alert(response.message);
                            $('.sejoli-update-order').toggle();
                            sejoli_table.ajax.reload();
                        }
                    })

                } else {
                    alert('<?php _e('Anda belum memilih user'); ?>');
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
                url : sejoli_admin.userlist.export_prepare.ajaxurl,
                type : 'POST',
                dataType: 'json',
                data : {
                    nonce : sejoli_admin.userlist.export_prepare.nonce,
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

<script id='user-detail' type="text/x-jsrender">
<a type='button' class='ui mini button' href='<?php echo admin_url('user-edit.php'); ?>?user_id={{:id}}' target='_blank'>DETAIL</a> {{:display_name}}
<div style='line-height:220%'>
    <span class="ui teal label">ID {{:id}}</span>
    {{for roles }}
    <span class="ui purple label" style='text-transform:uppercase;'><i class="tag icon"></i>{{:name}}</span>
    {{/for}}
</div>
</script>
