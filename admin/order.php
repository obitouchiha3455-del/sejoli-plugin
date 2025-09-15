<?php
namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Order {

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
	 * All available order statuses
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array 	   $status 		Order status
	 */
	protected $status = [];

	/**
	* Current order user_id
	* @since 	1.0.0
	* @access 	protected
	* @var		null|integer
	*/
   protected $user_id = NULL;

	 /**
 	 * Current order coupon_id
 	 * @since 	1.0.0
 	 * @access 	protected
 	 * @var		null|integer
 	 */
	protected $coupon_id = NULL;

	/**
	 * Current order affiliate_id
	 * @since 	1.0.0
	 * @access 	protected
	 * @var		null|integer
	 */
	protected $affiliate_id = NULL;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register cron jobs
	 * Hooked via action admin_init, priority 100
	 * @since 	1.4.1
	 * @return 	void
	 */
	public function register_cron_jobs() {

		// delete coupon post
		if(false === wp_next_scheduled('sejoli/order/cancel-incomplete-order')) :

			wp_schedule_event(time(), 'quarterdaily', 'sejoli/order/cancel-incomplete-order');

		else :

			$recurring 	= wp_get_schedule('sejoli/order/cancel-incomplete-order');

			if('quarterdaily' !== $recurring) :
				wp_reschedule_event(time(), 'quarterdaily', 'sejoli/order/cancel-incomplete-order');
			endif;

		endif;

	}

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {
		$js_vars['order'] = [
			'table' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-order-table'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-order-table')
			],
			'chart' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-order-chart'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-order-chart')
			],
			'update' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-order-update'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-order-update')
			],
			'shipping' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-order-shipping'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-order-shipping')
			],
			'input_resi' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-order-input-resi'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-order-input-resi')
			],
			'detail' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-order-detail'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-order-detail')
			],
			'export_prepare'   =>  [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-order-export-prepare'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-order-export-prepare')
			],
			'status'   => apply_filters('sejoli/order/status', [])
		];

		return $js_vars;
	}

	/**
	 * Get available order status
	 * Hooked via filter sejoli/order/status, priority 1
	 * @since  1.0.0
	 * @param  array  $status
	 * @return array
	 */
	public function get_status( $status = [] ) {

		$this->status      = [
            'on-hold'     	  => __('Menunggu pembayaran', 'sejoli'),
			'payment-confirm' => __('Pembayaran dikonfirmasi', 'sejoli'),
            'in-progress' 	  => __('Pesanan diproses', 'sejoli'),
            'shipping'    	  => __('Proses pengiriman', 'sejoli'),
            'completed'   	  => __('Selesai', 'sejoli'),
			'refunded'    	  => __('Refund', 'sejoli'),
			'cancelled'   	  => __('Batal', 'sejoli')
        ];

		return $this->status;

	}

	/**
	 * Calculate order total
	 * @param  float  $total
	 * @param  array  $order_data
	 * @return float
	 */
	public function calculate_total($total = 0.0, $order_data = array()) {
		$product_id    = $order_data['product_id'];
		$product_price = apply_filters('sejoli/product/price', 0, get_post($product_id));
		$quantity      = absint($order_data['quantity']);
		$quantity      = (0 < $quantity) ? $quantity : 1;

		return floatval($product_price * $quantity);
	}

	/**
	 * Set order user_id
	 * Hooked via action sejoli/order/set-user, priority 999
	 * @since 	1.0.0
	 * @param 	$user_id
	 */
	public function set_user($user_id) {
		$this->user_id = intval($user_id);
	}

	/**
	 * Set order coupon_id
	 * Hooked via action sejoli/order/set-coupon, priority 999
	 * @since 	1.0.0
	 * @param 	$user_id
	 */
	public function set_coupon($coupon_id) {
		$this->coupon_id = intval($coupon_id);
	}

	/**
	 * Set order affiliate id
	 * Hooked via action sejoli/order/set-affiliate, priority 999
	 * @since 	1.0.0
	 * @param 	1.5.2	Add $post_data paramater and check if current product is able to be affiliated
	 * @param 	1.5.3	Add conditional check if the order is able to be affiliated
	 * @return 	void
	 */
	public function set_affiliate( array $post_data ) {

		$affiliate_data         = sejolisa_get_affiliate_checkout();
		$is_affiliate_permanent = boolval( sejolisa_carbon_get_theme_option('sejoli_permanent_affiliate') );
		$enable_affiliate 		= boolval( sejolisa_carbon_get_post_meta( intval($post_data['product_id']), 'sejoli_enable_affiliate') );
		
		if( true !== $enable_affiliate ) :
			$this->affiliate_id = 0;
		elseif(empty($affiliate_data['user_meta']) && false !== $is_affiliate_permanent) :
			update_user_meta($post_data['user_id'], '_affiliate_id', intval($affiliate_data['link']));
			$affiliate_data['user_meta'] = $affiliate_data['link'];
			$this->affiliate_id = $affiliate_data['link'];
		elseif(!empty($affiliate_data['user_meta']) && false !== $is_affiliate_permanent) :
			$this->affiliate_id = $affiliate_data['user_meta'];
		elseif(!empty($affiliate_data['coupon'])) :
			$this->affiliate_id = $affiliate_data['coupon'];
		elseif(!empty($affiliate_data['link'])) :
			$this->affiliate_id = $affiliate_data['link'];
		endif;

		// Same user and affiliate ID value
		if(
			is_user_logged_in() &&
			get_current_user_id() === intval($this->affiliate_id)) :

			$this->affiliate_id = 0;

		/**
		 * @since 	1.5.3
		 */
		elseif(
			0 < $this->affiliate_id &&
			true !== sejolisa_user_can_affiliate_the_product( intval($post_data['product_id']), $this->affiliate_id )
		) :
			$this->affiliate_id = 0;
		endif;

	}

    /**
     * Order created
     * Hooked via action sejoli/order/create, priority 999
     * @since  1.0.0
     * @param  array $order_data
     * @return void
     */
    public function create(array $order_data) {

		$order_data = wp_parse_args($order_data, [
            'product_id'      => NULL,
            'user_id'         => $this->user_id,
            'affiliate_id'    => $this->affiliate_id,
            'coupon_id'       => $this->coupon_id,
			'coupon'		  => NULL,
            'quantity'        => 1,
            'status'          => 'on-hold',
            'payment_gateway' => 'manual',
			'type'			  => 'regular',
			'meta_data'		  => []
        ]);

        if( intval($order_data['user_id']) === intval($order_data['affiliate_id']) ):

			$order_data['affiliate_id'] = 0;

		endif;

		// Order type checking must be first before set grand total
		$order_data['type']	= apply_filters('sejoli/order/type', 'regular', $order_data);

		$payment_module = apply_filters('sejoli/payment/module', $order_data['payment_gateway']);
		if($payment_module == "cod") {
			$order_data['status'] = 'in-progress';
		} else {
			$order_data['status'] = 'on-hold';
		}

		// calculate grand total
		if(!isset($order_data['grand_total'])) :

			$order_data['grand_total'] = apply_filters('sejoli/order/grand-total', 0, $order_data);

		endif;

		// if grand total is 0, then status will be in-progress or completed
		if(0.0 === floatval($order_data['grand_total'])) :
			$product = sejolisa_get_product($order_data['product_id']);
			// if product is need of shipment
			if((isset($product->cod) && false !== $product->cod['cod-active'])) :
				$order_data['status'] = 'in-progress';
			else :
				$order_data['status'] = 'completed';
			endif;

			if(false !== $product->shipping['active']) :
				$order_data['status'] = 'in-progress';
			else :
				$order_data['status'] = 'completed';
			endif;
		endif;

		//set meta data value
		$order_data['meta_data'] = apply_filters('sejoli/order/meta-data', $order_data['meta_data'], $order_data);
		
		// Recalculate grand total based on payment
		$order_data['grand_total'] = apply_filters('sejoli/recalculate/grand-total', $order_data['grand_total'], $order_data);

		$respond = sejolisa_create_order($order_data);

		$respond['messages']['info']    = sejolisa_get_messages('info');
		$respond['messages']['warning'] = sejolisa_get_messages('warning');

		sejolisa_set_respond($respond, 'order');

		if(false !== $respond['valid']) :

			do_action('sejoli/log/write', 'order created', $order_data);

			if(!empty($order_data['coupon'])) :
				do_action('sejoli/coupon/update-usage', $order_data['coupon']);
			endif;

			$order_data = $respond['order'];

			do_action('sejoli/order/new', $order_data);
			do_action('sejoli/order/set-status/'.$order_data['status'], $order_data);

		endif;
    }

	/**
	 * Create renew order
	 * Hooked via action sejoli/order/renew, priority 999
	 * @since 	1.0.0
	 * @param  	array  $order_data
	 * @return 	void
	 */
	public function renew(array $order_data) {
		$order_data = wp_parse_args($order_data, [
			'order_parent_id' => NULL,
            'product_id'      => NULL,
            'user_id'         => get_current_user_id(),
            'affiliate_id'    => $this->affiliate_id,
            'coupon_id'       => $this->coupon_id,
            'coupon'		  => NULL,
            'quantity'        => 1,
            'status'          => 'on-hold',
            'payment_gateway' => 'manual',
			'type'			  => 'subscription-regular',
            'total'           => NULL,
			'meta_data'		  => []
        ]);    

		// Order type checking must be first before set grand total
		$order_data['type']	= apply_filters('sejoli/order/type', 'subscription-regular', $order_data);

		// calculate grand total
		if(!isset($order_data['grand_total'])) :
			$order_data['grand_total'] = apply_filters('sejoli/order/grand-total', 0, $order_data);
		endif;

		// if grand total is 0, then status will be in-progress or completed
		if(0.0 === floatval($order_data['grand_total'])) :
			$order_data['status'] = 'completed';
		endif;

		// if grand total is 0, then status will be in-progress or completed
		// if(0.0 === floatval($order_data['grand_total'])) :
		// 	$product = sejolisa_get_product($order_data['product_id']);
		// 	// if product is need of shipment
		// 	if(false !== $product->shipping['active']) :
		// 		$order_data['status'] = 'in-progress';
		// 	else :
		// 		$order_data['status'] = 'completed';
		// 	endif;
		// endif;

		//set meta data value
		$order_data['meta_data'] = apply_filters('sejoli/order/meta-data', $order_data['meta_data'], $order_data);

		if(isset($order_data['meta_data']['wallet'])):
			$order_data['meta_data']['wallet'] = $order_data['meta_data']['wallet'] - $order_data['meta_data']['coupon']['discount'];
		endif;

		// Recalculate grand total based on payment
		$order_data['grand_total'] = apply_filters('sejoli/recalculate/grand-total', $order_data['grand_total'], $order_data);

		$respond 						= sejolisa_create_order($order_data);
		$respond['messages']['info']    = sejolisa_get_messages('info');
		$respond['messages']['warning'] = sejolisa_get_messages('warning');

		sejolisa_set_respond($respond, 'order');

		if(false !== $respond['valid']) :

			$order_data = $respond['order'];
			do_action('sejoli/order/new', 		 $order_data);
			do_action('sejoli/order/set-status/'.$order_data['status'], $order_data);

		endif;
	}

	/**
	 * Set order meta data
	 * Hooked via filter sejoli/order/meta-data, priority 100
	 * @param array $meta_data
	 * @param array $order_data
	 */
	public function set_status_log_meta_data(array $meta_data, array $order_data) {

		global $current_user; wp_get_current_user();

		$meta_data['status_log'] = array(
			'order_id'    => '',
			'update_date' => '',
			'old_status'  => '',
			'new_status'  => '',
			'updated_by'  => $current_user->display_name
		);

        return $meta_data;

	}

    /**
     * Update status order
     * Hooked via action sejoli/order/update-status, priorirty
     * @since  1.0.0
     * @param  array  $args
     * @return void
     */
    public function update_status(array $args) {

		$args = wp_parse_args($args, [
	        'ID'     => NULL,
	        'status' => NULL
	    ]);

	    $respond = sejolisa_get_order([
	        'ID' => $args['ID']
	    ]);

		if(false !== $respond['valid'] && isset($respond['orders']) && isset($respond['orders']['ID'])) :

			$order       = $respond['orders'];
			$prev_status = $order['status'];
			$new_status  = $args['status'];

			if($prev_status === $new_status) :
				sejolisa_set_respond([
						'valid' => false,
						'order' => $order,
						'messages' => [
							'error' => [
								sprintf(__('Can\'t update since current order status and given status are same. The status is %s', 'sejoli'), $new_status)
							]
						]
					],
					'order'
				);
				return;
			endif;

			// We need this hook later to validate if we can allow moving status to another
			// For example, we prevent moving order with status completed to on-hold
			$allow_update_status = apply_filters('sejoli/order/allow-update-status',
				true,
				[
					'prev_status' => $prev_status,
					'new_status'  => $new_status
				],
				$order);

			// is allowed
			if(true === $allow_update_status) :

				do_action('sejoli/order/update-status-from/'. sanitize_title($order['status']), $new_status, $order);

				$respond = sejolisa_update_order_status($args);

				if(false !== $respond['valid']) :

					$order['status'] = $new_status;

					do_action('sejoli/order/status-updated', 	$order);
					do_action('sejoli/order/set-status/'.sanitize_title($new_status), $order);

				endif;

				$respond['messages']['success'][0] = sprintf(__('Order ID #%s updated from %s to %s', 'sejoli'), $order['ID'], $prev_status, $new_status);

				sejolisa_set_respond($respond, 'order');

				global $current_user; wp_get_current_user();

				$num = mt_rand();
				sejolisa_update_order_meta_data($order['ID'], array(
	                'status_log' => array(
	                    $num => array(
	                    	'order_id'    => $order['ID'],
	                    	'update_date' => current_time( 'd-m-Y H:i:s' ),
	                    	'old_status'  => $prev_status,
	                        'new_status'  => $new_status,
	                        'updated_by'  => $current_user->display_name
	                    )
	                )
	            ));

	            if( $new_status === "completed" ) :
				
					$fb_conversion_active = boolval(sejolisa_carbon_get_post_meta($order['product_id'], 'fb_conversion_active'));
		            $fb_eventString       = esc_attr(sejolisa_carbon_get_post_meta($order['product_id'], 'fb_conversion_event_change_order_status_page'));
	                if(true === $fb_conversion_active && !empty($fb_eventString)) :
	                	sejoli_facebook_tracker( $order, $fb_eventString );
	                endif;

	                $tiktok_conversion_active = boolval(sejolisa_carbon_get_post_meta($order['product_id'], 'tiktok_conversion_active'));
		            $tiktok_eventString       = esc_attr(sejolisa_carbon_get_post_meta($order['product_id'], 'tiktok_conversion_event_change_order_status_page'));
	                if(true === $tiktok_conversion_active && !empty($tiktok_eventString)) :
	                	sejoli_tiktok_tracker( $order, $tiktok_eventString );
	                endif;

	            endif;

			else :
				sejolisa_set_respond([
						'valid' => false,
						'order' => $order,
						'messages' => [
							'error' => [
								sprintf(__('Updating order status from %s to %s is not allowed', 'sejoli'), $prev_status, $new_status)
							]
						]
					],
					'order'
				);
			endif;
		else :
			sejolisa_set_respond($respond, 'order');
		endif;
    }

    /**
     * Delete order
     * Hooked via action sejoli/order/delete
     * @since  1.0.0
     * @param  int|Sejoli_Order $order
     * @return
     */
    public function delete($order) {

    }

	/**
	 * Update order status by ajax
	 * Hooked via action wp_ajax_sejoli-order-update, priority 1
	 * @return json
	 */
	public function update_status_by_ajax() {

		$response = [];

		if(wp_verify_nonce($_POST['nonce'], 'sejoli-order-update')) :

			$post = wp_parse_args($_POST,[
				'orders' => NULL,
				'status' => 'on-hold'
			]);

			if(is_array($post['orders']) && 0 < count($post['orders'])) :

				if(!in_array($post['status'], ['delete', 'resend'])) :
					$numbers = range(1, 20);
					foreach($post['orders'] as $order_id) :
						do_action('sejoli/order/update-status', [
							'ID'     => $order_id,
							'status' => $post['status']
						]);

						$get_response = sejolisa_get_order([ 'ID' => $order_id]);
						$order = $get_response['orders'];

						$response[] = sprintf( __('Order %s updated to %s', 'sejoli'), $order_id, $post['status']);
					endforeach;

				elseif('resend' === $post['status'] ) :

					foreach($post['orders'] as $order_id) :

						$get_response = sejolisa_get_order([ 'ID' => $order_id]);

						if(false !== $get_response['valid']) :

							$order = $get_response['orders'];

							do_action('sejoli/notification/order/' . $order['status'], $order);

							$response[] = sprintf( __('Order %s resent notification %s', 'sejoli'), $order_id, $order['status']);

						endif;

					endforeach;

				else :
					// delete
				endif;
			endif;
		endif;

		wp_send_json($response);
		exit;
	}

	/**
     * Register admin menu under sejoli main menu
     * Hooked via action admin_menu, priority 999
     * @since 1.0.0
     * @return void
     */
    public function register_admin_menu() {

        add_submenu_page( 'crb_carbon_fields_container_sejoli.php', __('Penjualan', 'sejoli'), __('Penjualan', 'sejoli'), 'manage_sejoli_orders', 'sejoli-orders', [$this, 'display_order_page']);

        $user = wp_get_current_user();
		if ( in_array( 'sejoli-manager', (array) $user->roles ) ) {
	        add_menu_page(
				__('Penjualan', 'sejoli'),
				__('Penjualan', 'sejoli'),
				'manage_sejoli_orders',
				'sejoli-orders',
				[$this, 'display_order_page'],
				plugin_dir_url( __FILE__ ) . 'images/icon.png',
				3
			);
		}

    }

    /**
     * Display order page
     * @since 1.0.0
     */
    public function display_order_page() {
        require plugin_dir_path( __FILE__ ) . 'partials/order/page.php';
    }

	/**
	 * Export order data to CSV
	 * Hooked via action sejoli_ajax_sejoli-order-export, priority 1
	 * @since 	1.1.0
	 * @since 	1.5.3 	Add more information data to CSV
	 * @return 	void
	 */
	public function export_csv() {

		$post_data = wp_parse_args($_GET,[
			'sejoli-nonce' => NULL,
			'backend'      => false
		]);

		if(wp_verify_nonce($post_data['sejoli-nonce'], 'sejoli-order-export')) :

			$filename = 'export-orders-' . strtoupper( sanitize_title( get_bloginfo('name') ) ) . '-' . date('Y-m-d-H-i-s', current_time('timestamp'));

			if(!current_user_can('manage_sejoli_orders') || false === $post_data['backend']) :
				$post_data['affiliate_id']	= get_current_user_id();
			endif;

			if(isset($post_data['affiliate_id'])) :
				$filename .= '-'. $post_data['affiliate_id'];
			endif;

			unset($post_data['backend'], $post_data['sejoli-nonce']);

			$response   = sejolisa_get_orders($post_data);

			$csv_data = [];
			$csv_data[0]	= array(
				'INV', 'product', 'created_at', 'name', 'email', 'phone', 'quantity', 'price', 'status', 'affiliate', 'affiliate_id',
				'address', 'payment', 'courier', 'variant', 'notes',
			);

			$i = 1;
			foreach($response['orders'] as $order) :

				$address = $courier = $variant = '-';

				if( isset( $order->meta_data['shipping_data'] ) ) :

					$shipping_data = wp_parse_args( $order->meta_data['shipping_data'], array(
						'courier'     => NULL,
						'service'     => NULL,
						'district_id' => 0,
						'district_name' => NULL,
						'cost'        => 0,
						'receiver'    => NULL,
						'phone'       => NULL,
						'address'     => NULL
					));

					if( !empty($shipping_data['courier']) ) :

						$courier = $shipping_data['courier'];

						$courier = $shipping_data['service'] ? $courier . ' - ' . $shipping_data['service'] : $courier;
						$courier = $shipping_data['service'] ? $courier . ' ' . sejolisa_price_format( $shipping_data['cost'] ) : $courier;

					endif;

					if( isset( $shipping_data['address'] ) ) :

						$address = $shipping_data['receiver'] . ' ('.$shipping_data['phone'].')' . PHP_EOL . $shipping_data['address'];

						$subdistrict = $shipping_data['district_name'];

						if( isset($subdistrict) ) :

							if (!empty($subdistrict)) :
							    $parts = explode(',', $subdistrict);

							    // Bersihkan whitespace
							    $parts = array_map('trim', $parts);

							    if (count($parts) === 5) {
								    $address = PHP_EOL . 
								        sprintf(__('KELURAHAN %s', 'sejoli'), strtoupper($parts[0])) . PHP_EOL .
								        sprintf(__('KECAMATAN %s', 'sejoli'), strtoupper($parts[1])) . PHP_EOL .
								        sprintf(__('KOTA %s', 'sejoli'), strtoupper($parts[2])) . PHP_EOL .
								        sprintf(__('PROPINSI %s', 'sejoli'), strtoupper($parts[3])) . PHP_EOL .
								        sprintf(__('KODE POS %s', 'sejoli'), $parts[4]);
								} else {
								    $address = '';
								    echo "Format distrik tidak sesuai.";
								}
							endif;

						endif;

					endif;

				endif;

				if( isset($order->meta_data['variants']) && 0 < count($order->meta_data['variants']) ) :

					$variant_data = array();

					foreach((array) $order->meta_data['variants'] as $variant ) :
						$variant_data[] = strtoupper($variant['type']) . ' : ' . $variant['label'];
					endforeach;

					$variant = implode(PHP_EOL, $variant_data);

				endif;

				$order_meta_note  = isset($order->meta_data['note']) ? $order->meta_data['note'] : '';

				$payment_module = apply_filters('sejoli/payment/module', $order->payment_gateway);
				$payment_gateway = isset($order->meta_data[$order->payment_gateway]) ? $order->meta_data[$order->payment_gateway] : null;
				if($payment_module === 'moota') :
					$bank_info = ($payment_gateway && isset($payment_gateway['bank'], $payment_gateway['account_number']))
					    ? ucfirst($payment_module) .' - '. ucfirst($payment_gateway['bank']) .' '. $payment_gateway['account_number']
					    : ucfirst($payment_module) .' - Data bank tidak tersedia';
				elseif($payment_module === 'xendit') :
					$bank_info = ($payment_gateway && isset($payment_gateway['method']))
					    ? ucfirst($payment_module) .' - '. ucfirst($payment_gateway['method'])
					    : ucfirst($payment_module) .' - Data bank tidak tersedia';
				else:
					$bank_info = ($payment_gateway && isset($payment_gateway['method']))
					    ? ucfirst($payment_module)
					    : ucfirst($payment_module);
				endif;
				$status_list = $this->get_status();
				$status_text = isset($status_list[$order->status]) ? $status_list[$order->status] : 'Menunggu Pembayaran';

				$csv_data[$i] = array(
					$order->ID,
					$order->product->post_title,
					$order->created_at,
					$order->user_name,
					$order->user_email,
					get_user_meta($order->user_id, '_phone', true),
					absint($order->quantity),
					$order->grand_total,
					$status_text,
					$order->affiliate_id,
					$order->affiliate_name,
					$address,
					$bank_info,
					$courier,
					$variant,
					$order_meta_note
				);

				$i++;

			endforeach;

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

			$fp = fopen('php://output', 'wb');
			foreach ($csv_data as $line) :
			    fputcsv($fp, $line, ',');
			endforeach;
			fclose($fp);

		endif;
		exit;
	}

	/**
	 * Cancel incomplete order
	 * Hooked via action sejoli/order/cancel-incomplete-order, priority 100
	 * @since 	1.2.3
	 * @return 	void
	 */
	public function cancel_incomplete_order() {

		$day = intval(sejolisa_carbon_get_theme_option('sejoli_autodelete_incomplete_order'));

		if(0 < $day) :

			$response = \SejoliSA\Model\Order::reset()
		                ->set_filter('status', 'on-hold')
						->set_filter('created_at', date('Y-m-d 00:00:00', strtotime('-' . $day.' day')), '<=')
						->set_data_length(20)
						->set_data_order('created_at', 'ASC')
		                ->get()
						->respond();

			if(false !== $response['valid'] && 0 < count($response['orders'])) :

				set_time_limit(0);

				foreach($response['orders'] as $order) :

					do_action('sejoli/order/update-status', [
						'ID'     => $order->ID,
						'status' => 'cancelled'
					]);

				endforeach;

				do_action(
					'sejoli/log/write',
					'autocancel-order',
					sprintf(
						__('Cancel %s orders', 'sejoli'),
						count($response['orders'])
					)
				);

			endif;

		endif;

		exit;
	}
}
