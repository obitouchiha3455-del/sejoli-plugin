<?php sejoli_header(); ?>
    <h2 class="ui header">Leaderboard</h2>
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
    <?php include('no-data-tmpl.php'); ?>
    </script>

    <script id="tmpl-leaderboard-table" type="text/x-js-render">
    <?php include('leaderboard-table-tmpl.php'); ?>
    </script>
    <script id="tmpl-leaderboard-table-row" type="text/x-js-render">
    <?php include('leaderboard-table-row-tmpl.php'); ?>
    </script>

    <script id="tmpl-leaderboard-cards" type="text/x-js-render">
    <?php include('leaderboard-cards-tmpl.php'); ?>
    </script>
    <script id="tmpl-leaderboard-card" type="text/x-js-render">
    <?php include('leaderboard-card-tmpl.php'); ?>
    </script>
    <script id="tmpl-leaderboard-card-placeholder" type="text/x-js-render">
    <?php include('leaderboard-card-placeholder-tmpl.php'); ?>
    </script>

    <script>
    (function( $ ) {
        'use strict';
        $(document).ready(function(){            
            sejoli.leaderBoard.init();
        });
    })( jQuery );
    </script>
<?php sejoli_footer(); ?>
