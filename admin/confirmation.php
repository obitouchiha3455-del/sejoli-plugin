<?php

namespace SejoliSA\Admin;

class Confirmation {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.6
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.6
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.6
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register cron jobs for confirmation module
	 * Hooked via action admin_init, priority 100
	 * @since 	1.5.2
	 * @return 	void
	 */
	public function register_cron_jobs() {

		// delete coupon post
		if(false === wp_next_scheduled('sejoli/confirmation/delete')) :

			wp_schedule_event(time(), 'daily', 'sejoli/confirmation/delete');

		else :

			$recurring 	= wp_get_schedule('sejoli/confirmation/delete');

			if('daily' !== $recurring) :
				wp_reschedule_event(time(), 'daily', 'sejoli/confirmation/delete');
			endif;

		endif;

	}

    /**
     * Register bulk notification menu under SEJOLI
     * Hooked via action admin_menu, priority 1010
     * @return  void
     */
    public function register_admin_menu() {

        add_submenu_page(
            'crb_carbon_fields_container_sejoli.php',
            __('Konfirmasi Pembayaran', 'sejoli'),
            __('Konfirmasi Pembayaran', 'sejoli'),
            'manage_sejoli_orders',
            'sejoli-confirmation',
            [$this, 'display_page']
        );

    }

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['confirmation'] = [
			'table' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-confirmation-table'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-confirmation-table')
			],
			'detail' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-confirmation-detail'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-confirmation-detail')
			]
		];

		return $js_vars;
	}

    /**
     * Display page
     * @return  void
     */
    public function display_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/confirmation/page.php';
    }

	/**
	 * Delete confirmation data from 30 days before
	 * Hooked via action sejoli/confirmation/delete, priority 100
	 * @since 	1.5.2
	 * @return 	void
	 */
	public function delete_data() {

		$response  = \SejoliSA\Model\Confirmation::reset()
	                    ->set_filter( 'created_at', date('Y-m-d H:i:s', current_time('timestamp') - 30 * DAY_IN_SECONDS), '<')
	                    ->get()
	                    ->respond();

		if( false !== $response['valid'] ) :

			foreach( (array) $response['confirmations'] as $data ) :

				$detail = maybe_unserialize( $data->detail );

				if( isset( $detail['proof_id'] ) ) :
					wp_delete_attachment( $detail['proof_id'] );
				endif;

			endforeach;

			\SejoliSA\Model\Confirmation::reset()
				->set_filter( 'created_at', date('Y-m-d H:i:s', current_time('timestamp') - 30 * DAY_IN_SECONDS), '<')
				->delete();

		endif;
	}

}
