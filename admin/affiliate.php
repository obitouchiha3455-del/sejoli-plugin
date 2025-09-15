<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Affiliate {

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
	 * Current product commission
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	null|array
	 */
	protected $current_commissions = NULL;

	/**
	 * Affiliate data when checkout done
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $affiliate_checkout = [
		'user_meta' => NULL,
		'link'      => NULL,
		'coupon'    => NULL
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

		if(false === wp_next_scheduled('sejoli/commission/recheck')) :

			wp_schedule_event(time(),'hourly','sejoli/commission/recheck');

		else :

			$recurring 	= wp_get_schedule('sejoli/commission/recheck');

			if('hourly' !== $recurring) :
				wp_reschedule_event(time(), 'hourly', 'sejoli/commission/recheck');
			endif;
		endif;

		add_action('admin_init', array($this, 'reset_affiliate_commissions'));
	}

	/**
	 * Reset affiliate commissions
	 * @since 1.6.3.1
	 */
	public function reset_affiliate_commissions() {

		if(isset($_GET['reset-affiliate']) && current_user_can('manage_options')) :

			global $wpdb;

			$order_id = absint($_GET['reset-affiliate']);

			$wpdb->delete(
				$wpdb->prefix . 'sejolisa_affiliates',
				array(
					'order_id' => $order_id
				)
			);

			$response = sejolisa_get_order(['ID' => $order_id]);
			$order    = $response['orders'];

			$this->set_commission($order);

			if('completed' === $order['status']) :
				$this->update_status_to_added($order);
			endif;

			?><pre><?php
			print_r(sejolisa_get_respond('commission'));
			?></pre><?php

			exit;
		endif;
	}

	/**
	 * Setup commission product fields
	 * Hooked via filter sejoli/product/commission/fields, priority 1
	 * @since 	1.3.3
	 * @return 	array
	 */
	public function setup_commission_fields() {

		$currency = 'Rp. '; // later will be using hook filter;

		return array(
			'number' => Field::make('text',	'number', sprintf(__('Besar Komisi (%s)','sejoli'), $currency))
				->set_width(50)
				->set_attribute('type', 'number')
				->set_default_value(0),

			'type' => Field::make('select',	'type',__('Tipe Komisi','sejoli'))
				->set_width(50)
				->set_options([
					'fixed' 		=> __('Nilai Tetap','sejoli'),
					'percentage'	=> __('Persentase','sejoli')
				])
		);
	}

	/**
	 * Setup affiliate fields for product
	 * Hooked via filter sejoli/product/fields, priority 20
	 * @since 	1.0.0
	 * @since 	1.5.2 	Add option to enable to disable affiliate feature
	 * @since 	1.5.3 	Add option to enable to hide affiliate if the user has not bought the product
	 * @param  	array  	$fields
	 * @return 	array
	 */
	public function setup_affiliate_setting_fields(array $fields) {

		$currency = 'Rp. '; // later will be using hook filter;

		$fields[] = [
			'title'	=> __('Affiliasi', 'sejoli'),
			'fields' =>  [

				// Affiliate Setting
				Field::make('separator', 'sep_sejoli_affiliate_setup', __('Pengaturan Affiliasi', 'sejoli'))
					->set_classes('sejoli-with-help'),

				Field::make('checkbox', 'sejoli_enable_affiliate', __('Aktifkan fitur affiliasi pada produk ini', 'sejoli'))
					->set_default_value( true )
					->set_help_text( __('Dengan menghilangkan cek pada opsi ini, maka produk ini tidak akan muncul pada halaman pembuatan link affiliasi dan semua link affiliasi yang sudah dibuat dengan produk ini, akan dinonaktifkan', 'sejoli')),

				Field::make('checkbox', 'sejoli_enable_affiliate_if_already_bought', __('Aktifkan fitur affiliasi jika user telah membeli produk ini', 'sejoli'))
					->set_default_value( false )
					->set_help_text( __('Dengan memberikan cek pada opsi ini, maka affiliate harus membeli produk ini terlebih dahulu untuk bisa mendapatkan komisi affiliasi', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'sejoli_enable_affiliate',
							'value'	=> true
						)
					)),

				// Commission Setting
				Field::make('separator', 'sep_sejoli_affiliate_commission', __('Komisi', 'sejoli'))
					->set_classes('sejoli-with-help'),

				// Field::make("checkbox"	, 'sejoli_commission_refundable',__('Komisi bisa dicairkan','sejoli'))
				// 	->set_help_text(__('Setiap komisi akan masuk ke dompet digital terlebih dahulu. <br />Aktifkan jika komisi tersebut bisa dicairkan','sejoli'))
				// 	->set_option_value('on'),
				//

				Field::make('complex', 'sejoli_commission',__('Komisi','sejoli'))
					->add_fields(
						apply_filters('sejoli/product/commission/fields', array())

					)
					->set_layout('tabbed-vertical')
					->set_header_template(__('Tier','sejoli').' <%- $_index+1 %>'),

				// Affiliate Link
				Field::make('separator', 'sep_sejoli_affiliate_link', __('Link Affiliasi', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('affiliate-link') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make( 'text', 'sejoli_landing_page', __('Link Landing Page', 'sejoli') ),
				Field::make( 'complex', 'sejoli_affiliate_links' ,__('Link Landing Page Lainnya', 'sejolis'))
					->set_help_text(__('Jika anda memiliki lebih dari satu landing page, ditambahkan menggunakan fasilitas ini','sejoli'))
					->set_layout('tabbed-vertical')
					->add_fields( array(
						Field::make( 'text', 'title', __('Title','sejoli') )
							->set_required( true )
							->set_help_text(__('Hanya gunakan angka dan nomor saja! Jangan gunakan spasi', 'sejoli')),
						Field::make( 'text', 'description', __('Deskripsi','sejoli') ),
						Field::make( 'text', 'link', __('Link','sejoli') )
							->set_required( true )
							->set_attribute( 'placeholder', 'https://')
							->set_help_text( __('Selalu awali dengan http:// atau https://', 'sejoli')),
					))
					->set_header_template( '
						<% if (title) { %>
							<%- title %>
						<% } else { %>
							<%- $_index+1 %>
						<% } %>
					' ),

				// Affiliate Tool
				Field::make('separator', 'sep_sejoli_affiliate_tool', __('Alat Bantu Affiliasi', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('affiliate-tool') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make( 'complex', 'sejoli_affiliate_tool', __( 'Banner Affiliasi', 'sejoli'))
					->set_layout('tabbed-vertical')
					->add_fields( array(
						Field::make( 'text', 'title', __('Judul','sejoli') )
							->set_required( true ),
						Field::make( 'file', 'file', __('File','sejoli') )
							->set_required( true ),
						Field::make( 'textarea', 'description', __('Deskripsi', 'sejoli'))
					))
					->set_header_template( '
						<% if (title) { %>
							<%- title %>
						<% } else { %>
							<%- $_index+1 %>
						<% } %>
					' )
			]
		];

		return $fields;
	}

	/**
	 * Add affiliate fields to profile
	 * Hooked via action sejoli/user/fields, priority 200
	 * @since  1.0.0
	 * @param  array $fields
	 * @return array
	 */
	public function add_affiliate_data_fields(array $fields) {
		$fields[] = [
			'title'		=> __('Affiliasi', 'sejoli'),
			'fields'	=> [
				Field::make('textarea',	'bank_info',	__('Informasi Rekening', 'sejoli'))
					->set_help_text(__('Digunakan untuk pencairan komisi', 'sejoli'))
			]
		];

		return $fields;
	}

	/**
	 * Set CSS and JS files for admin affiliate page
	 * Hooked via admin_enqueue_scripts, priority 200
	 * @since 1.3.2
	 */
	public function set_css_and_js_files() {
		if(is_admin() && isset($_GET['page']) && 'sejoli-affiliates' === $_GET['page']) :

			wp_enqueue_style( 	$this->plugin_name . '-widgets', SEJOLISA_URL . 'admin/css/widgets.css');

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

		$js_vars['commission'] = [
			'table' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-commission-table'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-commission-table')
			],
			'chart' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-commission-chart'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-commission-chart')
			],
			'confirm' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-commission-confirm'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-commission-confirm')
			],
			'update' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-commission-update'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-commission-update')
			],
			'status' => [
				'pending'	=> __('Order belum selesai', 'sejoli'),
				'added'		=> __('Belum dibayar', 'sejoli'),
				'cancelled' => __('Dibatalkan', 'sejoli'),
				'paid'		=> __('Sudah dibayar', 'sejoli')
			],
			'transfer'	=> [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-confirm-commission-transfer'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-confirm_commission_transfer')
			]
		];

		$js_vars['affiliate_commission'] = [
			'table' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-affiliate-commission-table'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-affiliate-commission-table')
			],
			'confirm' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-affiliate-commission-detail'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-affiliate-commission-detail')
			],
			'pay'	=> [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-pay-single-affiliate-commission'
				], admin_url('admin-ajax.php')),
			]
		];

		$js_vars['affiliate'] = [
			'placeholder' => __('Pencarian affiliasi', 'sejoli')
		];

		return $js_vars;
	}

	/**
	 * Setup product affiliate data
	 * Hooked via filter sejoli/product/meta-data, priority 10
	 * @since 	1.0.0
	 * @since 	1.5.2 	Add conditional check if current product is affiliate-able
	 * @param  WP_Post $product
	 * @return WP_Post
	 */
	public function setup_product_affiliate_data(\WP_Post $product) {

		$product->affiliate = [];

		$commissions = sejolisa_carbon_get_post_meta($product->ID, 'sejoli_commission');
		$enable_affiliate = boolval( sejolisa_carbon_get_post_meta( $product->ID, 'sejoli_enable_affiliate') );

		if( true !== $enable_affiliate ) :
			$this->current_commissions = NULL;
			return $product;
		endif;

		foreach((array) $commissions as $i => $commission) :
			$tier = $i + 1;
			$product->affiliate[$tier] = [
				'tier'	=> $tier,
				'fee'	=> floatval($commission['number']),
				'type'	=> $commission['type']
			];
		endforeach;

		$this->current_commissions = $product->affiliate;

		return $product;
	}

	/**
	 * Get order commission
	 * Hooked via filter sejoli/order/commission, priority 1
	 * @param  float  $commission
	 * @param  array  $commission_set
	 * @param  array  $order_data
	 * @return float
	 */
	public function get_order_commission(float $commission, array $commission_set, array $order_data) {

		$grand_total = apply_filters('sejoli/commission/order-grand-total', floatval($order_data['grand_total']), $order_data);

		if('percentage' === $commission_set['type']) :
			$commission = floatval($grand_total * $commission_set['fee'] /  100);
		else :
			$commission = $commission_set['fee'] * $order_data['quantity'];
		endif;

		return $commission;
	}

	/**
	 * Get upline by given affiliate id
	 * @since 	1.0.0
	 * @since 	1.6.2.1		Fix wrong tier value
	 * @param  int 			$current_affiliate_id	Affiliate ID
	 * @param  int 			$max_tier            	Max tier
	 * @return array 		Array of affiliate data, include upline
	 */
	protected function get_uplines($current_affiliate_id, $max_tier) {

		$affiliates[1] = $current_affiliate_id;
		$uplines       = sejolisa_user_get_uplines($current_affiliate_id, $max_tier);

		if( is_array($uplines) && 0 < count($uplines) ) :
			foreach( $uplines as $tier => $upline_id ) :
				$affiliates[$tier + 1] = $upline_id; 
			endforeach;
		endif;

		return $affiliates;
	}

	/**
	 * Get list of uplines
	 * Hooked via filter sejoli/affiliate/uplines, priority 1
	 * @since 	1.3.0
	 * @param  	array  $affiliates
	 * @param  	int    $current_affiliate_id
	 * @param  	int    $max_tier
	 * @return 	array
	 */
	public function get_list_uplines(array $affiliates, int $current_affiliate_id, int $max_tier) {

		return $this->get_uplines($current_affiliate_id, $max_tier);;

	}

	/**
	 * Set commission by order
	 * Hooked via action sejoli/order/new, priority 10
	 * @since 1.0.0
	 * @param array $order_data
	 */
	public function set_commission(array $order_data) {

		global $sejolisa;

		$affiliate_id = intval($order_data['affiliate_id']);

		if(0 === $affiliate_id) :
			return;
		endif;

		$order_id = $order_data['ID'];

		if(!is_array($this->current_commissions) || is_null($this->current_commissions)) :
			$product = get_post($order_data['product_id']);
			$this->setup_product_affiliate_data($product);
		endif;

		if(0 < count($this->current_commissions)) :
			$users      = [];
			$max_tier   = count($this->current_commissions);
			$affiliates = $this->get_uplines($affiliate_id, $max_tier);

			$tier_affiliates = [];
			foreach($affiliates as $tier => $affiliate_id) :

				if (in_array($affiliate_id, $tier_affiliates)) :
			        break;
			    else:
			        $tier_affiliates[] = $affiliate_id;
			    endif;

				if(isset($this->current_commissions[$tier])) :
					$commission_set = $this->current_commissions[$tier];
					$commission     = apply_filters( 'sejoli/order/commission', 0, $commission_set, $order_data, $tier, $affiliate_id);
					$args = [
						'order_id'     => $order_data['ID'],
						'affiliate_id' => $affiliate_id,
						'product_id'   => $order_data['product_id'],
						'tier'         => $tier,
						'commission'   => $commission,
						'status'       => 'pending'
					];

					$respond = sejolisa_add_commission($args);

					sejolisa_set_respond($respond, 'commission');

				endif;

			endforeach;
		endif;
	}

	/**
	 * Set affiliate data based on type
	 * Hooked via action sejoli/checkout/affiliate/set, priority 100
	 * @since 	1.0.0
	 * @param 	integer 	$affiliate_id
	 * @param 	string 	$type
	 */
	public function set_affiliate_checkout($affiliate_id, $type = 'link') {
		$this->affiliate_checkout[$type] = intval($affiliate_id);
	}

	/**
	 * Get affiliate checkout data
	 * Hooked via filter sejoli/checkout/affiliate-data, priority 1
	 * @since 	1.0.0
	 * @param 	array $affiliate_data
	 * @return 	array
	 */
	public function get_affiliate_checkout_data(array $affiliate_data) {
		return $this->affiliate_checkout;
	}

	/**
	 * Set user meta data
	 * Hooked via filter sejoli/user/meta-data, priority 200
	 * @since 	1.0.0
	 * @param 	WP_User $user
	 * @return 	WP_User
	 */
	public function set_user_meta($user) {

		$user->meta->affiliate = get_user_meta($user->ID, sejolisa_get_affiliate_key(), true);

		return $user;
	}

	/**
     * Get affiliate id from a user
     * Hooked via filter sejoli/user/affiliate, priority 99
     * @since 	1.0.0
     * @param  int affiliate_id
     * @param  int user_id
     * @return int
     */
    public function get_affiliate_id($affiliate_id, int $user_id) {
        $affiliate_id = get_user_meta($user_id, sejolisa_get_affiliate_key() , true);

        return intval($affiliate_id);
    }

	/**
	 * Set affiliate data to otder
	 * Hooked via filter sejoli/order/order-detail, priority 10
	 * @since 	1.0.0
	 * @param 	array $order_detail
	 * @return 	array
	 */
	public function set_affiliate_data_to_order_detail(array $order_detail) {

		$affiliate_id = intval($order_detail['affiliate_id']);

		if(0 !== $affiliate_id) :
			$order_detail['affiliate'] = sejolisa_get_user( $affiliate_id );
		else :
			$order_detail['affiliate'] = NULL;
		endif;

		return $order_detail;
	}

	/**
	 * Update commission status by order data
	 * @since  1.0.0
	 * @param  array  $order_data
	 * @param  string $status
	 * @return
	 */
	public function update_status(array $order_data, string $status) {

		$order_id = $order_data['ID'];

		$respond = sejolisa_get_commissions([
			'order_id'	=> $order_id
		]);

		if(false !== $respond['valid']) :

			foreach((array) $respond['commissions'] as $commission) :

				$commission = (array) $commission;

				if($status !== $commission['status']) :
					$respond = sejolisa_update_commission_status([
						'ID'     => $commission['ID'],
						'status' => $status
					]);

					if(false !== $respond['valid']) :
						// Do notification or etc
						$commission['status'] = $status;
						do_action('sejoli/commission/set-status/' . $status, $commission, $order_data);
					endif;
				endif;

			endforeach;

		endif;
	}

	/**
	 * Update commission paid_status by commission
	 * @since  1.0.0
	 * @param  array  $args
	 * @return
	 */
	public function update_paid_status($args = array()) {

		$args = wp_parse_args([
			'ID'          => NULL,
			'paid_status' => false
		]);

		$respond = sejolisa_get_commission($args['ID']);

		if(false !== $respond['valid']) :

			$respond = sejolisa_update_commission_paid_status($args);

		endif;
	}

	/**
	 * Update commission status to added
	 * Hooked via action sejoli/order/set-status/completed, priority 100
	 * @param  array  $order_data [description]
	 * @return void
	 */
	public function update_status_to_added(array $order_data) {
		$this->update_status($order_data, 'added');
	}

	/**
	 * Update commission status to cancelled
	 * Hooked via action sejoli/order/set-status/cancelled, priority 100
	 * Hooked via action sejoli/order/set-status/cancelled, priority 100
	 * @param  array  $order_data [description]
	 * @return void
	 */
	public function update_status_to_cancelled(array $order_data) {
		$this->update_status($order_data, 'cancelled');
	}

	/**
	 * Update commission status to cancelled
	 * Hooked via action sejoli/order/set-status/on-hold, priority 100
	 * Hooked via action sejoli/order/set-status/in-progress, priority 100
	 * Hooked via action sejoli/order/set-status/shipped, priority 100
	 * @param  array  $order_data [description]
	 * @return void
	 */
	public function update_status_to_pending(array $order_data) {
		$this->update_status($order_data, 'pending');
	}

	/**
	 * Automation function to recheck commission status by order
	 * Hooked via action sejoli/license/recheck, priority 1
	 * @since 	1.1.1
	 * @return 	void
	 */
	public function recheck_commission() {

		$respond = \SejoliSA\Model\Affiliate::get_misplaced_commission_status()
						->respond();

		if(false !== $respond['valid'] && is_array($respond['commission_order']) && 0 < count($respond['commission_order'])) :

			$order_ids = array();

			foreach($respond['commission_order'] as $data) :
				$order_ids[] = $data->ID;
			endforeach;

			$response = sejolisa_update_commission_status(array(
				'ID'     => $order_ids,
				'status' => 'added'
			));

			do_action('sejoli/log/write', 'recheck-commission', sprintf(__('Commission ID %s found and update', 'sejoli'), implode(',', $order_ids)));
		endif;
	}

	/**
     * Register commission menu under sejoli main menu
     * Hooked via action admin_menu, priority 1001
     * @since 1.0.0
     * @return void
     */
    public function register_admin_menu() {

        add_submenu_page(
			'crb_carbon_fields_container_sejoli.php',
			__('Komisi', 'sejoli'),
			__('Komisi', 'sejoli'),
			'manage_sejoli_commissions',
			'sejoli-commissions',
			[$this, 'display_commission_page']
		);

		add_submenu_page(
			'crb_carbon_fields_container_sejoli.php',
			__('Affiliasi', 'sejoli'),
			__('Affiliasi', 'sejoli'),
			'manage_sejoli_commissions',
			'sejoli-affiliates',
			[$this, 'display_affiliate_page']
		);

    }

    /**
     * Display commission page
     * @since 1.0.0
     */
    public function display_commission_page() {
        require plugin_dir_path( __FILE__ ) . 'partials/commission/page.php';
    }

	/**
     * Display commission page
     * @since 1.0.0
     */
    public function display_affiliate_page() {
        require plugin_dir_path( __FILE__ ) . 'partials/commission/affiliate-page.php';
    }

	/**
	 * Display commission
	 * Hooked via sejoli/notification/content/order-meta
	 * @param  string $content      	[description]
	 * @param  string $media        	[description]
	 * @param  string $recipient_type   [description]
	 * @param  array  $invoice_data 	[description]
	 * @return string
	 */
	public function display_commission(string $content, string $media, $recipient_type, array $invoice_data) {
		$order_data_status = isset($invoice_data['order_data']['status']) ? $invoice_data['order_data']['status'] : '';

		$order_data_status = isset($invoice_data['order_data']['status']) ? $invoice_data['order_data']['status'] : '';
		$get_product_type  = isset($invoice_data['order_data']['product']) ? $invoice_data['order_data']['product']->type : '';

		if(
			'completed' === $order_data_status &&
			in_array($recipient_type, ['affiliate']) &&
			'email'	=== $media &&
			is_object($invoice_data['affiliate_data'])
		) :
			$get_commission = sejolisa_get_commissions([
				'order_id'	=> $invoice_data['order_data']['ID']
			]);

			$affiliate = $invoice_data['affiliate_data'];
			$content .= sejoli_get_notification_content(
							'affiliate-commission',
							$media,
							array(
								'affiliate' => (isset($affiliate)) ? $affiliate : null,
							)
						);
		endif;

		if(
			'completed' === $order_data_status &&
			in_array($recipient_type, ['admin']) &&
			'email'	=== $media &&
			is_object($invoice_data['affiliate_data'])
		) :
			$get_commission = sejolisa_get_commissions([
				'order_id'	   => $invoice_data['order_data']['ID'],
				'affiliate_id' => $invoice_data['order_data']['affiliate_id']
			]);
			if (!empty($get_commission['commissions']) && isset($get_commission['commissions'][0])) {
			    $affiliate = $get_commission['commissions'][0];
			    $affiliate->display_name = $affiliate->affiliate_name;
			    $affiliate->commission   = sejolisa_price_format($affiliate->commission);
			} else {
			    $affiliate = null;
			}

			$content .= sejoli_get_notification_content(
			    'affiliate-commission',
			    $media,
			    array(
			        'affiliate' => $affiliate,
			    )
			);
		endif;

		if( 
			'on-hold' === $order_data_status &&
			in_array($recipient_type, ['affiliate']) &&
			'email'	=== $media &&
			is_object($invoice_data['affiliate_data']) &&
			$get_product_type === "physical"
		) :

			$shipping  = isset($invoice_data['order_data']['meta_data']['shipping_data']) ? $invoice_data['order_data']['meta_data']['shipping_data'] : '';
			$meta_data = $invoice_data['order_data']['meta_data'];
			$district  = $shipping['district_name'];

			$content .= sejoli_get_notification_content(
							'shipment',
							$media,
							array(
								'shipping'  => $shipping,
								'district'	=> $district,
								'meta_data' => $meta_data
							)
						);
		endif;

		return $content;

	}

	/**
	 * Create CSV file with affiliate commission data
	 * Hooked via action wp_ajax_sejoli-affiliate-commission-csv-export, priority 1
	 * @since 	1.1.3
	 * @return 	void
	 */
	public function export_affiliate_commission_csv() {

		if(
			isset($_GET['sejoli-nonce']) &&
			wp_verify_nonce($_GET['sejoli-nonce'], 'sejoli-affiliate-commission-export') &&
			current_user_can('manage_sejoli_commissions')
		):

			$table = [];
			if ( isset( $_GET['date_range'] ) && !empty( $_GET['date_range'] ) ) :
				$table['filter']['date-range'] = $_GET['date_range'];
			endif;
			if ( isset( $_GET['affiliate_id'] ) && !empty( $_GET['affiliate_id'] ) ) :
				$table['filter']['affiliate_id'] = $_GET['affiliate_id'];
			endif;

			$response       = sejolisa_get_affiliate_commission_info( $table );
			$affiliate_data = array();

			$affiliate_data[] = array(
				'user_id',
				'affiliate name',
				'email',
				'phone',
				'commission',
				'bank info'
			);

			if(false !== $response['valid']) :
				foreach($response['commissions'] as $_commission) :

					$affiliate    = sejolisa_get_user(intval($_commission->ID));

		            if(is_a($affiliate, 'WP_User') && 0 < floatval($_commission->unpaid_commission)) :

		                $affiliate_data[] = array(
		                    $affiliate->ID,
							$affiliate->display_name,
		                    $affiliate->user_email,
		                    $affiliate->meta->phone,
		                    sejolisa_price_format($_commission->unpaid_commission),
		                    sejolisa_carbon_get_user_meta($affiliate->ID,'bank_info')
		                );

					endif;

				endforeach;
			endif;

			$filename = 'data-komisi-'.date('Y-m-d').'.csv';

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$fp = fopen('php://output', 'wb');
			foreach($affiliate_data as $_data) :
				fputcsv($fp, $_data);
			endforeach;
			fclose($fp);

			exit;
		endif;

	}

	/**
	 * Get subdistrict detail
	 * @since 	1.2.0
	 * @since 	1.5.0 		Add conditional to check if subdistrict_id is 0
	 * @param  	integer 	$subdistrict_id 	District ID
	 * @return 	array|null 	District detail
	 */
	public function get_subdistrict_detail($subdistrict_id) {

		if( 0 !== intval($subdistrict_id) ) :

			ob_start();
			require SEJOLISA_DIR . 'json/subdistrict.json';
			$json_data = ob_get_contents();
			ob_end_clean();

			$subdistricts        = json_decode($json_data, true);
	        $key                 = array_search($subdistrict_id, array_column($subdistricts, 'subdistrict_id'));
	        $current_subdistrict = $subdistricts[$key];

			return $current_subdistrict;

		endif;

		return 	NULL;
	}

	/**
     *  Set end point for misplaced commission link
     *  Hooked via action init
     *  @since 1.0.0
     *  @access public
     *  @return void
     */
	public function add_misplaced_commission_endpoint() {
	    
	    // add_rewrite_rule(
	    //     'misplaced-commission/([0-9]+)/([^/]*)/?$',
	    //     'index.php?pagename=misplaced-commission&affiliate_id=$matches[1]&date_range=$matches[2]',
	    //     'top' );
	    add_rewrite_rule(
	        'misplaced-commission/([0-9]+)',
	        'index.php?pagename=misplaced-commission&affiliate_id=$matches[1]',
	        'top' );

	    flush_rewrite_rules();
	
	}

	/**
     * Set custom query vars for miplaced commission
     * Hooked via filter query_vars
     * @since   1.0.0
     * @access  public
     * @param   array $vars
     * @return  array
     */
	public function set_misplaced_commission_vars( $query_vars ) {

	    $query_vars[] = 'affiliate_id';

	    return $query_vars;

	}

	/**
     * Proceed misplaced commission
     * Hooked via action template_redirect
     * @since   1.0.0
     * @access  public
     * @param   array $vars
     * @return  array
     */
	public function proceed_misplaced_commission() {

		if( current_user_can( 'manage_sejoli_commissions' ) ) :

			$affiliate_id = get_query_var( 'affiliate_id' );
			// $date_range   = get_query_var( 'date_range' );

			if( $affiliate_id ) {

				global $sejolisa, $wpdb;

				$order_data = sejolisa_get_orders( ['affiliate_id' => $affiliate_id ] );

				// Function untuk cek ada ke tabel affiliates berdasarkan order id
				foreach ( $order_data['orders'] as $key => $value ) {
					
					$get_order_data = (array) $value;
					$getOrderID     = $get_order_data['ID'];
					$getProductID   = $get_order_data['product_id'];

					$check_commission_db = $wpdb->get_results( " SELECT order_id FROM  ". $wpdb->prefix . "sejolisa_affiliates where order_id = ".$getOrderID );
				    
				    if ( empty( array_filter( $check_commission_db ) ) ) {
						
						$affiliate_id = intval( $get_order_data['affiliate_id'] );

						if( 0 === $affiliate_id ) :
							return;
						endif;

						$order_id = $getOrderID;

						if ( empty( array_filter( $this->current_commissions ) ) ) :
						
							$product = get_post( $getProductID );
							$this->setup_product_affiliate_data( $product );

						endif;

						if( 0 < count( $this->current_commissions ) ) :
							
							$users      = [];
							$max_tier   = count( $this->current_commissions );
							$affiliates = $this->get_uplines( $affiliate_id, $max_tier );

							foreach( $affiliates as $tier => $affiliate_id ) :

								if( isset( $this->current_commissions[$tier] ) ) :

									$commission_set = $this->current_commissions[$tier];
									$commission     = apply_filters( 'sejoli/order/commission', 0, $commission_set, $get_order_data, $tier, $affiliate_id );
									
									$args = [
										'order_id'     => $getOrderID,
										'affiliate_id' => $affiliate_id,
										'product_id'   => $getProductID,
										'tier'         => $tier,
										'commission'   => $commission,
										'status'       => 'pending'
									];

									$respond = sejolisa_add_commission( $args );

									sejolisa_set_respond( $respond, 'commission' );

								endif;

							endforeach;
						
						endif;

				    }
				}

				die();

			}

		endif;
	    
	}

}
