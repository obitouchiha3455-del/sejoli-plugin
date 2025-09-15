<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Acquisition {

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
	 * Add acquisition menu for main setting
	 * Hooked via filter sejoli/general/fields, priority 50
	 * @since  	1.0.0
	 * @param  	array 	$fields
	 * @return 	array
	 */
	public function setup_acquisition_setting_fields(array $fields) {

		ob_start();

		?>fb;Facebook
<?php ?>ins;Instagram
<?php ?>insto;Instagram Story
<?php ?>what;whatsapp
<?php ?>email;Email
<?php ?>line;Line<?php

		$platforms = ob_get_contents();
		ob_end_clean();

		$fields[] = [
			'title'		=> __('Akuisisi', 'sejoli'),
			'fields'	=> [
				Field::make('separator', 'sep_sejoli_acquisition',	__('Pengaturan Akuisisi', 'sejoli')),

				Field::make('textarea', 'sejoli_acquisition_platform',	__('Daftar platform', 'sejoli'))
					->set_default_value($platforms)
					->set_help_text(__('Pisahkan platform dengan enter. Format penulisan (key);(nama platform).<br />Contoh : <strong>linkedin;LinkedIn</strong>', 'sejoli'))

			]
		];
		return $fields;
	}

	/**
	 * Add acquisition data to order meta
	 * Hooked via sejoli/order/meta-data, priority 200
	 * @since 	1.0.0
	 * @param 	array 	$order_meta	Array of order meta data
	 * @param 	array 	$order     	Array of order data
	 * @return 	array
	 */
	public function add_acquisition_data_to_order_meta(array $order_meta, array $order) {

		$cookie     = sejolisa_get_affiliate_cookie();
		$product_id = intval($order['product_id']);

		if(isset($cookie['acq']) && isset($cookie['acq'][$product_id])) :
			$order_meta['acquisition'] = $cookie['acq'][$product_id];
		endif;

		return $order_meta;
	}

	/**
	 * Update acquisition data when order created
	 * Hooked via action sejoli/order/new
	 * @since 	1.0.0
	 * @param  	array  $order_data 	Array of order data
	 * @return  void
	 */
	public function update_acquisition_data(array $order_data) {

		if(isset($order_data['ID']) && isset($order_data['meta_data']['acquisition'])) :

			$acquisition_value                 = $acquisition = $order_data['meta_data']['acquisition'];
			$acquisition['order_id']           = $order_data['ID'];
			$acquisition_value['affiliate_id'] = $order_data['affiliate_id'];
			$acquisition_value['product_id']   = $order_data['product_id'];

			sejolisa_add_order_acquisition($acquisition);
			sejolisa_update_acquisition_value($acquisition_value, 'lead');

			if(in_array($order_data['status'], ['in-progress', 'completed'])) :
				sejolisa_update_acquisition_value($acquisition_value, 'sales');
			endif;
		endif;
	}

	/**
	 * Update acquisition data when order completed ( for digital ) or in-progress ( for physic)
	 * Hooked via action sejoli/order/set-status/in-progress, priorty 110
	 * Hooked via action sejoli/order/set-status/completed, priorty 110
	 * @since 	1.0.0
	 * @param 	array 	$order_data 	Order data obviously
	 * @return 	void
	 */
	public function update_acquisition_data_to_sales(array $order_data) {

		if(isset($order_data['meta_data']['acquisition'])) :

			if(
				(
					'completed' === $order_data['status'] &&
					'digital' === $order_data['product']->type
				) || (
					'in-progress' === $order_data['status'] &&
					'physical' === $order_data['product']->type
				)
			) :
				$acquisition_value                 = $order_data['meta_data']['acquisition'];
				$acquisition_value['affiliate_id'] = $order_data['affiliate_id'];
				$acquisition_value['product_id']   = $order_data['product_id'];

				sejolisa_update_acquisition_value($acquisition_value, 'sales');
			endif;
		endif;
	}

}
