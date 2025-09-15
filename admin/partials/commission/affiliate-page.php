<?php
    $date = date('Y-m-d',strtotime('-29 days')) . ' - ' . date('Y-m-d');
    $export_link = add_query_arg(array(
                        'sejoli-nonce'  => wp_create_nonce('sejoli-affiliate-commission-export'),
                        'action'        => 'sejoli-affiliate-commission-csv-export'
                    ),admin_url('admin-ajax.php'));
?>
<div class="wrap">
    <div class="sejoli-table-wrapper">
        <h1 class="wp-heading-inline">
            <?php _e('Data Komisi per Affiliasi', 'sejoli'); ?>
        </h1>

        <div class="ui three stackable cards sejoli-full-widget information blocked">

            <div class="ui card orange orange">
                <div class="content">
                    <div class="header"><?php _e('Komisi potensial', 'sejoli'); ?></div>
                </div>
                <div class="content value">Rp. 0</div>
            </div>

            <div class="ui card green green">
                <div class="content">
                    <div class="header"><?php _e('Komisi belum dibayar', 'sejoli'); ?></div>
                </div>
                <div class="content value">Rp. 0</div>
            </div>

            <div class="ui card blue blue">
                <div class="content">
                    <div class="header"><?php _e('Komisi sudah dibayar', 'sejoli'); ?></div>
                </div>
                <div class="content value"> Rp. 0</div>
            </div>

        </div>
        <br />
        <div class="sejoli-form-action-holder">
            <div class="sejoli-form-information" style="float:left;">
                <h3 id='sejoli-filter-date'></h3>
            </div>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="date-range" value="<?php echo $date; ?>" placeholder="<?php _e('Pencarian berdasarkan tanggal', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="affiliate_id"></select>
                    <?php wp_nonce_field('search-commission', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
                <a href='<?php echo $export_link; ?>' name="button" class='export-csv button'><?php _e('Export CSV', 'sejoli'); ?></a>
            </div>
        </div>
        <div class="sejoli-table-wrapper">
            <div class="sejoli-table-holder">
                <table id="sejoli-commission" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th><?php _e('Affiliasi',               'sejoli'); ?></th>
                            <th><?php _e('Komisi potensial',        'sejoli'); ?></th>
                            <th><?php _e('Komisi belum dibayar',    'sejoli'); ?></th>
                            <th><?php _e('Komisi sudah dibayar',    'sejoli'); ?></th>
                            <th><?php _e('Pembayaran Komisi',          'sejoli'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><?php _e('Affiliasi',               'sejoli'); ?></th>
                            <th><?php _e('Komisi potensial',        'sejoli'); ?></th>
                            <th><?php _e('Komisi belum dibayar',    'sejoli'); ?></th>
                            <th><?php _e('Komisi sudah dibayar',    'sejoli'); ?></th>
                            <th><?php _e('Pembayaran Komisi',          'sejoli'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

let sejoli_table;

(function( $ ) {
    'use strict';
    $(document).ready(function() {

        sejoli.helper.filterData();

        sejoli_table = $('#sejoli-commission').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.affiliate_commission.table.ajaxurl,
                data: function(data) {
                    data.filter   = sejoli.var.search;
                    data.action   = 'sejoli-affiliate-commission-table';
                    data.security = sejoli_admin.affiliate_commission.table.nonce
                    data.backend  = true;
                }
            },
            pageLength : -1,
            lengthMenu : [
                [-1],
                [dataTableTranslation.all],
            ],
            order: [
                [ 0, "desc" ]
            ],
            columnDefs: [
                {
                    targets: [1, 2, 3],
                    orderable: false
                },{
                    targets: 0,
                    data : 'ID',
                    width: '20%',
                    render : function(data, type, full) {
                        let tmpl = $.templates('#order-detail'),
                            subsctype = null;

                        return tmpl.render({
                            id : full.ID,
                            name : full.display_name
                        })
                    }
                },{
                    targets: 1,
                    width: '20%',
                    data: 'pending_commission',
                    className : 'price',
                    render : function(data ,type, full) {
                        return sejoli_admin.text.currency + sejoli.helper.formatPrice(data);
                    }
                },{
                    targets: 2,
                    width: '20%',
                    data : 'unpaid_commission',
                    className : 'price',
                    render : function(data ,type, full) {
                        return sejoli_admin.text.currency + sejoli.helper.formatPrice(data);
                    }
                },{
                    targets: 3,
                    width: '20%',
                    data : 'paid_commission',
                    className : 'price',
                    render : function(data ,type, full) {
                        return sejoli_admin.text.currency + sejoli.helper.formatPrice(data);
                    }
                },{
                    targets: 4,
                    width: '20%',
                    data : 'informasi_rekening',
                    className : 'informasi_rekening',
                    render : function(data ,type, full) {

                        var dateNow = new Date,
                            dateNowFormatted = [
                                dateNow.getFullYear().toString().padStart(4, '0'),
                                (dateNow.getMonth()+1).toString().padStart(2, '0'),
                                dateNow.getDate().toString().padStart(2, '0'),
                            ].join('-')+' '+[
                                dateNow.getHours().toString().padStart(2, '0'),
                                dateNow.getMinutes().toString().padStart(2, '0'),
                                dateNow.getSeconds().toString().padStart(2, '0')
                            ].join(':');
                        
                        let tmpl = $.templates('#pay-commission');
                        return tmpl.render({
                            informasi_rekening: data,
                            display_name: full.display_name,
                            unpaid_commission: full.unpaid_commission,
                            unpaid_commission_html: sejoli_admin.text.currency + sejoli.helper.formatPrice(full.unpaid_commission),
                            ID: full.ID,
                        });

                    }
                }
            ],
            initComplete: function(settings, json) {
                $('.sejoli-full-widget .orange .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.pending_commission));
                $('.sejoli-full-widget .green .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.unpaid_commission));
                $('.sejoli-full-widget .blue .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.paid_commission));
            }
        });

        sejoli_table.on('preXhr',function(){
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(a,b,json){
            sejoli.helper.unblockUI('.sejoli-table-holder');
            $('.sejoli-full-widget .orange .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.pending_commission));
            $('.sejoli-full-widget .green .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.unpaid_commission));
            $('.sejoli-full-widget .blue .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.paid_commission));

            let date_range = $('input[name="date-range"]').val();
            $('#sejoli-filter-date').html('Data tanggal : ' + date_range);
        });

        $('body').on('click', '.affiliate-detail-trigger', function(){

            let affiliate_id = $(this).data('affiliate');
            let date_range = $('input[name="date-range"]').val();

            $.ajax({
               url : sejoli_admin.affiliate_commission.confirm.ajaxurl,
               type : 'POST',
               data : {
                   affiliate : affiliate_id,
                   date_range: date_range,
                   nonce : sejoli_admin.affiliate_commission.confirm.nonce
               },
               beforeSend : function() {
                   sejoli.helper.blockUI('.sejoli-table-holder');
               },success : function(response) {
                   sejoli.helper.unblockUI('.sejoli-table-holder');
                   if ( typeof response.affiliate.ID !== 'undefined' ) {
                        sejoli_render_confirmation(response);
                   } else {
                       alert('Data not found');
                   }
               }
           });
        });

        $(document).on('change','.bukti_transfer',function(e){

            e.preventDefault();

            if ( $(this).get(0).files.length !== 0 ) {

                // trigger submit .pay-commission-form
                $(this).parent().parent().trigger('submit');

            }

        });

        $(document).on('submit','.pay-commission-form',function(e){

            e.preventDefault();

            var formData = new FormData(this);

            var display_name = formData.get('_display_name');
            var unpaid_commission = formData.get('_unpaid_commission');
            var total_commission = formData.get('total_commission');
            var date_range = $("input[name='date-range']").val();
            formData.append('date_range',date_range);

            if ( unpaid_commission > 0 ) {
                var message = 'Apakah anda yakin akan membayar komisi '+display_name+' sebesar '+total_commission+' ?';
                if ( confirm( message ) ) {

                    // ajax here

                    $.ajax({
                        url:     sejoli_admin.affiliate_commission.pay.ajaxurl,
                        type:    'POST',
                        enctype: 'multipart/form-data',
                        processData: false,
                        contentType: false,
                        cache: false,
                        data: formData,
                        dataType: 'json',
                        beforeSend : function() {
                            sejoli.helper.blockUI('.sejoli-table-holder');
                        },
                        success : function(response) {

                            sejoli.helper.unblockUI('.sejoli-table-holder');

                            let tmpl = $.templates('#confirmation-message-content'),
                                html = tmpl.render(response.messages);

                            $('.commission-paid-modal-holder .message').html(html);
                            $('.commission-paid-modal-holder').modal('show');
                            sejoli_table.ajax.reload();

                            setInterval(function(){
                                $('.commission-paid-modal-holder').modal('hide');
                            },5000);

                        }
                    });

                    // ajax here
                    // alert('Pembayaran komisi sukses');

                } else {
                    alert('Pembayaran komisi dibatalkan');
                }
            } else {
                alert('Tidak dapat memproses pembayaran komisi, komisi belum dibayar '+total_commission);
            }

        });

        $(document).on('click', '.toggle-search', function(){
            $('.sejoli-form-filter-holder').toggle();
        });

        $(document).on('click', '.do-search', function(){
            sejoli.helper.filterData();
            sejoli_table.ajax.reload();
            $('.sejoli-form-filter-holder').hide();
        });

        sejoli.helper.select_2(
            "select[name='affiliate_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.affiliate.placeholder
        );

        sejoli.helper.daterangepicker("input[name='date-range']");

        /**
         * Do export csv
         */
        $(document).on('click', '.export-csv', function(){
            sejoli.helper.filterData();

            var link = $(this).attr('href');
            var date_range = $('input[name="date-range"]').val();
            var affiliate_id = $('select[name="affiliate_id"]').val();

            if ( link ) {
                if ( date_range ) {
                    link += '&date_range='+date_range;
                }
                if ( affiliate_id ) {
                    link += '&affiliate_id='+affiliate_id;
                }
            }

            window.location.replace(link);

            return false;
        });

    });
})(jQuery);
</script>

<script id='order-detail' type="text/x-jsrender">
<button type='button' class='affiliate-detail-trigger ui mini button' data-id='{{:order_id}}' data-affiliate='{{:id}}'>DETAIL</button>
<strong>
    {{:name}}
</strong>
</script>

<script id='pay-commission' type="text/x-jsrender">
<form method='POST' action='' enctype='multipart/form-data' class='pay-commission-form'>
    
    <?php echo wp_nonce_field('sejoli-pay-single-affiliate-commission', 'sejoli-nonce'); ?>

    <input type='hidden' name='total_commission' value='{{:unpaid_commission_html}}' />
    <input type='hidden' name='affiliate_id' value='{{:ID}}' />

    <input type='hidden' name='_display_name' value='{{:display_name}}' />
    <input type='hidden' name='_unpaid_commission' value='{{:unpaid_commission}}' />

    <div class="pay-commission">
        <?php _e('Rekening :', 'sejoli'); ?> {{if informasi_rekening}} {{:informasi_rekening}} {{else}} - {{/if}}<br>
        {{if unpaid_commission_html !== 'Rp. 0'}}
        <?php _e('Bukti Transfer :', 'sejoli'); ?> <input type="file" name="proof" class="bukti_transfer"><br>
        {{/if}}
    </div>
</form>
</script>

<?php require 'confirm-affiliate-commission-modal-content.php'; ?>
