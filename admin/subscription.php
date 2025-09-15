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
class Subscription {

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
	 * Subscription typ
	 * @since 	1.0.0
	 * @var 	null|string
	 */
	protected $type = NULL;

	/**
	 * Signup fee
	 * @since 	1.0.0
	 * @var 	null|float
	 */
	protected $signup_fee = NULL;

	/**
	 * Order type
	 * @since 	1.0.0
	 * @var 	string
	 */
	protected $order_type = 'regular';

	/**
	 * Subscription duration
	 * @var integer
	 */
	protected $duration_time = 0;

	/**
	 * Duration in number
	 * @since 	1.0.0
	 * @var 	integer
	 */
	protected $duration;

	/**
	 * Period
	 * @since 	1.0.0
	 * @var 	string
	 */
	protected $period;

	/**
	 * Check if product price is already calculated
	 * @var [type]
	 */
	protected $is_already_calculated = false;

	/**
	 * Order type
	 * @since 	1.0.0
	 * @var 	array
	 */
	protected $order_types = [
		'subscription-tryout',
		'subscription-signup',
		'subscription-regular'
	];

	/**
	 * Store product data
	 * @since 	1.0.0
	 * @var 	WP_Post
	 */
	protected $product_data;

	/**
	 * Subscription type
	 * @since 	1.0.0
	 * @var 	string
	 */
	protected $subscription_type = NULL;

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

		$js_vars['subscription'] = [
			'table' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-subscription-table'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-subscription-table')
			],
			'type' => [
				'subscription-tryout'  => 'tryout',
				'subscription-signup'  => 'signup',
				'subscription-regular' => 'regular',
			],
			'export' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-subscription-export'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-do-subscription-export')
			],
			'update' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-subscription-update'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-subscription-update')
			]
		];

		return $js_vars;
	}

	/**
	 * Setup subscription product meta data
	 * Hooked via filter sejoli/product/meta-data, priority 120
	 * @since 	1.0.0
	 * @since 	1.5.3	Add option to disable multi checkout for tryout page
	 * 					Add option to disable renew after x days expired
	 * @param  WP_Post 	$product
	 * @param  int     	$product_id
	 * @return WP_Post
	 */
	public function setup_product_meta(\WP_Post $product, int $product_id) {

		$product->subscription = [
			'active'	=> ('recurring' === sejolisa_carbon_get_post_meta($product->ID, 'payment_type')) ? true : false,
			'regular'	=> [
				'duration' => sejolisa_carbon_get_post_meta($product->ID, 'subscription_duration'),
				'period'   => sejolisa_carbon_get_post_meta($product->ID, 'subscription_period'),
				'price'	   => sejolisa_carbon_get_post_meta($product->ID, 'price'),
			],
			'tryout'	=> [
				'active'     => boolval(sejolisa_carbon_get_post_meta($product->ID, 'subscription_has_tryout')),
				'duration'   => sejolisa_carbon_get_post_meta($product->ID, 'subscription_tryout_duration'),
				'period'     => sejolisa_carbon_get_post_meta($product->ID, 'subscription_tryout_period'),
				'first_only' => sejolisa_carbon_get_post_meta($product->ID, 'subscription_tryout_first_time_only'),
			],
			'signup'	=> [
				'active' => boolval(sejolisa_carbon_get_post_meta($product->ID, 'subscription_has_signup_fee')),
				'fee'    => floatval(sejolisa_carbon_get_post_meta($product->ID, 'subscription_signup_fee'))
			],
			'max_renewal'	=> absint( sejolisa_carbon_get_post_meta($product->ID, 'subscription_max_renewal_days') )
		];

		return $this->product_data = $product;
	}

	/**
	 * Set duration time
	 * @since 	1.0.0
	 * @param 	integer 	$duration
	 * @param 	string 		$period   can be yearly, monthly or daily
	 */
	protected function set_duration($duration, $period) {

		if(!in_array($period, ['yearly', 'monthly', 'daily']) || empty($duration)) :
			return;
		endif;

		switch($period) :
			case 'yearly'	:
				$time = YEAR_IN_SECONDS;
				$period_text = __('Tahun', 'sejoli');
				break;

			case 'monthly' :
				$time = 30 * DAY_IN_SECONDS;
				$period_text = __('Bulan', 'sejoli');
				break;

			default :
				$time = DAY_IN_SECONDS;
				$period_text = __('Hari', 'sejoli');
				break;

		endswitch;

		$this->duration_time = $duration * $time;
		$this->duration      = $duration;
		$this->period        = $period_text;
	}

	/**
	 * Prepare order data after the order status updated
	 * Hooked via sejoli/order/status-updated, priority 999
	 * @since 	1.0.0
	 * @param  	array 	$order_data since
	 * @return 	void
	 */
	public function prepare_subscription_data($order_data) {

		$respond = sejolisa_check_subscription($order_data['order_parent_id']);

		// if previous subcription found
		if(false !== $respond['valid'] && 'completed' === $order_data['status']) :

			$subscription = $respond['subscription'];
			$product = sejolisa_get_product($subscription->product_id);
			$this->set_subscription_data($product, $subscription->type);

		// it is new subscription
		elseif(
			false == $respond['valid']
			&& 'completed' === $order_data['status']
			&& in_array($order_data['type'], $this->order_types)
		) :
			$product = sejolisa_get_product($order_data['product_id']);
			$this->set_subscription_data($product, '');
		endif;
	}

	/**
	 * Set subscription data
	 * @param WP_Post $product
	 * @param string  $subscription_type
	 */
	public function set_subscription_data(\WP_Post $product, $subscription_type = '') {

		$subscription_type = (empty($this->subscription_type)) ? $subscription_type : $this->subscription_type;

		if(false === boolval($product->subscription['active'])) :
			return;
		endif;

		// current product has tryout capability and order subscription type is not set
		if(false !== $product->subscription['tryout']['active'] && !in_array($subscription_type, ['tryout', 'signup', 'regular'])) :

			$this->type       = 'tryout';
			$this->order_type = 'subscription-tryout';
			$this->set_duration($product->subscription['tryout']['duration'], $product->subscription['tryout']['period']);

		else :

			// current product has signup capability and order subscription type is tryout
			if(false !==  $product->subscription['signup']['active'] && !in_array($subscription_type, ['signup', 'regular' ])) :

				$this->type       = 'signup';
				$this->signup_fee = $product->subscription['signup']['fee'];
				$this->order_type = 'subscription-signup';

			//do regular
			else :
				$this->type       = 'regular';
				$this->order_type = 'subscription-regular';
			endif;

			$this->set_duration($product->subscription['regular']['duration'], $product->subscription['regular']['period']);
		endif;
	}

	/**
     * Set product price
     * Hooked via filter sejoli/product/price, priority 10
     * @since   1.0.0
     * @param   float   $price
     * @param   WP_Post $product
     * @return  float
     */
	public function set_product_price(float $product_price, \WP_Post $product) {

		if($this->is_already_calculated) :
			return $product_price;
		endif;

		if(!property_exists($product,'subscription')) :
			$product = $this->setup_product_meta($product, $product->ID);
		endif;

		$this->set_subscription_data($product);

		switch($this->type) :
			case 'tryout' :
				$product_price = 0;
				break;

			// case 'signup' :
			// 	$product_price = $this->signup_fee + $product_price;
			// 	break;

		endswitch;

		$this->is_already_calculated = true;

		return $product_price;
	}

	/**
	 * Set order type
	 * Hooked via filter sejoli/order/set, priority 999
	 * @since 	1.0.0
	 * @param 	string 	$order_type
	 * @param 	array 	$order_data
	 * @return 	string
	 */
	public function set_order_type($order_type, $order_data) {

		// if order type is already set, then ngapain ngeset lagi?
		if('regular' !== $this->order_type) :
			return $this->order_type;
		endif;

		$this->order_type = $order_type;
		$order_data       = wp_parse_args($order_data,[
			'product_id' => NULL
		]);

		// the first order, not renewal
		if(!empty($order_data['product_id']) && 'regular' === $this->order_type) :

			$product = sejolisa_get_product($order_data['product_id']);

			if('digital' === $product->type && false !== $product->subscription['active']) :

				$this->set_subscription_data($product);

			endif;
		endif;

		return $this->order_type;
	}

	/**
	 * Add subscription data to cart details
	 * Hooked via sejoli/order/cart-detail, priority 5
	 * @since 	1.0.0
	 * @param 	array $cart_details
	 * @param 	array $post_data
	 * @return 	array
	 */
	public function set_data_to_cart_detail(array $cart_details, array $post_data) {

		if('regular' !== $this->order_type) : // && 'regular' !== $post_data['type']) :

			$cart_details['subscription']	= [
				'duration'	=> [
					'raw'		=> $this->duration_time,
					'string'	=> sprintf( __('per %s %s', 'sejoli'), $this->duration, $this->period )
				]
			];

			$regular_price = sejolisa_carbon_get_post_meta( $this->product_data->ID, 'price');

			$cart_details['subscription']['regular']['raw']   = $regular_price;
			$cart_details['subscription']['regular']['price'] = sejolisa_price_format($regular_price);

			if('subscription-signup' === $this->order_type) :
				$cart_details['subscription']['signup']['raw']   = $this->product_data->subscription['signup']['fee'];
				$cart_details['subscription']['signup']['price'] = sejolisa_price_format($this->product_data->subscription['signup']['fee']);
			endif;
		endif;

		return $cart_details;
	}

	/**
	 * Set order price total
	 * Hooked via filter sejoli/order/grand-total, priority 999
	 * @since 	1.0.0
	 * @param 	float $grand_total
	 * @param 	array $order_data
	 * @return  float
	 */
	public function set_order_total(float $grand_total, array $order_data) {

		switch($this->type) :
			case 'tryout' :
				$grand_total = 0;
				break;

			case 'signup' :
				$grand_total += ($this->signup_fee * $order_data['quantity']);
				break;

		endswitch;

		return $grand_total;
	}

	/**
	 * Add subscription data
	 * Hooked via action sejoli/order/set-status/completed, priority 999
	 * @since 	1.0.0
	 * @param 	array 	$order_data [description]
	 */
	public function add_subscription_data(array $order_data) {

		$order_data = wp_parse_args($order_data,[
			'ID'              => NULL,
			'order_parent_id' => 0,
			'type'            => 'subscription-regular'
		]);

		if(
			!empty($order_data['ID']) &&
			in_array($order_data['type'], $this->order_types)
		) :
			$respond = sejolisa_get_subscription_by_order($order_data['ID']);

			if(false === $respond['valid']) :
				$args = [
					'order_id'        => $order_data['ID'],
					'user_id'         => $order_data['user_id'],
					'product_id'      => $order_data['product_id'],
					'order_parent_id' => $order_data['order_parent_id'],
					'type'            => $this->type,
					'end_time'        => $this->duration_time,
					'status'          => 'active'
				];

				$respond = sejolisa_add_subscription($args);
				sejolisa_set_respond($respond, 'subscription');

			else :

				$subscription           = (array) $respond['subscription'];
				$subscription['status'] = 'active';

				$respond = sejolisa_update_subscription_status($subscription);
				sejolisa_set_respond($respond, 'subscription');

			endif;
		endif;
	}

	/**
	 * Update status subscription to pending
	 * Hooked via action sejoli/order/set-status/on-hold, 		priority 999
	 * Hooked via action sejoli/order/set-status/refunded, 		priority 999
	 * Hooked via action sejoli/order/set-status/cancelled, 	priority 999
	 * Hooked via action sejoli/order/set-status/in-progress, 	priority 999
	 * Hooked via action sejoli/order/set-status/shipped, 		priority 999
	 * @since 	1.0.0
	 * @param 	array 	$order_data [description]
	 */
	public function set_subcription_pending(array $order_data) {

		$order_data = wp_parse_args($order_data,[
			'ID'              => NULL,
			'order_parent_id' => 0,
			'type'            => 'regular'
		]);

		if(
			!empty($order_data['ID']) &&
			in_array($order_data['type'], $this->order_types)
		) :
			$respond = sejolisa_get_subscription_by_order($order_data['ID']);

			if(false !== $respond['valid']) :

				$subscription           = (array) $respond['subscription'];
				$subscription['status'] = 'pending';

				$respond = sejolisa_update_subscription_status($subscription);
				sejolisa_set_respond($respond, 'subscription');

			endif;
		endif;
	}

	/**
	 * Validate both product and subscription
	 * Hooked via filter sejoli/checkout/is-subscription-valid
	 * @param  bool     $valid
	 * @param  WP_Post  $product
	 * @param  stdClass $subscription
	 * @return bool
	 */
	public function validate_when_renew(bool $valid, \WP_Post $product, \stdClass $subscription) {

		if(true !== $product->subscription['active']) :
			$valid = false;
			sejolisa_set_message(__('Produk bukan tipe berlangganan', 'sejoli'));
		endif;

		if(false !== $valid) :
			$this->set_subscription_data($product, $subscription->type);
		endif;

		return $valid;
	}

	/**
	 * Check if order subscription is still active
	 * Hooked via filter sejoli/subscription/is-active, priority 1
	 * @since 	1.0.0
	 * @param  	boolean    	$active
	 * @param  	array   	$order_data
	 * @return 	boolean
	 */
	public function is_subscription_active(bool $active, array $order_data) {

		$order_data = wp_parse_args($order_data, [
	        'ID'              => NULL,
	        'order_parent_id' => 0,
	    ]);

		if(!empty($order_data['ID'])) :
			// $order_id = ( empty($order_data['order_parent_id']) ) ? $order_data['ID'] : $order_data['order_parent_id'];
			$order_id = $order_data['ID'];
			$response = sejolisa_check_subscription($order_id);

			if(false !== $response['valid']) :

				$subscription_end_time = strtotime($response['subscription']->end_date);

				if(current_time('timestamp') > $subscription_end_time) :
					$active = false;
				elseif('active' !== $response['subscription']->status) :
					$active = false;
				endif;

			endif;
		endif;

		return $active;
	}

	/**
     * Register subscription menu under sejoli main menu
     * Hooked via action admin_menu, priority 1005
     * @since 1.0.0
     * @return void
     */
    public function register_admin_menu() {

        add_submenu_page( 'crb_carbon_fields_container_sejoli.php', __('Langganan', 'sejoli'), __('Langganan', 'sejoli'), 'manage_sejoli_subscriptions', 'sejoli-subscriptions', [$this, 'display_subscription_page']);

    }

    /**
     * Display subscription page
     * @since 1.0.0
     */
    public function display_subscription_page() {
        require plugin_dir_path( __FILE__ ) . 'partials/subscription/page.php';
    }

	/**
	 * Display subscription date
	 * Hooked via sejoli/notification/content/order-meta, priority 40
	 * @param  string $content      	[description]
	 * @param  string $media        	[description]
	 * @param  string $recipient_type   [description]
	 * @param  array  $invoice_data 	[description]
	 * @return string
	 */
	public function display_subscription_date(string $content, string $media, $recipient_type, array $invoice_data) {
		$order_data_status = isset($invoice_data['order_data']['status']) ? $invoice_data['order_data']['status'] : '';

		if(
			'completed' === $order_data_status &&
			in_array($recipient_type, ['buyer', 'admin']) &&
			in_array($invoice_data['order_data']['type'], $this->order_types)
		) :

			$respond      = sejolisa_get_subscription_by_order($invoice_data['order_data']['ID']);
			$subscription = (array) $respond['subscription'];

			if(false !== $respond['valid'] && 'active' === 	$subscription['status']) :

				$content .= sejoli_get_notification_content(
								'subscription-info',
								$media,
								array(
									'subscription' => [
										'end_date' => $subscription['end_date']
									]
								)
							);
			endif;
		endif;

		return $content;
	}

	/**
	 * Export order data to CSV
	 * Hooked via action sejoli_ajax_sejoli-subscription-export, priority 1
	 * @since 	1.1.0
	 * @return 	void
	 */
	public function export_csv() {

		$post_data = wp_parse_args($_GET,[
			'sejoli-nonce'    => NULL,
			'backend'         => false,
			'max_renewal_day' => 0
		]);

		if(
			wp_verify_nonce($post_data['sejoli-nonce'], 'sejoli-subscription-export') &&
			current_user_can('manage_sejoli_sejoli')
		) :

			$filename = 'export-subscriptions-' . strtoupper( sanitize_title( get_bloginfo('name') ) ) . '-' . date('Y-m-d-H-i-s', current_time('timestamp'));
			$response = sejolisa_get_expired_subscriptions( absint( $post_data['max_renewal_day'] ) );
			$csv_data = [];

			$csv_data[0]	= array(
				'INV', 'product', 'created_at', 'name', 'email', 'phone', 'expired_date', 'expired_day', 'renewal_link'
			);

			$i = 1;
			foreach( (array) $response['subscriptions'] as $subscription) :

				$csv_data[$i] = array(
					$subscription->order_id,
					$subscription->product_name,
					$subscription->created_at,
					$subscription->user_name,
					$subscription->user_email,
					get_user_meta($subscription->user_id, '_phone', true),
					$subscription->end_date,
					sejolisa_get_difference_day( strtotime( $subscription->end_date) ),
					add_query_arg( array( 'order_id' => $subscription->order_id ), home_url('checkout/renew/') )
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

		else :

			wp_die( __('Sorry, you can\'t do this process', 'sejoli') );

		endif;

		exit;
	}
}
