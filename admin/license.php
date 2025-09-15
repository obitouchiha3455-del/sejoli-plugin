<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class License {

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
	 * Order data
	 * @since 1.0.0
	 * @var   array
	 */
	protected $order = false;

	/**
	 * License valid
	 * @since 	1.0.0
	 * @var		boolean
	 */
	private $license_valid = true;

	/**
	 * License detail
	 * @since 	1.0.0
	 * @var		boolean
	 */
	private $license_detail = [];

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
	 * Setup license fields for product
	 * Hooked via filter sejoli/product/fields, priority 50
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_license_setting_fields(array $fields) {

        $conditionals = [
            'digital'  => [
                [
                    'field' => 'product_type',
                    'value' => 'digital'
                ],[
                    'field' => 'license_active',
                    'value' => true
                ]
            ]
        ];

		$fields[] = [
			'title'	=> __('Lisensi', 'sejoli'),
            'fields' =>  [
				Field::make( 'separator', 'sep_license' , __('Pengaturan Lisensi', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('license') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make('html',     'html_info_license')
                    ->set_html('<div class="sejoli-html-message info"><p>'. __('Pengaturan ini hanya akan muncul jika tipe produk adalah produk digital', 'sejoli') . '</p></div>'),

                Field::make('checkbox', 'license_active', __('Aktifkan pengaturan lisensi', 'sejoli'))
                    ->set_option_value('yes')
                    ->set_default_value(false)
                    ->set_conditional_logic([
                        [
                            'field' => 'product_type',
                            'value' => 'digital'
                        ]
                    ]),

				Field::make('html',     'html_info_license_created')
                    ->set_html('<div class="sejoli-html-message info"><p>'. __('Lisensi akan dibuat otomatis oleh sistem dan dikirimkan melalui email setelah order selesai', 'sejoli') . '</p></div>')
					->set_conditional_logic($conditionals['digital']),

				Field::make('text',		'license_count', __('Jumlah penggunaan lisensi', 'sejoli'))
					->set_attribute('type', 'number')
					->set_attribute('min', 0)
					->set_default_value(1)
					->set_required(true)
					->set_conditional_logic($conditionals['digital']),

				Field::make('html',     'html_info_license_active')
                    ->set_html('<div class="sejoli-html-message info"><p>'. __('Pengaturan jumlah pemakaian lisensi per kuantitas pembelian. <br /><br />Sebagai contoh jika per satu kuantitas pembelian, pembeli mendapatkan 10 lisensi, maka isian dibawah diisi dengan nilai 10. <br /><br />Jika pembeli membeli sebanyak 2, maka akan mendapatkan 20 lisensi. <br /><br />Isi dengan 0 jika lisensi tidak terbatas', 'sejoli') . '</p></div>')
					->set_conditional_logic($conditionals['digital']),
            ]
        ];

        return $fields;
    }

	/**
	 * Add license option to product
	 * Hooked via filter sejoli/product/meta-data, priority 999
     * @since 	1.0.0
	 * @param  	WP_Post 	$product
	 * @param  	int     	$product_id
	 * @return 	WP_Post
	 */
	public function setup_product_meta(\WP_Post $product, int $product_id) {

		$product_id = (0 === $product_id) ? $product->ID : $product_id;

		$product->license = [
			'active' => sejolisa_carbon_get_post_meta($product_id, 'license_active'),
			'count'  => sejolisa_carbon_get_post_meta($product_id, 'license_count'),
		];

		return $product;
	}

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['license'] = [
			'table' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-license-table'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-license-table')
			],
			'update' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-license-update'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-license-update')
			]
		];

		return $js_vars;
	}

	/**
	 * Check current license
	 * Hooked via action plugins_loaded
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function check_license() {

		global $sejolisa;

		$check = get_option('_sejoli_license_check');

		if(false === $check) :
			$this->license_valid = false;
			$this->license_detail = [
				'valid'	=> false,
				'messages'	=> [
					__('Kode lisensi belum dimasukkan', 'sejoli')
				]
			];
		else :
			$check 	= wp_parse_args($check, [
				'valid'    => false,
				'detail'   => [],
				'messages' => []
			]);

			$this->license_valid = $check['valid'];
			$this->license_detail = $check;
		endif;

		$sejolisa['license']	= [
			'valid'		=> $this->license_valid,
			'detail'	=> $this->license_detail
		];

	}

	/**
	 * Register routine
	 * Hooked via action admin_init, priority 1
	 * @since 	1.1.0
	 * @return 	void
	 */
	public function register_routine() {

		if(false === wp_next_scheduled('sejoli/license/berkah')) :
			wp_schedule_event(time() + 60,'twicedaily','sejoli/license/berkah');
		else :

			$recurring 	= wp_get_schedule('sejoli/license/berkah');

			if('twicedaily' !== $recurring) :
				wp_reschedule_event(time() + 60, 'twicedaily', 'sejoli/license/berkah');
			endif;
		endif;
	}


	/**
	 * Check license routine
	 * Hooked via action sejoli/license/berkah, priority 1
	 * @since 	1.1.0
	 * @since 	1.5.6	Modify host if $_SERVER['HTTP_HOST'] return empty
	 * @since 	1.6.4	Remove www. from domain checking
	 * @return 	void
	 */
	public function check_license_routine() {
		// DEMI ALLAH, SIAPAPUN YANG MENGAKALI LICENSE INI, SAYA TIDAK IKHLAS. REZEKI KELUARGA DAN ANAK SAYA ADA DISINI
		// SAYA HANYA MENDOAKAN SIAPAPUN YANG MENGAKALI LICENSE INI AGAR BERTAUBAT

		$host 	= $_SERVER['HTTP_HOST'];

		if( empty($host) ) :
			$host = safe_str_replace(array( 'https://', 'http://', 'www.' ), '', get_option('site_url'));
		endif;

		$post_data = [
			'host' => $host
		];

		$request_url   = add_query_arg($post_data, 'https://member.sejoli.co.id/sejoli-validate-license/');
		$response      = wp_remote_get($request_url);
		$json_result   = json_decode(wp_remote_retrieve_body($response), true);
		$response_code = (int) wp_remote_retrieve_response_code($response);

		if(200 === $response_code && isset($json_result['valid'])) :

			do_action('sejoli/log/write', 'checking-license', $response);

			if(true === boolval($json_result['valid'])) :

				// do nothing

			else :
				delete_option('_sejoli_license_check');
			endif;

		else :

			if( !is_wp_error($response)) :

				$message = $response['response']['message'];

			else :

				$message = $response->get_error_message();

			endif;

			do_action('sejoli/log/write', 'error-checking-license', array(
				'code'    => $response_code,
				'message' => $message
			));

		endif;
	}

	/**
	 * Display license message
	 * Hooked via action admin_notices, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_license_message() {

		if(false === sejolisa_check_own_license()) :
			global $sejolisa;

			$license_form_link = add_query_arg([
				'page' => 'sejoli-license-form'
			], admin_url('admin.php'));

			?>
		    <div class="notice notice-error">
				<h3>SEJOLI</h3>
		        <p><?php echo implode('<br />', $sejolisa['license']['detail']['messages']); ?></p>
				<p><?php _e('Gunakan produk yang sah agar anda bisa mendapatkan support dan update serta bisnis anda berkah', 'sejoli'); ?></p>
				<p><a href='<?php echo $license_form_link; ?>' class='button button-primary'><?php _e('Masukkan kode lisensi disini', 'sejoli'); ?></a></p>
		    </div>
		    <?php

		endif;

		if(isset($_GET['error']) && 'license-not-valid' === $_GET['error']) :
			?>
		    <div class="notice notice-error">
				<h3>SEJOLI - Lisensi Anda</h3>
		        <p><?php echo implode('<br />', array_map('urldecode', $_GET['messages'])); ?></p>
		    </div>
		    <?php
		endif;

		if(isset($_GET['success']) && 'license-valid' === $_GET['success']) :
			?>
		    <div class="notice notice-success">
				<h3>SEJOLI - Lisensi Anda</h3>
		        <p><?php _e('Alhamdulillah.. Sejoli sudah bisa anda gunakan. <br />Saya doakan semoga bisnis anda sukses selalu, membawa berkah dan kebaikan untuk keluarga anda. <br />Saya ucapkan banyak terima kasih karena telah membeli dengan sah karya kami. Sekali lagi.. terima kasih.'); ?></p>
		    </div>
		    <?php
		endif;
	}

	/**
	 * Display your license message
	 * Hooked via action admin_notices, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_your_license_message() {
		global $pagenow;

		if('admin.php' === $pagenow && isset($_GET['page']) && 'sejoli-your-license' === $_GET['page']) :
		?>
			<div class="sejoli-license-response notice" style='display:none'>

			</div>
		<?php
		endif;
	}

	/**
	 * Register license form
	 * Hooked via action admin_menu, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function register_license_form() {

		if(false === sejolisa_check_own_license()) :

			add_menu_page(
				__('License Form - Sejoli', 'sejoli'),
				__('Sejoli', 'sejoli'),
				'manage_options',
				'sejoli-license-form',
				[$this, 'display_license_form'],
				'',
				3
			);

		endif;
	}

	/**
     * Register license menu under sejoli main menu
     * Hooked via action admin_menu, priority 1005
     * @since 1.0.0
     * @return void
     */
    public function register_admin_menu() {

		add_submenu_page(
			'crb_carbon_fields_container_sejoli.php',
			__('Lisensi', 'sejoli'),
			__('Lisensi', 'sejoli'),
			'manage_sejoli_licenses',
			'sejoli-licenses',
			[$this, 'display_license_page']
		);
    }

	/**
     * Register your license menu under sejoli main menu
     * Hooked via action admin_menu, priority 999999
     * @since 	1.4.1
     * @return 	void
     */
    public function register_your_license_menu() {

		add_submenu_page(
			'crb_carbon_fields_container_sejoli.php',
			__('Lisensi Anda', 'sejoli'),
			__('Lisensi Anda', 'sejoli'),
			'manage_options',
			'sejoli-your-license',
			[$this, 'display_your_license_page']
		);
    }

	/**
	 * Check license code
	 * Hooked via action admin_init, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function check_license_form() {

		// DEMI ALLAH, SIAPAPUN YANG MENGAKALI LICENSE INI, SAYA TIDAK IKHLAS. REZEKI KELUARGA DAN ANAK SAYA ADA DISINI
		// SAYA HANYA MENDOAKAN SIAPAPUN YANG MENGAKALI LICENSE INI AGAR BERTAUBAT

		if(isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'sejoli-input-license') && isset($_POST['data'])) :

			$post_data = wp_parse_args($_POST['data'],[
 				'user_email' => NULL,
 				'license'    => NULL
 			]);

			$post_data['string'] = $_SERVER['HTTP_HOST'];
			$request_url         = 'https://member.sejoli.co.id/sejoli-license/';
			$response            = wp_remote_post($request_url, array(
									 'timeout' => 120,
									 'body'    => $post_data
								   ));

			if(is_wp_error($response)) :

				wp_die(
					__( 'Terjadi kesalahan yang disebabkan hosting anda. <br />Silahkan hubungi hosting anda dengan menyertakan pesan berikut ini :', 'sejoli') . '<br />&nbsp;<br />' .
					implode('<br />', $response->get_error_messages()),
					__( 'Tidak bisa mengakses server lisensi', 'sejoli')
				);

				exit;
			else :
				$json_result   = json_decode(wp_remote_retrieve_body($response), true);
				$response_code = intval(wp_remote_retrieve_response_code($response));

				if(200 === $response_code) :

					if(isset($json_result['valid']) && true === boolval($json_result['valid'])) :

						update_option('_sejoli_license_check', $json_result);

						$theme_option_url = add_query_arg([
							'page'		=> 'crb_carbon_fields_container_sejoli.php',
							'success'	=> 'license-valid'
						], admin_url('admin.php'));


						wp_redirect($theme_option_url);

					else :

						$args             = array();
						$args['page']     = 'sejoli-license-form';
						$args['error']	  = 'license-not-valid';
						$args['messages'] = array_map('urlencode', array_map('strip_tags', $json_result['messages']));

						wp_redirect(add_query_arg($args, admin_url('admin.php')));

					endif;

				// beside response code
				else :
					$args               = array();
					$args['page']       = 'sejoli-license-form';
					$args['error']      = 'license-not-valid';
					$args['messages'][] = sprintf( __('Error response code : %s. Tidak bisa menghubungi server lisensi', 'sejoli'), $response_code );

					wp_redirect(add_query_arg($args, admin_url('admin.php')));

				endif;
			endif;

			exit;
		endif;
	}

	/**
	 * Display license form
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_license_form() {

		if(false === sejolisa_check_own_license()) :
			require_once( plugin_dir_path( __FILE__ ) . 'partials/license-form.php' );
		endif;
	}

	/**
	 * Prepare to create license
	 * Hooked via action sejoli/order/set-status/completed
	 * @since 	1.0.0
	 * @param  	array  $order_data
	 * @return 	void
	 */
	public function prepare_to_create_license( array $order_data ) {

		// check if current order is renew so system don't need to create license
		if(!empty($order_data['order_parent_id'])) :

			$response = sejolisa_update_status_license_by_order('active', $order_data['order_parent_id']);
	
			return;
			
		endif;

		do_action('sejoli/license/create', $order_data['ID']);

	}

	/**
	 * Update license status inactive
	 * Hooked via sejoli/order/set-status/refunded, priority 200
	 * Hooked via sejoli/order/set-status/cancelled, priority 200
	 * Hooked via sejoli/order/set-status/on-hold, priority 200
	 * @param  array  	$order_data
	 */
	public function update_status_to_inactive( array $order_data ) {

		// check if current order is renew so system don't need to create license
		if(!empty($order_data['order_parent_id'])) :
			return;
		endif;

		$response = sejolisa_update_status_license_by_order('pending', $order_data['ID']);

	}

    /**
     * Display license page
     * @since 1.0.0
     */
    public function display_license_page() {
        require plugin_dir_path( __FILE__ ) . 'partials/license/page.php';
    }

	/**
	 * Generate license code
	 * Hooked via filter sejoli/license/code priority 1
	 * @since 	1.0.0
	 * @param  	string 	$license_code
	 * @param  	array  	$order
	 * @return 	string
	 */
	public function generate_license_code($license_code, array $order) {

		$num_segments  = 4;
		$segment_chars = 5;
		$tokens        = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	    for ($i = 0; $i < $num_segments; $i++) :

	        $segment = '';

	        for ($j = 0; $j < $segment_chars; $j++) :
				$segment .= $tokens[rand(0, strlen($tokens) - 10)];
	        endfor;

	        $license_code .= $segment;

	        if ($i < ($num_segments - 1)) :
				$license_code .= '-';
	        endif;

	    endfor;

		$license_code = str_pad($order['product_id'].$order['ID'].$order['user_id'], 10, '0', STR_PAD_LEFT).'-'.$license_code;

	    return $license_code;
	}

	/**
	 * Create license based on order id
	 * Hooked via action sejoli/license/create, priority 999
	 * @since 	1.0.0
	 * @param  	integer 		$order_id
	 * @param  	null|WP_Post 	$product
	 * @return 	void
	 */
	public function create_license( $order, $product = NULL ) {

		// validate order
		if( !is_array( $order ) ) :
			$respond = sejolisa_get_order([
				'ID' => intval( $order )
			]);
		else :
			$respond = [
				'valid'  => true,
				'orders' => $order
			];
		endif;

		if(false !== $respond['valid']) :
			$order = $respond['orders'];;

			// get product data
			if( !is_a($product, 'WP_Post') || !isset( $product->license['active'] ) || false !== $product->license['active'] ) :
				$product = sejolisa_get_product( $order['product_id'] );
			endif;

			if( false !== $product->license['active'] ) :

				// validate license
				$respond = sejolisa_get_license_by_order($order['ID']);

				// license not generated then create new one
				if( false === $respond['valid'] ) :

					$order_quantity = intval( $order['quantity'] );

					for ( $i = 1; $i <= $order_quantity; $i++ ) {
						
						$license_code = apply_filters('sejoli/license/code', '', $order);

						$args = [
							'order_id'   => $order['ID'],
							'user_id'    => $order['user_id'],
							'product_id' => $order['product_id'],
							'code'       => $license_code,
							'status'     => 'active'
						];

						$respond = sejolisa_add_license($args);

						sejolisa_set_respond( $respond, 'license' );
						
					}

				// license is already set
				else :

					$license = $respond['licenses'];

					sejolisa_update_status_license_by_order( 'active', $license['order_id'] );

					sejolisa_set_respond([
						'valid'    => false,
						'license'  => $license,
						'messages' => [
							'error'	=> [
								sprintf(__('License for order %s is already created. The code is %s', 'sejoli'), $license['order_id'], $license['code'])
							]
						]
					],'license');

				endif;

			endif;

		endif;

	}

	/**
	 * Get license quantity
	 * Hooked via filter sejoli/license/quantity
	 * @since  1.0.0
	 * @param  integer $quantity [description]
	 * @param  integer $args     [description]
	 * @return integer
	 */
	public function get_license_quantity($quantity = 0, $order_id = 0) {

		if(false === $this->order) :
			$response = $response = sejolisa_get_order([ 'ID' => $order_id ]);

			if(false !== $response['valid']) :
				$this->order = $response['orders'];
			endif;
		endif;

		if(false !== $this->order) :
			if(false !== boolval($this->order['product']->license['active']) ) :
				$order_quantity = intval($this->order['quantity']);
				$product_license_quantity = intval($this->order['product']->license['count']);
				return $order_quantity * $product_license_quantity;

			endif;
		endif;

		return $quantity;
	}

	/**
	 * Get all strings
	 * @since 	1.0.0
	 * @param  	array  $license [description]
	 * @return 	array
	 */
	protected function arrange_licenses_by_string(array $licenses) {
		$strings = [];

		foreach($licenses as $license) :
			if(!empty($license->string)) :
				$strings[]	= $license->string;
			endif;
		endforeach;

		return $strings;
	}

	/**
	 * Check license availbility
	 * Hooked via filter sejoli/license/availability, priority 1
	 * @since 	1.0.0
	 * @since 	1.3.3 	Fixing bug with license checking
	 * @param  	array 	$check
	 * @param  	array   $post_data 	 * @return 	boolean
	 */
	public function get_license_availability($check = [], $post_data = array()) {

		$valid = true;
		$check = [
			'valid'    => false,
			'detail'   => [],
			'messages' => []
		];

		$post_data = array_map('trim', wp_parse_args($post_data,[
			'user_email' => NULL,
			'user_pass'  => NULL,
			'license'    => NULL,
			'string'     => NULL
		]));

		$user = wp_authenticate($post_data['user_email'], $post_data['user_pass']);

		// Check user authentication
		if(is_wp_error($user)) :
			$valid = false;
			$check['messages']	= $user->get_error_messages();
		endif;

		// Check license match
		if($valid) :
			$response = sejolisa_get_license_by_code($post_data['license']);

			if(false === $response['valid']) :
				$valid = false;
				$check['messages'][]	= sprintf( __('License code %s not found' ,'sejoli'), $post_data['license']);
			else :
				$licenses = $response['licenses'];
			endif;
		endif;

		// Check order data
		if($valid) :
			$first_license = $licenses[0];

			$checkOrderParent = sejolisa_get_renewall_order($first_license->order_id, $user->ID);

			if( $checkOrderParent > 0 ) :
				$response = sejolisa_get_order([ 'ID' => $checkOrderParent ]);
			else:
				$response = sejolisa_get_order([ 'ID' => $first_license->order_id ]);
			endif;

			if(false === $response['valid']) :
				$valid = false;
				$check['messages'][] = sprintf( __('Invalid order data by license. Please contact your vendor. The order ID is %s', 'sejoli'), $first_license->order_id );
			else :
				$this->order = $response['orders'];
			endif;
		endif;

		// Check order status
		if($valid) :
			if('completed' !== $this->order['status']) :
				$valid = false;
				$check['messages'][] = sprintf( __("Order ID %s is not completed yet. You can't use the license", 'sejoli'), $first_license->ID );
			endif;
		endif;

		// Check if current user is the right owner of the license
		if($valid && $user->ID !== intval($first_license->user_id)) :
			$valid = false;
			$check['messages'][] = __("You don\'t have any permission to use this license", 'sejoli');
		endif;

		if($valid && false === sejolisa_subscription_is_active($this->order)) :
			$valid = false;
			$check['messages'][] = __("Your subscription is not active. Please renew", 'sejoli');
		endif;

		// GOOD! everything seems ok
		if($valid) :

			$max_license = apply_filters('sejoli/license/quantity', 0, [
				'order_id'   => $first_license->order_id
			]);

			$all_strings = $this->arrange_licenses_by_string($licenses);

			// String is already registered
			if(in_array($post_data['string'], $all_strings)) :

				$check['valid'] = true;
				$check['messages'][] = sprintf(
											__('License code %s is already registered to %s. Your remaining license is %s', 'sejoli'),
											$post_data['license'],
											$post_data['string'],
											(0 === $max_license) ? __('Unlimited', 'sejoli') : ($max_license - count($licenses))
										);

			// The string is not registered and only one license data
			// we assume that this is first time the license is  activated
			elseif(1 === count($licenses) && empty($first_license->string) ) :

				$response = sejolisa_update_string_license([
					'ID'     => $licenses[0]->ID,
					'string' => $post_data['string']
				]);

				$check['valid'] = true;
				$check['product'] = array(
					'order-id'   => $this->order['ID'],
					'product-id' => $this->order['product']->ID,
					'post-name'  => $this->order['product']->post_name,
					'post-title' => $this->order['product']->post_title
				);

				$check['messages'][] = sprintf(
											__('License code %s is registered to %s. Your remaining license is %s', 'sejoli'),
											$post_data['license'],
											$post_data['string'],
											(0 === $max_license) ? __('Unlimited', 'sejoli') : ($max_license - count($licenses))
										);

			// check if thee is still available license
			elseif(0 !== $max_license && $max_license <= count($all_strings)) :

				$check['messages'][] = __('Sorry, you have used all available licenses', 'sejoli');

			else :

				$add_license = true;

				// Check available license with empty string
				foreach($licenses as $license) :

					if(empty($license->string)) :

						$register_response = sejolisa_update_string_license([
							'ID'     => $license->ID,
							'string' => $post_data['string']
						]);

						$add_license = false;
						$type = 'UPDATE';
						$remaining_licenses = (0 === $max_license) ? __('Unlimited', 'sejoli') : ($max_license - count($licenses));
						break;

					endif;

				endforeach;

				// Add new license
				if($add_license) :
					$register_response = sejolisa_add_license([
						'order_id'   => $first_license->order_id,
						'user_id'    => $first_license->user_id,
						'product_id' => $first_license->product_id,
						'code'       => $first_license->code,
						'string'	=> $post_data['string']
					]);
					$type = 'ADD';
					$remaining_licenses = (0 === $max_license) ? __('Unlimited', 'sejoli') : ($max_license - count($licenses) - 1);
				endif;

				if(false !== $register_response['valid']) :

					$check['valid']  = true;
					$check['detail'] = array(
						'order-id'   => $this->order['ID'],
						'product-id' => $this->order['product']->ID,
						'post-name'  => $this->order['product']->post_name,
						'post-title' => $this->order['product']->post_title
					);

					$check['messages'][] = sprintf(
												__('License code %s is registered to %s [%s]. Your remaining license is %s', 'sejoli'),
												$post_data['license'],
												$post_data['string'],
												$type,
												$remaining_licenses
											);

				else :
					$check['messages'][]	= sprintf( __('Something wrong happened when register your license code %s with string %s for order ID %s. Please contact your vendor', 'sejoli'),
						$post_data['license'],
						$post_data['string'],
						$first_license->order_id
					);
				endif;

			endif;

		endif;

		return $check;
	}

	/**
	 * Display license code
	 * Hooked via sejoli/notification/content/order-meta
	 * @param  string $content      	[description]
	 * @param  string $media        	[description]
	 * @param  string $recipient_type   [description]
	 * @param  array  $invoice_data 	[description]
	 * @return string
	 */
	public function display_license_code(string $content, string $media, $recipient_type, array $invoice_data) {
		$order_data_status = isset($invoice_data['order_data']['status']) ? $invoice_data['order_data']['status'] : '';
		if(
			'completed' === $order_data_status &&
			in_array($recipient_type, ['buyer', 'admin']) &&
			false !== $invoice_data['product_data']->license['active']
		) :
			$respond = sejolisa_get_license_by_order($invoice_data['order_data']['ID']);
			if(false !== $respond['valid']) :

				$license = $respond['licenses'];
				$content .= sejoli_get_notification_content(
								'license',
								$media,
								array(
									'license' => [
										'code' => $license['code']
									]
								)
							);

			endif;
		endif;

		return $content;
	}

	/**
	 * Request to validate sejoli license
	 * Hooked via action wp_ajax_sejoli-validate-license
	 * @since 	1.4.1
	 * @return 	void	Echo json response
	 */
	public function validate_sejoli_license() {

		$response = array(
			'valid'   => false,
			'message' => __('Terjadi kesalahan di sistem', 'sejoli')
		);

		$post = wp_parse_args($_POST, array(
					'noncekey'	=> NULL,
					'data'		=> array()
				));


		if(wp_verify_nonce($post['noncekey'], 'sejoli-validate-license')) :

			$data = wp_parse_args($post['data'], array(
						'user_email'	=> NULL,
						'user_pass'		=> NULL,
						'license'		=> NULL,
					));

			$link = add_query_arg(array(
						'license'	=> $data['license'],
						'string'	=> $_SERVER['HTTP_HOST']
					), 'https://member.sejoli.co.id/sejoli-validate-license');


			$response = wp_remote_get($link);
			$response = json_decode(wp_remote_retrieve_body($response), true);

			if(false !== $response['valid']) :
				$response['message'] = __('<p>Lisensi ditemukan dan valid untuk instalasi ini.</p> <p>Anda bisa melakukan reset lisensi</p>', 'sejoli');
			endif;

		endif;

		wp_send_json($response);

		exit;
	}

	/**
	 * Request to reset sejoli license
	 * Hooked via action wp_ajax_sejoli-reset-license
	 * @since 	1.4.1
	 * @return 	void	Echo json response
	 */
	public function reset_sejoli_license() {

		$response = array(
			'valid'   => false,
			'message' => __('Terjadi kesalahan di sistem', 'sejoli')
		);

		$post = wp_parse_args($_POST, array(
					'noncekey'	=> NULL,
					'data'		=> array()
				));

		if(wp_verify_nonce($post['noncekey'], 'sejoli-validate-license')) :

			$data = wp_parse_args($post['data'], array(
						'user_email'	=> NULL,
						'user_pass'		=> NULL,
						'license'		=> NULL,
						'string'		=> $_SERVER['HTTP_HOST']
					));

			$curl_response = wp_remote_post('https://member.sejoli.co.id/sejoli-delete-license', array(
							'body'	=> $data
					    ));

			$response    = json_decode(wp_remote_retrieve_body($curl_response), true);
			$plugin_file = basename( dirname( dirname( __FILE__ ))) . '/sejoli.php';

			if(false !== $response['valid']) :

				global $wpdb;

				delete_option('_sejoli_license_check');

				$tables = array(
					'acquisition',
					'acquisition_order',
					'affiliates',
					'bca_transaction',
					'bni_transaction',
					'bri_transaction',
					'confirmations',
					'coupons',
					'duitku_transaction',
					'licenses',
					'mandiri_transaction',
					'manual_transaction',
					'moota_transaction',
					'orders',
					'reminders',
					'reward_points',
					'subscriptions',
					'wallet'
				);

				foreach($tables as $table) :
					$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sejolisa_' . $table);
				endforeach;

				deactivate_plugins( array($plugin_file), true);

			endif;

			$response['message'] = '<p>' . $response['message'] . '</p>';

		endif;

		wp_send_json($response);

		exit;
	}

	/**
	 * Display your license page
	 * @since 	1.4.1
	 * @return 	void
	 */
	public function display_your_license_page() {
		require plugin_dir_path( __FILE__ ) . 'partials/license/your-license-page.php';
	}
}