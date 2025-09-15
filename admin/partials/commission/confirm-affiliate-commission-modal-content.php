<form method='POST' action='' id='confirmation-confirmed-modal' enctype='multipart/form-data' class="commission-confirmation-modal-holder ui modal"></form>
<div class="commission-paid-modal-holder ui modal">
    <div class="ui positive message">

    </div>
</div>
<script type="text/javascript">

let sejoli_render_confirmation

(function( $ ) {
    'use strict';

    sejoli_render_confirmation = function(response){
        let confirmation_form = $.templates('#confirmation-modal-popup-content'),
            content = '';

        content = confirmation_form.render(response.affiliate);

        $('.commission-confirmation-modal-holder').html(content).modal('show');
    }

    $('body').on('click', '.sejolisa-confirm-commission-transfer', function(){

        let form = $('#confirmation-confirmed-modal')[0],
            data = new FormData(form),
            post_data = [],
            confirmed = confirm('<?php _e('Anda yakin akan mengupdate status komisi affiliasi ini?', 'sejoli'); ?>');

        var date_range = $("input[name='date-range']").val();
        data.append('date_range',date_range);

        if(confirmed) {
            $.ajax({
                url:     sejoli_admin.affiliate_commission.pay.ajaxurl,
                type:    'POST',
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                data: data,
                dataType: 'json',
                success : function(response) {

                    $('.commission-confirmation-modal-holder').html('').modal('show');

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
        }

        return false;
    });

})(jQuery);
</script>
<script id='confirmation-message-content' type="text/x-jsrender">
<p>{{:message}}</p>
</script>
<script id='confirmation-modal-popup-content' type="text/x-jsrender">
<i class="close icon"></i>
<div class="header">
    <?php _e('Pembayaran Komisi', 'sejoli'); ?>
</div>
<?php echo wp_nonce_field('sejoli-pay-single-affiliate-commission', 'sejoli-nonce'); ?>
<input type='hidden' name='current_time' value='{{:current_time}}' />
<input type='hidden' name='total_commission' value='{{:unpaid_commission_html}}' />
<input type='hidden' name='affiliate_id' value='{{:ID}}' />

<div class="content">
    <div class="ui divided items">
        <div class="item">
            <div class="ui tiny image">
                <img src="{{:avatar}}">
            </div>
            <div class='content'>
                <div class='header'>{{:display_name}}</div>
                <div class='meta'>
                    <span class='email'><?php _e('Email : ', 'sejoli'); ?>{{:user_email}}</span>
                    <span class='phone'><?php _e('Nomor Telpon : ', 'sejoli'); ?>{{:user_phone}}</span>
                </div>
                <div class='description'>
                    <p>
                        <strong><?php _e('Total Komisi', 'sejoli'); ?> : </strong>
                        {{:unpaid_commission_html}}
                    </p>
                    <p>
                        {{:info}}
                    </p>
                    {{if unpaid_commission_html !== 'Rp. 0'}}
                    <div class='field'>
                        <label><?php _e('Bukti Transfer', 'sejoli'); ?></label>
                        <input type='file' name='proof' />
                    </div>
                    {{/if}}
                </div>
            </div>
        </div>
    </div>
</div>
{{if unpaid_commission_html !== 'Rp. 0'}}
<div class="actions">
    <button type="button" class=" sejolisa-confirm-commission-transfer ui button"><?php _e('Update komisi untuk affiliate {{:display_name}} ke TELAH DIBAYAR', 'sejoli'); ?></button>
</div>
{{/if}}
</script>
 