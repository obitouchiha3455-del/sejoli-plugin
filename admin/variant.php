<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli
 * @subpackage Sejoli/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli
 * @subpackage Sejoli/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Variant {

	/**
	 * Variant price
	 * @since 	1.0.0
	 * @var 	float
	 * @access 	protected
	 */
	protected $variant_price = 0;

	/**
	 * Variant weight
	 * @since 	1.0.0
	 * @var 	integer
	 * @access 	protected
	 */
	protected $variant_weight = 0;

	/**
	 * Selected variant product
	 * @since 	1.0.0
	 * @var 	false|array
	 * @access 	protected
	 */
	protected $selected_variants = [];

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
	 * Setup product variant fields for product
	 * Hooked via filter sejoli/product/fields, priority 40
	 * @since  1.0.0
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_variant_product_fields(array $fields) {

        $conditionals = [
            'physical'  => [
                [
                    'field' => 'product_type',
                    'value' => 'physical'
                ]
            ]
        ];

		$fields[] = [
			'title'	=> __('Variasi', 'sejoli'),
			'fields' =>  [
				// Subscription setting
				Field::make( 'separator', 'sep_variant' , __('Pengaturan Variasi', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('variant') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make('html',     'html_info_variant')
                    ->set_html('<div class="sejoli-html-message info"><p>'. __('Pengaturan ini hanya akan muncul jika tipe produk adalah produk fisik', 'sejoli') . '</p></div>'),

                Field::make('complex'   ,'product_variants', __('Tipe Variasi', 'sejoli'))
                    ->add_fields([

                        Field::make('text', 'name', __('Nama Variasi', 'sejoli'))
                            ->set_required(true)
                            ->set_attribute('placeholder', __('Warna, Ukuran dll', 'sejoli'))
                            ->set_help_text( __('Diisi dengan nama variasi, seperti warna, ukuran dan lain-lain', 'sejoli') ),

						Field::make('checkbox', 'required', __('Variasi ini wajib dipilih', 'sejoli')),

                        Field::make('complex', 'variant', __('Data Variasi', 'sejoli'))
                            ->add_fields([

                                Field::make('text' ,'name', __('Data'))
                                    ->set_required(true)
                                    ->set_attribute('placeholder', __('Merah, XL dll', 'sejoli'))
                                    ->set_help_text( __('Diisi dengan data variasi, seperti merah, XL dan lain-lain')),

                                Field::make('text', 'extra_price', __('Tambahan Biaya', 'sejoli'))
                                    ->set_attribute('type', 'number')
                                    ->set_default_value(0)
                                    ->set_help_text( __('Jika diisi, maka harga variasi ini akan ditambahkan ke harga utama produk', 'sejoli') ),

								Field::make('text', 'extra_weight', __('Tambahan Berat (gram)', 'sejoli'))
                                    ->set_attribute('type', 'number')
                                    ->set_default_value(0)
                                    ->set_help_text( __('Jika diisi, maka berat variasi ini akan ditambahkan ke berat utama produk', 'sejoli') ),

                            ])
                            ->set_required(true)
                            ->set_layout('tabbed-vertical')
                            ->set_header_template('<% if( name ) { %>
                                <%- name %>
                            <% } %>')
                    ])
                    ->set_layout('tabbed-vertical')
                    ->set_header_template('<% if( name ) { %>
                        <%- name %>
                    <% } %>')
                    ->set_conditional_logic( $conditionals['physical'] )
            ]
        ];

        return $fields;
    }


	/**
	 * Setup variant product meta data
	 * Hooked via filter sejoli/product/meta-data, priority 130
	 * @param  WP_Post 	$product
	 * @param  int     	$product_id
	 * @return WP_Post
	 */
    public function setup_variant_product_meta(\WP_Post $product, int $product_id) {
        if(
            property_exists($product, 'type') &&
            'physical' === $product->type) :

            $product->variants  = [];

            $variants = sejolisa_carbon_get_post_meta($product->ID, 'product_variants');

            foreach( $variants as $_variant_types ) :

                $name     = $_variant_types['name'];
                $key      = sanitize_title($name);
                $variants = $_variant_types['variant'];

                $product->variants[$key] = [
                    'label'    => $name,
					'required' => boolval($_variant_types['required']),
                    'options'  => [],
                ];

                foreach( $_variant_types['variant'] as $i => $_variant_type ) :

                    $name          = $_variant_type['name'];
                    $price         = floatval($_variant_type['extra_price']);
                    $vkey          = $key.':::'.sanitize_title($name).':::'.$i;
                    $display_price = sejolisa_price_format($price);

                    $product->variants[$key]['options'][$vkey] = [
                        'label' 		=> $name,
                        'price' 		=> (0.0 === $price) ? NULL : $display_price,
						'raw_price' 	=> $price,
						'weight'		=> intval($_variant_type['extra_weight'])
                    ];

                endforeach;

            endforeach;
        endif;

        return $product;
    }

	/**
	 * Validate variants on checkout
	 * Hooked via filter sejoli/variant/are-variants-valid. priority 1
	 * @since 	1.0.0
	 * @param  	bool   $valid
	 * @param  	array  $post_data
	 * @return 	bool
	 */
	public function validate_variants_when_checkout(bool $valid, array $post_data) {

		$product = sejolisa_get_product($post_data['product_id']);

		if(isset($product->variants) && 0 < count($product->variants)) :

			foreach($product->variants as $type => $_detail) :

				$options = array_keys($_detail['options']);
				if (isset($post_data['variants']) && is_array($post_data['variants'])) :
				    $variants = $post_data['variants'];
				else:
				    $variants = array();
				endif;

				$matchs = array_intersect($options, $variants);

				if(false !== $_detail['required'] && 0 === count($matchs)) :

					sejolisa_set_message( sprintf( __('Anda belum memilih %s produk', 'sejoli'), strtoupper($_detail['label'])));
					$valid = false;

				elseif(0 < count($matchs)) :

					$selected                      = array_values($matchs)[0];
					$selected_variant_data         = $_detail['options'][$selected];
					$selected_variant_data['type'] = $type;

					$this->selected_variants[]     = $selected_variant_data;

					$this->variant_weight += $selected_variant_data['weight'];
					$this->variant_price += $selected_variant_data['raw_price'];
				endif;

			endforeach;

		endif;

		return $valid;
	}

	/**
	 * Add variant product price to grand total
	 * Hooked via filter sejoli/order/grand-total, priority 101
	 * @since 	1.0.0
	 * @param 	float 	$grand_total [description]
	 * @param 	array 	$post_data   [description]
	 * @return 	float
	 */
	public function set_grand_total(float $grand_total, array $post_data) {

	    $quantity = isset($post_data['quantity']) && is_numeric($post_data['quantity']) 
	        ? (float) $post_data['quantity']
	        : 0;  

	    $variant_price = is_numeric($this->variant_price) ? (float) $this->variant_price : 0; 

	    return $grand_total + ($quantity * $variant_price);
	    
	}

	/**
	 * Add selected variant weight to product weight
	 * Hooked via filter sejoli/checount
	 * @since 	1.0.0
	 * @param 	int   $product_weight
	 * @param 	array $post_data
	 * @return 	int
	 */
	public function set_product_weight(int $product_weight, array $post_data) {
		return $product_weight + $this->variant_weight; // single product
	}

	/**
	 * Add variant data to cart details
	 * Hooked via sejoli/order/cart-detail, priority 5
	 * @since 	1.0.0
	 * @param 	array $cart_details
	 * @param 	array $post_data
	 * @return 	array
	 */
	public function set_data_to_cart_detail(array $cart_details, array $post_data) {

		if(is_array($this->selected_variants) && 0 < count($this->selected_variants)) :
			foreach($this->selected_variants as $_variant) :
				$key = sanitize_title('variant-' . $_variant['type']);
				$cart_details[$key] = $_variant;
			endforeach;
		endif;

		return $cart_details;
	}

	/**
	 * Add variant data to order meta data
	 * Hooked via sejoli/order/meta-data, priority 100
	 * @since 	1.0.0
	 * @param 	array 	$meta_data  [description]
	 * @param 	array 	$order_data [description]
	 * @return 	array
	 */
	public function set_data_to_order_meta_data(array $meta_data, array $order_data) {

		if(is_array($this->selected_variants) && 0 < count($this->selected_variants)) :
			$meta_data['variants'] = array();
			foreach($this->selected_variants as $_variant) :
				$meta_data['variants'][] = $_variant;
			endforeach;
		endif;

		return $meta_data;
	}

	/**
	 * Display variant data to notificiton content
	 * Hooked via sejoli/notification/content/order-meta, priority 5
	 * @since 	1.0.0
	 * @param  	string $content      	[description]
	 * @param  	string $media        	[description]
	 * @param  	string $recipient_type   [description]
	 * @param  	array  $invoice_data 	[description]
	 * @return 	string
	 */
	public function display_data_to_notification(string $content, string $media, $recipient_type, array $invoice_data) {

		if(isset($invoice_data['order_data']) && isset($invoice_data['order_data']['meta_data']['variants'])) :

			$variants = $invoice_data['order_data']['meta_data']['variants'];

			$content .= sejoli_get_notification_content(
							'product-variant',
							$media,
							array(
								'variants' => $variants
							)
						);
		endif;

		return $content;
	}
}
