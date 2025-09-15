<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class BulkNotification {

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
     * Register bulk notification menu under SEJOLI
     * Hooked via action admin_menu, priority 1010
     * @return  void
     */
    public function register_admin_menu() {

        add_submenu_page(
            'crb_carbon_fields_container_sejoli.php',
            __('Notifikasi Massal', 'sejoli'),
            __('Notifikasi Massal', 'sejoli'),
            'manage_sejoli_orders',
            'sejoli-bulk-notification',
            [$this, 'display_page']
        );

    }

	/**
	 * Send bulk notification process
	 * Hooked via action wp_ajax_sejoli-send-bulk-notification. priority 1
	 * @since 	1.1.0
	 * @return 	void
	 */
	public function send_notification() {

		$post_data = wp_parse_args($_POST['data'],[
			'invoices'         => NULL,
			'total'            => NULL,
			'send-email'       => false,
			'email-title'      => '',
			'email-content'    => '',
			'send-whatsapp'    => false,
			'whatsapp-content' => '',
			'send-sms'         => false,
			'sms-content'      => '',
			'messsage'		   => ''
		]);

		if(isset($_POST['sejoli-nonce']) && wp_verify_nonce($_POST['sejoli-nonce'], 'sejoli-send-bulk-notification')) :
			$invoices   = explode('|', $post_data['invoices']);
			$invoice_id = $invoices[0];

			unset($invoices[0]);

			$response = sejolisa_get_order(['ID' => $invoice_id]);
			$order    = $response['orders'];

			do_action('sejoli/bulk-notification/process', $order, [
				'send-email'       => ("false" === $post_data['send-email']) ? false : true,
				'email-title'      => $post_data['email-title'],
				'email-content'    => wp_kses_post($post_data['email-content']),
				'send-whatsapp'    => ("false" === $post_data['send-whatsapp']) ? false : true,
				'whatsapp-content' => $post_data['whatsapp-content'],
				'send-sms'         => ("false" === $post_data['send-sms']) ? false : true,
				'sms-content'      => $post_data['sms-content'],
			]);

			$post_data['message']  = sprintf(__('Notifikasi untuk invoice #%s sudah diproses', 'sejoli'), $order['ID']);
			$post_data['invoices'] = implode('|', $invoices);
			$post_data['total']    = $post_data['total'] - 1;
		endif;

		wp_send_json($post_data);
		exit;
	}

	/**
	 * Register CSS and JS
	 * Hooked via admin_enqueue_scripts, priority 1
	 * @return 	void
	 */
	public function register_css_and_js() {
		global $pagenow;

		if('admin.php' === $pagenow && isset($_GET['page']) && 'sejoli-bulk-notification' === $_GET['page']) :

			wp_enqueue_script( 'jquery-blockUI' );
			wp_enqueue_script( 'semantic-ui' );
			wp_enqueue_script( 'js-render' );
			wp_enqueue_script( 'daterangepicker' );
			wp_enqueue_script( $this->plugin_name . '-bulk-notification', SEJOLISA_URL . 'admin/js/sejoli-bulk-notification.js', ['jquery', 'select2', $this->plugin_name], $this->version, true);

			wp_enqueue_style( 'semantic-ui' );
			wp_enqueue_style( 'daterangepicker' );
			wp_enqueue_style( 'select2',					'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css',  [], '4.0.7', 'all');
			wp_enqueue_style( $this->plugin_name . '-bulk-notification', SEJOLISA_URL . 'admin/css/sejoli-bulk-notification.css', $this->version);

			wp_localize_script ($this->plugin_name . '-bulk-notification', 'sejoli_bulk', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'order'	  => [
					'check'	=> [
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-bulk-notification-order',
						], admin_url('admin-ajax.php')),
						'nonce' => wp_create_nonce('sejoli-bulk-notification-order')
					]
				],
				'product' => [
					'select' => [
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-product-options',
						], admin_url('admin-ajax.php')),
						'nonce' => wp_create_nonce('sejoli-render-product-options')
					],
					'placeholder' => __('Pencarian produk', 'sejoli')
				],
				'send_notification'	=> [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-send-bulk-notification',
					], admin_url('admin-ajax.php')),
					'nonce' => wp_create_nonce('sejoli-send-bulk-notification')
				]
			));
		endif;
	}

    /**
     * Display page
     * @return  void
     */
    public function display_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/bulk-notification/page.php';
    }

}
