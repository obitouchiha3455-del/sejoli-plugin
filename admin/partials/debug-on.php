<?php

if(
    ( defined('WP_DEBUG') && true === WP_DEBUG && !defined('WP_DEBUG_DISPLAY') ) ||
    ( defined('WP_DEBUG') && true === WP_DEBUG && defined('WP_DEBUG_DISPLAY') && true === WP_DEBUG_DISPLAY )
) :
?>
<div class="notice notice-error is-dismissible sejoli-help-message">
    <h2>WP_DEBUG AKTIF</h2>
    <p>
        Status WP_DEBUG pada website anda dalam keadaan AKTIF. <br />
        Harap dimatikan karena bisa menyebabkan sistem tidak berjalan. <br />
        Cara untuk menonaktifkan WP_DEBUG bisa dilihat dari <a href='https://www.youtube.com/watch?v=9HDfhIiTqpc' target="_blank">sini</a>
    </p>
</div>
<?php
endif;
