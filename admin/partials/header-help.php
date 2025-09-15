<?php
/**
 * @since   1.0.0
 * @since   1.5.1.1     Change facebook support to fanpage
 * @since   1.14.3      Add show/hide use of sejoli widget based on user preferencing
 */
defined( 'ABSPATH' ) || exit;

$user_id = get_current_user_id();
$sejoli_hide_widget_use_of_sejoli = get_user_meta($user_id, 'sejoli_hide_widget_use_of_sejoli', true);

if( !current_user_can('manage_sejoli_orders') ) { return; }
if( $sejoli_hide_widget_use_of_sejoli === '1' ) { return false; } ?>
<div id="use-of-sejoli-widgets" class="notice notice-info is-dismissible sejoli-help-message">
    <h2><?php _e('Penggunaan SEJOLI', 'sejoli'); ?></h2>
    <p><?php _e('Yang perlu anda atur untuk penggunaan SEJOLI adalah sebagai berikut :', 'sejoli'); ?></p>
    <p>
        <a href='<?php echo admin_url('admin.php?page=crb_carbon_fields_container_sejoli.php'); ?>' class='button button-primary'><?php _e('Pengaturan Umum', 'sejoli'); ?></a>
        <a href='<?php echo admin_url('admin.php?page=crb_carbon_fields_container_'.strtolower(__('Notifikasi', 'sejoli')).'.php'); ?>' class='button button-primary'><?php _e('Pengaturan Notifikasi', 'sejoli'); ?></a>
        <a href='<?php echo admin_url('admin.php?page=sejoli-coupons'); ?>' class='button button-primary'><?php _e('Pengaturan Kupon', 'sejoli'); ?></a>
        <a href='<?php echo admin_url('post-new.php?post_type=sejoli-product'); ?>' class='button button-primary'><?php _e('Buat Produk', 'sejoli'); ?></a>
        <a href='<?php echo admin_url('post-new.php?post_type=sejoli-access'); ?>' class='button button-primary'><?php _e('Buat Akses Produk', 'sejoli'); ?></a>
        <a href='https://m.me/sejoli.id' target="_blank" class='button' style='background-color: #007100;color: white;border-color: #007100;'><?php _e('Messenger', 'sejoli'); ?></a>
    </p>
</div>
<style>
.toggle-hide-use-of-sejoli {
    position: absolute;
    top: 0;
    right: 1px;
    border: none;
    margin: 0;
    padding: 9px;
    background: none;
    color: #787c82;
    cursor: pointer;
}
.toggle-hide-use-of-sejoli:before {
    background: none;
    color: #787c82;
    content: "\f153";
    display: block;
    font: normal 16px / 20px dashicons;
    speak: never;
    height: 20px;
    text-align: center;
    width: 20px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
.toggle-hide-use-of-sejoli:hover:before, .toggle-hide-use-of-sejoli:active:before, .toggle-hide-use-of-sejoli:focus:before {
    color: #d63638;
}
</style>
<script>
jQuery(window).on('load', function($) {
    jQuery('#use-of-sejoli-widgets button').removeClass('notice-dismiss').addClass('toggle-hide-use-of-sejoli');
    jQuery('#use-of-sejoli-widgets button.toggle-hide-use-of-sejoli').on('click', function() {
        jQuery.post(ajaxurl, { 
            action: 'sejoli_save_user_panel_preference', 
            sejoli_hide_widget_use_of_sejoli: 1 
        }, function() {
            jQuery('#use-of-sejoli-widgets').hide();
        });
    });
});
</script>
