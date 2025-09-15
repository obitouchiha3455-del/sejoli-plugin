
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Log Pengiriman Pengingat', 'sejoli'); ?>
	</h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>
            <div class="sejoli-update-reminder box">
                <button type="button" name="button" class='send-reminder button button-primary'><?php _e('Kirim Pengingat Sekarang', 'sejoli'); ?></button>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-reminder" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Tanggal', 'sejoli'); ?></th>
                        <th><?php _e('Detail', 'sejoli'); ?></th>
                        <th><?php _e('Media', 'sejoli'); ?></th>
                        <th><?php _e('Dikirim',  'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><input type='checkbox' class='parent-checkbox' /></th>
                        <th><?php _e('Tanggal', 'sejoli'); ?></th>
                        <th><?php _e('Detail', 'sejoli'); ?></th>
                        <th><?php _e('Media', 'sejoli'); ?></th>
                        <th><?php _e('Dikirim',  'sejoli'); ?></th>
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

        sejoli.helper.filterData();

        let tmpl = {
            detail : $.templates("#sejoli-detail-tmpl"),
            send : $.templates("#sejoli-send-tmpl"),
        }

        let sejoli_table = $('#sejoli-reminder').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.reminder.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-reminder-table';
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
                    targets: [0, 2, 3, 4],
                    orderable: false
                },{
                    targets: 0,
                    width: '18px',
                    orderable: false,
                    className: 'center',
                    render: function ( data, type, full) {
                      return '<input type="checkbox" value="' + full.ID + '" data-id="' + full.ID +'" />';
                    }
                },{
                    targets: 1,
                    width: '120px',
                    data : 'ID',
                    render: function(data, type, full) {
                        return full.sent_at;
                    }
                },{
                    targets: 2,
                    render: function(data, type, full) {
                        return tmpl.detail.render({
                            order : full.order_id,
                            title : full.title
                         });
                    }
                },{
                    targets: 3,
                    width: '80px',
                    data: 'media_type',
                    render: function(data, type, full) {
                        return data.toUpperCase();
                    }
                },{
                    targets: 4,
                    width: '120px',
                    data: 'is_sent',
                    render: function(data, type, full) {
                        return tmpl.send.render({
                            'text' : data.toUpperCase(),
                            'status' : full.status
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

        $(document).on('click', '.send-reminder', function(){
            let proceed  = true;
            let reminder_id = [];

            if('delete' === status) {
                proceed = confirm('<?php _e('Anda yakin akan menghapus kupon yang dipilih', 'sejoli'); ?>');
            }

            $("tbody input[type=checkbox]:checked").each(function(i, el){
                reminder_id.push($(el).data('id'));
            });

            if(proceed && 'delete' !== status) {

                if(0 < reminder_id.length) {
                    $.ajax({
                        url : sejoli_admin.reminder.resend.ajaxurl,
                        type : 'POST',
                        data : {
                            reminders : reminder_id,
                            status : status,
                            nonce : sejoli_admin.reminder.resend.nonce
                        },
                        beforeSend : function() {
                            sejoli.helper.blockUI('.sejoli-table-holder');
                        },success : function(response) {
                            sejoli.helper.unblockUI('.sejoli-table-holder');
                            sejoli_table.ajax.reload();
                        }
                    });
                } else {
                    alert('<?php _e('Anda belum memilih pesan yang akan dikirim'); ?>');
                    return;
                }
            }
        });
    });
})(jQuery);
</script>
<script id="sejoli-detail-tmpl" type="text/x-jsrender">
    <span class="ui olive label">INV {{:order}}</span>
    <span class="ui label">{{:title}}</span>
</script>
<script id="sejoli-send-tmpl" type="text/x-jsrender">
    {{if 0 == status}}
    <span class="ui yellow label">{{:text}}</span>
    {{else}}
    <span class="ui blue label">{{:text}}</span>
    {{/if}}
</script>
