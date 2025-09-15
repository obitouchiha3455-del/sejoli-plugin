<h3 class="ui header"><?php _e('Data Hari Ini', 'sejoli'); ?></h3>
<div class="ui three stackable cards sejoli-full-widget information daily">
    <div class="ui card orange member-today-lead">
        <div class="content">
            <div class="header"><?php _e('Total Lead', 'sejoli'); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card green member-today-sales">
        <div class="content">
            <div class="header"><?php _e('Total Sales', 'sejoli'); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card blue member-today-omset">
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

<script type="text/javascript">
(function( $ ) {
    'use strict';
    $(document).ready(function(){

        $.ajax({
            url : '<?php echo site_url('/sejoli-ajax/get-member-statistic-today'); ?>',
            dataType : 'json',
            data : {
                nonce : '<?php echo wp_create_nonce('sejoli-render-member-statistic') ?>'
            },
            success : function(response) {
                $('.information.daily .member-today-lead .content.value').html(response.lead);
                $('.information.daily .member-today-sales .content.value').html(response.sales);
                $('.information.daily .member-today-omset .content.value').html(response.omset);
            }
        });
    });
})( jQuery );
</script>
