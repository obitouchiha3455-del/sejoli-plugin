<h3 class="ui header"><?php _e('Penjualan 12 bulan terakhir', 'sejoli'); ?></h3>
<canvas id='chart-yearly-statistic'>

</canvas>
<script type="text/javascript">
(function( $ ) {

    let yearly_canvas;

    $(document).ready(function(){
        var canvas = document.getElementById('chart-yearly-statistic');

        $.ajax({
            url : '<?php echo site_url('/sejoli-ajax/get-chart-statistic-yearly'); ?>',
            dataType : 'json',
            data : {
                nonce : '<?php echo wp_create_nonce('sejoli-render-chart-member-statistic') ?>'
            },
            success : function(response) {
                yearly_canvas = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: response.labels,
                        datasets: [{
                            label: 'Quantity',
                            yAxisID: 'A',
                            data: response.data.quantity,
                            backgroundColor : '<?php echo sejolisa_carbon_get_theme_option('graph_quantity'); ?>',
                            borderColor: '<?php echo sejolisa_carbon_get_theme_option('graph_quantity'); ?>'
                        }, {
                            label: 'Omset',
                            yAxisID: 'B',
                            data: response.data.omset,
                            backgroundColor : '<?php echo sejolisa_carbon_get_theme_option('graph_omset'); ?>',
                            borderColor: '<?php echo sejolisa_carbon_get_theme_option('graph_omset'); ?>'
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                id: 'A',
                                type: 'linear',
                                position: 'left',
                                ticks : {
                                    min : 0
                                }
                            }, {
                                id: 'B',
                                type: 'linear',
                                position: 'right',
                                ticks : {
                                    min : 0,
                                    callback: function(value, index, values) {
                                        return sejoli.formatPrice(value);
                                    }
                                }
                            }]
                        }
                    }
                });
            }
        });
    });
})( jQuery );
</script>
