<h3 class="ui header"><?php printf( __('Data Akuisisi %s', 'sejoli'),date('F Y') ); ?></h3>
<div class="">

</div>
<div class="ui two stackable cards sejoli-full-widget acquisition">
    <div class="ui card table-data">
        <div class="content">
            <table style="width:100%;" class="ui striped table">
                <thead>
                    <tr>
                        <th>Sumber Traffic</th>
                        <th>ID</th>
                        <th class='center'>View</th>
                        <th class='center'>Lead</th>
                        <th class='center'>Sale</th>
                        <th class='right'>Nilai</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
    <div class="ui card pie-data">
        <div class="content">
            <div class="header"><?php _e('Top Sales', 'sejoli'); ?></div>
            <canvas id="acquisition-pie"></canvas>
        </div>
    </div>
</div>
<script type="text/javascript">
(function( $ ) {
    'use strict';

    let sejoli_acquisition = {
        table : function(data) {
            let template = $.templates('#sejoli-acquisition-table'),
                html = '';
            $.each(data, function(i,source){
                $.each(source, function(ii, val){
                    html += template.render(val);
                });
            });
            $('.sejoli-full-widget.acquisition table tbody').html(html);
        },
        pie  : function(data) {
            var ctx = document.getElementById('acquisition-pie');
            var myChart = new Chart(ctx, {
                type : 'pie',
                data : {
                    labels : data.labels,
                    datasets: [{
                        label: 'Total Sales',
                        data : data.data,
                        backgroundColor: data.color
                    }]
                }
            });
        }
    }

    $(document).ready(function(){

        $.ajax({
            url : '<?php echo site_url('/sejoli-ajax/get-acquisition-member-data'); ?>',
            dataType : 'json',
            data : {
                nonce : '<?php echo wp_create_nonce('sejoli-get-acquisition-member-data') ?>'
            },
            success : function(response) {
                sejoli_acquisition.table(response.table);
                sejoli_acquisition.pie(response.pie);
            }
        });
    });
})( jQuery );
</script>
<script id='sejoli-acquisition-table' type="text/x-jsrender">
<tr>
    <td>{{:label}}</td>
    <td>{{:media}}</td>
    <td class='center'>{{:view}}</td>
    <td class='center'>{{:lead}}</td>
    <td class='center'>{{:sales}}</td>
    <td class='right'>{{:value}}</td>
</tr>
</script>
