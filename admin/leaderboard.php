<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Leaderboard {

    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
    private $version;
    
    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
     * Register leaderboard menu under sejoli main menu
     * Hooked via action admin_menu, priority 1005
     * @since 1.0.0
     * @return void
     */
    public function register_admin_menu() {
        add_submenu_page( 'crb_carbon_fields_container_sejoli.php', __('Leaderboard', 'sejoli'), __('Leaderboard', 'sejoli'), 'manage_sejoli_licenses', 'sejoli-leaderboard', [$this, 'display_leaderboard_page']);
    }
    
    /**
     * Display leaderboard page
     * @since 1.0.0
     */
    public function display_leaderboard_page() {
        require plugin_dir_path( __FILE__ ) . 'partials/leaderboard/page.php';
    }

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['leaderboard'] = [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'ajaxnonce' => wp_create_nonce('sejoli-statistic-get-commission-data'),
			'product' => [
				'select' => [
					'nonce' => wp_create_nonce('sejoli-render-product-options'),
				],
				'placeholder' => __('Pencarian produk', 'sejoli')
			]
		];

		return $js_vars;
	}

}