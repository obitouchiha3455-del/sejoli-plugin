<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Coupon {

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
	 * Action type coupon
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $action_type;

	/**
	 * Store coupon data
	 * @since	1.0.0
	 * @access 	protected
	 * @var 	false|array
	 */
	protected $coupon_data = false;

	/**
	 * Is coupon valid to use
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	bool
	 */
	protected $coupon_valid_use = true;

	/**
	 * Discount value
	 * @since 1.0.0
	 * @var integer
	 */
	protected $discount = 0;

	/**
	 * Set free shipping
	 * @since	1.1.1
	 * @var 	boolean
	 */
	protected $free_shipping = false;

	/**
	 * Set renewal coupon
	 * @since	1.1.1
	 * @var 	boolean
	 */
	protected $renewal_coupon = false;

	/**
	 * Shipping cost
	 * @since 	1.3.2
	 * @var 	float
	 */
	protected $shipping_cost = 0.0;

	/**
	 * Set post type name variable
	 * @since	1.2.3
	 * @var 	string
	 */
	protected $post_type = 'sejoli-coupon';

	/**
	 * Coupon used in order
	 * @since 	1.4.2
	 * @var 	array
	 */
	protected $coupon_used = array(
		'coupon'        => NULL,
		'discount'      => 0,
		'free_shipping' => false

	);

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

		// recheck coupon usage
		if(false === wp_next_scheduled('sejoli/coupon/recheck-use')) :

			wp_schedule_event(time(), 'hourly', 'sejoli/coupon/recheck-use');

		else :

			$recurring 	= wp_get_schedule('sejoli/coupon/recheck-use');

			if('hourly' !== $recurring) :
				wp_reschedule_event(time(), 'hourly', 'sejoli/coupon/recheck-use');
			endif;
		endif;


		// delete coupon post
		if(false === wp_next_scheduled('sejoli/coupon/delete-post')) :

			wp_schedule_event(time(), 'daily', 'sejoli/coupon/delete-post');

		else :

			$recurring 	= wp_get_schedule('sejoli/coupon/delete-post');

			if('daily' !== $recurring) :
				wp_reschedule_event(time(), 'daily', 'sejoli/coupon/delete-post');
			endif;
		endif;
	}

	/**
	 * Update all total coupon usage
	 * Hooked via action sejoli/coupon/recheck-use, priority 1
	 * @since 	1.1.4
	 * @return 	void
	 */

	public function update_total_usage() {

		$response   = sejolisa_get_total_use_all_coupons();
		$update_log = array();

		if(false !== $response['valid'] && 0 < count($response['coupons'])) :

			foreach($response['coupons'] as $_coupon) :

				$update_response = sejolisa_update_total_usage_coupon(array(
					'id'    => $_coupon->coupon_id,
					'usage' => $_coupon->total_use
				));

				$update_log[] = array(
					'id'    => $_coupon->coupon_id,
					'usage' => $_coupon->total_use
				);

			endforeach;

			if(0 < count($update_log)) :
				do_action('sejoli/log/write', 'coupon-update-usage', $update_log);
			endif;

		endif;
	}

	/**
	 * Register coupon post type
	 * Actually we don't need this post type, what we need only the form for UI to create/edit the coupon
	 * Hooked via action init, priority 999
	 *
	 * @since  	1.0.0
	 * @since 	1.2.3 	Change 'sejoli-coupon' to use property $this->post_type
	 * @return 	void
	 */
	public function register_post_type() {
		$labels = [
    		'name'               => _x( 'Coupons', 'post type general name', 'sejoli' ),
    		'singular_name'      => _x( 'Coupon', 'post type singular name', 'sejoli' ),
    		'menu_name'          => _x( 'Coupons', 'admin menu', 'sejoli' ),
    		'name_admin_bar'     => _x( 'Coupon', 'add new on admin bar', 'sejoli' ),
    		'add_new'            => _x( 'Add New', 'coupon', 'sejoli' ),
    		'add_new_item'       => __( 'Tambah Kupon', 'sejoli' ),
    		'new_item'           => __( 'Tambah Kupon', 'sejoli' ),
    		'edit_item'          => __( 'Ubah Coupon', 'sejoli' ),
    		'view_item'          => __( 'View Coupon', 'sejoli' ),
    		'all_items'          => __( 'All Coupons', 'sejoli' ),
    		'search_items'       => __( 'Search Coupons', 'sejoli' ),
    		'parent_item_colon'  => __( 'Parent Coupons:', 'sejoli' ),
    		'not_found'          => __( 'No coupons found.', 'sejoli' ),
    		'not_found_in_trash' => __( 'No coupons found in Trash.', 'sejoli' )
    	];

    	$args = [
    		'labels'             => $labels,
            'description'        => __( 'Description.', 'sejoli' ),
    		'public'             => false,
    		'publicly_queryable' => false,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
    		'rewrite'            => [ 'slug' => 'coupon' ],
    		'capability_type'    => 'post',
    		'has_archive'        => false,
    		'hierarchical'       => false,
    		'menu_position'      => null,
    		'supports'           => ['title']
    	];

    	register_post_type( $this->post_type, $args );

		remove_post_type_support($this->post_type, 'revisions');
	}

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['coupon'] = [
			'table' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-coupon-table'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-coupon-table')
			],
			'update' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-coupon-update'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-coupon-update')
			],
			'delete' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-coupon-delete'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-coupon-delete')
			],
			'check' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-coupon-check'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-check-coupon')
			],
			'text' => [
				'checking'          => __('Mengecek ketersediaan kupon', 'sejoli'),
				'coupon_exists'     => __('Kode kupon ini sudah terdaftar', 'sejoli'),
				'coupon_not_exists' => __('Kode kupon ini belum terdaftar', 'sejoli')
			]
		];

		return $js_vars;
	}

    /**
     * Create coupon
     * Hooked via action sejoli/coupon/create, priority 100
     * @param  array  $coupon_data
     * @return void
     */
    public function create(array $coupon_data) {

        // Check coupon first
        $code    = $coupon_data['code'];
        $respond = sejolisa_get_coupon_by_code($code);

        // Coupon exists
        if(false !== $respond['valid']) :
            $respond['valid'] = false;
            $respond['messages']['error'][] = __('Kupon sudah digunakan oleh pihak lain', 'sejoli');

            sejolisa_set_respond($respond, 'coupon');
        else :
            // create coupon
            $respond = sejolisa_create_coupon($coupon_data);
            sejolisa_set_respond($respond, 'coupon');
        endif;
    }

    /**
     * Update coupon
     * Hooked via action sejoli/coupon/update, priority 100
     * @param  array  $coupon_data
     * @return void
     */
    public function update(array $coupon_data) {
        $respond = sejolisa_update_coupon($coupon_data);
        sejolisa_set_respond($respond, 'coupon');
    }

    /**
     * Update coupon
     * Hooked via action sejoli/coupon/update-status, priority 100
     * @param  array  $coupon_data
     * @return void
     */
    public function update_status(array $coupon_data) {
        $respond = sejolisa_update_coupon_status($coupon_data);
        sejolisa_set_respond($respond, 'coupon');
    }

	/**
	 * Update coupon usages
	 * Hooked via action sejoli/coupon/update-usage, priority 100
	 * @since 	1.0.0
	 * @param  	string	$coupon_code
	 * @return 	void
	 */
	public function update_usage($coupon_code) {
		$respond = sejolisa_update_coupon_usage($coupon_code);
		sejolisa_set_respond($respond, 'coupon');
	}

	/**
	 * Routine to remove all coupon post data
	 * Hooked via action sejoli/coupon/delete-coupotn
	 * @since 	1.2.3
	 * @return 	void
	 */
	public function delete_coupon_post() {

		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'any',
			'posts_per_page' => 30,
			'fields'         => 'ids'
		);

		$query = new \WP_Query( $args );

		if(0 < $query->post_count ) :

			foreach($query->posts as $post_id) :
				wp_delete_post( $post_id, true);
			endforeach;

			do_action('sejoli/log/write',
				'delete-coupon-posts',
				sprintf( __('Delete %s coupon posts', 'sejoli'), $query->count)
			);
		endif;
	}

	/**
	 * Register coupon metabox
	 * Hooked via action carbon_fields_register_fields, priority 999l
	 * @since 	1.0.0
	 * @since 	1.2.3 	Change 'sejoli-coupon' to use property $this->post_type
	 * @return 	void
	 */
	public function setup_carbon_fields() {

		Container::make('post_meta', 'sejoli_coupon_settings', __('Pengaturan', 'sejoli'))
			->where('post_type', '=', $this->post_type)
			->set_classes('sejoli-metabox')
			->add_tab(__('Umum', 'sejoli'),[

				Field::make( 'separator', 'sep_coupon_configuration', __('Pengaturan Kupon', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('coupon') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make('checkbox',	'active',	__('Aktifkan kupon', 'sejoli'))
					->set_option_value('yes')
					->set_default_value(true),

				Field::make('text',	'discount_number', sprintf(__('Besar potongan (%s)','sejoli'), sejolisa_currency_format()))
					->set_width(50)
					->set_attribute('type','numeric')
					->set_default_value(0),

				Field::make('select',	'discount_type',__('Tipe potongan','sejoli'))
					->set_width(50)
					->set_options([
						'fixed' 		=> __('Nilai Tetap','sejoli'),
						'percentage'	=> __('Persentase','sejoli')
					]),

				Field::make('checkbox',	'discount_fixed_quantity',	__('Jumlah potongan disesuaikan dengan kuantitas item', 'sejoli'))
					->set_option_value('yes')
					->set_default_value(true)
					->set_conditional_logic([
						[
							'field' => 'discount_type',
							'value' => 'fixed'
						]
					])
					->set_help_text(__('Sebagai contoh misalkan pembeli membeli barang sebanyak 4 item. Dengan mengaktifkan opsi ini, maka besar potongannya adalah 4 x nilai potongan', 'sejoli')),

				Field::make('text',	'max_discount_number', sprintf(__('Maksimal potongan (%s)','sejoli'), sejolisa_currency_format()))
					->set_attribute('type','numeric')
					->set_default_value(0)
					->set_help_text( __('Kosongkan jika tidak ada maksimum potongan', 'sejoli')),

				Field::make('checkbox',	'free_shipping',	__('Gratiskan ongkos kirim', 'sejoli'))
					->set_option_value('yes')
					->set_help_text(__('Jika produk merupakan produk fisik, dengan mengaktifkan opsi ini akan meniadakan perhitungan ongkos kirim', 'sejoli')),
			])

			->add_tab(__('Peraturan', 'sejoli'),[

				Field::make('text',	'limit_use', __('Batas jumlah penggunaan', 'sejoli'))
					->set_attribute('type','numeric')
					->set_default_value(0)
					->set_help_text( __('Kosongkan jika tidak ada batas jumlah penggunaan', 'sejoli')),

				Field::make('date_time', 'limit_date', __('Batas waktu penggunaan', 'sejoli'))
					->set_storage_format('Y-m-d H:i:s', 'Y-m-d H:i')
					->set_picker_options([
						'time_24hr' => false,
						'timeFormat'=> 'H:i'
					])
					->set_help_text( sprintf(__('Kosongkan jika tidak ada batas waktu penggunaan. <br />Disesuaikan dengan waktu server. Waktu server saat ini <strong>%s</strong>', 'sejoli'), current_time('mysql'))),

				Field::make('checkbox',	'renewal_coupon',	__('Kupon perpanjangan langganan', 'sejoli'))
					->set_default_value(false)
					->set_help_text(__('Kupon khusus perpanjangan langganan', 'sejoli')),

				Field::make('association', 'apply_only_on', __('Kupon hanya bisa digunakan di produk', 'sejoli'))
					->set_types([
						[
							'type'      => 'post',
							'post_type' => 'sejoli-product'
						]
					]),

				Field::make('association', 'cant_apply_only_on', __('Kupon tidak bisa digunakan di produk', 'sejoli'))
					->set_types([
						[
							'type'      => 'post',
							'post_type' => 'sejoli-product'
						]
					])
			])

			->add_tab(__('Affiliasi', 'sejoli'),[
				Field::make('checkbox',	'use_by_affiliate',	__('Kupon ini bisa digunakan oleh affiliasi', 'sejoli'))
					->set_option_value('yes')
					->set_default_value(true)
					->set_help_text(__('Dengan tidak mengaktifkan opsi ini, maka kupon ini tidak akan muncul di halaman pembuatan kupon untuk affiliasi', 'sejoli')),

				Field::make('text', 'limit_affiliate_coupon', __('Jumlah kupon yang bisa dibuat oleh masing-masing affiliasi', 'sejoli'))
					->set_attribute('type', 'number')
					->set_default_value(1)
					->set_required(true)
					->set_conditional_logic(array(
						array(
							'field'	=> 'use_by_affiliate',
							'value'	=> true
						)
					))
			]);
	}

	/**
	 * If the request is to edit coupon, then we will have to setup post data first
	 * Hooked via action admin_init, priority 999
	 * @since 	1.0.0
	 * @since 	1.2.3 	Change 'sejoli-coupon' to use property $this->post_type
	 * @return 	void
	 */
	public function prepare_before_edit() {

		if(isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'sejoli-edit-coupon') && isset($_GET['code'])) :

			$respond = sejolisa_get_coupon_by_code($_GET['code']);

			if(false !== $respond) :

				$coupon    = $respond['coupon'];
				$coupon_id = wp_insert_post([
					'post_title'  => $coupon['code'],
					'post_status' => ('active' === $coupon['status']) ? 'publish' : 'draft',
					'post_type'   => $this->post_type,
					'author'      => $coupon['user_id'],
				]);

				if(is_array($coupon['rule']['apply_only'])) :
					if(0 < $coupon['rule']['apply_only']) :
						foreach($coupon['rule']['apply_only'] as $i => $product_id) :
							update_post_meta($coupon_id, '_apply_only_on|||'. $i .'|value', 'post:sejoli-product:'.$product_id);
							update_post_meta($coupon_id, '_apply_only_on|||'. $i .'|type', 'post');
							update_post_meta($coupon_id, '_apply_only_on|||'. $i .'|subtype', 'sejoli-product');
							update_post_meta($coupon_id, '_apply_only_on|||'. $i .'|id', $product_id);
						endforeach;
					else :
						update_post_meta($coupon_id, '_apply_on_on|||0|_empty','');
					endif;
				endif;

				if(is_array($coupon['rule']['cant_apply'])) :
					if(0 < $coupon['rule']['cant_apply']) :
						foreach($coupon['rule']['cant_apply'] as $i => $product_id) :
							update_post_meta($coupon_id, '_cant_apply_only_on|||'. $i .'|value', 'post:sejoli-product:'.$product_id);
							update_post_meta($coupon_id, '_cant_apply_only_on|||'. $i .'|type', 'post');
							update_post_meta($coupon_id, '_cant_apply_only_on|||'. $i .'|subtype', 'sejoli-product');
							update_post_meta($coupon_id, '_cant_apply_only_on|||'. $i .'|id', $product_id);
						endforeach;
					else :
						update_post_meta($coupon_id, '_cant_apply_on_on|||0|_empty','');
					endif;
				endif;

				$limit_affiliate_coupon = absint($coupon['rule']['limit_affiliate_coupon']);
				$limit_affiliate_coupon = ( 0 === $limit_affiliate_coupon ) ? 1 : $limit_affiliate_coupon;

				update_post_meta($coupon_id, '_active', ('active' === $coupon['status']) ? 'yes' : '');
				update_post_meta($coupon_id, '_discount_number', $coupon['discount']['value']);
				update_post_meta($coupon_id, '_discount_type', $coupon['discount']['type']);
				update_post_meta($coupon_id, '_discount_fixed_quantity', ('per_item' === $coupon['discount']['usage']) ? 'yes' : '');
				update_post_meta($coupon_id, '_max_discount_number', $coupon['rule']['max_discount']);
				update_post_meta($coupon_id, '_use_by_affiliate', (true === boolval($coupon['rule']['use_by_affiliate'])) ? 'yes' : false );
				update_post_meta($coupon_id, '_renewal_coupon', (true === boolval($coupon['rule']['renewal_coupon'])) ? 'yes' : false );
				update_post_meta($coupon_id, '_free_shipping', (false !== $coupon['discount']['free_shipping']) ? 'yes' : '');
				update_post_meta($coupon_id, '_limit_use', $coupon['limit_use']);
				update_post_meta($coupon_id, '_limit_date', $coupon['limit_date']);
				update_post_meta($coupon_id, '_limit_affiliate_coupon', $limit_affiliate_coupon);

				wp_redirect(add_query_arg([
					'post'   => $coupon_id,
					'action' => 'edit'
				],admin_url('post.php')));
				exit;

			else :
				wp_die(
					sprintf(__('Terjadi kesalahan. <br />Detil kesalahan :<br />%s', 'sejoli'), implode('<br />', $respond['messages']['error'])),
					__('Ada Kesalahan', 'sejoli')
				);
				exit;
			endif;
			exit;
		endif;
	}

	/**
	 * Render product IDS from carbon field association
	 * @since 	1.0.0
	 * @param  	mixed  $data
	 * @return 	array
	 */
	protected function render_product_id_from_association($data) {
		$post_ids = [];

		foreach((array) $data as $_data) :
			$post_ids[] = $_data['id'];
		endforeach;

		return $post_ids;
	}

	/**
	 * Save coupon data to custom coupon table and then delete the coupon data in post type
	 * The coupon will be parent coupon
	 * Hooked via action save_post, priority 999
	 * @since 	1.0.0
	 * @since 	1.2.3 	Change 'sejoli-coupon' to use property $this->post_type
	 * @param  	int 	$post_id 	Current coupon post ID
	 * @return 	void
	 */
	public function save_coupon_data($post_id) {
		global $post;

		if ( wp_is_post_revision( $post_id ) ) :
			return;
		endif;

		if(is_a($post, 'WP_Post') && property_exists($post, 'post_type') && $this->post_type === $post->post_type) :
			$post_id = intval($_POST['ID']);

			$coupon_data = [
		        'code'             => $_POST['post_title'],
		        'user_id'          => 0,
		        'coupon_parent_id' => NULL,
		        'discount'         => sejolisa_carbon_get_post_meta($post_id, 'discount_number'),
		        'discount_type'    => sejolisa_carbon_get_post_meta($post_id, 'discount_type'),
		        'discount_usage'   => (true === boolval(sejolisa_carbon_get_post_meta($post_id, 'discount_fixed_quantity'))) ? 'per_item' : 'total',
				'free_shipping'    => sejolisa_carbon_get_post_meta($post_id, 'free_shipping'),
		        'limit_use'        => sejolisa_carbon_get_post_meta($post_id, 'limit_use'),
		        'limit_date'       => sejolisa_carbon_get_post_meta($post_id, 'limit_date'),
				'rule'             => [
					'apply_only'             => $this->render_product_id_from_association(sejolisa_carbon_get_post_meta($post_id, 'apply_only_on')),
					'cant_apply'             => $this->render_product_id_from_association(sejolisa_carbon_get_post_meta($post_id, 'cant_apply_only_on')),
					'max_discount'           => floatval(sejolisa_carbon_get_post_meta($post_id, 'max_discount_number')),
					'use_by_affiliate'       => sejolisa_carbon_get_post_meta($post_id, 'use_by_affiliate'),
					'limit_affiliate_coupon' => absint(sejolisa_carbon_get_post_meta($post_id, 'limit_affiliate_coupon')),
					'renewal_coupon'    	 => boolval(sejolisa_carbon_get_post_meta($post_id, 'renewal_coupon'))
				],
		        'status'           => (true === boolval(sejolisa_carbon_get_post_meta($post_id, 'active'))) ? 'active' : 'pending'
		    ];

			$action_type = 'coupon-created';
			$request     = strtok(basename($_POST['_wp_http_referer'], ".php"), '?');

			if('post-new.php' !== $request) :

				$action_type       = 'coupon-updated';
				$respond           = sejolisa_get_coupon_by_code($coupon_data['code']);
				$coupon            = $respond['coupon'];
				$coupon_data['ID'] = $coupon['ID'];
				$respond           = sejolisa_update_coupon($coupon_data);

			else :
				$respond = sejolisa_create_coupon($coupon_data);
			endif;

			if(false !== $respond['valid']) :
				//cleaning
				wp_delete_post($post_id, true);

				wp_redirect(add_query_arg([
					'page'        => 'sejoli-coupons',
					'notice'      => $action_type,
					'coupon-code' => sanitize_title($_POST['post_title'])
				],admin_url('admin.php')));

				exit;
			else :
				wp_die(
					sprintf(__('Terjadi kesalahan ketika pembuatan kupon. <br />Detil kesalahan :<br /> %s', 'sejoli'), implode('<br />', $respond['messages']['error'])),
					__('Ada Kesalahan', 'sejoli')
				);
				exit;
			endif;
		endif;
	}

	/**
	 * Display admin notices
	 * Hooked via action admin_notices, priority 1000
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_admin_notices() {
		if(isset($_GET['notice'])) :

			switch($_GET['notice']) :
				case 'coupon-updated' :
					$notice_type = 'success';
					$message = sprintf(__('Kupon <strong>%s</strong> berhasil diperbaharui', 'sejoli'), strtoupper($_GET['coupon-code']));
					break;

				case 'coupon-created' :
					$notice_type = 'success';
					$message = sprintf(__('Kupon <strong>%s</strong> berhasil dibuat', 'sejoli'), strtoupper($_GET['coupon-code']));
					break;
			endswitch;

			?><div class="notice notice-<?php echo $notice_type; ?>"><p><?php echo $message; ?></p></div><?php
		endif;
	}

	/**
     * Register coupon menu under sejoli main menu
     * Hooked via action admin_menu, priority 1001
     * @since 1.0.0
     * @return void
     */
    public function register_admin_menu() {

        add_submenu_page( 'crb_carbon_fields_container_sejoli.php', __('Kupon', 'sejoli'), __('Kupon', 'sejoli'), 'manage_sejoli_coupons', 'sejoli-coupons', [$this, 'display_coupon_page']);

    }

    /**
     * Display coupon page
     * @since 1.0.0
     */
    public function display_coupon_page() {
        require plugin_dir_path( __FILE__ ) . 'partials/coupon/page.php';
    }

	/**
	 * Validate coupon
	 * Hooked via filter sejoli/checkout/is-coupon-valid, priority 1
	 * @since  	1.0.0
	 * @since 	1.4.2	Add parameter $action_type
	 * @param  	bool    	$valid
	 * @param  	WP_Post 	$coupon
	 * @param  	array  	 	$post_data
	 * @param 	string 		$action_type, can be 'calculate' or 'checkout'
	 * @return 	bool
	 */
	public function validate_coupon_when_checkout(bool $valid, \WP_Post $coupon, array $post_data, $action_type = 'calculate') {

		if(empty($post_data['coupon'])) :
			$this->coupon_valid_use = false;
			return $valid;
		endif;

		// Ordinary checkout

		$this->action_type = $action_type;
		$respond           = sejolisa_get_coupon_by_code($post_data['coupon']);

		if(true !== $respond['valid']) :

			$this->coupon_valid_use = $valid = false;
			sejolisa_set_message(sprintf(__('Kupon %s tidak valid', 'sejoli'), $post_data['coupon']));

		else :

			$this->coupon_data = $coupon = $respond['coupon'];

			// check oupon status
			if('active' !== $coupon['status']) :

				$valid = $this->coupon_valid_use = false;
				sejolisa_set_message(sprintf(__('Kupon %s tidak aktif', 'sejoli'), $post_data['coupon']));

			// validate limit usage
			elseif(
				0 !== intval($coupon['limit_use']) &&
				$coupon['usage'] >= $coupon['limit_use']
			) :
				$valid = $this->coupon_valid_use = false;
				sejolisa_set_message(sprintf(__('Batas penggunaan kupon %s sudah mencapai batas', 'sejoli'), $post_data['coupon']));

			// validate limit date
			elseif(
				(
					!empty($coupon['limit_date']) &&
					'0000-00-00 00:00:00' !== $coupon['limit_date']
				) &&
				current_time('timestamp') > strtotime($coupon['limit_date'])
			) :
				$valid = $this->coupon_valid_use = false;

				sejolisa_set_message(
					sprintf(__('Batas penggunaan kupon %s berakhir pada %s', 'sejoli'),
					$post_data['coupon'],
					date('d F Y', strtotime($coupon['limit_date']))
				));

			// check if is affiliated coupon
			elseif(
				is_user_logged_in() &&
				get_current_user_id() === intval($coupon['user_id']) &&
				'calculate' === $this->action_type
			) :
				$valid = $this->coupon_valid_use = false;

				sejolisa_set_message(
					__('Anda tidak diperkenankan menggunakan kupon affiliate anda sendiri', 'sejoli'),
					'warning'
				);

			else :

				// Check affiliate coupon used in checkout
				if(
					0 < $coupon['user_id'] &&
					get_current_user_id() === intval($coupon['user_id']) &&
					'checkout' === $this->action_type
				) :
					$this->coupon_valid_use = false;

					sejolisa_set_message(
						__('Anda tidak diperkenankan menggunakan kupon affiliate anda sendiri', 'sejoli'),
						'error'
					);

				endif;

				// check if product is in apply_only
				if(
					( is_array($coupon['rule']['apply_only']) && 0 < count($coupon['rule']['apply_only']) ) &&
					( !in_array($post_data['product_id'], $coupon['rule']['apply_only']) )
				) :

					$valid = $this->coupon_valid_use = false;

					sejolisa_set_message(
						sprintf(__('Kupon %s tidak bisa digunakan pada produk ini', 'sejoli'), strtoupper($post_data['coupon'])),
						'warning'
					);

					sejolisa_set_message(
						sprintf(__('Kupon %s tidak bisa digunakan pada produk ini', 'sejoli'), strtoupper($post_data['coupon'])),
						'error'
					);
				endif;

				if(
					( is_array($coupon['rule']['cant_apply']) && 0 < count($coupon['rule']['cant_apply']) ) &&
					( in_array($post_data['product_id'], $coupon['rule']['cant_apply']) )
				) :

					$valid = $this->coupon_valid_use = false;

					sejolisa_set_message(
						sprintf(__('Kupon %s tidak bisa digunakan pada produk ini', 'sejoli'), strtoupper($post_data['coupon'])),
						'warning'
					);

					sejolisa_set_message(
						sprintf(__('Kupon %s tidak bisa digunakan pada produk ini', 'sejoli'), strtoupper($post_data['coupon'])),
						'error'
					);
				endif;

				// Check coupon renewal
				$request_calculate = isset($_POST['renew_page']) ? $_POST['renew_page'] : null;
				$renewal_coupon    = isset($coupon['rule']['renewal_coupon']) ? $coupon['rule']['renewal_coupon'] : null;

				if(
					true === $request_calculate && true === $renewal_coupon
				) :

					$valid = $this->coupon_valid_use = true;

				endif;

				if(
					null === $request_calculate && true === $renewal_coupon
				) :

					$valid = $this->coupon_valid_use = false;

					sejolisa_set_message(
						sprintf(__('Kupon %s hanya untuk perpanjangan langganan', 'sejoli'), $post_data['coupon']),
						'error'
					);

				endif;

				if($this->coupon_valid_use) :

					$coupon_id = intval($coupon['coupon_parent_id']);
					$user_id   = get_current_user_id();

					if(
						0 !== $coupon_id ||
						(
							0 < $coupon['user_id'] &&
							$user_id !== intval($coupon['user_id'])
						)
					) :
						do_action('sejoli/checkout/affiliate/set', $coupon['user_id'], 'coupon');
					endif;

					do_action('sejoli/order/set-coupon', $coupon['ID']);

				endif;

			endif;

		endif;

		return $valid;
	}

	/**
	 * Calculate coupon value based on coupon setting and product price
	 * Will return with discount value
	 * @since 	1.4.2
	 * @param  	array 	$coupon_data
	 * @param  	float 	$product_price
	 * @param  	array 	$order_data
	 * @param 	boolean	$with_shipment
	 * @return 	float
	 */
	protected function calculate_coupon_value($coupon_data, $product_price, $order_data, $with_shipment = false) {

		$discount_data = $coupon_data['discount'];
		$discount_rule = $coupon_data['rule'];
		$discount      = 0;
		if('percentage' === $discount_data['type']) :
			$discount = $product_price * ($discount_data['value']) / 100;
		else :
			if('per_item' === $discount_data['usage']) :
				$discount = $order_data['quantity'] * $discount_data['value'];
			else :
				$discount = $discount_data['value'];
			endif;
		endif;

		if(!empty($discount_rule['max_discount']) && $discount_rule['max_discount'] < $discount) :
			$discount = $discount_rule['max_discount'];
		endif;

		if(true === boolval($discount_data['free_shipping'])) :

			$this->free_shipping = true;

			if($with_shipment) :

				if('undefined' !== $order_data['shipment'] && !empty($order_data['shipment'])) :

					$shipment = explode(":::", $order_data['shipment']);

					if(is_array($shipment) && 2 < count($shipment)) :

						list($courier, $service, $cost) = $shipment;

						$discount += $cost;

					endif;

				elseif(isset($order_data['shipping_own_value']) && 'undefined' !== $order_data['shipping_own_value'] && !empty($order_data['shipping_own_value'])) :

					$cost     = floatval($order_data['shipping_own_value']);
					$discount += $cost;

				endif;

			endif;

		endif;

		return $discount;
	}

	/**
	 * Set discount by coupon
	 * Hooked via filter sejoli/order/grand-total, priority 200
	 * @since 	1.0.0
	 * @param 	float 	$total
	 * @param 	array 	$order_data
	 * @return	float
	 */
	public function set_discount( float $total, array $order_data ) {

		if( $this->coupon_valid_use ) :

			$product = sejolisa_get_product( $order_data['product_id'] );

			// Check coupon renewal
			$request_calculate = isset($_POST['renew_page']) ? $_POST['renew_page'] : null;
			
			if( $product->subscription['signup']['fee'] > 0 && null === $request_calculate ) :
				$setProduct_price = $product->price + $product->subscription['signup']['fee'];
			else :
				$setProduct_price = $product->price;
			endif;

			if( true === boolval($this->coupon_data['discount']['free_shipping']) ) :
				$shipping_cost = 0;
				if( 'undefined' !== $order_data['shipment'] && !empty( $order_data['shipment'] ) ) :

					$shipment = explode( ":::", $order_data['shipment'] );

					if( is_array( $shipment ) && 2 < count( $shipment ) ) :

						list( $courier, $service, $cost ) = $shipment;

						$shipping_cost = $cost;

					endif;

				elseif( isset($order_data['shipping_own_value']) && 'undefined' !== $order_data['shipping_own_value'] && !empty($order_data['shipping_own_value']) ) :

					$shipping_cost = floatval( $order_data['shipping_own_value'] );

				endif;


				if($this->coupon_data['discount']['type'] === 'percentage') :
					$discount = $this->calculate_coupon_value( $this->coupon_data, $setProduct_price, $order_data, true );
				else:
					if('per_item' === $this->coupon_data['discount']['usage']) :
						$discount = $this->calculate_coupon_value( $this->coupon_data, $setProduct_price, $order_data, true );
					else :
						$discount = $this->calculate_coupon_value( $this->coupon_data, $total, $order_data, true );
					endif;
				endif;

				$discount = $discount - $shipping_cost;

				if( 0 > $total - $discount ) :
				
					$total = 0;
				
				else :
				
					$this->discount = floatval( $discount );
					$total = $total - $discount;

				endif;

				if( $this->coupon_data['discount']['value'] > 0 || true === boolval($this->coupon_data['discount']['free_shipping']) && $this->coupon_data['discount']['value'] > 0 ) :

					sejolisa_set_message(
						sprintf(__('Anda mendapatkan diskon sebesar %s', 'sejoli'), sejolisa_price_format( $discount )),
						'info'
					);

					sejolisa_set_message(
						sprintf(__('Anda mendapatkan diskon gratis ongkir', 'sejoli')),
						'info'
					);

				elseif( true === boolval($this->coupon_data['discount']['free_shipping']) ) :

					sejolisa_set_message(
						sprintf(__('Anda mendapatkan diskon gratis ongkir', 'sejoli')),
						'info'
					);

				endif;

			else:

				if($this->coupon_data['discount']['type'] === 'percentage') :
					$discount = $this->calculate_coupon_value( $this->coupon_data, $setProduct_price, $order_data, true );
				else:
					if('per_item' === $this->coupon_data['discount']['usage']) :
						$discount = $this->calculate_coupon_value( $this->coupon_data, $setProduct_price, $order_data, true );
					else :
						$discount = $this->calculate_coupon_value( $this->coupon_data, $total, $order_data, true );
					endif;
				endif;

				if( 0 > $total - $discount ) :
				
					$total = 0;
				
				else :
				
					$this->discount = floatval( $discount );
					$total = $total - $discount;
				
				endif;

				sejolisa_set_message(
					sprintf(__('Anda mendapatkan diskon sebesar %s', 'sejoli'), sejolisa_price_format( $discount )),
					'info'
				);

			endif;

			$this->coupon_used = array(
				'coupon'        => $this->coupon_data['code'],
				'discount'      => $discount,
				'free_shipping' => $this->free_shipping
			);

		endif;

		return floatval( $total );

	}

	/**
	 * Set coupon value
	 * Hooked via filter sejoli/coupon/value, priority 1
	 * @since 	1.4.2
	 * @param 	float 	$value
	 * @param 	array 	$request
	 * @return 	float
	 */
	public function set_coupon_value($value, $product_price, $coupon, $request) {

		return $this->calculate_coupon_value($coupon, $product_price, $request, true);
	}

	/**
     * Set coupon value to cart
     * Hooked via filter sejoli/order/cart-detail, priority 10
     * @since 1.0.0
     * @param array $cart_detail
     * @param array $order_data
     * @return array $cart_detail
     */
    public function set_cart_detail(array $cart_detail, array $order_data) {

        if(0 < $this->discount) :
            $cart_detail['coupon_value']  = '-' .$this->discount;
		endif;

        return $cart_detail;
    }

	/**
	 * Set free shipping in cart detail
	 * Hooked via filter sejoli/order/cart-detail, priority 20
	 * @since 	1.3.2
	 * @param 	array $cart_detail [description]
	 * @param 	array $order_data  [description]
	 * @return 	array
	 */
	public function set_free_shipping_in_cart_detail(array $cart_detail, array $order_data) {

		if(false !== boolval($this->free_shipping)) :
			$cart_detail['free_shipping'] = $this->free_shipping;
			$cart_detail['shipping_cost'] = '-' . $this->shipping_cost;
		endif;

		return $cart_detail;
	}

	/**
	 * Return free shipping condition
	 * Hooked via filter sejoli/order/is-free-shipping, priority 1
	 * @since 	1.3.2
	 * @param  	boolean $free_shipping
	 * @return 	boolean
	 */
	public function is_free_shipping(bool $free_shipping) {
		return boolval( $this->free_shipping );
	}

	/**
	 * Return renewal coupon condition
	 * Hooked via filter sejoli/order/is-renewal-coupon, priority 1
	 * @since 	1.3.2
	 * @param  	boolean $free_shipping
	 * @return 	boolean
	 */
	public function is_renewal_coupon(bool $renewal_coupon) {
		return boolval( $this->renewal_coupon );
	}

	/**
	 * Set coupon data to order meta,
	 * Hooked via filter sejoli/order/meta-data, priority 100
	 * @since 	1.0.0
	 * @param 	array 	$meta_data
	 * @param 	array  	$order_data
	 * @return  array
	 */
	public function set_order_meta($meta_data = [], $order_data = array()) {

		$meta_data['coupon'] = $this->coupon_used;

		return $meta_data;
		
	}

	/**
	 * Set inline JS for coupon editor
	 * Hooked via action admin_footer, priority 999
	 * @since 	1.0.0
	 * @since 	1.2.3 	Change 'sejoli-coupon' to use property $this->post_type
	 * @return 	void
	 */
	public function set_inline_js_editor(){
		global $post, $pagenow;

		if(in_array($pagenow, ['post-new.php', 'post.php']) && $this->post_type === $post->post_type) : ?>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('.toplevel_page_crb_carbon_fields_container_sejoli').addClass('wp-menu-open');
			jQuery('.toplevel_page_crb_carbon_fields_container_sejoli > a.wp-has-submenu').addClass('wp-menu-open');
			jQuery('.toplevel_page_crb_carbon_fields_container_sejoli li:nth-child(6)').addClass('current');
		});
		</script>
		<?php
		endif;

		if(
			'post.php' === $pagenow && $this->post_type === $post->post_type &&
			isset($_GET['action']) && 'edit' === $_GET['action']
		) : ?>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("input[name='post_title']").attr('readonly', true);
		});
		</script>
		<?php
		endif;

	}

	/**
	 * Set coupon data to order
	 * Hooked via filter sejoli/order/order-detail, priority 20
	 * @since 	1.0.0
	 * @param 	array $order_detail
	 * @return 	array
	 */
	public function set_coupon_data_to_order_detail(array $order_detail) {

		$coupon_id = intval($order_detail['coupon_id']);
		$response  = sejolisa_get_coupon_by_id( $coupon_id );

		if(false !== $response['valid'] ) :
			$order_detail['coupon'] = $response['coupon'];
		endif;
		
		return $order_detail;
	}

}
