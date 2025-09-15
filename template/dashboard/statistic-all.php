<h3 class="ui header"><?php _e('Semua Data', 'sejoli'); ?></h3>
<div class="ui four stackable cards information all">

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

    <div class="ui card light-green member-today-commission">
        <div class="content">
            <div class="header"><?php _e('Total Komisi', 'sejoli'); ?></div>
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
            url : '<?php echo site_url('/sejoli-ajax/get-member-statistic-all'); ?>',
            dataType : 'json',
            data : {
                nonce : '<?php echo wp_create_nonce('sejoli-render-member-statistic') ?>'
            },
            success : function(response) {
                $('.information.all .member-today-lead .content.value').html(response.lead);
                $('.information.all .member-today-sales .content.value').html(response.sales);
                $('.information.all .member-today-omset .content.value').html(response.omset);
                $('.information.all .member-today-commission .content.value').html(response.commission);
            }
        });

    });
})( jQuery );
</script>
