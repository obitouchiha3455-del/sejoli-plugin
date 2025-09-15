<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class FollowUp {

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
	 * Setup follow up fields for product
	 * Hooked via filter sejoli/product/fields, priority 80
	 * @since  1.0.0
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_followup_setting_fields(array $fields) {

        $fields[] = [
			'title'	=> __('Follow Up', 'sejoli'),
            'fields' =>  [
                Field::make('separator', 'sep_followup_content',    __('Pengaturan Konten Follow Up', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('followup') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),
                Field::make('html',      'followup_info')
                    ->set_html('<p>Isi follow up ini hanya akan muncul di order yang berstatus <strong>Menunggu Pembayaran</strong> saja.</p>'),

                Field::make('complex',   'followup_content',        __('Follow Up', 'sejoli'))
                    ->add_fields([
                        Field::make('textarea', 'content',  __('Konten', 'sejoli'))
                            ->set_default_value(sejoli_get_notification_content('order-on-hold-customer', 'whatsapp'))
                    ])

            ]
        ];

        return $fields;
    }

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['followup'] = [
			'basic_link' => home_url('followup/')
		];

		return $js_vars;
	}

    /**
     * Add follow up content to product meta
     * Hooked via filter sejoli/product/meta-data, priority 100
     * @param  WP_Post $product
     * @param  int     $product_id
     * @return WP_Post
     */
    public function setup_followup_product_meta(\WP_Post $product, int $product_id) {

        $followup  = sejolisa_carbon_get_post_meta($product->ID, 'followup_content');
        $product->has_followup_content = count($followup);

        return $product;
    }

    /**
     * Setup follow up for order meta data
     * Hooked via filter sejoli/order/table/meta-data, priority 10
     * @since   1.0.0
     * @param  null|array   $order_metadata
     * @param   stdClass  $order
     * @return  array
     */
    public function setup_order_table_metadata($order_metadata, \stdClass $order) {

        if(0 < $order->product->has_followup_content && is_array($order_metadata)) :
            if(!isset($order_metadata['followup'])) :
                $order_metadata['followup'] = array();
            endif;

            if(!isset($order_metadata['followup']['affiliate'])) :
                $order_metadata['followup']['affiliate'] = array();
                for($i = 1; $i <= $order->product->has_followup_content; $i++) :
                    $order_metadata['followup']['affiliate'][$i] = '';
                endfor;
            endif;

            if(!isset($order_metadata['followup']['admin'])) :
                $order_metadata['followup']['admin'] = array();
                for($i = 1; $i <= $order->product->has_followup_content; $i++) :
                    $order_metadata['followup']['admin'][$i] = '';
                endfor;
            endif;
        endif;

        return $order_metadata;
    }
}
