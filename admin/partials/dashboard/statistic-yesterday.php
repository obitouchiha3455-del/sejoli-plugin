<h3 class="ui header">Data Kemarin</h3>
<div class="ui three stackable cards sejoli-full-widget information monthly">

    <div class="ui card orange member-yesterday-lead">
        <div class="content">
            <div class="header"><?php _e('Total Lead', 'sejoli'); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card green member-yesterday-sales">
        <div class="content">
            <div class="header"><?php _e('Total Sales', 'sejoli'); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card blue member-yesterday-omset">
        <div class="content">
            <div class="header"><?php _e('Total Omset', 'sejoli'); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

</div>

<script>
(function( $ ) {
    'use strict';
    $(document).ready(function(){

        $.ajax({
            url : '<?php echo site_url('/sejoli-ajax/get-member-statistic-yesterday'); ?>',
            dataType : 'json',
            data : {
                nonce : '<?php echo wp_create_nonce('sejoli-render-member-statistic') ?>'
            },
            success : function(response) {
                $('.information.monthly .member-yesterday-lead .content.value').html(response.lead);
                $('.information.monthly .member-yesterday-sales .content.value').html(response.sales);
                $('.information.monthly .member-yesterday-omset .content.value').html(response.omset);
            }
        });

    });
})( jQuery );
</script>
