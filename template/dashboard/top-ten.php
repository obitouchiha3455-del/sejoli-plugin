<div class="ui two stackable cards top-ten">

    <div class="ui card blue top-ten-omset-alltime">
        <div class="content">
            <div class="header"><?php _e('Top 10 Omset Produk Sepanjang Waktu', 'sejoli'); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card blue top-ten-omset-monthly">
        <div class="content">
            <div class="header"><?php printf(__('Top 10 Omset Produk Bulan %s', 'sejoli'), date('M Y')); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card green top-ten-quantity-alltime">
        <div class="content">
            <div class="header"><?php _e('Top 10 Produk Sepanjang Waktu', 'sejoli'); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card green top-ten-quantity-monthly">
        <div class="content">
            <div class="header"><?php printf(__('Top 10 Produk Bulan %s', 'sejoli'), date('M Y')); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card cyan top-ten-commission-alltime">
        <div class="content">
            <div class="header"><?php _e('Top 10 Komisi Sepanjang Waktu', 'sejoli'); ?></div>
        </div>
        <div class="content value">
            <div class="ui placeholder">
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui card cyan top-ten-commission-monthly">
        <div class="content">
            <div class="header"><?php printf(__('Top 10 Komisi Bulan %s', 'sejoli'), date('M Y')); ?></div>
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
    $(document).ready(function(){
        $.ajax({
            url : '<?php echo site_url('/sejoli-ajax/get-top-ten'); ?>',
            dataType : 'json',
            data : {
                nonce : '<?php echo wp_create_nonce('sejoli-render-top-ten-statistic') ?>'
            },
            success : function(response) {
                $.each(response, function(key, data){
                    let content_value = '',
                        template = $.templates('#sejoli-top-ten-template');
                    if(data.length === 0) {
                        content_value = '<?php _e('Tidak ada data', 'sejoli'); ?>';
                    } else {
                        content_value = '<div class="ui middle aligned divided list">' + template.render(data) + '</div>';
                    }

                    $('.' + key ).find('.content.value').html(content_value);
                });

            }
        });
    });
})( jQuery );
</script>
<script id='sejoli-top-ten-template' type="text/x-jsrender">
<div class="item">
    <img class="ui avatar mini circular image" src="{{:image}}">
    <div class="content">
        <div class="product-name">{{:name}}</div>
        <div class="ui blue horizontal label" style="float:right;font-weight:normal;font-size:.8rem">{{:total}}</div>
    </div>
</div>
</script>
