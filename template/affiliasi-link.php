<?php
/**
 * @since   1.0.0
 * @since   1.5.1   Restructurize 'Parameter Akuisisi Data'
 *                  Add coupon parameter to affiliate link
 */

if( !defined('ABSPATH') ) :
    exit;
endif;

sejoli_header();

$affiliate_coupons = sejolisa_get_affiliate_coupons();
?>

    <h2 class="ui header"><?php _e('Affiliasi Link', 'sejoli'); ?></h2>
    <form id="affiliate-link-generator" class="ui form">
        <div class="ui fluid action input">
            <select id="product_id" name="product_id" class="ui fluid dropdown">
                <option value=""><?php _e( 'Tunggu sebentar... kami sedang mengambil semua data produk', 'sejoli' ); ?></option>
            </select>
            <button id="affiliate-link-generator-button" class="ui primary button">
                <?php _e( 'Generate', 'sejoli' ); ?>
            </button>
        </div>
    </form><br>
    <div id="affiliate-link-holder">
        <div class="ui info message"><?php _e( 'Silahkan pilih produk', 'sejoli' ); ?></div>
    </div>

    <form id="aff-link-parameter" class="ui form">

        <h2><?php _e('Tambah Parameter ke Link Affiliasi Anda','sejoli'); ?></h2>

        <h3><?php _e('Parameter Akuisisi Data', 'sejoli'); ?></h3>

        <div class="two fields">

            <div class="field">
                <label><?php _e('Platform','sejoli'); ?></label>
                <select name="param-platform" id="param-platform" class="select2-filled">
                    <option value=""><?php _e('--Pilih Platform--','sejoli'); ?></option>
                    <?php
                    $platforms = sejolisa_get_acquisition_platforms();
                    foreach ( $platforms as $key => $value ) :
                        ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php
                    endforeach;
                    ?>
                </select>
            </div>

            <div class="field">
                <label><?php _e('ID','sejoli'); ?></label>
                <input type="text" name="param-id" id="param-id" placeholder="<?php _e('Untuk ID, anda bisa isi dengan identifikasi apapun.','sejoli'); ?>">
            </div>

        </div>

        <div class='ui info message'>
            <p><?php _e('Parameter akuisisi data berfungsi jika anda ingin mengetahui asal lead atau pembeli anda', 'sejoli'); ?></p>
        </div>

        <h3><?php _e('Parameter Kupon', 'sejoli'); ?></h3>

        <div class="field">
            <label for="param-coupon"><?php _e('Kupon', 'sejoli'); ?></label>
            <select name="param-coupon" id="param-coupon" class="select2-filled">
                <option value=""><?php _e('--Pilih Kupon--','sejoli'); ?></option>
                <?php foreach ( $affiliate_coupons as $key => $value ) : ?>
                <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="ui blue button" type="submit"><?php _e('Tambahkan parameter','sejoli'); ?></button>
    </form>
    <script id="affiliate-link-tmpl" type="text/x-jsrender">
    {{props data}}
        <div class='field'>
            <label for="aff-link-{{:key}}"><b>{{:prop.label}}</b></label>
            <p>{{:prop.description}}</p>
            <div class="ui fluid action input">
                <input id="aff-link-{{:key}}" name="aff-link-{{:key}}" type="text" value="{{:prop.affiliate_link}}" readonly>
                <button class="ui teal right labeled icon button copy-btn" data-clipboard-target="#aff-link-{{:key}}"><i class="copy icon"></i> <?php _e( 'Copy', 'sejoli' ); ?></button>
            </div>
        </div>
    {{/props}}
    </script>
    <script>
    (function( $ ) {
        'use strict';
        $(document).ready(function(){
            sejoli.affiliate.link.init();
        });
    })( jQuery );
    </script>
<?php sejoli_footer(); ?>
