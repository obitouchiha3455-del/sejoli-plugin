<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Checkout {

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

	protected $messages = [
		'success'	=> [],
		'error'		=> [],
		'info'		=> []
	];

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
	 * Set message
	 * @param string $message
	 * @param string $type
	 */
	protected function set_message(string $message, $type = 'error') {
		$this->messages[$type][] = $message;
	}

	/**
	 * Check current user's cookie
	 * Hooked via action sejoli/checkout/check-cookie, priority 1
	 * @param 	array 	$post_data 	Array of orde data
	 * @return 	void
	 */
	public function check_cookie(array $post_data) {

		$affiliate_id = NULL;
		$product_id   = intval($post_data['product_id']);
		$cookie       = sejolisa_get_affiliate_cookie();

		if(isset($cookie['product'][$product_id]) && !empty($cookie['product'][$product_id])) :
			$affiliate_id 	= $cookie['product'][$product_id];
		elseif(isset($cookie['general']) && !empty($cookie['general'])) :
			$affiliate_id 	= $cookie['general'];
		endif;

		if(!empty($affiliate_id)) :
			do_action('sejoli/checkout/affiliate/set', $affiliate_id, 'link');
		endif;

	}

	/**
	 * Do shipping calculation
	 * Hooked via action sejoli/checkout/shipment-calculate
	 * @since 	1.0.0
	 * @param  	array  $post_data [description]
	 * @return 	void
	 */
	public function do_shipping_calculation(array $post_data) {

		$post_data = wp_parse_args($post_data,[
			'product_id'	=> NULL,
			'district_id'	=> NULL,
			'district_name'	=> NULL,
			'quantity'		=> 1,
			'variants'		=> NULL
		]);

		$valid   = true;
		$product = sejolisa_get_product($post_data['product_id']);

		if(is_a($product, 'WP_Post') && 'publish' === $product->post_status) :

			// validate product
			$valid = apply_filters('sejoli/checkout/is-product-valid', $valid, $product, $post_data);

			// validate variations
			$variants_valid = apply_filters('sejoli/variant/are-variants-valid', $valid, $post_data);

			if($valid) :
				do_action('sejoli/shipment/calculation', $post_data);
			else :
				sejolisa_set_respond([
					'valid' => false,
					'messages' => [
						'error' => sejolisa_get_messages()
					]
				],'shipment');
			endif;

		else :
			sejolisa_set_respond([
				'valid' => false,
				'messages' => [
					'error' => [
						__('Produk tidak valid', 'sejoli')
					]
				]
			],'checkout');
		endif;
	}

	/**
	 * Do calculation grand total by item, shipment, coupon
	 * Hooked via action sejoli/checkout/calculate
	 * @since 	1.0.0
	 * @since 	1.5.3 	Add $product to sejolisa_get_affiliate_detail_checkout
	 * @param  	array  $post_data
	 * @return 	void
	 */
	public function do_calculation(array $post_data) {

		global $sejolisa;

		$post_data = wp_parse_args($post_data, [
			'product_id'         => NULL,
			'coupon'             => NULL,
            'quantity'           => 1,
			'type'               => 'regular',
			'payment_gateway'    => 'manual',
            'shipment'         	 => NULL,
            'markup_price'       => NULL,
			'shipping_own_value' => NULL,
			'variants'			 => NULL,
			'wallet'			 => false,
        ]);

		$valid   = true;
		$product = sejolisa_get_product( $post_data['product_id'] );

		if( is_a( $product, 'WP_Post' ) && 'publish' === $product->post_status ) :

			// validate product
			$valid = apply_filters( 'sejoli/checkout/is-product-valid', $valid, $product, $post_data );

			// validate shipping
			$shipping_valid = apply_filters( 'sejoli/checkout/is-shipping-valid', $valid, $product, $post_data, true );

			// validate coupon
			$coupon_valid_use = apply_filters( 'sejoli/checkout/is-coupon-valid', $valid, $product, $post_data );

			// validate variations
			$variants_valid = apply_filters( 'sejoli/variant/are-variants-valid', $valid, $post_data );

			if( $valid ) :

				$grand_total = apply_filters( 'sejoli/order/grand-total', 0, $post_data );

				do_action( 'sejoli/checkout/check-cookie', $post_data );

				sejolisa_set_respond([
					'valid'		   => true,
					'coupon_valid' => $coupon_valid_use,
					'detail'	   => [
						'quantity'	 => $post_data['quantity']
					],
					'total'        => $grand_total,
					'cart_detail'  => apply_filters('sejoli/order/cart-detail', [], $post_data),
					'affiliate'	   => sejolisa_get_affiliate_detail_checkout( $product ),
					'messages'     => [
						'warning'  => sejolisa_get_messages('error'),
						'info'     => sejolisa_get_messages('info'),
						'success'  => sejolisa_get_messages('success'),
						'warning'  => sejolisa_get_messages('warning')
					]
				], 'total');

			else :

				sejolisa_set_respond([
					'valid' => false,
					'messages' => [
						'error' => sejolisa_get_messages()
					]
				],'checkout');

			endif;

		else :

			sejolisa_set_respond([
				'valid' => false,
				'messages' => [
					'error' => [
						__('Produk tidak valid', 'sejoli')
					]
				]
			],'checkout');
		
		endif;
	
	}

	/**
	 * Do calculation grand total by item and coupon for renew
	 * Hooked via action sejoli/checkout/calculate-renew
	 * @since 	1.0.0
	 * @since 	1.5.3 	Fixing wrong data when validating product
	 * @param  	array  $post_data
	 * @return 	void
	 */
	public function do_renew_calculation( array $post_data ) {

		global $sejolisa;

		$post_data = wp_parse_args($post_data, [
			'order_id'		  => NULL,
			'product_id'      => NULL,
			'coupon'          => NULL,
            'quantity'        => 1,
			'type'            => 'regular',
			'payment_gateway' => 'manual',
            'shipment'        => NULL,
			'variants'		  => NULL
        ]);

		$valid   = true;
		$respond = sejolisa_check_subscription( $post_data['order_id'] );

		if( true === $respond['valid'] ) :

			$subscription = $respond['subscription'];

			$product = sejolisa_get_product( $subscription->product_id );
			$respond = sejolisa_get_order([
				'ID' => $subscription->order_id
			]);

			$order = $respond['orders'];

			// validate product
			$valid = apply_filters('sejoli/checkout/is-product-valid', $valid, $product, $post_data);

			// validate subscription
			$valid = apply_filters('sejoli/checkout/is-subscription-valid', $valid, $product, $subscription);
			
			// validate coupon
			$valid = apply_filters('sejoli/checkout/is-coupon-valid', $valid, $product, $post_data);

			if( $valid ) :

				$grand_total = apply_filters('sejoli/order/grand-total', 0, $post_data);

				do_action('sejoli/checkout/check-cookie', $post_data);

				sejolisa_set_respond([
					'valid'		  => true,
					'detail'	  => [
						'quantity' => $post_data['quantity']
					],
					'total'       => $grand_total,
					'cart_detail' => apply_filters('sejoli/order/cart-detail', [], $post_data),
					'affiliate'	  => sejolisa_get_affiliate_detail_checkout( $product ),
					'messages'    => [
						'warning' => sejolisa_get_messages('error'),
						'info'    => sejolisa_get_messages('info'),
						'success' => sejolisa_get_messages('success'),
						'warning' => sejolisa_get_messages('warning')
					]
				], 'total');

			else :

				global $sejolisa;

				sejolisa_set_respond([
					'valid' => false,
					'messages' => [
						'warning' => sejolisa_get_messages('error'),
						'info'    => sejolisa_get_messages('info'),
						'success' => sejolisa_get_messages('success'),
						'warning' => sejolisa_get_messages('warning')
					]
				],'total');
			endif;

		else :

			sejolisa_set_respond([
				'valid' => false,
				'messages' => [
					'error' => [
						__('Produk tidak valid', 'sejoli')
					]
				]
			],'checkout');

		endif;

	}

	/**
	 * Checkout action. there are validations
	 * - validate product
	 * - validate coupon
	 * - validate user
	 * Hooked via action sejoli/checkout/do
	 * @since  	1.0.0
	 * @since 	1.4.0	Add $post_data into sejoli/checkout/is-product-valid
	 * @param  	array  $args
	 * @return 	void
	 */
	public function do_checkout( array $post_data ) {

		$enable_register = $valid = true;

		$post_data = wp_parse_args($post_data, [
			'user_id'            => NULL,
            'affiliate_id'       => NULL,
            'coupon'             => NULL,
            'payment_gateway'    => 'manual',
            'quantity'           => 1,
            'user_email'         => NULL,
            'user_name'          => NULL,
            'user_password'      => NULL,
            'postal_code'		 => NULL,
            'user_phone'         => NULL,
            'shipment'           => NULL,
            'markup_price'	     => NULL,
			'shipping_own_value' => NULL,
            'product_id'         => NULL,
			'meta_data'          => [],
			'address'		     => NULL,
			'variants'		     => NULL,
			'wallet'		     => NULL,
		]);

		$product = sejolisa_get_product( $post_data['product_id'] );

		if( is_a( $product, 'WP_Post' ) ) :

			// validate product
			$valid = apply_filters( 'sejoli/checkout/is-product-valid', $valid, $product, $post_data );

			// get user data by checkout data
			// if the value is in valid, then later need to register
			$user_data = apply_filters( 'sejoli/checkout/user-data', false, $post_data );

			// validate shipping
			$valid = apply_filters( 'sejoli/checkout/is-shipping-valid', $valid, $product, $post_data );

			// validate coupon
			$valid = apply_filters( 'sejoli/checkout/is-coupon-valid', $valid, $product, $post_data, 'checkout' );

			// validate variant
			$valid = apply_filters( 'sejoli/variant/are-variants-valid', $valid, $post_data );

			$password_field = boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_password_field'));

			if ( is_user_logged_in() && false === $user_data ) :
				
				$valid = apply_filters( 'sejoli/checkout/is-user-data-valid', $valid, $post_data );
				
			elseif ( !is_user_logged_in() && false !== $password_field && false === $user_data ) :
			   
				$valid = apply_filters( 'sejoli/checkout/is-user-data-valid', $valid, $post_data );
					
			endif;

			$request = wp_parse_args( $_POST,[
                'recaptcha_response' => '',
            ]);

            $reCaptcha = sejolisa_validating_g_recaptcha($request, $valid, 'checkout', 'yes', 'json');

			// Processing checkout complete
			// Everything is valid
			// Now we move to order 

			if( true === $valid ) :

				$order_data = [
		            'product_id'         => $product->ID,
		            'quantity'           => $post_data['quantity'],
		            'payment_gateway'    => $post_data['payment_gateway'],
					'meta_data'		     => $post_data['meta_data'],
					'coupon'		     => $post_data['coupon'],
					'shipment'           => $post_data['shipment'],
					'markup_price'       => $post_data['markup_price'],
					'shipping_own_value' => $post_data['shipping_own_value'],
					'wallet'		     => $post_data['wallet']
		        ];

				//affiliate link simulation
				if( defined( 'WP_CLI' ) && !empty( $post_data['affiliate_id'] ) ) :

					do_action( 'sejoli/checkout/affiliate/set', $post_data['affiliate_id'], 'link' );

				else :

					do_action( 'sejoli/checkout/check-cookie', $post_data );
				
				endif;

				do_action( 'sejoli/order/set-affiliate', $post_data );

				if ($user_data && isset($user_data->ID)) :
					$order_data['user_id'] = $user_data->ID;
				endif;

				// user is not registered
				if(false === $user_data) :
        			if( $product->type === "physical" ) :
						do_action('sejoli/user/register', $post_data);
						$user_data = sejolisa_get_user($post_data['user_phone']); // user phone
						if ($user_data && isset($user_data->ID)) :
							$order_data['user_id'] = $user_data->ID;
						endif;
					else:
						do_action('sejoli/user/register', $post_data);
						$user_data = sejolisa_get_user($post_data['user_email']); // user email
						if ($user_data && isset($user_data->ID)) :
							$order_data['user_id'] = $user_data->ID;
						endif;
					endif;
				endif;

				sejolisa_set_respond([
					'valid' => true,
				], 'checkout');

				do_action( 'sejoli/log/write', 'order create', $order_data );
				do_action( 'sejoli/order/create', $order_data );

			else :
	
				sejolisa_set_respond([
					'valid' => false,
					'messages' => [
						'error' => sejolisa_get_messages()
					]
				], 'checkout');
		
			endif;

		else :
		
			sejolisa_set_respond([
				'valid' => false,
				'messages' => [
					'error' => [
						__('Produk tidak valid', 'sejoli')
					]
				]
			], 'checkout');
		
		endif;

		// validate product
		// validate coupon
		// validate user
	
	}

	/**
	 * Checkout renew subscription action. there are validations
	 * - validate order
	 * - validate subscription
	 * - validate product
	 * - validate coupon
	 * - validate user
	 * Hooked via action sejoli/checkout/renew
	 * @since  1.0.0
	 * @param  array  $args
	 * @return void
	 */
	public function renew(array $post_data) {

		$enable_register = $valid = true;

		$post_data  = wp_parse_args($post_data, [
			'user_id'         => NULL,
			'order_id'        => NULL,
			'coupon'		  => NULL,
            'payment_gateway' => 'manual',
			'meta_data'       => [],
			'wallet'		  => NULL,
		]);

		$respond = sejolisa_check_subscription($post_data['order_id']);

		if(true === $respond['valid']) :

			$subscription = $respond['subscription'];
			$product      = sejolisa_get_product($subscription->product_id);
			$respond	  = sejolisa_get_order([
				'ID' => $subscription->order_id
			]);

			$order = $respond['orders'];

			// validate product
			$valid = apply_filters('sejoli/checkout/is-product-valid', $valid, $product, $post_data);

			// validate subscription
			$valid = apply_filters('sejoli/checkout/is-subscription-valid', $valid, $product, $subscription);

			// validate coupon
			$valid = apply_filters('sejoli/checkout/is-coupon-valid', $valid, $product, $post_data);

			// get user data by checkout data
			// if the value is in valid, then later need to register
			$user_data = apply_filters( 'sejoli/checkout/user-data', false, $post_data );

			if ( is_user_logged_in() && false === $user_data ) :
				
				$valid = apply_filters( 'sejoli/checkout/is-user-data-valid', $valid, $post_data );
				
			elseif ( !is_user_logged_in() && false !== $password_field && false === $user_data ) :
			   
				$valid = apply_filters( 'sejoli/checkout/is-user-data-valid', $valid, $post_data );
					
			endif;

			$request = wp_parse_args( $_POST,[
                'recaptcha_response' => '',
            ]);

            $reCaptcha = sejolisa_validating_g_recaptcha($request, $valid, 'checkout', 'yes', 'json');

			if(false !== $valid) :

				$order_data = [
					'order_parent_id' => (!empty($order['order_parent_id'])) ? $order['order_parent_id'] : $order['ID'],
					'product_id'      => $product->ID,
					'user_id'		  => $order['user_id'],
					'quantity'        => $order['quantity'],
					'payment_gateway' => $post_data['payment_gateway'],
					'wallet'		  => $post_data['wallet'],
					'meta_data'		  => $post_data['meta_data']
				];

				do_action('sejoli/order/set-affiliate', $post_data);
				do_action('sejoli/order/renew', $order_data);

				sejolisa_set_respond([
					'valid' => true,
				],'checkout');

			else :
				sejolisa_set_respond([
					'valid' => false,
					'messages' => [
						'error' => sejolisa_get_messages()
					]
				],'checkout');
			endif;
		else :
			sejolisa_set_respond([
				'valid'	=> false,
				'messages' => [
					'error' => [
						sprintf(__('Order %s tidak memiliki data langganan', 'sejoli'), $post_data['order_id'])
					]
				]
			],'checkout');
		endif;
	}

	/**
	 * Setup product form fields for product
	 * Hooked via filter sejoli/product/fields, priority 55
	 * @since  1.1.7
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_form_product_fields(array $fields) {

		$fields[]	= array(
			'title'		=> __('Tampilan', 'sejoli'),
			'fields'	=> array(
				Field::make( 'checkbox', 'display_product_description', __('Tampilkan deskripsi produk di halaman checkout', 'sejoli')),

				Field::make( 'rich_text', 'checkout_product_description', __('Deskripsi produk', 'sejoli'))
					->set_conditional_logic([
						[
							'field' => 'display_product_description',
							'value' => true
						]
					])
					->set_required(true)
					->set_help_text(__('Saran kami untuk deskripsi produk di bagian checkout CUKUP SESINGKATNYA SAJA. Tujuan dari checkout adalah agar calon buyer SEGERA menginput data.', 'sejoli')),

				Field::make( 'checkbox', 'display_email_field', __('Tampilkan isian email untuk produk fisik', 'sejoli' ))
					->set_conditional_logic([
						[
							'field'	=> 'product_type',
							'value'	=> 'physical'
						]
					]),

				Field::make( 'checkbox', 'display_password_field',   __('Tampilkan isian password untuk produk digital', 'sejoli'))
					->set_default_value(true)
					->set_conditional_logic([
						[
							'field'	=> 'product_type',
							'value'	=> 'digital'
						]
					]),

				Field::make( 'checkbox', 'display_postalcode_field',   __('Tampilkan isian kode pos untuk produk fisik', 'sejoli'))
					->set_default_value(true)
					->set_conditional_logic([
						[
							'field'	=> 'product_type',
							'value'	=> 'physical'
						]
					]),

				Field::make( 'checkbox', 'display_note_field', __('Tampilkan isian catatan pemesanan', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'product_type',
							'value'	=> 'physical'
						]
					]),

				Field::make( 'textarea', 'note_field_placeholder', __('Instruksi pengisian catatan pemesanan', 'sejoli'))
					->set_default_value(__('Silahkan diisi dengan warna yang anda inginkan, jika tidak diisi kami akan memilihkan secara acak', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'display_note_field',
							'value'	=> true
						]
					]),

				Field::make( 'textarea', 'note_field_placeholder_text', __('Contoh pengisian catatan pemesanan', 'sejoli'))
					->set_default_value(__('XXL, rasa barbeque dll', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'display_note_field',
							'value'	=> true
						]
					]),

				Field::make( 'checkbox', 'display_warranty_label', __('Tampilkan label garansi uang kembali 100%', 'sejoli')),

				Field::make( 'checkbox', 'display_coupon_field',   __('Tampilkan pengisian kupon', 'sejoli'))
					->set_default_value(true),

				Field::make( 'checkbox', 'display_login_field',   __('Tampilkan link login', 'sejoli'))
					->set_default_value(true),

				Field::make( 'checkbox', 'display_text_field_full_name',   __('Tampilkan keterangan isian nama lengkap', 'sejoli'))
					->set_default_value(true)
					->set_conditional_logic([
						[
							'field'	=> 'product_type',
							'value'	=> 'digital'
						]
					]),

				Field::make( 'checkbox', 'display_text_field_email',   __('Tampilkan keterangan isian email', 'sejoli'))
					->set_default_value(true)
					->set_conditional_logic([
						[
							'field'	=> 'product_type',
							'value'	=> 'digital'
						]
					]),

				Field::make( 'checkbox', 'display_text_field_password',   __('Tampilkan keterangan isian password', 'sejoli'))
					->set_default_value(true)
					->set_conditional_logic([
						[
							'field'	=> 'product_type',
							'value'	=> 'digital'
						]
					]),

				Field::make( 'checkbox', 'display_text_field_phone_number',   __('Tampilkan keterangan isian nomor telepon', 'sejoli'))
					->set_default_value(true)
					->set_conditional_logic([
						[
							'field'	=> 'product_type',
							'value'	=> 'digital'
						]
					]),

				Field::make( 'checkbox', 'display_text_payment_channel',   __('Tampilkan keterangan nama bank', 'sejoli'))
					->set_default_value(true),

				Field::make( 'checkbox', 'display_detail_order_resume',   __('Tampilkan detail pesanan', 'sejoli'))
					->set_default_value(true),

				Field::make( 'text', 'custom_checkout_button_text', __('Kustom Text Button Checkout', 'sejoli'))
					->set_default_value("BUAT PESANAN"),

				Field::make( 'select', 'checkout_design', __('Desain', 'sejoli'))
					->set_options( apply_filters( 'sejoli/checkout/design/options', []))
					->set_default_value('version-2')
					->set_width(50)
			)
		);

		return $fields;
	}

	/**
	 * Setup product form fields for product
	 * Hooked via filter sejoli/checkout/design/options, priority 60
	 * @since  1.1.7
	 * @param  array  $options
	 * @return array
	 */
	public function sejoli_modify_checkout_design_options( $options ) {

	    // Tambahkan opsi baru
	    $options['default']   = __('Legacy', 'sejoli');
	    $options['version-2'] = __('Versi 2', 'sejoli');
	    $options['modern']    = __('Modern', 'sejoli');
	    $options['compact']   = __('Compact', 'sejoli');
	    $options['less']      = __('Less is More', 'sejoli');
	    $options['smart']     = __('Smart', 'sejoli');

	    // Sample Hapus opsi "Less is More"
	    // unset( $options['less'] );

	    return $options;

	}

	/**
     * Add form product meta
     * Hooked via filter sejoli/product/meta-data, priority 100
     * @param  WP_Post $product
     * @param  int     $product_id
     * @return WP_Post
     */
	public function setup_form_product_meta(\WP_Post $product, int $product_id) {

		$product->form = array(
			'email_field'      => boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_email_field')),
			'note_field'       => boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_note_field')),
			'note_placeholder' => esc_textarea(sejolisa_carbon_get_post_meta($product->ID, 'note_field_placeholder')),
			'warranty_label'   => boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_warranty_label')),
			'coupon_field'     => boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_coupon_field')),
			'login_field'      => boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_login_field')),
			'password_field'   => boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_password_field')),
			'postal_code'      => boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_postalcode_field')),
			'detail_order'     => boolval(sejolisa_carbon_get_post_meta($product->ID, 'display_detail_order_resume')),
			'checkout_button_text' => esc_attr(sejolisa_carbon_get_post_meta($product->ID, 'custom_checkout_button_text'))
		);

        return $product;
    }

	/**
	 * Setup product desain fields for product
	 * Hooked via filter sejoli/product/fields, priority 40
	 * @since  1.0.0	Initialization
	 * @since  1.1.7	Remove checkout details
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_desain_product_fields(array $fields) {

        $conditionals = array(
			'desain_bg_size' => array(
				'relation' => 'AND',
				array(
					'field' => 'desain_bg_repeat',
					'value' => 'no-repeat',
					'compare' => '=',
				)
			)
		);

		$fields[] = [
			'title'	=> __('Desain', 'sejoli'),
			'fields' =>  [
				Field::make( 'separator', 'sep_desain' , __('Pengaturan Desain', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('design') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make('image',	'desain_logo',	   __('Logo', 'sejoli'))
					->set_help_text(__('Dianjurkan panjang logo tidak melebihi 480px dengan tinggi tidak lebih dari 300px', 'sejoli')),

				Field::make('image',	'desain_bg_image', __('Background Image', 'sejoli')),
				Field::make('color',	'desain_bg_color', __('Background Color', 'sejoli')),
				Field::make('select',	'desain_bg_position',__('Background Position', 'sejoli'))
					->set_options( array(
						'' => 'Background Position',
						'left top' => 'left top',
						'left center' => 'left center',
						'left bottom' => 'left bottom',
						'right top' => 'right top',
						'right center' => 'right center',
						'right bottom' => 'right bottom',
						'center top' => 'center top',
						'center center' => 'center center',
						'center bottom' => 'center bottom'
					) ),
				Field::make('select',	'desain_bg_repeat',__('Background Repeat', 'sejoli'))
					->set_options( array(
						'' => 'Background Repeat',
						'repeat' => 'repeat',
						'repeat-x' => 'repeat-x',
						'repeat-y' => 'repeat-y',
						'no-repeat' => 'no-repeat',
					) ),
				Field::make('select',	'desain_bg_size',	__('Background Size', 'sejoli'))
					->set_options( array(
						'' => 'Background Size',
						'contain' => 'contain',
						'cover' => 'cover',
					) )
					->set_conditional_logic( $conditionals['desain_bg_size'] )
			]
        ];

        return $fields;
    }
}