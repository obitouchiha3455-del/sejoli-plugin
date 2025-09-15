<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Integration {

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
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['product']['autoresponder'] = [
			'ajaxurl' => add_query_arg([
				'action' => 'sejoli-check-autoresponder'
			], admin_url('admin-ajax.php')),
			'nonce' => wp_create_nonce('sejoli-check-autoresponder')
		];

		return $js_vars;
	}

    /**
	 * Setup fb pixel fields for product
	 * Hooked via filter sejoli/product/fields, priority 60
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_fb_pixel_setting_fields(array $fields) {

        $fb_pixel_events = [
            ''                     => __('Pilih facebook pixel event', 'sejoli'),
            'AddPaymentInfo'       => 'Add payment info',
            'AddToCart'            => 'Add to cart',
            'AddToWishlist'        => 'Add to wishlist',
            'CompleteRegistration' => 'Complete Registration',
            'Contact'              => 'Contact',
            'CustomizeProduct'     => 'Customize Product',
            'Donate'               => 'Donate',
            'Find Location'        => 'FindLocation',
            'InitiateCheckout'     => 'InitiateCheckout',
            'Lead'                 => 'Lead',
            'Purchase'             => 'Purchase',
            'Schedule'             => 'Schedule',
            'Search'               => 'Search',
            'StartTrial'           => 'Start Trial',
            'SubmitApplication'    => 'Submit Application',
            'Subscribe'            => 'Subscribe',
            'ViewContent'          => 'View Content'
        ];

        $fields[] = [
			'title'	=> __('Facebook Pixel', 'sejoli'),
            'fields' =>  [
                Field::make('separator', 'sep_sejoli_fb_pixel',         __('Pengaturan Facebook Pixel', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('facebook-pixel') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),
                Field::make('checkbox', 'fb_pixel_active',              __('Aktifkan Facebook Pixel untuk produk ini', 'sejoli')),
                Field::make('checkbox', 'fb_pixel_affiliate_active',    __('Aktifkan Facebook Pixel untuk affiliasi', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'fb_pixel_active',
                            'value' => true
                        ]
                    ]),
                Field::make('text',     'fb_pixel_id',               __('ID Facebook Pixel', 'sejoli'))
                	->set_required(true)
                	->set_conditional_logic([
                        [
                            'field' => 'fb_pixel_active',
                            'value' => true
                        ]
                    ]),
                // Field::make('text',     'fb_pixel_content_category', __('Content Category', 'sejoli')),
                // Field::make('text',     'fb_pixel_content_type',     __('Content Type', 'sejoli'))
                //     ->set_default_value('product'),

                Field::make('select',   'fb_pixel_event_load_checkout_page', __('Event pada halaman checkout', 'sejoli'))
                    ->add_options($fb_pixel_events)
                    ->set_help_text(__('Event ketika halaman checkout dikunjungi', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'fb_pixel_active',
                            'value' => true
                        ]
                    ]),

                Field::make('select',   'fb_pixel_event_submit_checkout_button', __('Event pada tombol submit di halaman checkout', 'sejoli'))
                    ->add_options($fb_pixel_events)
                    ->set_help_text(__('Event ketika user menekan tombol BELI SEKARANG', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'fb_pixel_active',
                            'value' => true
                        ]
                    ]),

                Field::make('select',   'fb_pixel_event_load_redirect_page', __('Event pada halaman redirect', 'sejoli'))
                    ->add_options($fb_pixel_events)
                    ->set_help_text(__('Event ketika user berada di halaman redirect setelah checkout', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'fb_pixel_active',
                            'value' => true
                        ]
                    ]),

                Field::make('select',   'fb_pixel_event_load_invoice_page', __('Event pada halaman invoice', 'sejoli'))
                    ->add_options($fb_pixel_events)
                    ->set_help_text(__('Event ketika user berada di halaman invoice', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'fb_pixel_active',
                            'value' => true
                        ]
                    ]),

            ]
        ];

        return $fields;
    }

    /**
	 * Setup autoresponder fields for product
	 * Hooked via filter sejoli/product/fields, priority 70
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_autoresponder_setting_fields(array $fields) {

		ob_start();

		require_once plugin_dir_path( __FILE__ ) . 'partials/product/autoresponder.php';

		$autoresponder = ob_get_contents();

		ob_end_clean();

        $fields[] = [
			'title'	=> __('Autoresponder', 'sejoli'),
            'fields' =>  [
				Field::make('separator', 'sep_sejoli_autoresponder', __('Pengaturan Autoresponder', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('autoresponder') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),
				Field::make('textarea',	'autoresponder_html_code',	__('Kode HTML Form', 'sejoli'))
					->set_help_text(
						__('Paste kode HTML Form yang anda dapatkan dari autoresponder. Jika anda masih belum mengerti hal, silahkan tanyakan ke autoresponder yang anda gunakan', 'sejoli')
					),
				Field::make('html',		'autoresponder_html_info')
					->set_html($autoresponder)
            ]
        ];

        return $fields;
    }

	/**
	 * Add autoresponder info to product meta
	 * Hooked via filter sejoli/product/meta-data, priority 50
	 * @param  WP_Post $product    [description]
	 * @param  int     $product_id [description]
	 * @return WP_Post
	 */
	public function setup_autoresponder_info(\WP_Post $product, int $product_id) {

		$autoresponder = sejolisa_carbon_get_post_meta($product->ID, 'autoresponder_html_code');

		$product->has_autoresponder = !empty($autoresponder) ? true : false;

		return $product;
	}

	/**
	 * Register buyer to selected autoresponder setup.
	 * Register when order status in-progress if product type is physical,
	 * when order status completed if digital
	 *
	 * Hooked via action sejoli/order/set-status/in-progress, priority 200
	 * Hooked via action sejoli/order/set-status/completed, priority 200
	 * @param  array  $order_data [description]
	 * @return [type]             [description]
	 */
	public function register_autoresponder(array $order_data) {

		$product = sejolisa_get_product( intval($order_data['product_id']) );

		if(
			false !== $product->has_autoresponder && (
				( 'digital'  === $product->type && 'completed' === $order_data['status'] ) ||
				( 'physical' === $product->type && 'in-progress' === $order_data['status'] )
			)
		) :
			$code          = sejolisa_carbon_get_post_meta($product->ID, 'autoresponder_html_code');
			$autoresponder = sejolisa_parsing_form_html_code( $code );

			if( false !== $autoresponder['valid'] ) :

				$user        = sejolisa_get_user($order_data['user_id']);
				$body_fields = [];

				foreach($autoresponder['fields'] as $field) :

					if('email' === $field['type']) :
						$body_fields[$field['name']] = $user->user_email;
					elseif('name' === $field['type']) :
						$body_fields[$field['name']] = $user->display_name;
					else :
						$body_fields[$field['name']] = $field['value'];
					endif;

				endforeach;


				$response = wp_remote_post( $autoresponder['form']['action'][0], [
					'method'  => 'POST',
					'timeout' => 30,
					'headers' => array(
						'Referer'    => site_url(),
						'User-Agent' => $_SERVER['HTTP_USER_AGENT']
					),
					'body'    => $body_fields
				]);

				do_action('sejoli/log/write', 'response autoresponder subscription', [
					'url'         => $autoresponder['form']['action'][0],
					'body_fields' => $body_fields,
					'response'    => strip_tags(wp_remote_retrieve_body($response))
				]);

			endif;
		endif;
	}
}
