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
class Product {

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
     * Register product post type
     * Hooked via action init, priority 999
     * @return void
     */
    public function register_post_type() {

		if(false === sejolisa_check_own_license()) :
			return;
		endif;

		$labels = [
    		'name'               => _x( 'Products', 'post type general name', 'sejoli' ),
    		'singular_name'      => _x( 'Product', 'post type singular name', 'sejoli' ),
    		'menu_name'          => _x( 'Products', 'admin menu', 'sejoli' ),
    		'name_admin_bar'     => _x( 'Product', 'add new on admin bar', 'sejoli' ),
    		'add_new'            => _x( 'Add New', 'product', 'sejoli' ),
    		'add_new_item'       => __( 'Add New Product', 'sejoli' ),
    		'new_item'           => __( 'New Product', 'sejoli' ),
    		'edit_item'          => __( 'Edit Product', 'sejoli' ),
    		'view_item'          => __( 'View Product', 'sejoli' ),
    		'all_items'          => __( 'All Products', 'sejoli' ),
    		'search_items'       => __( 'Search Products', 'sejoli' ),
    		'parent_item_colon'  => __( 'Parent Products:', 'sejoli' ),
    		'not_found'          => __( 'No products found.', 'sejoli' ),
    		'not_found_in_trash' => __( 'No products found in Trash.', 'sejoli' )
    	];

    	$args = [
    		'labels'             => $labels,
            'description'        => __( 'Description.', 'sejoli' ),
    		'public'             => true,
    		'publicly_queryable' => true,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
    		'rewrite'            => [ 'slug' => 'product' ],
    		'capability_type'    => 'sejoli_product',
			'capabilities'		 => array(
				'publish_posts'      => 'publish_sejoli_products',
				'edit_posts'         => 'edit_sejoli_products',
				'edit_others_posts'  => 'edit_others_sejoli_products',
				'read_private_posts' => 'read_private_sejoli_products',
				'edit_post'          => 'edit_sejoli_product',
				'delete_posts'       => 'delete_sejoli_product',
				'read_post'          => 'read_sejoli_product'
			),
    		'has_archive'        => true,
    		'hierarchical'       => false,
    		'menu_position'      => null,
    		'supports'           => [ 'title', 'editor', 'thumbnail' ],
			'menu_icon'			 => plugin_dir_url( __FILE__ ) . 'images/icon.png'
    	];

    	register_post_type( SEJOLI_PRODUCT_CPT, $args );
    }

	/**
	 * Register CSS and JS for product related pages
	 * Hooked via action admin_enqueue_scripts, priority 1200
	 * @since 	1.1.6
	 * @return 	void
	 */
	public function register_css_and_js() {

		global $pagenow, $post;

		if(
			in_array($pagenow, ['post-new.php', 'post.php', 'edit.php']) &&
			is_a($post, 'WP_Post') &&
			property_exists($post, 'post_type') &&
			'sejoli-product' && $post->post_type
		) :
			wp_enqueue_style( $this->plugin_name.'-product');
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

		$js_vars['product'] = [
			'select' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-product-options',
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-product-options')
			],
			'placeholder' => __('Pencarian produk', 'sejoli')
		];

		return $js_vars;
	}

	/**
	 * Setup custom fields for product
	 * Hooked via action carbon_fields_register_fields, priority 999
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function setup_carbon_fields() {

		$fields = apply_filters( 'sejoli/product/fields', []);

		if(is_array($fields) && 0 < count($fields)) :
			$container = Container::make('post_meta', __('Sejoli Setup', 'sejoli'))
				->where( 'post_type', '=', 'sejoli-product')
				->set_classes('sejoli-metabox');

			foreach($fields as $field) :
				$container->add_tab($field['title'], $field['fields']);
			endforeach;
		endif;
	}

	/**
	 * Add custom columns to product columns
	 * Hooked via filter manage_sejoli-product_posts_columns, priority 50
	 * @since 	1.1.6
	 * @param	array $columns
	 * @return 	array
	 */
	public function add_product_columns(array $columns) {

		unset($columns['date']);

		$columns['sejoli-price']	= __('Harga', 'sejoli');

		return $columns;
	}

	/**
	 * Display custom columns data to product column
	 * Hooked via manage_posts_custom_column, priority 50
	 * @since 	1.1.6
	 * @param  	string 		$column
	 * @param  	integer 	$post_id
	 * @return 	void
	 */
	public function display_product_custom_columns($column, $post_id) {

		global $post;

		switch ( $column ) :

			case 'sejoli-price' :
				$price = apply_filters('sejoli/product/price', 0, get_post($post));
				echo sejolisa_price_format($price);
				break;

		endswitch;
	}

	/**
	 * Modify product title
	 * Hooked via filter display_post_states, priority 10
	 * @since 	1.1.6
	 * @param 	array 	$post_states
	 * @return 	array
	 */
	public function display_product_states(array $post_states) {

		global $post;

		if(!is_a($post, 'WP_Post') || !property_exists($post, 'post_type') || 'sejoli-product' !== $post->post_type) :
			return $post_states;
		endif;

		$is_closed = sejolisa_is_product_closed();

		if($is_closed) :
			$post_states[]	= __('Tutup', 'sejoli');
		endif;

		$product_type = sejolisa_carbon_get_post_meta($post->ID, 'product_type');

		if('digital' === $product_type) :
			$post_states[] = __('Non Fisik', 'sejoli');

			$payment_plan = sejolisa_carbon_get_post_meta($post->ID, 'payment_type');

			if('recurring' === $payment_plan) :
				$post_states[]	= __('Berlangganan', 'sejoli');
			endif;
		endif;

		return $post_states;
	}

	/**
	 * Generate coupon access
	 * @since 	1.1.6
	 * @param  	integer 	$product_id
	 * @return 	string
	 */
	protected function generate_coupon_access($product_id) {

		$product_id = intval($product_id);
		$product_id = (0 === $product_id) ? rand(0,20) : $product_id;
		$tokens     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$chars      = substr(str_shuffle($tokens), 0, $product_id);

		return substr($chars, 0, 6);
	}

	/**
	 * Display sale setting for product
	 * Hooked via filter sejoli/product/fields, priority 10
	 * @since 	1.0.0
	 * @since 	1.5.3		Add option to disable multi checkout for tryout page
	 * 						Add option to disable renew after x days expired
	 * @since 	1.5.3.3 	Add checkout time limitation
	 * @param  	array $fields
	 * @return 	array
	 */
	public function setup_sale_setting_fields($fields) {

		global $post;

		$product_id = (is_object($post) && property_exists($post, 'ID')) ? $post->ID : 0;


		// Set conditional fields
		$conditionals = [
			'subscription'	=> [
				[
					'field'	=> 'payment_type',
					'value'	=> 'recurring'
				],[
					'field' => 'product_type',
					'value' => 'digital'
				]
			],
			'subscription-tryout'	=> [
				[
					'field'	=> 'payment_type',
					'value'	=> 'recurring'
				],[
					'field' => 'product_type',
					'value' => 'digital'
				],[
					'field' => 'subscription_has_tryout',
					'value' => true
				]
			],
			'subscription-tryout-bump'	=> [
				[
					'field'	=> 'payment_type',
					'value'	=> 'recurring'
				],[
					'field' => 'product_type',
					'value' => 'digital'
				],[
					'field' => 'product_format',
					'value' => 'main-product'
				]
			],
			'subscription-signup'	=> [
				[
					'field'	=> 'payment_type',
					'value'	=> 'recurring'
				],[
					'field' => 'product_type',
					'value' => 'digital'
				],[
					'field' => 'subscription_has_signup_fee',
					'value' => true
				]
			]
		];

		// Set fields for Pengaturan Umum
		$fields['general'] = [
			'title'		=> __('Umum', 'sejoli'),
			'fields'	=> [
				Field::make( 'separator', 'sep_enable_sale', __('Pengaturan penjualan', 'sejoli'))
					->set_classes('sejoli-with-help'),

				Field::make( 'checkbox', 'enable_sale', __('Aktifkan penjualan produk ini', 'sejoli'))
					->set_option_value('yes')
					->set_default_value(true),

				Field::make( 'date_time', 'disable_sale_time', __('Waktu Penutupan penjualan produk'))
					->set_help_text(__('Isi jika anda akan menutup penjualan sesuai dengan waktu yang ditentukan. Jika ingin membuka penjualan kembali, kosongkan isian ini', 'sejoli')),

				Field::make( 'text', 'coupon_access_checkout', __('Kode untuk mengakses halaman checkout'))
					->set_default_value($this->generate_coupon_access($product_id))
					->set_help_text(__('Kode ini bisa diberikan ke user untuk bisa mengakses halaman checkout yang ditutup', 'sejoli'))
					->set_conditional_logic(array(
						'relation' => 'OR',
						array(
							'field'	=> 'enable_sale',
							'value'	=> false
						),
						array(
							'field'   => 'disable_sale_time',
							'value'   => array(""),
							'compare' => 'NOT IN'
						)
					)),

				Field::make( 'text', 'limit_buy_time', __('Limitasi pembelian selanjutnya (Dalam satuan menit)', 'sejoli'))
					->set_attribute('type', 'number')
					->set_default_value(5)
					->set_help_text(
						__('Isian ini bisa untuk mencegah invoice dari user yang sama terbuat berkali-kali. Sistem akan mengecek kapan terakhir kali user melakukan checkout dengan produk yang sama. <br /><br />Contohnya jika diisi dengan nilai 10, maka sistem akan mengecek apakah dalam 10 menit yang lalu user telah membeli produk yang sama. Jika iya, maka user tidak bisa melakukan checkout', 'sejoli')
					),

				Field::make( 'separator', 'sep_product_opt', __('Pengaturan Tipe Produk', 'sejoli'))
					->set_classes('sejoli-with-help'),

				Field::make( 'radio', 'product_type', __('Tipe Produk', 'sejoli'))
					->set_options([
						'digital'	=> __('Produk digital' ,'sejoli'),
						'physical'	=> __('Produk fisik', 'sejoli')
					])
					->set_default_value('digital')
					->set_width(50),

				Field::make( 'radio', 'payment_type', __('Tipe Pembayaran', 'sejoli'))
					->set_options([
						'one-time'	=> __('Sekali Bayar', 'sejoli'),
						'recurring'	=> __('Berlangganan', 'sejoli')
					])
					->set_default_value('one-time')
					->set_conditional_logic([
						[
							'field' => 'product_type',
							'value' => 'digital'
						]
					])
					->set_width(50),

				Field::make( 'radio', 'product_format', __('Format Produk', 'sejoli'))
					->set_options([
						'main-product' => __('Main Produk' ,'sejoli'),
						'bump-product' => __('Bump Produk', 'sejoli')
					])
					->set_default_value('main-product')
					->set_width(50)
					->set_conditional_logic([
						[
							'field' => 'product_type',
							'value' => 'digital'
						]
					]),

				Field::make( 'text', 'price', __('Harga Satuan', 'sejoli'))
					->set_attribute('type', 'number')
					->set_attribute('min', 0)
					->set_attribute('step', 'any')
					->set_required(true),

				Field::make( 'checkbox', 'enable_ppn', __('Mengaktifkan PPN', 'sejoli'))
					->set_option_value('no')
					->set_help_text(__('Dengan mengaktifkan opsi ini, akan menampilkan nominal ppn di halaman checkout', 'sejoli')),

				Field::make( 'checkbox', 'enable_quantity', __('Mengaktifkan pembelian secara kuantitas', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'product_format',
							'value' => 'main-product'
						]
					])
					->set_option_value('yes')
					->set_help_text(__('Dengan mengaktifkan opsi ini, akan menampilkan isian jumlah pembelian di halaman checkout', 'sejoli')),
					// ->set_conditional_logic([
					// 	[
					// 		'field'   => 'payment_type',
					// 		'value'   => ['recurring'],
					// 		'compare' => 'NOT IN'
					// 	]
					// ]),

				Field::make( 'select', 'dimesale', __('Kenaikan Harga', 'sejoli'))
					->set_options([
						''                 => __('Tidak ada kenaikan harga', 'sejoli'),
						'dimesale-by-sale' => __('Kenaikan harga berdasarkan jumlah order'),
						'dimesale-by-time' => __('Kenaikan harga berdasarkan waktu')
					])
					->set_conditional_logic([
						'relation'	=> 'OR',
						[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				// Dimesale setup
				Field::make( 'separator', 'sep_dimesale', __('Pengaturan kenaikan harga', 'sejoli'))
					->set_conditional_logic([
						[
							'field'   => 'dimesale',
							'value'   => '',
							'compare' => '!='
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					])
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('dimesale') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make( 'text', 'max_dimesale_price', __('Maksimal Harga', 'sejoli'))
					->set_attribute('type', 'number')
					->set_attribute('min', 0)
					->set_attribute('step', 'any')
					->set_help_text(__('Kosongkan atau isi dengan 0 jika tidak ada maksimal harga', 'sejoli'))
					->set_conditional_logic([
						[
							'field'   => 'dimesale',
							'value'   => '',
							'compare' => '!='
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'html', 'dimesale_by_sale_help', __('Help', 'sejoli'))
					->set_html('<p>Jika anda ingin kenaikan harga sebesar Rp. 5000 per 3 penjualan. <br />Maka yang perlu diisi adalah <br /><strong>Kenaikan sebesar</strong> : 5000<br /><strong>Per X penjualan</strong> : 3</p>')
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-sale'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'text', 'dimesale_by_sale_price_step', __('Kenaikan sebesar', 'sejoli'))
					->set_attribute('type', 'number')
					->set_attribute('min', 1)
					->set_default_value(5000)
					->set_attribute('step', 'any')
					->set_required(true)
					->set_help_text(__('Besar kenaikan per X penjualan', 'sejoli'))
					->set_width(50)
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-sale'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'text', 'dimesale_by_sale_step', __('Per X Penjualan', 'sejoli'))
					->set_attribute('type', 'number')
					->set_attribute('min', 1)
					->set_default_value(3)
					->set_attribute('step', 'any')
					->set_required(true)
					->set_help_text(__('Besar kenaikan per X penjualan', 'sejoli'))
					->set_width(50)
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-sale'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'checkbox', 'dimesale_by_sale_calculate_completed_only', __('Hitung jumlah penjualan berdasarkan status COMPLETED saja', 'sejoli'))
					->set_option_value('yes')
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-sale'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'html', 'dimesale_by_time_help', __('Help', 'sejoli'))
					->set_html('<p>Jika anda ingin kenaikan harga sebesar Rp. 15000 per 3 hari. <br />Maka yang perlu diisi adalah <br /><strong>Kenaikan sebesar</strong> : 15000<br /><strong>Per X jam</strong> : 72</p>')
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-time'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'text', 'dimesale_by_time_price_step', __('Kenaikan sebesar', 'sejoli'))
					->set_attribute('type', 'number')
					->set_attribute('min', 1)
					->set_default_value(15000)
					->set_attribute('step', 'any')
					->set_required(true)
					->set_help_text(__('Besar kenaikan per X jam', 'sejoli'))
					->set_width(50)
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-time'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'text', 'dimesale_by_time_step', __('Per X jam', 'sejoli'))
					->set_attribute('type', 'number')
					->set_attribute('step', 'any')
					->set_attribute('min', 1)
					->set_default_value(7)
					->set_required(true)
					->set_help_text(__('Dalam satuan jam', 'sejoli'))
					->set_width(50)
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-time'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'date_time', 'dimesale_by_time_start', __('Waktu mulai kenaikan harga', 'sejoli'))
					->set_input_format('Y-m-d H:i:s', 'Y-m-d H:i:S')
					->set_help_text( __('Kosongkan jika kenaikan dimulai dari produk ini dibuat. Waktu disesuaikan dengan waktu server', 'sejoli'))
					->set_width(50)
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-time'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				Field::make( 'date_time', 'dimesale_by_time_end', __('Waktu berakhir kenaikan harga', 'sejoli'))
					->set_input_format('Y-m-d H:i:s', 'Y-m-d H:i:S')
					->set_help_text( __('Kosongkan jika tidak ada batasan waktu. Waktu disesuaikan dengan waktu server', 'sejoli'))
					->set_width(50)
					->set_conditional_logic([
						[
							'field' => 'dimesale',
							'value' => 'dimesale-by-time'
						],[
							'field' => 'payment_type',
							'value'	=> 'one-time'
						]
					]),

				// Subscription setting
				Field::make( 'separator', 'sep_subscription' , __('Pengaturan Berlangganan', 'sejoli'))
					->set_conditional_logic($conditionals['subscription'])
					->set_classes('sejoli-with-help'),

				Field::make( 'text', 'subscription_duration', __('Durasi Waktu', 'sejoli'))
					->set_conditional_logic($conditionals['subscription'])
					->set_attribute('type', 'number')
					->set_attribute('min', 1)
					->set_default_value(30)
					->set_required(true)
					->set_width(50),

				Field::make( 'select', 'subscription_period' , __('Tipe Periode', 'sejoli'))
					->set_options([
						'daily'   => __('Harian' ,'sejoli'),
						'monthly' => __('Bulanan', 'sejoli'),
						'yearly'  => __('Tahunan', 'sejoli')
					])
					->set_conditional_logic($conditionals['subscription'])
					->set_width(30),

				Field::make( 'text', 'subscription_max_renewal_days' , __('Masa habis berlakunya pembaharuan dalam satuan hari', 'sejoli'))
					->set_attribute('type', 'number')
					->set_attribute('min', 0)
					->set_default_value(0)
					->set_conditional_logic($conditionals['subscription'])
					->set_help_text( __('Jika diisi dengan nilai lebih dari 0, maka sistem secara otomatis akan meniadakan pembaharuan langganan jika melewati lama hari yang telah dilakukan .', 'sejowoo')),

				// Tryout fee setting

				// Tryout subscription setting
				Field::make( 'separator', 'sep_subscription_tryout', __('Tryout', 'sejoli'))
					->set_conditional_logic($conditionals['subscription-tryout-bump']),

				Field::make( 'checkbox', 'subscription_has_tryout', __('Ada Tryout', 'sejoli'))
					->set_option_value('yes')
					->set_width(30)
					->set_conditional_logic($conditionals['subscription-tryout-bump']),

				Field::make( 'checkbox', 'subscription_tryout_first_time_only' , __('Tryout hanya sekali', 'sejoli'))
					->set_conditional_logic($conditionals['subscription-tryout'])
					->set_width(70)
					->set_help_text( __('Jika fitur ini diaktifkan maka tryout hanya boleh diorder sekali saja per akun', 'sejoli') ),

				Field::make( 'text', 'subscription_tryout_duration' , __('Durasi', 'sejoli'))
					->set_conditional_logic($conditionals['subscription-tryout'])
					->set_attribute('type', 'number')
					->set_attribute('min', 1)
					->set_default_value(30)
					->set_width(60)
					->set_required(true)
					->set_help_text(__('Jika tryout diaktifkan, selama masa tryout tidak akan dikenakan biaya', 'sejoli')),

				Field::make( 'select', 'subscription_tryout_period' , __('Periode', 'sejoli'))
					->set_conditional_logic($conditionals['subscription-tryout'])
					->set_options([
						'daily'   => __('Hari' ,'sejoli'),
						'monthly' => __('Bulan', 'sejoli'),
						'yearly'  => __('Tahun', 'sejoli')
					])
					->set_width(40)
					->set_required(true),


				// Sign up fee setting
				Field::make( 'separator' ,'sep_subscription_signup', 'Biaya Awal Langganan ')
					->set_conditional_logic($conditionals['subscription']),

				Field::make( 'checkbox', 'subscription_has_signup_fee', __('Ada Biaya Awal', 'sejoli'))
					->set_option_value('yes')
					->set_width(30)
					->set_conditional_logic($conditionals['subscription']),

				Field::make( 'text', 'subscription_signup_fee', __('Biaya Awal', 'sejoli'))
					->set_conditional_logic($conditionals['subscription-signup'])
					->set_attribute('type', 'number')
					->set_attribute('step', 'any')
					->set_attribute('min', 1)
					->set_required(true)
					->set_width(70)
					->set_help_text(__('Biaya ini akan ditambahkan ke harga satuan produk', 'sejoli')),

			]
		];

		return $fields;
	}

	/**
	 * Setup product meta data
	 * Hooked via filter sejoli/product/meta-data, filter 999
	 * @param  WP_Post $product
	 * @param  int     $product_id
	 * @return WP_Post
	 */
	public function setup_product_meta(\WP_Post $product, int $product_id) {

		$product->active            = boolval(sejolisa_carbon_get_post_meta($product_id, 'enable_sale'));
		$product->disable_sale_time = sejolisa_carbon_get_post_meta($product_id, 'disable_sale_time');
		$product->price             = floatval(apply_filters( 'sejoli/product/price', 0, $product));
		$product->type              = sejolisa_carbon_get_post_meta($product_id, 'product_type');
		$product->enable_quantity   = boolval(sejolisa_carbon_get_post_meta($product_id, 'enable_quantity'));
		$product->enable_ppn   		= boolval(sejolisa_carbon_get_post_meta($product_id, 'enable_ppn'));
		$product->access_code 		= sejolisa_carbon_get_post_meta($product_id, 'coupon_access_checkout');
		$product->bump_product 		= sejolisa_carbon_get_post_meta($product_id, 'sejoli_bump_sales');

		return $product;
	}

	/**
	 * Validate product when checkout
	 * Hooked via filter sejoli/checkout/is-product-valid, priority 1
	 * @since  	1.0.0
	 * @since 	1.4.1.2			Fix problem with access code, reason : i don't know
	 * @param  	bool    		$valid
	 * @param  	mixed|WP_Post 	$product
	 * @param 	bool 			$is_subscription 	Check if current is from subscription renewal form or no
	 * @return 	bool
	 */
	public function validate_product_when_checkout(bool $valid, $product, $is_subscription = false) {

		if( false !== $is_subscription ) :
			return $valid;
		endif;

		if(!is_a($product,'WP_Post')) :
			$valid = false;
			sejolisa_set_message(__('Data produk tidak ada', 'sejoli'));

		elseif('sejoli-product' !== $product->post_type) :
			$valid = false;
			sejolisa_set_message(__('Data produk tidak valid', 'sejoli'));

		elseif(false !== sejolisa_is_product_closed($product->ID)) :

			$valid = false;
			sejolisa_set_message(__('Penjualan produk ini sudah ditutup', 'sejoli'));

		endif;

		return $valid;
	}

	/**
	 * Check previous order data
	 * Hooked via filter sejoli/checkout/is-product-valid, priority 10
	 * @since 	1.5.3
	 * @param  	bool   		$valid
	 * @param  	WP_Product 	$product
	 * @param  	array 		$post_data
	 * @return 	bool
	 */
	public function check_previous_order_when_checkout( bool $valid, $product, $post_data ) {

		if( array_key_exists('user_id', $post_data) ) :

			$user_id = ( !empty($post_data['user_id'] ) ) ? $post_data['user_id'] : get_current_user_id();

			$order   = sejolisa_get_previous_order( $product->ID, $user_id);

			if( false !== $order ) :

				$valid = false;

				sejolisa_set_message(
					sprintf(
						__('Anda sudah pernah order produk ini dengan nomor invoice %s dan belum diselesaikan.', 'sejoli'),
						$order->ID
					)
				);

				sejolisa_set_message(
					__('Silahkan cek email anda untuk melihat instruksi pembayaran', 'sejoli')
				);

			endif;

		endif;

		return $valid;
	}

	/**
	 * Set product data to order
	 * Hooked via filter sejoli/order/order-detail, priority 20
	 * @since 	1.0.0
	 * @param 	array $order_detail
	 * @return 	array
	 */
	public function set_product_data_to_order_detail(array $order_detail) {

		$product_id              = intval($order_detail['product_id']);
		$order_detail['product'] = sejolisa_get_product( $product_id );

		return $order_detail;
	}
}
