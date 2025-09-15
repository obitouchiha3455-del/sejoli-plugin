<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

final class Reset {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.5.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.5.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.5.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Display admin notice
	 * Hooked via action admin_notices, priority 999
	 * @since 	1.5.0
	 * @return 	void
	 */
	public function display_notice() {

		global $pagenow;

		if('admin.php' === $pagenow && isset($_GET['page']) && 'sejoli-reset-data' === $_GET['page']) :
		?>
			<div class="sejoli-reset-data-response notice" style='display:none'>

			</div>
		<?php
		endif;
	}

	/**
	 * Register reset database menu under sejoli main menu
	 * Hooked via action admin_menu, priority 9999999
	 * @since 	1.5.0
	 * @return 	void
	 */
	public function register_reset_menu() {

		add_submenu_page(
			'crb_carbon_fields_container_sejoli.php',
			__('Reset Data', 'sejoli'),
			__('Reset Data', 'sejoli'),
			'manage_options',
			'sejoli-reset-data',
			[$this, 'display_reset_page']
		);

	}

	/**
	 * Display reset page
	 * @since 1.5.0
	 */
	public function display_reset_page() {
		require plugin_dir_path( __FILE__ ) . 'partials/reset/page.php';
	}

	/**
	 * Do the reset data
	 * Hooked via action wp_ajax_sejoli-reset-data, priority 1
	 * @since 	1.5.0
	 */
	public function reset_data() {

		global $wpdb;

		$respond = array();

		$post = wp_parse_args($_POST, array(
			'noncekey' => NULL
		));

		if(
			wp_verify_nonce($post['noncekey'], 'sejoli-reset-data') &&
			current_user_can( 'manage_options' )
		) :

			$all_tables = (array) $wpdb->get_results("SHOW TABLES LIKE '" . $wpdb->prefix . "sejolisa%'", ARRAY_A );

			foreach($all_tables as $table) :

				$_table = reset ( $table ) ;

				$wpdb->query( sprintf( 'TRUNCATE TABLE %s', $_table ) );

			endforeach;

			$respond = array(
				'success'	=> true,
				'message'	=> '<p>' . __('Semua data yang ada di SEJOLI sudah berhasil dihapus', 'sejoli') . '</p>'
			);

		else :

			$respond = array(
				'success'	=> false,
				'message'	=> '<p>' . __('Maaf, anda tidak diizinkan untuk melakukan proses ini', 'sejoli') . '</p>'
			);

		endif;

		echo wp_send_json($respond);
		exit;

	}

}
