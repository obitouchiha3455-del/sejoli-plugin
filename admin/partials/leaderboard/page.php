<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data Leaderboard', 'sejoli'); ?>
	</h1>
    <div class="ui grid">
        <div class="column">
            <form id="leaderboard-filter" class="ui form">
                <div class="inline fields">
                    <div class="four wide field">
                        <select id="product_id" name="product_id" class="filter-data">
                            <option value=""><?php _e( '--Pilih Produk--', 'sejoli' ); ?></option>
                        </select>
                    </div>
                    <div class="four wide field">
                        <input type="text" id="date-range" name="date_range" class="filter-data date-range">
                    </div>
                    <div class="field">
                        <button id="leaderboard-filter-button" class="ui primary button">
                            <?php _e( 'Filter', 'sejoli' ); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="nodata-holder">
    </div>
    <div id="leaderboard-cards-holder" class="ui centered stackable grid">
    </div>
    <div id="leaderboard-table-holder" class="ui centered stackable grid">
    </div>

    <script id="tmpl-nodata" type="text/x-js-render">
    <?php include(SEJOLISA_DIR.'template/no-data-tmpl.php'); ?>
    </script>

    <script id="tmpl-leaderboard-table" type="text/x-js-render">
    <?php include(SEJOLISA_DIR.'template/leaderboard-table-tmpl.php'); ?>
    </script>
    <script id="tmpl-leaderboard-table-row" type="text/x-js-render">
    <?php include(SEJOLISA_DIR.'template/leaderboard-table-row-tmpl.php'); ?>
    </script>

    <script id="tmpl-leaderboard-cards" type="text/x-js-render">
    <?php include(SEJOLISA_DIR.'template/leaderboard-cards-tmpl.php'); ?>
    </script>
    <script id="tmpl-leaderboard-card" type="text/x-js-render">
    <?php include(SEJOLISA_DIR.'template/leaderboard-card-tmpl.php'); ?>
    </script>
    <script id="tmpl-leaderboard-card-placeholder" type="text/x-js-render">
    <?php include(SEJOLISA_DIR.'template/leaderboard-card-placeholder-tmpl.php'); ?>
    </script>

    <script>
    let sejoli_leaderboard;
    (function( $ ) {
        'use strict';
        $(document).ready(function(){

            sejoli_leaderboard = {

                init: function() {

                    sejoli_leaderboard.renderData();

                    $(document).on('click', '#leaderboard-filter-button', function(e){
                        e.preventDefault();
                        sejoli_leaderboard.renderData();
                    });

                    sejoli_leaderboard.product.select2();

                    sejoli.helper.daterangepicker("#date-range");

                },
                renderData: function() {

                    var date_range_str = $('#date-range').val();
                    var date_range_arr = date_range_str.split(' - ');
                    var start_date = date_range_arr[0];
                    var end_date = date_range_arr[1];

                    $.ajax({
                        url : sejoli_admin.leaderboard.ajaxurl,
                        method: 'POST',
                        data : {
                            action: 'sejoli-statistic-commission',
                            product_id: $('#product_id').val(),
                            start_date: start_date,
                            end_date: end_date,
                            order_status: 'completed',
                            nonce: sejoli_admin.leaderboard.ajaxnonce,
                        },
                        beforeSend : function() {
                            sejoli.helper.blockUI();
                        },
                        success : function(data) {
                            sejoli.helper.unblockUI();
                            // console.log(data);
                            if ( data ) {

                                $('#nodata-holder').hide();
                                $('#leaderboard-table-holder').hide();

                                var cards = [];
                                for (var i = 0; i < data.slice(0,10).length; i++) {
                                    cards[i] = $.templates('#tmpl-leaderboard-card').render(data[i]);
                                }

                                if ( data.length < 10 ) {
                                    for (var i = data.length; i < 10; i++) {
                                        cards[i] = $.templates('#tmpl-leaderboard-card-placeholder').render();
                                    }
                                }

                                var tmpl = $.templates('#tmpl-leaderboard-cards');
                                $('#leaderboard-cards-holder').html(tmpl.render({content:cards})).show();

                                if ( data.length > 10 ) {

                                    var table = $.templates('#tmpl-leaderboard-table-row').render(data.slice(10));

                                    var tmpl = $.templates('#tmpl-leaderboard-table');
                                    $('#leaderboard-table-holder').html(tmpl.render({content:table})).show();
                                }

                            } else {
                                var tmpl = $.templates('#tmpl-nodata');
                                $('#nodata-holder').html(tmpl.render()).show();
                                $('#leaderboard-cards-holder').hide();
                                $('#leaderboard-table-holder').hide();
                            }
                        }
                    });
                },
                product: {
                    select2:function() {

                        $.ajax({
                            url : sejoli_admin.leaderboard.ajaxurl,
                            data : {
                                action : 'sejoli-product-options',
                                nonce : sejoli_admin.leaderboard.product.select.nonce
                            },
                            type : 'GET',
                            dataType : 'json',
                            beforeSend : function() {
                            },
                            success : function(response) {
                                // console.log(response);
                                $('#product_id').select2({
                                    allowClear: true,
                                    placeholder: sejoli_admin.leaderboard.product.placeholder,
                                    width:'100%',
                                    data : response.results,
                                    templateResult : function(data) {
                                        return $("<textarea/>").html(data.text).text();
                                    },
                                    templateSelection : function(data) {
                                        return $("<textarea/>").html(data.text).text();
                                    }
                                });
                            }
                        });
                    }
                },
            };

            sejoli_leaderboard.init();

        });
    })( jQuery );
    </script>
</div>
