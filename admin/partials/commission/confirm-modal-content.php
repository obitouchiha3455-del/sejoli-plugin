<form method='POST' action='' id='confirmation-confirmed-modal' enctype='multipart/form-data' class="commission-confirmation-modal-holder ui modal"></form>
<div class="commission-paid-modal-holder ui modal">
    <div class="ui positive message">

    </div>
</div>
<script type="text/javascript">

let sejoli_render_confirmation;

(function( $ ) {

    'use strict';

    sejoli_render_confirmation = function(response){
        let confirmation_form = $.templates('#confirmation-modal-popup-content'),
            content = '';

        content = confirmation_form.render({
            commission_ids : response.id.join(','),
            commissions : response.commissions
        });

        $('.commission-confirmation-modal-holder').html(content).modal('show');
    }

    $('body').on('click', '.sejolisa-confirm-commission-transfer', function(){
        let form = $('#confirmation-confirmed-modal')[0],
            data = new FormData(form),
            post_data = [];

        $.ajax({
            url:     sejoli_admin.commission.transfer.ajaxurl,
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
<?php echo wp_nonce_field('sejoli-confirm-commission-transfer', 'sejoli-nonce'); ?>
<input type='hidden' name='commission_ids' value='{{:commission_ids}}' />

<div class="content">
    <div class="ui divided items">
        {{for commissions}}
        <div class="item">
            <div class="ui tiny image">
                <img src="{{:avatar}}">
            </div>
            <div class='content'>
                <div class='header'>{{:affiliate_name}}</div>
                <div class='meta'>
                    <span class='email'><?php _e('Email : ', 'sejoli'); ?>{{:affiliate_email}}</span>
                    <span class='phone'><?php _e('Nomor Telpon : ', 'sejoli'); ?>{{:affiliate_phone}}</span>
                </div>
                <div class='description'>
                    <p>
                        <strong><?php _e('Total Komisi', 'sejoli'); ?> : </strong>
                        {{:total_commission}}
                    </p>
                    <p>
                        {{:bank_info}}
                    </p>
                    {{if total_commission !== 'Rp. 0'}}
                    <div class='field'>
                        <label><?php _e('Bukti Transfer', 'sejoli'); ?></label>
                        <input type='file' name='commission[{{:affiliate_id}}]' />
                    </div>
                    {{/if}}
                </div>
            </div>
        </div>
        {{/for}}
    </div>
</div>
{{if total_commission !== 'Rp. 0'}}
<div class="actions">
    <button type="button" class=" sejolisa-confirm-commission-transfer ui button"><?php _e('Semua komisi yang dipilih diupdate ke TELAH DIBAYAR', 'sejoli'); ?></button>
</div>
{{/if}}
</script>
