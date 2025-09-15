<?php

namespace SejoliSA\Front;

class Checkout
{

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
	 * Disable checkout page
	 * @since 	1.1.6
	 * @access 	protected
	 * @var 	boolean
	 */
	protected $disable_checkout = false;

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
	 * If current page is loading page
	 * @since	1.3.2
	 * @var 	boolean
	 */
	protected $is_loading_page = false;

	/**
	 * If current page is thank you page
	 * @since	1.3.2
	 * @var 	boolean
	 */
	protected $is_thankyou_page = false;

	/**
	 * If current page is renew order page
	 * @since	1.3.2
	 * @var 	boolean
	 */
	protected $is_renew_order_page = false;

	/**
	 * Current order
	 * @since 	1.3.2
	 * @var 	false|array
	 */
	protected $current_order = false;

    /**
     * enqueue scripts
     * hooked via action wp_enqueue_scripts
     *
     * @return void
     */
    public function enqueue_scripts()
    {
    	global $post;

        // register css
        // wp_register_style( '', '',[],'','all');
        wp_register_style( 'select2', 			'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.9/css/select2.min.css',	[],'','all');
        wp_register_style( 'google-font', 		'https://fonts.googleapis.com/css?family=Nunito+Sans&display=swap',			[],'','all');
        wp_register_style( 'semantic-ui', 		'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css',[],'2.4.1','all');
        wp_register_style( 'flipclock', 		'https://cdnjs.cloudflare.com/ajax/libs/flipclock/0.7.8/flipclock.min.css',	[],'0.7.8','all');

		if(
			is_singular(SEJOLI_PRODUCT_CPT) ||
			$this->is_loading_page ||
			$this->is_thankyou_page ||
			sejolisa_verify_page( 'confirm' ) ||
			sejolisa_verify_checkout_page( 'renew' )
		) :

			$style_url = SEJOLISA_URL.'public/css/sejoli-checkout.css';

			if(isset($_GET['order_id'])) :
				$respond = sejolisa_get_order([
					'ID' => $_GET['order_id']
				]);
				$product_id = $respond['orders']['product_id'];
			else :
				$product_id = !empty($post) ? $post->ID : null;
			endif;

		    $checkout_design = isset($_GET['design']) ? $_GET['design'] : '';

		    if (empty($checkout_design)) {
		        $checkout_design = sejolisa_carbon_get_post_meta($product_id, 'checkout_design');
		    }

		    $designs = apply_filters('sejoli/checkout/design/style', []);

		    $style_url = isset($designs[$checkout_design]) ? $designs[$checkout_design] : $style_url;

		    if (!empty($style_url)) :

		        wp_register_style('sejoli-checkout', $style_url, [], $this->version, 'all');

		    endif;

		endif;

        // register js
        // wp_register_script( '', '',['jquery'],'',false);
        wp_register_script( 'select2', 				'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.9/js/select2.min.js',['jquery'],'',false);
        wp_register_script( 'blockUI', 				'https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js',['jquery'],'2.70',false);
        wp_register_script( 'jsrender', 			'https://cdnjs.cloudflare.com/ajax/libs/jsrender/1.0.4/jsrender.min.js',['jquery'],'1.0.4',false);
        wp_register_script( 'semantic-ui', 			'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js',['jquery'],'2.4.1',false);
        wp_register_script( 'flipclock', 			'https://cdnjs.cloudflare.com/ajax/libs/flipclock/0.7.8/flipclock.min.js',['jquery'],'0.7.8',false);
		wp_register_script( 'sejoli-public', 		SEJOLISA_URL. 'public/js/sejoli-public.js', 		['jquery'], $this->version, false );
        wp_register_script( 'sejoli-checkout', 		SEJOLISA_URL. 'public/js/sejoli-checkout.js',	  	['jquery'], 	$this->version,false);
		wp_register_script( 'sejoli-checkout-renew',SEJOLISA_URL. 'public/js/sejoli-checkout-renew.js',	['jquery', 'sejoli-checkout'],	$this->version,false);
		
		$g_recaptcha          = boolval(sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_enabled' ));
		$g_recaptcha_checkout = boolval( sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_checkout_page' ) );
		$g_recaptcha_sitekey  = esc_attr(sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_sitekey' ));

		if(
			is_singular(SEJOLI_PRODUCT_CPT)
		) :


			if( true === $g_recaptcha && !empty($g_recaptcha_sitekey) ) :
				wp_register_script( 'g-recaptcha', 		 'https://www.google.com/recaptcha/api.js?render='.$g_recaptcha_sitekey, [], null, true );
			endif;

			if( true === $g_recaptcha && !empty($g_recaptcha_sitekey) && true === $g_recaptcha_checkout ) :
           		wp_enqueue_script('g-recaptcha');
           	endif;

           	wp_localize_script('sejoli-checkout', 'sejoli_checkout', [
                'g_recaptcha_enabled' => $g_recaptcha_checkout,
                'g_recaptcha_sitekey' => $g_recaptcha_sitekey
            ]);
		
		endif;

        if ( sejolisa_is_checkout_page() ) :

            // load css
            wp_enqueue_style('select2');
            wp_enqueue_style('google-font');
            wp_enqueue_style('semantic-ui');
            wp_enqueue_style('flipclock');
            wp_enqueue_style('sejoli-checkout');

            // load js
            wp_enqueue_script('jquery');
            wp_enqueue_script('select2');
            wp_enqueue_script('blockUI');
            wp_enqueue_script('jsrender');
            wp_enqueue_script('semantic-ui');
            wp_enqueue_script('flipclock');

            wp_enqueue_script('sejoli-checkout');
            wp_localize_script('sejoli-checkout', 'sejoli_checkout', [
                'product_id' => get_the_ID(),
				'order_id'   => (isset($_GET['order_id'])) ? intval($_GET['order_id']) : null,
                'ajax_url'   => site_url('/'),
                'ajax_nonce' => [
                    'get_calculate'        => wp_create_nonce('sejoli-checkout-ajax-get-calculate'),
                    'get_payment_gateway'  => wp_create_nonce('sejoli-checkout-ajax-get-payment-gateway'),
                    'apply_coupon'         => wp_create_nonce('sejoli-checkout-ajax-apply-coupon'),
                    'submit_checkout'      => wp_create_nonce('sejoli-checkout-ajax-submit-checkout'),
                    'submit_login'         => wp_create_nonce('sejoli-checkout-ajax-submit-login'),
                    'get_current_user'     => wp_create_nonce('sejoli-checkout-ajax-get-current-user'),
                    'delete_coupon'        => wp_create_nonce('sejoli-checkout-ajax-delete-coupon'),
                    'loading'              => wp_create_nonce('sejoli-checkout-ajax-loading'),
                    'confirm'              => wp_create_nonce('sejoli-checkout-ajax-confirm'),
                    'get_shipping_methods' => wp_create_nonce('sejoli-checkout-ajax-get-shipping-methods'),
                    'get_subdistrict'      => wp_create_nonce('sejoli-checkout-ajax-get-subdistrict'),
                    'check_user_email'     => wp_create_nonce('sejoli-checkout-ajax-check-user-email'),
                    'check_user_phone'     => wp_create_nonce('sejoli-checkout-ajax-check-user-phone'),
                ],
                'countdown_text' => [
					'jam'   => __('Jam', 'sejoli'),
					'menit' => __('Menit', 'sejoli'),
					'detik' => __('Detik', 'sejoli'),
				],
                'district_select' => __('Silakan Ketik Nama Kecamatannya', 'sejoli'),
                'affiliasi_oleh'  => __('Affiliasi oleh', 'sejoli'),
                'please_wait'     => __('Please wait...', 'sejoli')
            ]);

        endif;

		if( sejolisa_verify_checkout_page('renew') ) :

			if( true === $g_recaptcha && !empty($g_recaptcha_sitekey) && true === $g_recaptcha_checkout ) :
           		wp_enqueue_script('g-recaptcha');
           	endif;
           	
			wp_enqueue_script('sejoli-checkout-renew');

			wp_localize_script(	'sejoli-checkout-renew', 'sejoli_checkout_renew', array(
				'order_id'	=> intval($_GET['order_id']),
				'ajax_url'   => site_url('/'),
				'ajax_nonce' => [
                    'get_calculate'        => wp_create_nonce('sejoli-checkout-renew-ajax-get-calculate'),
                    'get_payment_gateway'  => wp_create_nonce('sejoli-checkout-renew-ajax-get-payment-gateway'),
                    'apply_coupon'         => wp_create_nonce('sejoli-checkout-renew-ajax-apply-coupon'),
                    'submit_checkout'      => wp_create_nonce('sejoli-checkout-renew-ajax-submit-checkout'),
                    'delete_coupon'        => wp_create_nonce('sejoli-checkout-renew-ajax-delete-coupon')

				],
				'g_recaptcha_enabled' => $g_recaptcha_checkout,
				'g_recaptcha_sitekey' => $g_recaptcha_sitekey
			));
		endif;
    }

    /**
	 * Filter to add design style css
	 * Hooked via filter sejoli/checkout/design/style, priority 10, 1
	 * @since  1.1.7
	 * @param  array  $designs
	 * @return array
	 */
	public function sejoli_checkout_default_designs($designs) {

	    $designs = [
	        'version-2' => SEJOLISA_URL . 'public/css/v2/sejoli-checkout.css',
	        'modern'    => SEJOLISA_URL . 'public/css/modern/sejoli-checkout.css',
	        'compact'   => SEJOLISA_URL . 'public/css/compact/sejoli-checkout.css',
	        'less'      => SEJOLISA_URL . 'public/css/less/sejoli-checkout.css',
	        'smart'     => SEJOLISA_URL . 'public/css/smart/sejoli-checkout.css'
	    ];

	    return $designs;

	}
	
    /**
     * Replace single product template with current sejoli
     * Hooked via filter single_template, priority 100
     * @since 	1.0.0
     * @param 	string 	$template	Single template file
     * @return 	string	Modified single template file
     */
    public function set_single_template( $template ) {

    	global $post;

	    $checkout_design = isset($_GET['design']) ? $_GET['design'] : '';

	    if($checkout_design):
	        $design = $checkout_design;
	    else:
	        $design = sejolisa_carbon_get_post_meta($post->ID, 'checkout_design');
	    endif;

	    if ( $post->post_type === 'sejoli-product' ) :

	        // Tentukan file template berdasarkan jenis produk
	        $file_name = ( $post->_product_type === 'digital' ) ? 'checkout.php' : 'checkout-fisik.php';

	        // Gunakan filter untuk mendapatkan template berdasarkan desain
	        $template = apply_filters('sejoli/checkout/design/template', $template, $design, $file_name);
	    endif;

	    return $template;

    }

    /**
	 * Filter to add design template
	 * Hooked via filter sejoli/checkout/design/template, priority 10, 3
	 * @since  1.1.7
	 * @param  array  $designs
	 * @return array
	 */
	public function sejoli_checkout_template_filter( $template, $design, $file_name ) {

	    $default_template = SEJOLISA_DIR . 'template/checkout/' . $file_name;

	    switch ($design) :

	        case 'version-2':
	            $template = file_exists(SEJOLISA_DIR . 'template/checkout/v2/' . $file_name)
	                ? SEJOLISA_DIR . 'template/checkout/v2/' . $file_name
	                : $default_template;
	            break;

	        case 'modern':
	            $template = file_exists(SEJOLISA_DIR . 'template/checkout/modern/' . $file_name)
	                ? SEJOLISA_DIR . 'template/checkout/modern/' . $file_name
	                : $default_template;
	            break;

	        case 'compact':
	            $template = file_exists(SEJOLISA_DIR . 'template/checkout/compact/' . $file_name)
	                ? SEJOLISA_DIR . 'template/checkout/compact/' . $file_name
	                : $default_template;
	            break;

	        case 'less':
	            $template = file_exists(SEJOLISA_DIR . 'template/checkout/less/' . $file_name)
	                ? SEJOLISA_DIR . 'template/checkout/less/' . $file_name
	                : $default_template;
	            break;

	        case 'smart':
	            $template = file_exists(SEJOLISA_DIR . 'template/checkout/smart/' . $file_name)
	                ? SEJOLISA_DIR . 'template/checkout/smart/' . $file_name
	                : $default_template;
	            break;

	        default:
	            $template = file_exists(SEJOLISA_DIR . 'template/checkout/' . $file_name)
	                ? SEJOLISA_DIR . 'template/checkout/' . $file_name
	                : $default_template;
	            break;
	    
	    endswitch;

	    return $template;

	}

	/**
	 * Display checkout header
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_checkout_header() {
		sejoli_get_template_part( 'checkout/partials/header.php');
	}

	/**
	 * Display checkout footer
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_checkout_footer() {
		sejoli_get_template_part( 'checkout/partials/footer.php');
	}

	/**
	 * Set checkout respond
	 * @since 	1.4.0
	 * @param 	array 		$respond
	 * @param 	array 		$calculate
	 * @param 	integer 	$quantity
	 * @param 	string 		$coupon
	 * @param 	boolean 	$is_coupon
	 */
	protected function set_checkout_respond( $respond, $calculate = array(), $quantity = 1, $coupon = NULL, $is_coupon = false ) {

		$prepare_calculate = array();

		$request = wp_parse_args( $_POST,[
            'product_id'      => 0,
            'coupon'          => NULL,
            'quantity'        => 1,
            'type'            => 'regular',
            'payment_gateway' => 'manual',
            'shipment'        => NULL,
            'variants'        => [],
			'wallet'		  => false,
        ]);

		if(
			isset( $respond['cart_detail'] ) &&
			is_array( $respond['cart_detail'] )
		) :

			foreach ( $respond['cart_detail'] as $key => $value ) :

				if ( strpos($key, 'variant-') !== false ) :

					$total_price_variants = $value['raw_price'] * $quantity;

					$prepare_calculate['variants'][] = [
						'type'  => ucwords($value['type']),
						'label' => $value['label'],
						'price' => ($total_price_variants > 0) ? sejolisa_price_format($total_price_variants) : ''
					];

				endif;

			endforeach;

		endif;

		$get_product_total = isset($respond['total']) ? $respond['total'] : 0;

		if ( isset( $respond['cart_detail']['shipment_fee'] ) ) :
			$get_product_total = ($respond['total'] - $respond['cart_detail']['shipment_fee']);
		elseif( isset( $respond['cart_detail']['transaction_fee'] ) ) :
			$get_product_total = ($respond['total'] - $respond['cart_detail']['transaction_fee']);
		elseif( isset( $respond['cart_detail']['shipment_fee'] ) && isset( $respond['cart_detail']['transaction_fee'] ) ) :
			$get_product_total = ($respond['total'] - $respond['cart_detail']['shipment_fee']) - $respond['cart_detail']['transaction_fee'];
		endif;

		if ( isset( $respond['cart_detail']['subscription'] ) ) :
			$prepare_calculate['subscription'] = $respond['cart_detail']['subscription'];
		endif;

		if ( isset( $respond['cart_detail']['transaction_fee'] ) ) :
			$prepare_calculate['transaction']['value'] = sejolisa_price_format( $respond['cart_detail']['transaction_fee'] );
		endif;

		if ( isset( $respond['cart_detail']['shipment_fee'] ) ) :
			$prepare_calculate['shipment']['value'] = sejolisa_price_format( $respond['cart_detail']['shipment_fee'] );
		endif;
		
		if ( isset( $respond['cart_detail']['ppn'] ) ) :
			$price_without_ppn = ($get_product_total / (1 + $respond['cart_detail']['ppn'] / 100));
			$value_ppn         = ($price_without_ppn * $respond['cart_detail']['ppn']) / 100;
			$prepare_calculate['ppn']['total'] = sejolisa_price_format( $value_ppn );
			$prepare_calculate['ppn']['value'] = number_format($respond['cart_detail']['ppn'], 2, ',', ' ');
		endif;

		if ( isset( $respond['cart_detail']['markup_price_fee'] ) ) :
			$prepare_calculate['markup_price']['value'] = sejolisa_price_format( $respond['cart_detail']['markup_price_fee'] );
		endif;

		if ( isset( $respond['cart_detail']['markup_price_label'] ) ) :
			$prepare_calculate['markup_price']['label'] = $respond['cart_detail']['markup_price_label'];
		endif;

		if ( isset( $respond['cart_detail']['coupon_value'] ) && !empty( $coupon ) ) :
			$getCoupon                                    = sejolisa_get_coupon_by_code( $coupon );
			$prepare_calculate['coupon']['code']          = $coupon;
			$prepare_calculate['coupon']['usage']         = $getCoupon['coupon']['usage'];
			$prepare_calculate['coupon']['limit_use']     = $getCoupon['coupon']['limit_use'];
			$prepare_calculate['coupon']['limit_date']    = $getCoupon['coupon']['limit_date'];
			$prepare_calculate['coupon']['status']        = $getCoupon['coupon']['status'];
			$prepare_calculate['coupon']['free_shipping'] = $getCoupon['coupon']['discount']['free_shipping'];

			if( true === boolval($getCoupon['coupon']['discount']['free_shipping']) ) :
			
				if ( isset( $respond['cart_detail']['shipment_fee'] ) ) :
			
					$setDiscountValue = $respond['cart_detail']['coupon_value'] + $respond['cart_detail']['shipment_fee'];
			
				else:
			
					$setDiscountValue = $respond['cart_detail']['coupon_value'];
			
				endif;
			
				$prepare_calculate['coupon']['disc_value_w_ongkir'] = sejolisa_price_format( $setDiscountValue );
			
			endif;
			
			$prepare_calculate['coupon']['value'] = sejolisa_price_format( $respond['cart_detail']['coupon_value'] );
		endif;

		// if ( isset( $respond['cart_detail']['wallet'] ) ) :
		// 	if ( isset( $respond['cart_detail']['shipment_fee'] ) ) :
		// 		$getWallet = $respond['cart_detail']['wallet'] + $respond['cart_detail']['shipment_fee'];
		// 		$prepare_calculate['wallet'] =  '-' . sejolisa_price_format( $getWallet );
		// 	else:
		// 		$prepare_calculate['wallet'] =  '-' . sejolisa_price_format( $respond['cart_detail']['wallet'] );
		// 	endif;
		// endif;

		if ( isset( $respond['cart_detail']['wallet'] ) ) :
			$prepare_calculate['wallet'] = sejolisa_price_format( $respond['cart_detail']['wallet']);
		endif;

		if( false !== $is_coupon ) :
			$calculate['data'] = array_merge( $calculate['data'], $prepare_calculate );
		else :
			$calculate = array_merge( $calculate, $prepare_calculate );
		endif;

		return $calculate;

	}

	/**
	 * Set close template
	 * Hooked via single_template, priority 9999
	 * @since 	1.0.0
	 * @since 	1.4.1 	Add condition for only-group buy
	 * @param 	string $template
	 * @return 	string
	 */
	public function set_close_template($template) {

		global $post;

		if ( $post->post_type === 'sejoli-product' )  :

			if( true === $this->disable_checkout ) :
				$template = SEJOLISA_DIR . 'template/checkout/close.php';
			else :

				$response = sejolisa_check_user_permission_by_product_group($post->ID);

				if(false === boolval($response['allow'])) :
					$template = SEJOLISA_DIR . 'template/checkout/restricted.php';
				endif;
			endif;

		endif;

		return $template;
	}

	/**
	 * Disable checkout page
	 * Hooked via action template_redirect, priority 999
	 * @since 	1.0.0
	 * @return 	void
	 */
    public function close_checkout()
    {
        if ( is_singular('sejoli-product') ) :

			global $post;

			$this->disable_checkout = sejolisa_is_product_closed($post->ID);

        endif;

    }

    /**
     * sejoli get calculate by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function get_calculate_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-get-calculate' ) ) :

            $request = wp_parse_args( $_POST,[
                'product_id'      => 0,
                'coupon'          => NULL,
                'quantity'        => 1,
                'type'            => 'regular',
                'payment_gateway' => 'manual',
                'shipment'        => NULL,
                'markup_price'    => NULL,
                'variants'        => [],
				'wallet'		  => false,
            ]);

            $response = [];

            if ( $request['product_id'] > 0 ) :

                do_action( 'sejoli/frontend/checkout/calculate', $request );

                $response['calculate'] = sejolisa_get_respond('calculate');

            endif;

            wp_send_json( $response );

        endif;
    }

	/**
     * Renew calculate renew by ajax
     * Hooked via action parse_request
     * @since 	1.1.9
     * @return 	json
     */
    public function get_renew_calculate_by_ajax() {

        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-renew-ajax-get-calculate' ) ) :

            $request = wp_parse_args( $_POST,[
				'order_id'		  => NULL,
                'product_id'      => NULL,
                'coupon'          => NULL,
                'quantity'        => 1,
                'type'            => 'regular',
                'payment_gateway' => 'manual',
                'shipment'        => NULL,
                'variants'        => [],
                // 'wallet'		  => false,
            ]);

            $calculate = $response = [];

            if (
				!empty( $request['product_id'] )  &&
				!empty( $request['order_id'] )
			) :

                do_action('sejoli/checkout/calculate-renew', $request);

                $calculation = sejolisa_get_respond('total');

				if( false !== $calculation['valid'] ) :

					$product = sejolisa_get_product( $request['product_id'] );

		        	if ( is_a( $product, 'WP_Post' ) ) :

			            $quantity            = intval($calculation['detail']['quantity']);
			            $product_price       = $calculation['cart_detail']['subscription']['regular']['raw'];
			            $product_total_price = $quantity * $product_price;

			            $calculate = [
			                'product' => [
			                    'id'        => $product->ID,
			                    'image'     => get_the_post_thumbnail_url( $product->ID, 'full' ),
			                    'title'     => sprintf( __('Perpanjangan order INV %s<br /> Produk: %s', 'sejoli'), $request['order_id'], $product->post_title),
			                    'price'     => sejolisa_price_format( $product_price ),
			                    'stock'     => 0,
			                    'variation' => NULL,
			                    'quantity'  => $quantity,
			                    'subtotal'  => sejolisa_price_format( $product_total_price ),
								'fields'	=> $product->form
			                ],

			                'affiliate' => $calculation['affiliate'],
			                'total'     => sejolisa_coloring_unique_number( sejolisa_price_format( $calculation['total'] ) ),
							'raw_total' => floatval( $calculation['total'] )
			            ];

			            if ( isset( $calculation['cart_detail']['subscription'] ) ) :
			                $calculate['subscription'] = $calculation['cart_detail']['subscription'];
			            endif;

			            if ( isset( $calculation['cart_detail']['transaction_fee'] ) ) :
			                $calculate['transaction']['value'] = sejolisa_price_format( $calculation['cart_detail']['transaction_fee'] );
			            endif;

			            if ( isset( $calculation['cart_detail']['coupon_value'] ) ) :
			                $calculate['coupon']['code'] = $request['coupon'];
			                $calculate['coupon']['value'] = sejolisa_price_format( $calculation['cart_detail']['coupon_value'] );
			            endif;

			            if ( isset( $calculation['cart_detail']['wallet'] ) ) :
							$calculate['wallet'] = sejolisa_price_format( $calculation['cart_detail']['wallet']);
							$calculate['transaction']['value'] = sejolisa_price_format( 0 );
						endif;

					endif;

		        endif;

            endif;

            wp_send_json( array( 'calculate' => $calculate ) );

        endif;

    }

    /**
     * setup calculate data
     * hooked via action sejoli/frontend/checkout/calculate
     *
     * @return void
     */
    public function calculate( $request ) {
        $calculate = [];

        $product = sejolisa_get_product( $request['product_id'] );

        if ( is_a( $product, 'WP_Post' ) ) :

            do_action('sejoli/checkout/calculate', $request);

            $respond = sejolisa_get_respond('total');

            $variation = '';

            if ( !empty( $product->variants ) ) :
                $variation = $product->variants;
            endif;

            $donation_active     = boolval( sejolisa_carbon_get_post_meta($product->ID, 'donation_active') );

            if(false === $donation_active) :
            	$quantity        = intval($respond['detail']['quantity']);
            else :
            	$quantity        = isset($respond['detail']['quantity']) ? intval($respond['detail']['quantity']) : '';
            endif;

			$coupon 			 = (array_key_exists('coupon', $request)) ? $request['coupon'] : NULL;
            $product_price       = $product->price;
            $product_total_price = floatval($quantity) * floatval($product->price);

            $product_format = sejolisa_carbon_get_post_meta( $product->ID, 'product_format' );
            $product_type   = sejolisa_carbon_get_post_meta( $product->ID, 'product_type' );

            if($product_type === "digital" && $product_format === "main-product") :

	            foreach ($product->bump_product as $key => $id_bump_product) :

					$product_bump_sales = sejolisa_get_product( $id_bump_product );

					$order_parent = isset($request['order_parent_id']) ? $request['order_parent_id']  : null;

					if($product->subscription['signup']['fee'] > 0 && $order_parent <= 0) :
						$setProduct_price = $product->price + $product->subscription['signup']['fee'];
					else :
						$setProduct_price = $product->price;
					endif;

					$biaya_awal_bump_product = floatval(sejolisa_carbon_get_post_meta($product_bump_sales->ID, 'subscription_signup_fee'));

					if($coupon):

			            $get_coupon = sejolisa_get_coupon_by_code($coupon);

				        $discount_data = $get_coupon['coupon']['discount'];

				        if($biaya_awal_bump_product > 0) :

							$set_bump_product_price = ($product_bump_sales->price + $biaya_awal_bump_product) - $setProduct_price;

							if('percentage' === $get_coupon['coupon']['discount']['type']) :
								$discount = $set_bump_product_price * ($quantity * $get_coupon['coupon']['discount']['value']) / 100;
							else :
								if('per_item' === $discount_data['usage']) :
									$discount = 0;//$quantity * $get_coupon['coupon']['discount']['value'];
								else :
									$discount = $discount_data['value'];
								endif;
							endif;

							if($respond['coupon_valid']):

								$bump_product_price = $set_bump_product_price - $discount;

							else:
								
								$bump_product_price = $set_bump_product_price;

							endif;
						
						else:

							$set_bump_product_price = $product_bump_sales->price - $setProduct_price;

							if('percentage' === $get_coupon['coupon']['discount']['type']) :
								$discount = $set_bump_product_price * ($quantity * $get_coupon['coupon']['discount']['value']) / 100;
							else :
								if('per_item' === $discount_data['usage']) :
									$discount = 0;//$quantity * $get_coupon['coupon']['discount']['value'];
								else :
									$discount = $discount_data['value'];
								endif;
							endif;

							if($respond['coupon_valid']):

								$bump_product_price = $set_bump_product_price - $discount;

							else:
								
								$bump_product_price = $set_bump_product_price;

							endif;
						
						endif;

			        else:

						if($biaya_awal_bump_product > 0) :
							$bump_product_price = ($product_bump_sales->price + $biaya_awal_bump_product) - $setProduct_price;
						else :
							$bump_product_price = $product_bump_sales->price - $setProduct_price;
						endif;

					endif;

            		$bump_product_total_price = floatval($quantity) * floatval($bump_product_price);

					$bump_sales_product[] = [
						'ID'      		  => $product_bump_sales->ID,
		                'image'   	      => get_the_post_thumbnail_url($product_bump_sales->ID,'full'),
		                'price'     	  => sejolisa_price_format( $bump_product_price ),
		                'subtotal'  	  => sejolisa_price_format( $bump_product_total_price ),
		                'enable_quantity' => sejolisa_carbon_get_post_meta( $product_bump_sales->ID, 'enable_quantity' ),
						'product' 		  => $product_bump_sales
					];

	            endforeach;

            endif;
			// End check if product has bump product

			$calculate = [
				'product' => [
					'id'        => $product->ID,
					'image'     => get_the_post_thumbnail_url($product->ID,'full'),
					'title'     => $product->post_title,
					'price'     => sejolisa_price_format( $product_price ),
					'stock'     => 0,
					'variation' => $variation,
					'quantity'  => $quantity,
					'subtotal'  => sejolisa_price_format( $product_total_price ),
					'fields'	=> $product->form,
					'bump_sales' => (isset($bump_sales_product) ? $bump_sales_product : '')
				],
				'affiliate' => isset($respond['affiliate']) ? $respond['affiliate'] : '',
				'total'     => isset($respond['total']) ? sejolisa_coloring_unique_number( sejolisa_price_format( $respond['total'] ) ) : '',
				'raw_total' => isset($respond['total']) ? floatval($respond['total']) : ''
			];

            if(true === $donation_active) :

            	$calculate['product']['bump_sales'] = array();
	            $calculate['affiliate'] = isset($respond['affiliate']) ? $respond['affiliate'] : '';
	            $calculate['total']     = isset($respond['total']) ? sejolisa_coloring_unique_number( sejolisa_price_format( $respond['total'] ) ) : '';
				$calculate['raw_total'] = isset($respond['total']) ? floatval($respond['total']) : '';

            endif;

			$calculate = $this->set_checkout_respond($respond, $calculate, $quantity, $coupon);

        endif;

        sejolisa_set_respond( $calculate, 'calculate' );

    }

    /**
     * sejoli check user email by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function check_user_email_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-check-user-email' ) ) :

            $request = wp_parse_args( $_POST,[
                'email'      => '',
            ]);

            if ( is_email( $request['email'] ) ) :

                $user = sejolisa_get_user( $request['email'] );

                if ( is_a($user,'WP_User') && $user->ID > 0 ) :

                    wp_send_json_error([__('Alamat Email sudah terdaftar silahkan login menggunakan akun anda','sejoli')]);

                endif;

            endif;

            wp_send_json_success();

        endif;
    }

    /**
     * sejoli check user phone by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function check_user_phone_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-check-user-phone' ) ) :

            $request = wp_parse_args( $_POST,[
                'phone'      => '',
            ]);

            if ( ! empty( $request['phone'] ) ) :

                $user = sejolisa_get_user( $request['phone'] );

                if ( is_a($user,'WP_User') && $user->ID > 0 ) :

                    wp_send_json_error([__('No Handphone sudah terdaftar silahkan login menggunakan akun anda','sejoli')]);

                endif;

            endif;

            wp_send_json_success();

        endif;
    }

    /**
     * sejoli get payment gateway by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function get_payment_gateway_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-get-payment-gateway' ) ) :

            $request = wp_parse_args( $_POST, []);

            $response = [];

            $response['payment_gateway'] = apply_filters('sejoli/frontend/checkout/payment-gateway', [], $request );

            wp_send_json($response);

        endif;
    }

    /**
     * setup payment gateway data
     * hooked via filter sejoli/frontend/checkout/payment-gateway
     *
     * @return void
     */
    public function payment_gateway( $payment_gateway, $request )
    {

        $payment_gateway = [];

        $payment_options = sejolisa_get_payment_options();

		$product_id = isset($request['product_id']) ? $request['product_id'] : null;
		
        $display_text_payment_channel = boolval(sejolisa_carbon_get_post_meta($product_id, 'display_text_payment_channel'));

        foreach ( $payment_options as $key => $value ) :

        	if( \str_contains( strtolower( $key ), 'moota' ) || \str_contains( strtolower( $key ), 'duitku' ) ) {
        		$label_check = __('(dicek otomatis)', 'sejoli');
        	} else {
        		$label_check = '';
        	}

            $payment_gateway[] = [
                'id' => $key,
                'title' => $value['label'],
                'image' => $value['image'],
                'display_payment' => $display_text_payment_channel,
                'label_check' => $label_check
            ];

        endforeach;

        return $payment_gateway;

    }

    /**
     * sejoli check coupon conditional limit ok, date ok
     * hooked via action parse_request
     *
     * @return json
     */
    protected function couponLimitOkDateOk($coupon) {

		return $coupon['coupon']['limit_use'] > 0 && $coupon['coupon']['limit_date'] == null && $coupon['coupon']['usage'] < $coupon['coupon']['limit_use'];

	}

	/**
     * sejoli check coupon conditional limit ok, date by current date
     * hooked via action parse_request
     *
     * @return json
     */
    protected function couponLimitOkDateOkUsageOk($coupon, $currentDateTime) {

		return $coupon['coupon']['limit_use'] > 0 && $coupon['coupon']['limit_date'] != null && $coupon['coupon']['usage'] < $coupon['coupon']['limit_use'] && $currentDateTime < $coupon['coupon']['limit_date'];

	}

	/**
     * sejoli check coupon conditional limit ok, date by current date
     * hooked via action parse_request
     *
     * @return json
     */
    protected function couponLimitOkDateByCurrDate($coupon, $currentDateTime) {

		return $coupon['coupon']['limit_use'] == 0 && $coupon['coupon']['limit_date'] != null && $currentDateTime < $coupon['coupon']['limit_date'];

	}	

	/**
     * sejoli check coupon conditional limit ok, status is active
     * hooked via action parse_request
     *
     * @return json
     */
    protected function couponLimitOkStatusOk($coupon) {

		return $coupon['coupon']['limit_use'] == 0 && $coupon['coupon']['limit_date'] == null;

	}

	/**
     * sejoli coupon type
     * hooked via action parse_request
     *
     * @return json
     */
    protected function couponCalculateType($request, $type) {

		return $request['calculate'] === $type;

	}

	/**
     * sejoli coupon valid
     * hooked via action parse_request
     *
     * @return json
     */
    protected function couponValidActive($coupon) {

		return $coupon['valid'] && $coupon['coupon']['status'] == 'active';

	}	

    /**
     * sejoli apply coupon by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function apply_coupon_by_ajax()
    {
		$request = NULL;

		// Ordinary checkout
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-apply-coupon' ) ) :

            $request = wp_parse_args( $_POST,[
                'product_id'      => 0,
                'coupon'          => NULL,
                'quantity'        => 1,
                'type'            => 'regular',
                'payment_gateway' => 'manual',
                'shipment'        => NULL,
				'calculate'		  => 'default'
            ]);

		elseif ( sejoli_ajax_verify_nonce( 'sejoli-checkout-renew-ajax-apply-coupon' ) ) :

			$request = wp_parse_args( $_POST,[
				'order_id'		  => 0,
                'product_id'      => 0,
                'coupon'          => NULL,
                'quantity'        => 1,
				'calculate'		  => 'renew'
            ]);

        endif;

		if(is_array($request) && !empty($request['coupon']) ) :

			$coupon          = sejolisa_get_coupon_by_code($request['coupon']);
			$renewal_coupon  = isset($coupon['coupon']['rule']['renewal_coupon']) ? $coupon['coupon']['rule']['renewal_coupon'] : null;
			$currentDateTime = current_datetime()->format( 'Y-m-d H:i:s' );



			if( $this->couponValidActive($coupon) && (
					($this->couponCalculateType($request, 'renew') && true === $renewal_coupon && $this->couponLimitOkDateOkUsageOk($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'renew') && true === $renewal_coupon && $this->couponLimitOkDateOk($coupon)) ||
	                ($this->couponCalculateType($request, 'renew') && true === $renewal_coupon && $this->couponLimitOkDateByCurrDate($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'renew') && true === $renewal_coupon && $this->couponLimitOkStatusOk($coupon)) ||
					($this->couponCalculateType($request, 'renew') && false === $renewal_coupon && $this->couponLimitOkDateOkUsageOk($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'renew') && false === $renewal_coupon && $this->couponLimitOkDateOk($coupon)) ||
	                ($this->couponCalculateType($request, 'renew') && false === $renewal_coupon && $this->couponLimitOkDateByCurrDate($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'renew') && false === $renewal_coupon && $this->couponLimitOkStatusOk($coupon)) ||
	                ($this->couponCalculateType($request, 'default') && false === $renewal_coupon && $this->couponLimitOkDateOkUsageOk($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'default') && false === $renewal_coupon && $this->couponLimitOkDateOk($coupon)) ||
	                ($this->couponCalculateType($request, 'default') && false === $renewal_coupon && $this->couponLimitOkDateByCurrDate($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'default') && false === $renewal_coupon && $this->couponLimitOkStatusOk($coupon)) ||
	                ($this->couponCalculateType($request, 'renew') && null === $renewal_coupon && $this->couponLimitOkDateOkUsageOk($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'renew') && null === $renewal_coupon && $this->couponLimitOkDateOk($coupon)) ||
	                ($this->couponCalculateType($request, 'renew') && null === $renewal_coupon && $this->couponLimitOkDateByCurrDate($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'renew') && null === $renewal_coupon && $this->couponLimitOkStatusOk($coupon)) ||
	                ($this->couponCalculateType($request, 'default') && null === $renewal_coupon && $this->couponLimitOkDateOkUsageOk($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'default') && null === $renewal_coupon && $this->couponLimitOkDateOk($coupon)) ||
	                ($this->couponCalculateType($request, 'default') && null === $renewal_coupon && $this->couponLimitOkDateByCurrDate($coupon, $currentDateTime)) ||
	                ($this->couponCalculateType($request, 'default') && null === $renewal_coupon && $this->couponLimitOkStatusOk($coupon))
				)
			):

				do_action('sejoli/frontend/checkout/apply-coupon', $coupon, $request);

				$response = sejolisa_get_respond('apply-coupon');

			elseif($coupon['valid'] && $coupon['coupon']['status'] == 'inactive') :

				$response = [
					'valid' => false,
					'messages' => [__('Kupon tidak aktif', 'sejoli')]
				];

			elseif($coupon['valid'] && $coupon['coupon']['limit_use'] > 0 && $coupon['coupon']['limit_date'] == null && $coupon['coupon']['usage'] >= $coupon['coupon']['limit_use'] && $coupon['coupon']['status'] == 'active') :

				$response = [
					'valid' => false,
					'messages' => [__('Batas penggunaan kupon sudah mencapai batas')]
				];

			elseif($coupon['valid'] && $coupon['coupon']['limit_use'] >= 0 && $coupon['coupon']['limit_date'] != null && $currentDateTime > $coupon['coupon']['limit_date'] && $coupon['coupon']['status'] == 'active') :

				$response = [
					'valid' => false,
					'messages' => [__('Batas penggunaan kupon sudah berakhir', 'sejoli')]
				];

			elseif($coupon['valid'] && $coupon['coupon']['limit_use'] > 0 && $coupon['coupon']['limit_date'] != null && $coupon['coupon']['usage'] >= $coupon['coupon']['limit_use'] && $currentDateTime > $coupon['coupon']['limit_date'] && $coupon['coupon']['status'] == 'active') :

				$response = [
					'valid' => false,
					'messages' => [__('Batas penggunaan kupon sudah mencapai batas dan sudah berakhir', 'sejoli')]
				];

			elseif($coupon['valid'] && $request['calculate'] === 'default' && $renewal_coupon) :

				$response = [
					'valid' => false,
					'messages' => [__('Kode kupon ini hanya untuk perpanjangan langganan', 'sejoli')]
				];

			else :

				$response = [
					'valid' => false,
					'messages' => [__('Kode kupon tidak valid', 'sejoli')]
				];

			endif;

			wp_send_json($response);

		endif;

		if(is_array($request) && empty($request['coupon']) ) :

			$response = [
				'valid' => false,
				'messages' => [__('Kode kupon belum diisi!', 'sejoli')]
			];

			wp_send_json($response);

		endif;

    }

    /**
     * sejoli apply coupon
     * hooked via action sejoli/frontend/checkout/apply-coupon
     *
     * @return json
     */
    public function apply_coupon( $coupon, $request )
    {
		if(!isset($request['calculate']) || 'default' === $request['calculate']) :
        	do_action('sejoli/checkout/calculate', $request);
		else :
			do_action('sejoli/checkout/calculate-renew', $request);
		endif;

        $respond = sejolisa_get_respond('total');

        if (
			isset( $respond['messages']['warning'] ) &&
            is_array( $respond['messages']['warning'] ) &&
			0 < count( $respond['messages']['warning'] ) &&
			empty( $respond['messages']['info'] )
		) :
            $respond = [
                'valid' => false,
                'messages' => $respond['messages']['warning'],
            ];

			sejolisa_set_respond($respond, 'apply-coupon');

        else:

            $product = sejolisa_get_product( $request['product_id'] );
            $variant_price = 0;

            foreach ( $respond['cart_detail'] as $key => $value ) :

				if ( strpos($key, 'variant-') !== false ) :

					$variant_price = $value['raw_price'];

				endif;

			endforeach;

			if($product->subscription['signup']['fee'] > 0 && 'renew' !== $request['calculate']):

				$setProduct_price = $product->price + $product->subscription['signup']['fee'];

			else:

				$setProduct_price = $product->price;

			endif;

            $discount_value = apply_filters('sejoli/coupon/value', $coupon['coupon']['discount']['value'], $setProduct_price, $coupon['coupon'], $request);
            if ( $coupon['coupon']['discount']['type'] === 'percentage' ) :
                // $discount_value = ( $discount_value / 100 ) * $product->price;
                $discount_value = $discount_value;
            else:
            	$discount_value = $discount_value;
            endif;

			$discount_data = $coupon['coupon']['discount'];

            $thumbnail_url = get_the_post_thumbnail_url($product->ID,'full');
            if ( $thumbnail_url === false ) :
                $thumbnail_url = '';
            endif;

			$quantity            = intval($respond['detail']['quantity']);
			$product_price       = $product->price;
			$shipment_fee        = isset($respond['cart_detail']['shipment_fee']) ?
									$respond['cart_detail']['shipment_fee'] : 0;
			$transaction_fee     = isset($respond['cart_detail']['transaction_fee']) ?
									$respond['cart_detail']['transaction_fee'] : 0;
			$product_total_price = $quantity * $product_price;

            if($variant_price > 0){
            	$checkTotal = $product->price + $shipment_fee + $variant_price;
            } else {
            	if($shipment_fee > 0){
            		$checkTotal = $product->price + $shipment_fee;
            	} else {
            		$checkTotal = $product->price;
            	}
            }
            if($discount_value == $checkTotal){
            	if( $coupon['coupon']['discount']['value'] == 100 ){
            		$respond['total'] =  $respond['total'] - $transaction_fee;
            	} else {
            		if($shipment_fee > 0){
            			$respond['total'] =  $respond['total'];
            		} else {
            			$respond['total'] =  $respond['total'];
            		}
            	}
            } else {
            	$respond['total'] =  $respond['total'];
            }

            $product_format = sejolisa_carbon_get_post_meta( $product->ID, 'product_format' );
            $product_type   = sejolisa_carbon_get_post_meta( $product->ID, 'product_type' );
            if($product_type === "digital" && $product_format === "main-product") :

	            foreach ($product->bump_product as $key => $id_bump_product) :

					$product_bump_sales = sejolisa_get_product( $id_bump_product );

					$biaya_awal_bump_product = floatval(sejolisa_carbon_get_post_meta($product_bump_sales->ID, 'subscription_signup_fee'));
					
					if($biaya_awal_bump_product > 0) :

						$set_bump_product_price = ($product_bump_sales->price + $biaya_awal_bump_product) - $setProduct_price;

						if('percentage' === $coupon['coupon']['discount']['type']) :
							$discount = $set_bump_product_price * ($quantity * $coupon['coupon']['discount']['value']) / 100;
						else :
							if('per_item' === $discount_data['usage']) :
								$discount = 0;//$quantity * $coupon['coupon']['discount']['value'];
							else :
								$discount = $discount_data['value'];
							endif;
						endif;

						if($respond['coupon_valid']):

							$bump_product_price = $set_bump_product_price - $discount;

						else:
							
							$bump_product_price = $set_bump_product_price;

						endif;

					else:

						$set_bump_product_price = $product_bump_sales->price - $setProduct_price;

						if('percentage' === $coupon['coupon']['discount']['type']) :
							$discount = $set_bump_product_price * ($quantity * $coupon['coupon']['discount']['value']) / 100;
						else :
							if('per_item' === $discount_data['usage']) :
								$discount = 0;//$quantity * $coupon['coupon']['discount']['value'];
							else :
								$discount = $discount_data['value'];
							endif;
						endif;

						if($respond['coupon_valid']):

							$bump_product_price = $set_bump_product_price - $discount;

						else:
							
							$bump_product_price = $set_bump_product_price;

						endif;

					endif;

            		$bump_product_total_price = floatval($quantity) * floatval($bump_product_price);

					$bump_sales_product[] = [
						'ID'      		  => $product_bump_sales->ID,
		                'image'   	      => get_the_post_thumbnail_url($product_bump_sales->ID,'full'),
		                'price'     	  => sejolisa_price_format( $bump_product_price ),
		                'subtotal'  	  => sejolisa_price_format( $bump_product_total_price ),
		                'enable_quantity' => sejolisa_carbon_get_post_meta( $product_bump_sales->ID, 'enable_quantity' ),
						'product' 		  => $product_bump_sales
					];

	            endforeach;

            endif;

            if(isset($request['main_product_id'])):

	            $main_bump_product = sejolisa_get_product( $request['main_product_id'] );
	            if($product_type === "digital" && $product_format === "bump-product") :

		            foreach ($main_bump_product->bump_product as $key => $id_bump_product) :

		            	if($main_bump_product->subscription['signup']['fee'] > 0 && 'renew' !== $request['calculate']):
							$setProduct_price = $main_bump_product->price + $main_bump_product->subscription['signup']['fee'];
						else:
							$setProduct_price = $main_bump_product->price;
						endif;

						$product_bump_sales = sejolisa_get_product( $id_bump_product );

						$biaya_awal_bump_product = floatval(sejolisa_carbon_get_post_meta($product_bump_sales->ID, 'subscription_signup_fee'));
						
						if($biaya_awal_bump_product > 0) :

							$set_bump_product_price = ($product_bump_sales->price + $biaya_awal_bump_product) - $setProduct_price;

							if('percentage' === $coupon['coupon']['discount']['type']) :
								$discount = $set_bump_product_price * ($quantity * $coupon['coupon']['discount']['value']) / 100;
							else :
								if('per_item' === $discount_data['usage']) :
									$discount = 0;//$quantity * $coupon['coupon']['discount']['value'];
								else :
									$discount = $discount_data['value'];
								endif;
							endif;

							if($respond['coupon_valid']):

								$bump_product_price = $set_bump_product_price - $discount;

							else:
								
								$bump_product_price = $set_bump_product_price;

							endif;
						
						else:

							$set_bump_product_price = $product_bump_sales->price - $setProduct_price;

							if('percentage' === $coupon['coupon']['discount']['type']) :
								$discount = $set_bump_product_price * ($quantity * $coupon['coupon']['discount']['value']) / 100;
							else :
								if('per_item' === $discount_data['usage']) :
									$discount = 0;//$quantity * $coupon['coupon']['discount']['value'];
								else :
									$discount = $discount_data['value'];
								endif;
							endif;

							if($respond['coupon_valid']):

								$bump_product_price = $set_bump_product_price - $discount;

							else:
								
								$bump_product_price = $set_bump_product_price;

							endif;
						
						endif;

	            		$bump_product_total_price = floatval($quantity) * floatval($bump_product_price);

						$bump_sales_product[] = [
							'ID'      		  => $product_bump_sales->ID,
			                'image'   	      => get_the_post_thumbnail_url($product_bump_sales->ID,'full'),
			                'price'     	  => sejolisa_price_format( $bump_product_price ),
			                'subtotal'  	  => sejolisa_price_format( $bump_product_total_price ),
			                'enable_quantity' => sejolisa_carbon_get_post_meta( $product_bump_sales->ID, 'enable_quantity' ),
							'product' 		  => $product_bump_sales
						];

		            endforeach;

	            endif;

	        endif;

            $calculate = [
                'valid'    => true,
                'messages' => $respond['messages']['info'],
                'data'     => [
                    'affiliate' => $respond['affiliate'],
                    'product'   => [
                        'id'    => $product->ID,
                        'image' => $thumbnail_url,
                        'title' =>
							(!isset($request['calculate']) || 'default' === $request['calculate']) ?
							$product->post_title :
							sprintf( __('Perpanjangan order INV %s<br /> Produk: %s', 'sejoli'), $request['order_id'], $product->post_title),
                        'price' 	=> sejolisa_price_format( $product_price ),
						'quantity'	=> $quantity,
						'subtotal'	=> sejolisa_price_format( $product_total_price ),
						'bump_sales' => (isset($bump_sales_product) ? $bump_sales_product : '')
                    ],
                    'coupon' => [
                        'code'      => $coupon['coupon']['code'],
                        'type'      => $coupon['coupon']['discount']['type'],
                        'nominal'   => $coupon['coupon']['discount']['value'],
                        'value'     => sejolisa_price_format($discount_value),
                        'limit_use' => $coupon['coupon']['limit_use'],
                        'usage'     => $coupon['coupon']['usage'],
                        'limit_date'=> $coupon['coupon']['limit_date'],
                        'status'    => $coupon['coupon']['status'],
                    ],
                    'total' 	=> sejolisa_coloring_unique_number( sejolisa_price_format( $respond['total'] ) ),
					'raw_total' => floatval( $respond['total'] )
                ]
            ];

			$calculate = $this->set_checkout_respond($respond, $calculate, $quantity, $request['coupon'], true);

			sejolisa_set_respond($calculate, 'apply-coupon');

        endif;

    }

    /**
     * sejoli submit checkout by ajax
     * hooked via action parse_request
     * @since 	1.0.0
     * @return json
     */
    public function submit_checkout_by_ajax()
    {
		$request = NULL;

        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-submit-checkout' ) ) :

            $request = wp_parse_args($_POST,[
                'user_id'            => NULL,
                'affiliate_id'       => NULL,
                'coupon'             => NULL,
                'payment_gateway'    => 'manual',
                'quantity'           => 1,
                'user_email'         => NULL,
                'user_name'          => NULL,
                'user_password'      => NULL,
                'postal_code'	     => NULL,
                'user_phone'         => NULL,
                'district_id'        => NULL,
                'district_name'        => NULL,
                'shipment'           => NULL,
				'wallet'		     => NULL,
                'product_id'         => 0,
                'variants'           => [],
				'other'			     => [],
				'recaptcha_response' => '',
            ]);

			$checkout_type = 'default';

		elseif ( sejoli_ajax_verify_nonce( 'sejoli-checkout-renew-ajax-submit-checkout' ) ) :

			$request = wp_parse_args($_POST,[
                'user_id'            => NULL,
                'affiliate_id'       => NULL,
                'coupon'             => NULL,
				'wallet'		     => NULL,
                'payment_gateway'    => 'manual',
                'quantity'           => 1,
                'product_id'         => 0,
				'order_id'           => 0,
				'recaptcha_response' => '',
            ]);

			$checkout_type = 'renew';

		endif;

		if(is_array($request)) :

            $current_user = wp_get_current_user();

            if ( isset( $current_user->ID ) && $current_user->ID > 0 ) :
                $request['user_id'] = $current_user->ID;
            endif;

			if('default' === $checkout_type) :
            	do_action('sejoli/checkout/do', $request);
			else :
				do_action('sejoli/checkout/renew', $request);
			endif;

            $order    = sejolisa_get_respond('order');
            $checkout = sejolisa_get_respond('checkout');

            if(false === $checkout['valid']) :

                $response = [
                    'valid' => false,
                    'messages' => $checkout['messages']['error'],
                ];

            elseif(false == $order['valid']) :

                $response = [
                    'valid' => false,
                    'messages' => $order['messages']['error'],
                ];

            else:

                $d_order = $order['order'];

                $messages = [sprintf( __('Order created successfully. Order ID #%s', 'sejoli'), $d_order['ID'] )];

                if(0 < count($order['messages']['warning'])) :
                    foreach($order['messages']['warning'] as $message) :
                        $messages[] = $message;
                    endforeach;
                endif;

                if(0 < count($order['messages']['info'])) :
                    foreach($order['messages']['info'] as $message) :
                        $messages[] = $message;
                    endforeach;
                endif;

                $fast_checkout = boolval( carbon_get_post_meta($request['product_id'], 'fast_checkout_option') );
        		
        		if( false !== $fast_checkout ) :

	                $response = [
	                    'valid'         => true,
	                    'messages'      => $messages,
	                    'redirect_link' => site_url('checkout/thank-you?order_id='.$d_order['ID']),
	                    'data' 			=> [
	                        'order' => $d_order
	                    ]
	                ];

	            else:

	            	$response = [
	                    'valid'         => true,
	                    'messages'      => $messages,
	                    'redirect_link' => site_url('checkout/loading?order_id='.$d_order['ID']),
	                    'data' 			=> [
	                        'order' => $d_order
	                    ]
	                ];

	           	endif;

                $fb_conversion_active           = boolval(sejolisa_carbon_get_post_meta($d_order['product_id'], 'fb_conversion_active'));
                $fb_eventString                 = esc_attr(sejolisa_carbon_get_post_meta($d_order['product_id'], 'fb_conversion_event_submit_checkout_button'));
                if(true === $fb_conversion_active && !empty($fb_eventString)) :
                	sejoli_facebook_tracker( $d_order, $fb_eventString );
                endif;

                $tiktok_conversion_active           = boolval(sejolisa_carbon_get_post_meta($d_order['product_id'], 'tiktok_conversion_active'));
                $tiktok_eventString                 = esc_attr(sejolisa_carbon_get_post_meta($d_order['product_id'], 'tiktok_conversion_event_submit_checkout_button'));
                if(true === $tiktok_conversion_active && !empty($tiktok_eventString)) :
                	sejoli_tiktok_tracker( $d_order, $tiktok_eventString );
                endif;

            endif;

            wp_send_json($response);

        endif;
    }

    /**
     * sejoli submit login by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function submit_login_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-submit-login' ) ) :

            $request = wp_parse_args($_POST,[
                'login_email'    => NULL,
                'login_password' => NULL,
            ]);

            $errors = [];

            if ( empty( $request['login_email'] ) ) :
                $errors[] = __('Alamat email wajib diisi');
            endif;

            if ( empty( $request['login_password'] ) ) :
                $errors[] = __('Password wajib diisi');
            endif;

            if ( empty( $errors ) ) :

                $credentials = array(
                    'user_login'    => $request['login_email'],
                    'user_password' => $request['login_password'],
                    'remember'      => 1,
                );

                $secure_cookie = apply_filters( 'secure_signon_cookie', '', $credentials );

                $user = wp_authenticate( $credentials['user_login'], $credentials['user_password'] );

                if ( !is_wp_error( $user ) ) :

                    wp_set_auth_cookie( $user->ID, $credentials['remember'], $secure_cookie );

                    wp_send_json_success(['Login success']);

                else:

                    $errors[] = __('Alamat Email atau Password salah');

                endif;

            endif;

            wp_send_json_error($errors);

        endif;
    }

    /**
     * sejoli get current user by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function get_current_user_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-get-current-user' ) ) :

            $request = wp_parse_args( $_POST, []);

            $response = [];

            $response['current_user'] = apply_filters('sejoli/frontend/checkout/current-user', [], $request );

            wp_send_json($response);

        endif;
    }

    /**
     * setup current user data
     * hooked via filter sejoli/frontend/checkout/current-user
     *
     * @return void
     */
    public function current_user( $current_user, $request )
    {

        $current_user = [];

        $user = wp_get_current_user();

        if ( $user->ID > 0 ) :

            if ( !empty( $user->first_name ) ) :
                $name = $user->first_name;
            else:
                $name = $user->display_name;
            endif;

            $address = $user->_address;

            $subdistrict = $user->_destination_name;
			$subdistrict_id = $user->_destination;

			if (!empty($subdistrict)) :
			    $district = sejolisa_get_district_options_by_ids($subdistrict); // pastikan ini array

			    if (!empty($district['results'])) :
			        // Cari yang cocok berdasarkan ID
			        foreach ($district['results'] as $item) {
			            if ((string)$item['id'] === (string)$subdistrict_id) {
			                $subdistrict = $item;
			                break;
			            }
			        }
			    endif;
			endif;
			
            $current_user = [
                'id'    => $user->ID,
                'name'  => $name,
                'email' => $user->user_email,
                'phone' => $user->_phone,
                'postal_code' => $user->_postal_code,
                'address' => $address,
                'subdistrict' => $subdistrict,
            ];

        endif;

        return $current_user;

    }

    /**
     * sejoli delete coupon by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function delete_coupon_by_ajax()
    {
		$request = NULL;

		// Ordinary order
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-delete-coupon' ) ) :

            $request = wp_parse_args( $_POST,[
                'product_id'      => 0,
                'coupon'          => NULL,
                'quantity'        => 1,
                'type'            => 'regular',
                'payment_gateway' => 'manual',
				'calculate'		  => 'default'
            ]);

		// Renew order
		elseif ( sejoli_ajax_verify_nonce( 'sejoli-checkout-renew-ajax-delete-coupon' ) ) :

            $request = wp_parse_args( $_POST,[
				'order_id'		  => 0,
                'product_id'      => 0,
                'coupon'          => NULL,
                'quantity'        => 1,
                'type'            => 'regular',
                'payment_gateway' => 'manual',
				'calculate'		  => 'renew'
            ]);

        endif;

		if(is_array($request)) :

			$request['coupon'] = NULL;

			if ( intval($request['product_id']) > 0 ) :

				do_action('sejoli/frontend/checkout/delete-coupon', $request);

				$response = sejolisa_get_respond('delete-coupon');

				wp_send_json($response);

			endif;

			$response = [
				'valid' => false,
				'messages' => [__('Hapus kupon gagal')]
			];

			wp_send_json($response);

		endif;
    }

    /**
     * sejoli delete coupon
     * hooked via action sejoli/frontend/checkout/delete-coupon
     *
     * @return json
     */
    public function delete_coupon( $request )
    {
		if(!isset($request['calculate']) || 'default' === $request['calculate']) :
        	do_action('sejoli/checkout/calculate', $request);
		else :
			do_action('sejoli/checkout/calculate-renew', $request);
		endif;

        $respond    	 = sejolisa_get_respond('total');
        $product      	 = sejolisa_get_product( $request['product_id'] );
		$quantity        = intval($respond['detail']['quantity']);
		$product_price   = isset($respond['cart_detail']['subscription']['regular']['raw']) ?
								$respond['cart_detail']['subscription']['regular']['raw'] : $product->price;
		$product_total_price = $quantity * $product_price;

		$product = sejolisa_get_product( $request['product_id'] );

		if($product->subscription['signup']['fee'] > 0 && 'renew' !== $request['calculate']):

			$setProduct_price = $product->price + $product->subscription['signup']['fee'];

		else:

			$setProduct_price = $product->price;

		endif;

		$product_format = sejolisa_carbon_get_post_meta( $product->ID, 'product_format' );
        $product_type   = sejolisa_carbon_get_post_meta( $product->ID, 'product_type' );

        if($product_type === "digital" && $product_format === "main-product") :

            foreach ($product->bump_product as $key => $id_bump_product) :

				$product_bump_sales = sejolisa_get_product( $id_bump_product );

				$biaya_awal_bump_product = floatval(sejolisa_carbon_get_post_meta($product_bump_sales->ID, 'subscription_signup_fee'));
				
				if($biaya_awal_bump_product > 0) :

					$set_bump_product_price = ($product_bump_sales->price + $biaya_awal_bump_product) - $setProduct_price;
						
					$bump_product_price = $set_bump_product_price;

				else:

					$set_bump_product_price = $product_bump_sales->price - $setProduct_price;

					$bump_product_price = $set_bump_product_price;

				endif;

        		$bump_product_total_price = floatval($quantity) * floatval($bump_product_price);

				$bump_sales_product[] = [
					'ID'      		  => $product_bump_sales->ID,
	                'image'   	      => get_the_post_thumbnail_url($product_bump_sales->ID,'full'),
	                'price'     	  => sejolisa_price_format( $bump_product_price ),
	                'subtotal'  	  => sejolisa_price_format( $bump_product_total_price ),
	                'enable_quantity' => sejolisa_carbon_get_post_meta( $product_bump_sales->ID, 'enable_quantity' ),
					'product' 		  => $product_bump_sales
				];

            endforeach;

        endif;

        if(isset($request['main_product_id'])):

            $main_bump_product = sejolisa_get_product( $request['main_product_id'] );
            if($product_type === "digital" && $product_format === "bump-product") :

	            foreach ($main_bump_product->bump_product as $key => $id_bump_product) :

		            	if($main_bump_product->subscription['signup']['fee'] > 0 && 'renew' !== $request['calculate']):
							$setProduct_price = $main_bump_product->price + $main_bump_product->subscription['signup']['fee'];
						else:
							$setProduct_price = $main_bump_product->price;
						endif;

						$product_bump_sales = sejolisa_get_product( $id_bump_product );

						$biaya_awal_bump_product = floatval(sejolisa_carbon_get_post_meta($product_bump_sales->ID, 'subscription_signup_fee'));
						
						if($biaya_awal_bump_product > 0) :

							$set_bump_product_price = ($product_bump_sales->price + $biaya_awal_bump_product) - $setProduct_price;
								
							$bump_product_price = $set_bump_product_price;
						
						else:

							$set_bump_product_price = $product_bump_sales->price - $setProduct_price;

							$bump_product_price = $set_bump_product_price;

						endif;

	            		$bump_product_total_price = floatval($quantity) * floatval($bump_product_price);

						$bump_sales_product[] = [
							'ID'      		  => $product_bump_sales->ID,
			                'image'   	      => get_the_post_thumbnail_url($product_bump_sales->ID,'full'),
			                'price'     	  => sejolisa_price_format( $bump_product_price ),
			                'subtotal'  	  => sejolisa_price_format( $bump_product_total_price ),
			                'enable_quantity' => sejolisa_carbon_get_post_meta( $product_bump_sales->ID, 'enable_quantity' ),
							'product' 		  => $product_bump_sales
						];

		            endforeach;

	            endif;

	        endif;

        $calculate = [
            'valid'    => true,
            'messages' => [__('Hapus kupon berhasil')],
            'data'     => [
                'product' => [
                    'id'    => $product->ID,
                    'image' => get_the_post_thumbnail_url($product->ID,'full'),
                    'title' => (!isset($request['calculate']) || 'default' === $request['calculate']) ?
								$product->post_title :
								sprintf( __('Perpanjangan order INV %s<br /> Produk: %s', 'sejoli'), $request['order_id'], $product->post_title),
                    'price' 	=> sejolisa_price_format( $product_price ),
					'quantity'  => $quantity,
					'subtotal'  => sejolisa_price_format( $product_total_price ),
					'bump_sales' => (isset($bump_sales_product) ? $bump_sales_product : '')
                ],
                'total' => sejolisa_coloring_unique_number( sejolisa_price_format( $respond['total'] ) )
            ]
        ];

		$calculate = $this->set_checkout_respond($respond, $calculate, $quantity, '', true);

        sejolisa_set_respond($calculate, 'delete-coupon');
    }

	/**
	 * Register custom query variables
	 * Hooked via filter parse_query, priority 999
	 * @since 	1.0.0
	 * @param  	array 	$vars [description]
	 * @return 	array
	 */
    public function custom_query_vars( $vars )
    {
        $vars[] = "sejolisa_checkout_page";
        $vars[] = "sejolisa_checkout_id";
		$vars[] = 'order_id';

        return $vars;
    }

	/**
	 * Check requested page
	 * Hooked via action parse_request, priority 999
	 * @since 	1.3.2
	 * @return 	void
	 */
	public function check_requested_page() {

		global $sejolisa;

		if( sejolisa_verify_checkout_page('loading') ) :

			$this->is_loading_page = true;
			$this->current_order   = $sejolisa['order'];

		elseif( sejolisa_verify_checkout_page( 'thank-you' ) ) :

			$this->is_thankyou_page = true;
			$this->current_order   = $sejolisa['order'];

		elseif( sejolisa_verify_checkout_page('renew') ) :

			$this->is_renew_order_page = true;
			$this->current_order   = $sejolisa['order'];
		endif;

		// __print_debug(array(
		// 	'loading'     => $this->is_loading_page,
		// 	'regular'     => $this->is_regular_page,
		// 	'renew_order' => $this->is_renew_order_page,
		// 	'order'		  => $this->current_order
		// ));
		// exit;
	}

	/**
	 * Check request template page
	 * Hooked via action template_redirect, priority 800
	 * @since 	1.3.2
	 * @return 	void
	 */
	public function check_requested_template() {

		global $sejolisa;

		if(
			$this->is_thankyou_page &&
			$this->current_order
		) :

			do_action('sejoli/thank-you/render', $this->current_order);

		endif;
	}

	/**
	 * Set template file based on request
	 * Hooked via filter template_include, priority 999
	 * @since 	1.3.2
	 * @since 	1.5.3 	Add expired renew template
	 * @param  	string 	$template_file
	 * @return 	string
	 */
	public function set_template_file($template_file) {

		global $post;

		if($this->is_loading_page) :

			$template_file = SEJOLISA_DIR . 'template/checkout/loading.php';

		elseif($this->is_thankyou_page) :

			$payment_gateway = $this->current_order['payment_gateway'];
			$grand_total = floatval($this->current_order['grand_total']);

			if(isset($_GET['order_id'])) {
	        	$respond = sejolisa_get_order([
					'ID' => $_GET['order_id']
				]);
	        	$product_id = $respond['orders']['product_id'];
	        } else {
	        	$product_id = $post->ID;
	        }

	        $checkout_design = isset($_GET['design']) ? $_GET['design'] : sejolisa_carbon_get_post_meta($product_id, 'checkout_design');

            $template_file = apply_filters('sejoli/checkout/design/thankyou', $template_file, $checkout_design, $payment_gateway, $grand_total, false);

		elseif($this->is_renew_order_page) :

			$max_renewal_day = absint( $this->current_order['product']->subscription['max_renewal'] );

			if(isset($_GET['order_id'])) {
	        	$respond = sejolisa_get_order([
					'ID' => $_GET['order_id']
				]);
	        	$product_id = $respond['orders']['product_id'];
	        } else {
	        	$product_id = $post->ID;
	        }

	        $design = sejolisa_carbon_get_post_meta($product_id, 'checkout_design');

			if( 0 < $max_renewal_day ) :

				$response = sejolisa_get_subscription_by_order( $this->current_order['ID'] );

				if( true === $response['valid'] ) :
					if ($max_renewal_day <= sejolisa_get_difference_day( strtotime( $response['subscription']->end_date) ) ) :
						return SEJOLISA_DIR . 'template/checkout/renew-closed.php';
					endif;
				endif;

			endif;

			$template_file = apply_filters('sejoli/checkout/design/thankyou', $template_file, $design, '', 0, true);

		endif;

		return $template_file;

	}

	/**
	 * Filter to add design thankyou template
	 * Hooked via filter sejoli/checkout/design/thankyou, priority 10, 3
	 * @since  1.1.7
	 * @param  array  $designs
	 * @return array
	 */
	public function sejoli_checkout_thankyou_filter($template_file, $design, $payment_gateway, $grand_total, $is_renew_order_page) {
	   
	    if (true === $is_renew_order_page) :

	        switch ($design) :
	            case 'version-2':
	                $template_file = SEJOLISA_DIR . 'template/checkout/v2/checkout-renew.php';
	                break;
	            case 'modern':
	            	$template_file = SEJOLISA_DIR . 'template/checkout/modern/checkout-renew.php';
	                break;
	            case 'compact':
	            	$template_file = SEJOLISA_DIR . 'template/checkout/compact/checkout-renew.php';
	                break;
	            case 'less':
	            	$template_file = SEJOLISA_DIR . 'template/checkout/less/checkout-renew.php';
	                break;
	            case 'smart':
	            	$template_file = SEJOLISA_DIR . 'template/checkout/smart/checkout-renew.php';
	                break;
	            default:
	            	$template_file = SEJOLISA_DIR . 'template/checkout/checkout-renew.php';
	                break;
	        endswitch;

	    else:

	        switch ($design) :
	            case 'version-2':
	                $template_file = ($payment_gateway == "cod")
	                    ? ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/v2/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/v2/thankyou-cod.php')
	                    : ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/v2/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/v2/thankyou.php');
	                break;
	            case 'modern':
	                $template_file = ($payment_gateway == "cod")
	                    ? ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/modern/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/modern/thankyou-cod.php')
	                    : ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/modern/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/modern/thankyou.php');
	                break;
	            case 'compact':
	                $template_file = ($payment_gateway == "cod")
	                    ? ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/compact/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/compact/thankyou-cod.php')
	                    : ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/compact/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/compact/thankyou.php');
	                break;
	            case 'less':
	                $template_file = ($payment_gateway == "cod")
	                    ? ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/less/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/less/thankyou-cod.php')
	                    : ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/less/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/less/thankyou.php');
	                break;
	            case 'smart':
	                $template_file = ($payment_gateway == "cod")
	                    ? ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/smart/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/smart/thankyou-cod.php')
	                    : ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/smart/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/smart/thankyou.php');
	                break;
	            default:
	                $template_file = ($payment_gateway == "cod")
	                    ? ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/thankyou-cod.php')
	                    : ($grand_total == 0 ? SEJOLISA_DIR . 'template/checkout/thankyou-free.php' : SEJOLISA_DIR . 'template/checkout/thankyou.php');
	                break;
	        endswitch;

	   	endif;
	   	
	    return $template_file;

	}

	/**
	 * Display renew checkout
	 * Hooked via action parse_request, priority 999
	 * @since 	1.1.9
	 * @return 	void
	 */
	public function setup_checkout_renew()
	{

		if(sejolisa_verify_checkout_page('renew') && isset($_GET['order_id'])) :

			global $sejolisa, $post;

			$order_id = intval($_GET['order_id']);

			if( !is_user_logged_in() ) :

				ob_start();

				sejoli_get_template_part('checkout/renew-login.php');

				$login_form = ob_get_contents();

				ob_end_clean();

				wp_die(
					sprintf(
						__('Anda harus login terlebih dahulu untuk melakukan pembaharuan langganan order %s. %s', 'sejoli'),
						$order_id,
						$login_form
					),
					__('Login terlebih dahulu', 'sejoli')
				);

			endif;

			$order_id 		 = intval($_GET['order_id']);
			$current_user_id = get_current_user_id();
			$response        = sejolisa_check_subscription($order_id);
			$subscription    = $response['subscription'];

			if(false === $response['valid'] || $current_user_id !== intval($subscription->user_id) ):

				wp_die(
					sprintf( __('Anda tidak bisa mengakses pembaharuan langganan order %s', 'sejoli'), $order_id),
					__('Tidak bisa melakukan pembaharuan langganan', 'sejoli')
				);

			else :

				$sejolisa['subscription'] = (array) $subscription;

			endif;

		endif;
	}

    /**
     * sejoli checkout loading by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function loading_by_ajax()
    {

        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-loading' ) ) :

            $request = wp_parse_args($_POST, [
                'order_id' => 0
            ]);

            if ( intval( $request['order_id'] ) > 0 ) :

                do_action('sejoli/frontend/checkout/loading', $request);

                $response = sejolisa_get_respond('loading');

            else:

                $response = [
                    'valid' => false
                ];

            endif;

            wp_send_json($response);

        endif;

    }

    /**
     * sejoli checkout loading
     * hooked via action sejoli/frontend/checkout/loading
     *
     * @return json
     */
    public function loading( $request )
    {

        $response = [
            'valid' => true,
            'redirect_link' => site_url('checkout/thank-you?order_id='.$request['order_id']),
        ];

        sejolisa_set_respond($response,'loading');

    }

    /**
     * sejoli get shipping methods by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function get_shipping_methods_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-get-shipping-methods' ) ) :

            $request = wp_parse_args( $_POST, [
                'product_id'    => NULL,
                'district_id'	=> NULL,
                'district_name'	=> NULL,
                'quantity'		=> 1,
                'shipment'      => NULL,
                'variants'      => [],
            ]);


            $response = [];

            $response = apply_filters('sejoli/frontend/checkout/shipping-methods', [], $request );

            wp_send_json($response);

        endif;
    }

    /**
     * setup shipping methods data
     * hooked via filter sejoli/frontend/checkout/shipping-methods
     *
     * @return void
     */
    public function shipping_methods( $shipping_methods, $request )
    {

        $shipping_details = [
			'shipping_methods'	 => [],
			'messages'			 => ''
		];

        do_action('sejoli/checkout/shipment-calculate', $request);


        $shipment_data = sejolisa_get_respond('shipment');

        if ( $shipment_data['valid'] &&
            !empty( $shipment_data['shipment'] ) ) :

            foreach ( $shipment_data['shipment'] as $key => $value ) :

                $shipping_details['shipping_methods'][] = [
                    'id' => $key,
                    'title' => $value,
                    'image' => '',
                ];

            endforeach;

			$shipping_details['messages'] = $shipment_data['messages'];

        endif;

        return $shipping_details;

    }

    /**
     * sejoli get subdistrict by ajax
     * hooked via action parse_request
     *
     * @return json
     */
    public function get_subdistrict_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-get-subdistrict' ) ) :

            $request = wp_parse_args( $_POST,[
                'term' => '',
            ]);

            $response = sejolisa_get_district_options( $request['term'] );

            wp_send_json($response);

        endif;
    }

	/**
	 * Trigger auto-fill coupon if there is coupon data in cookie
	 * Hooked via action wp_footer, priority 999
	 * @since 	1.5.1
	 * @return 	void
	 */
	public function trigger_coupon_fill() {

		global $post;

		if ( is_singular('sejoli-product') ) :

			$data = sejolisa_get_affiliate_cookie();

			if(
				(
					isset($data['coupon']) &&
					isset($data['coupon'][$post->ID])
				) ||
				( isset( $_GET['coupon'] ) )
			) :
				$user_coupon = isset( $_GET['coupon'] ) ? $_GET['coupon'] : $data['coupon'][$post->ID];
				require_once( plugin_dir_path( __FILE__ ) . 'partials/coupon/autofill.php' );
			endif;

		endif;

	}

}
