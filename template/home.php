<?php sejoli_header(); ?>
    <h2 class="ui header"><?php _e('Dashboard', 'sejoli'); ?></h2>
    <p><?php
    	/* translators: 1: user display name 2: logout url */
        printf(
            __( 'Halo %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'sejoli' ),
            '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
            esc_url( wp_logout_url( site_url('member-area/login/') ) )
        );
    ?></p>

    <?php require plugin_dir_path( __FILE__ ) . 'dashboard/statistic-today.php'; ?>
    <?php require plugin_dir_path( __FILE__ ) . 'dashboard/statistic-monthly.php'; ?>
    <?php require plugin_dir_path( __FILE__ ) . 'dashboard/statistic-all.php'; ?>
    <?php require plugin_dir_path( __FILE__ ) . 'dashboard/chart-monthly.php'; ?>
    <?php require plugin_dir_path( __FILE__ ) . 'dashboard/chart-yearly.php'; ?>
    <?php require plugin_dir_path( __FILE__ ) . 'dashboard/top-ten.php'; ?>
    <?php require plugin_dir_path( __FILE__ ) . 'dashboard/acquisition.php'; ?>

<?php sejoli_footer(); ?>
