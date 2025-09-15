<?php
namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Fast_Checkout {

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
		$this->version 	   = $version;

	}

    /**
	 * Setup shipment fields for product
	 * Hooked via filter sejoli/product/fields, priority 30
	 * @since  1.0.0	Initialization
	 * @since  1.2.0 	Add ability to modify product shipment fields
	 * @param  array  	$fields
	 * @return array
	 */
	public function setup_setting_fields(array $fields) {

		$fields['fast-checkout'] = [
			'title'	=> __('Fast Checkout', 'sejoli'),
			'fields' =>  [
				Field::make( 'separator', 'sep_fast_checkout' , __('Pengaturan Fast Checkout', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('fast-checkout') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make('html', 'html_info_fast_checkout')
                    ->set_html('<div class="sejoli-html-message info"><p>'. __('Pengaturan ini untuk mengaktifkan fitur checkout yang lebih cepat tanpa menampilkan halaman loading', 'sejoli') . '</p></div>'),

                Field::make('checkbox', 'fast_checkout_option', __('Aktifkan pengaturan fast checkout'))
                    ->set_option_value('yes')
                    ->set_default_value(true),  
            ]
        ];

        return $fields;

    }
	
}
