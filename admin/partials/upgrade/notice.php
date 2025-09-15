<?php if(!current_user_can('manage_sejoli_orders')) { return; } ?>
<div class="notice notice-warning sejoli-help-message">
    <h2><?php _e('Upgrade sistem Sejoli', 'sejoli'); ?></h2>
    <p><?php
        printf(
            __('Sejoli yang anda gunakan adalah versi %s, silahkan klik tombol dibawah ini untuk pembaharuan database', 'sejoli'),
            $this->version
    ); ?>
    </p>
    <p>
        <a href='<?php echo admin_url('admin.php?page=sejoli-system-upgrade'); ?>' class='button button-primary'><?php _e('Update Database', 'sejoli'); ?></a>
    </p>
</div>
