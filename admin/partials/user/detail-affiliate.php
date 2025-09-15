<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejolisa
 * @subpackage Sejolisa/admin/partials/user
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Detail Jaringan Member', 'sejoli'); ?>
	</h1>
    <div class="content-area">
        <div id="network-tree-area"></div>
    </div>
</div>

<script type="text/javascript">
(function( $ ) {
	'use strict';
    $(document).ready(function() {

        $('#network-tree-area').jstree({
            core:   {
                data:   {
                    url    : sejoli_admin.network.user.ajaxurl
                }
            },
            plugins : [ "themes", "json_data", "ui" ]
        });

    });
})(jQuery);
</script>
