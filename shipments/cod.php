<?php
namespace SejoliSA\Shipment;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class COD {

    /**
     * Construction
     * @since   1.2.0
     */
    public function __construct() {

        add_filter( 'sejoli/product/fields',   array($this, 'set_product_shipping_fields'), 35);
        add_filter( 'sejoli/shipment/options', array($this, 'set_shipping_options'), 10, 2);

    }

    /**
     * Check if district in cities
     * @since   1.2.0
     * @param   int     $district_id    District ID
     * @param   array   $cities         All City IDs
     * @return  boolean
     */
    protected function check_if_subdistrict_in_cities(int $subdistrict_id, array $cities) {

        $is_in_cities = false;

        ob_start();
		require SEJOLISA_DIR . 'json/subdistrict.json';
		$json_data = ob_get_contents();
		ob_end_clean();

		$subdistricts        = json_decode($json_data, true);
        $key                 = array_search($subdistrict_id, array_column($subdistricts, 'subdistrict_id'));
        $current_subdistrict = $subdistricts[$key];

        if( in_array( $current_subdistrict['city_id'], $cities) ) :
            return true;
        endif;

        return $is_in_cities;

    }

    /**
	 * Get city options
	 * @since 	1.2.0
	 * @param  	array  $options 	City options
	 * @return 	array
	 */
	public function get_city_options($options = array()) {

		$options = [];

		ob_start();
		require SEJOLISA_DIR . 'json/city.json';
		$json_data = ob_get_contents();
		ob_end_clean();

		$subdistricts = json_decode($json_data, true);

		foreach($subdistricts as $data):
			$options[$data['city_id']] = $data['province'] . ' - ' . $data['type'].' '.$data['city_name'] ;
		endforeach;

		asort($options);

		return $options;

	}

    /**
     * Add COD shipping product fields
     * @since   1.2.0
     * @param   array   $product_fields     Current product fields
     * @return  array
     */
    public function set_product_shipping_fields($product_fields) {

        $fields = array(

            Field::make('separator', 'sep_sejoli_shipment_code', __('Cash on Delivery (COD)', 'sejoli'))
                ->set_classes('sejoli-with-help')
                ->set_help_text('<a href="' . sejolisa_get_admin_help('affiliate-link') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>')
                ->set_conditional_logic(array(
                    array(
                        'field' => 'product_type',
                        'value' => 'physical'
                    ), array(
                        'field' => 'shipment_active',
                        'value' => true
                    )
                )),

            Field::make( 'checkbox', 'shipment_cod_active', __('Aktifkan COD', 'sejoli')),

            Field::make( 'text', 'shipment_cod_title', __('Nama Kurir', 'sejoli'))
                ->set_required(true)
                ->set_default_value('COD / Kurir Toko')
                ->set_conditional_logic(array(
                    array(
                        'field'	=> 'shipment_cod_active',
                        'value'	=> true
                    ),
                    array(
                        'field' => 'product_type',
                        'value' => 'physical'
                    )
                )),

            Field::make( 'text', 'shipment_cod_fee', __('Biaya', 'sejoli'))
                ->set_attribute('type', 'number')
                ->set_default_value(0)
                ->set_help_text( __('Kosongkan jika tidak ada biaya kurir. Dalam rupiah', 'sejoli') )
                ->set_conditional_logic(array(
                    array(
                        'field'	=> 'shipment_cod_active',
                        'value'	=> true
                    ),
                    array(
                        'field' => 'product_type',
                        'value' => 'physical'
                    )
                )),

            Field::make('checkbox', 'shipment_cod_cover', __('Aktifkan jika COD hanya meliputi wilayah tertentu', 'sejoli'))
                ->set_conditional_logic(array(
                    array(
                        'field'	=> 'shipment_cod_active',
                        'value'	=> true
                    ),
                    array(
                        'field' => 'product_type',
                        'value' => 'physical'
                    )
                )),

            Field::make( 'multiselect', 'shipment_cod_city', __('Nama kota yang mendukung COD', 'sejoli'))
                ->set_required(true)
                ->set_options(array($this, 'get_city_options'))
                ->set_help_text( __('Pilih kota yang mendukung COD. WAJIB DIISI', 'sejoli'))
                ->set_conditional_logic(array(
                    array(
                        'field'	=> 'shipment_cod_active',
                        'value'	=> true
                    ),
                    array(
                        'field' => 'product_type',
                        'value' => 'physical'
                    ),
                    array(
                        'field'	=> 'shipment_cod_cover',
                        'value'	=> true
                    )
                ))
        );

        $product_fields['shipping']['fields'] = array_merge( $product_fields['shipping']['fields'], $fields );

        return $product_fields;

    }

    /**
     * Set COD shipping options
     * @since   1.2.0
     * @param   array $shipping_options     Current shipping options
     * @param   array $post_data            Post data options
     * @return  array
     */
    public function set_shipping_options($shipping_options, array $post_data) {

        $product_id    = intval($post_data['product_id']);
        $is_cod_active = boolval(sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_active' ));

        if(false !== $is_cod_active) :

            $cod_title      = sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_title');
            $cod_fee        = floatval(sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_fee'));
            $is_cod_locally = boolval(sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_cover'));
            $add_options    = true;
            $fee_title      = '';

            if(0 < $cod_fee) :
                $fee_title = ' - ' . sejolisa_price_format($cod_fee);
            endif;


            if(true === $is_cod_locally) :
                $city_cover  = sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_city');
                $district_id = intval($post_data['district_id']);
                $add_options = $this->check_if_subdistrict_in_cities($district_id, $city_cover);
            endif;

            if(true === $add_options) :
                $key_options                    = 'COD:::COD:::' . sanitize_title($cod_fee);
                $shipping_options[$key_options] = sanitize_text_field($cod_title) . $fee_title;
            endif;

        endif;

        return $shipping_options;
        
    }

}
