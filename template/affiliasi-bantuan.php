<?php sejoli_header(); ?>
    <h2 class="ui header"><?php _e('Affiliasi Bantuan', 'sejoli'); ?></h2>
    <p><?php _e('Halaman ini berisi marketing material kit', 'sejoli'); ?></p>
    <form id="affiliate-help-filter" class="ui form">
        <div class="ui fluid action input">
            <select id="product_id" name="product_id" class="ui fluid dropdown">
                <option value=""><?php _e( '--Pilih Produk--', 'sejoli' ); ?></option>
            </select>
            <button id="affiliate-help-filter-button" class="ui primary button">
                <?php _e( 'Generate', 'sejoli' ); ?>
            </button>
        </div>
    </form><br>
    <div id="affiliate-help-holder">
        <div class="ui info message"><?php _e( 'Silahkan pilih produk', 'sejoli' ); ?></div>
    </div>
    <div id="affiliate-help-detail-holder" class="ui modal scrolling"></div>

    <script id="affiliate-help-tmpl" type="text/x-jsrender">
    {{props data}}
        <div class='field'>
            <label for="aff-help-{{:key}}"><b>{{:prop.title}}</b></label>
            <p>{{:prop.description}}</p>
            <div class="ui fluid action input">
                <input id="aff-help-{{:key}}" name="aff-help-{{:key}}" type="text" value="{{:prop.file}}" readonly>
                <button class="ui teal right labeled icon button copy-btn" data-clipboard-target="#aff-help-{{:key}}"><i class="copy icon"></i> <?php _e( 'Copy', 'sejoli' ); ?></button>
            </div>
        </div><br>
    {{/props}}
    </script>

    <script id="tmpl-affiliate-help-detail" type="text/x-js-render">
    <?php include('affiliasi-bantuan-detail-tmpl.php'); ?>
    </script>
    <script id="tmpl-affiliate-help-list" type="text/x-js-render">
    <?php include('affiliasi-bantuan-list-tmpl.php'); ?>
    </script>
    <script id="tmpl-affiliate-help" type="text/x-js-render">
    <?php include('affiliasi-bantuan-tmpl.php'); ?>
    </script>

    <script>
    (function( $ ) {
        'use strict';
        $(document).ready(function(){
            sejoli.affiliate.help.init();
        });
    })( jQuery );
    </script>
<?php sejoli_footer(); ?>
