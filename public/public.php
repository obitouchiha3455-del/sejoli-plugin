<?php

namespace SejoliSA;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli
 * @subpackage Sejoli/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Sejoli
 * @subpackage Sejoli/public
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Front {

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
	 * List of CSS that will be enabled in custom page
	 * @var array
	 */
	private $css_enabled = [
		'key' => [],
		'src' => []
	];

	/**
	 * List of JS that will be enabled in custom page
	 * @var array
	 */
	private $js_enabled = [
		'key' => [],
		'src' => []
	];

	/**
	 * List of messages
	 * @var array
	 */
	private $messages = [
		'info'    => [],
		'warning' => [],
		'error'   => [],
		'success' => []
	];

	/**
	 * Enable semantic theme
	 * @since 	1.1.7
	 * @var 	boolean
	 */
	protected $enable_semantic = false;

	/**
	 * Set post type that Sejoli Member Area tempate page cant be used in
	 * @since 	1.3.2
	 * @var 	array
	 */
	protected $exclude_post_types = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @since 	1.3.2 	  Initialized exclude post types variable for Sejoli Member Page use
	 * @param   string    $plugin_name      The name of the plugin.
	 * @param   string    $version    		The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name        = $plugin_name;
		$this->version            = $version;
		$this->exclude_post_types = array(
			SEJOLI_PRODUCT_CPT
		);

		$this->enable_css_and_js();
	}

	/**
	 * Give permission to selected CSS and JS
	 * @return void
	 */
	private function enable_css_and_js()
	{
		$this->css_enabled = [
			'src' => [
				// 'plugins/woocommerce',
				'wp-includes',
				'wp-admin'
			],
			'key' => [
				// /'wc-'
			]
		];

		$this->js_enabled = [
			'src' => [
				// 'woocommerce',
				'wp-includes',
				'wp-admin'
			],
			'key' => [
				// 'wc-'
			]
		];
	}

	/**
	 * Register CSS Files.
	 * Hooked via action hook wp_enqueue_scripts, priority 999
	 * @since 	1.0.0 	Initialization
	 * @since 	1.1.7 	Enable semantic
	 * @since 	1.6.2 	Add JSTree for affiliasi-network page
	 * @return void
	 */
	public function enqueue_styles()
	{
		$enable_semantic = apply_filters('sejoli/enable', $this->enable_semantic);

		if(true === $enable_semantic) :
			wp_register_style	('semantic-ui',			'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css',[],'2.4.1','all');
			wp_enqueue_style	($this->plugin_name, 	plugin_dir_url( __FILE__ ) . 'css/style.css', ['semantic-ui'], $this->version, 'all' );
		endif;

		wp_register_style( 'select2', 					'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css', [], '4.0.10', 'all' );
		wp_register_style( 'daterangepicker', 			'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css', [], '3.0.5', 'all' );
		wp_register_style( 'datatables-semantic-ui',	'https://cdn.datatables.net/1.10.19/css/dataTables.semanticui.min.css', ['semantic-ui'], '1.10.19', 'all' );
		wp_register_style( 'sejoli-member-area', 		plugin_dir_url( __FILE__ ) . 'css/sejoli-member-area.css', [], $this->version, 'all' );
		wp_register_style( 'sejoli-dashboard', 			plugin_dir_url( __FILE__ ) . 'css/home.css', [], $this->version, 'all' );
		wp_register_style( 'jstree', 					'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.10/themes/default/style.min.css', '3.3.10' );

		// enqueue jstree style di sini


		if ( sejolisa_is_member_area_page() ) :
			wp_enqueue_style( 'daterangepicker' );
			wp_enqueue_style( 'datatables-semantic-ui' );
			wp_enqueue_style( 'sejoli-member-area' );
			wp_enqueue_style( 'select2' );
		endif;

		if( sejolisa_is_member_area_page('home') ) :
			wp_enqueue_style( 'sejoli-dashboard' );
		endif;

		if( sejolisa_is_member_area_page('affiliasi-network') )  :
			wp_enqueue_style( 'jstree' );
		endif;

		if(true === $enable_semantic) :
			wp_enqueue_style( 'sejoli-member-area' );
		endif;

	}

	/**
	 * Register JS Files.
	 * Hooked via action hook wp_enqueue_scripts, priority 999
	 * @since 	1.0.0 	initialization
	 * @since 	1.1.7 	Enable semantic
	 * @since 	1.6.2 	Add JSTree for affiliasi-network page
	 * @return void
	 */
	public function enqueue_scripts()
	{
		$enable_semantic = apply_filters('sejoli/enable', $this->enable_semantic);

		if(true === $enable_semantic) :
			wp_register_script	('semantic-ui',			'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js',['jquery'],'2.4.1',false);
			wp_enqueue_script	($this->plugin_name, 	plugin_dir_url( __FILE__ ) . 'js/sejoli-public.js', ['semantic-ui','jquery'], $this->version, false );
		endif;

		wp_register_script( 'tinymce', 			 	 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.0.15/tinymce.min.js', ['jquery'], NULL, false );
		wp_register_script( 'select2', 			 	 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js', ['jquery'], NULL, false );
		wp_register_script( 'moment-js', 			 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/moment.min.js', ['jquery'], NULL, false );
		wp_register_script( 'daterangepicker', 	     'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js', ['jquery'], NULL, false );
		wp_register_script( 'datatables', 			 'https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', ['jquery'], NULL, false );
		wp_register_script( 'datatables-semantic-ui','https://cdn.datatables.net/1.10.16/js/dataTables.semanticui.min.js', ['jquery'], NULL, false );
		wp_register_script( 'chartjs', 				 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js', [], NULL, false );
		wp_register_script( 'jsrender', 			 'https://cdnjs.cloudflare.com/ajax/libs/jsrender/0.9.90/jsrender.min.js', [], NULL, false );
		wp_register_script( 'clipboardjs', 			 'https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js', [], NULL, false );
		wp_register_script( 'blockUI', 			 	 'https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js', [], NULL, false );
		wp_register_script( 'sejoli-member-area', 	 plugin_dir_url( __FILE__ ) . 'js/sejoli-member-area.js', ['semantic-ui','jquery'], $this->version, false );
		wp_register_script( 'jstree',				'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.10/jstree.min.js', array('jquery'), '3.3.10', true);

		wp_localize_script( "datatables", "dataTableTranslation", array(
			"all"			 => __('Semua','sejoli'),
			"decimal"        => ",",
			"emptyTable"     => __("Tidak ada data yang bisa ditampilkan","sejoli"),
			"info"           => __("Menampikan _START_ ke _END_ dari _TOTAL_ data","sejoli"),
			"infoEmpty"      => __("Menampikan 0 ke 0 dari 0 data","sejoli"),
			"infoFiltered"   => __("Menyaring dari total _MAX_ data","sejoli"),
			"infoPostFix"    => "",
			"thousands"      => ".",
			"lengthMenu"     => __("Menampilkan _MENU_ data","sejoli"),
			"loadingRecords" => __("Mengambil data...","sejoli"),
			"processing"     => __("Memproses data...","sejoli"),
			"search"         => __("Cari data :","sejoli"),
			"zeroRecords"    => __("Tidak ditemukan data yang sesuai","sejoli"),
			"paginate"       =>
				array(
				"first"    => __("Pertama","sejoli"),
				"last"     => __("Terakhir","sejoli"),
				"next"     => __("Selanjutnya","sejoli"),
				"previous" => __("Sebelumnya","sejoli")
			),
			"aria"           => array(
				"sortAscending"  => __("Klik untuk mengurutkan kolom naik","sejoli"),
				"sortDescending" => __("Klik untuk mengurutkan kolom turun","sejoli")
			)
		));

		wp_localize_script( 'sejoli-member-area', 'sejoli_member_area', array(
			'ajax_nonce' 						=> wp_create_nonce('ajax-nonce'),
			'ajaxurl'							=>	admin_url( 'admin-ajax.php' ),
			'ajax_url' 							=> site_url('/'),
			'get_commission'              		=> site_url( 'sejoli-ajax/get-commission/' ),
			'get_commission_detail'		  		=> site_url( 'sejoli-ajax/get-commission-detail/' ),
			'get_affiliate_link'          		=> site_url( 'sejoli-ajax/get-affiliate-link/' ),
			'get_affiliate_help'          		=> site_url( 'sejoli-ajax/get-affiliate-help/' ),
			'get_affiliate_help_detail'	  		=> site_url( 'sejoli-ajax/get-affiliate-help-detail/' ),
			'get_affiliate_coupon_user'	  		=> site_url( 'sejoli-ajax/get-affiliate-coupon-user/' ),
			'get_coupon_parent_list_select2'	=> site_url( 'sejoli-ajax/get-affiliate-coupon-parent-list/' ),
			'get_affiliate_order'	  	  		=> site_url( 'sejoli-ajax/get-affiliate-order/' ),
			'add_affiliate_coupon_user'	  		=> site_url( 'sejoli-ajax/add-affiliate-coupon-user/' ),
			'get_order_detail'	  	  	  		=> site_url( 'sejoli-ajax/get-order-detail/' ),
			'get_leaderboard'                 	=> site_url( 'sejoli-ajax/get-leaderboard/' ),
			'get_subdistrict' 					=> wp_create_nonce('sejoli-checkout-ajax-get-subdistrict'),
			'update_profile'					=> site_url( 'sejoli-ajax/update-profile/' ),
			'text' => [
				'main'     => __('Pengaturan', 'sejoli'),
				'currency' => sejolisa_currency_format(),
				'status'   => [
					'pending'  => __('Belum Aktif', 'sejoli'),
					'inactive' => __('Tidak Aktif', 'sejoli'),
					'active'   => __('Aktif', 'sejoli'),
					'expired'  => __('Berakhir', 'sejoli')
				]
			],
			'commission' => [
				'status' => [
					'pending'	=> __('Order belum selesai', 'sejoli'),
					'added'		=> __('Belum dibayar', 'sejoli'),
					'cancelled' => __('Dibatalkan', 'sejoli'),
					'paid'		=> __('Sudah dibayar', 'sejoli')
				],
				'table' => [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-commission-table'
					], admin_url('admin-ajax.php')),
					'nonce' => wp_create_nonce('sejoli-render-commission-table')
				],
			],
			'color' => sejolisa_get_all_colors(),
			'affiliate' => [
				'order' => [
					'nonce' => wp_create_nonce('sejoli-list-orders')
				],
				'export_prepare'   =>  [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-order-export-prepare'
					], admin_url('admin-ajax.php')),
					'nonce' => wp_create_nonce('sejoli-order-export-prepare')
				],
				'link' => [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-product-affiliate-link-list'
					], admin_url('admin-ajax.php')),
					'nonce' => wp_create_nonce('sejoli-list-product-affiliate-link')
				],
				'help' => [
					'nonce' => wp_create_nonce('sejoli-list-product-affiliate-help'),
				],
				'bonus_editor' => [
					'get' => [
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-affiliate-get-bonus-content'
						], admin_url('admin-ajax.php')),
						'nonce' => wp_create_nonce('sejoli-affiliate-bonus-content'),
					],
					'update' => [
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-affiliate-update-bonus-content'
						], admin_url('admin-ajax.php')),
						'nonce' => wp_create_nonce('sejoli-affiliate-bonus-content'),
					]
				],

				'network' => [
					'list' =>[
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-affiliate-get-network-list',
							'nonce' => wp_create_nonce('sejoli-affiliate-get-network-list')
						], admin_url('admin-ajax.php')),
					],
					'detail' =>[
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-affiliate-get-network-detail',
							'nonce' => wp_create_nonce('sejoli-affiliate-get-network-detail')
						], admin_url('admin-ajax.php')),
					],

				],

				'facebook_pixel' => [
					'get' => [
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-affiliate-get-facebook-pixel'
						], admin_url('admin-ajax.php')),
						'nonce' => wp_create_nonce('sejoli-affiliate-get-facebook-pixel'),
					],
					'update' => [
						'nonce' => wp_create_nonce('sejoli-affiliate-update-facebook-pixel'),
					]
				],

				'facebook_conversion' => [
					'get' => [
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-affiliate-get-facebook-conversion'
						], admin_url('admin-ajax.php')),
						'nonce' => wp_create_nonce('sejoli-affiliate-get-facebook-conversion'),
					],
					'update' => [
						'nonce' => wp_create_nonce('sejoli-affiliate-update-facebook-conversion'),
					]
				],

				'tiktok_conversion' => [
					'get' => [
						'ajaxurl' => add_query_arg([
							'action' => 'sejoli-affiliate-get-tiktok-conversion'
						], admin_url('admin-ajax.php')),
						'nonce' => wp_create_nonce('sejoli-affiliate-get-tiktok-conversion'),
					],
					'update' => [
						'nonce' => wp_create_nonce('sejoli-affiliate-update-tiktok-conversion'),
					]
				]
			],

			'order' => [
				'table' => [
					'nonce' => wp_create_nonce('sejoli-list-orders')
				],
				'detail' => [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-order-detail'
					], admin_url('admin-ajax.php')),
					'nonce' => wp_create_nonce('sejoli-order-detail')
				],
				'status' => apply_filters('sejoli/order/status', [])
			],
			'subscription' => [
				'table' => [
					'nonce' => wp_create_nonce('sejoli-list-subscriptions')
				],
				'detail' => [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-order-detail'
					], admin_url('admin-ajax.php')),
					'nonce' => wp_create_nonce('sejoli-order-detail')
				],
				'status' => apply_filters('sejoli/order/status', [])
			],
			'subscription' => [
				'type' => [
					'subscription-tryout'  => 'tryout',
					'subscription-signup'  => 'signup',
					'subscription-regular' => 'regular',
				]
			],
			'coupon' => [
				'table' => [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-coupon-table'
					], admin_url('admin-ajax.php')),
					'nonce' => wp_create_nonce('sejoli-render-coupon-table')
				],
				'list' => [
					'nonce' => wp_create_nonce('sejoli-list-parent-coupons'),
				],
				'add' => [
					'nonce' => wp_create_nonce('sejoli-create-affiliate-coupon'),
				]
			],
			'product' => [
				'table' => [
					'nonce' => wp_create_nonce('sejoli-product-table')
				],
				'select' => [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-product-options',
					], admin_url('admin-ajax.php')),
					'nonce' => wp_create_nonce('sejoli-render-product-options')
				],
				'placeholder' => __('Pencarian produk', 'sejoli')
			],
			'leaderboard' => [
				'nonce' => wp_create_nonce('sejoli-statistic-get-commission-data')
			],
			'akses' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-access-list-by-product',
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-access-list-by-product'),
			],
			'bonus'	=> [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-access-get-bonus',
				], admin_url('admin-ajax.php')),
				'nonce'	=> wp_create_nonce('sejoli-access-bonus')
			],
			'district_select' => __('Silakan Ketik Nama Kecamatannya', 'sejoli'),
			'coupon_select'   => __('--Pilih Kupon Utama--', 'sejoli')
		) );

		if ( sejolisa_is_member_area_page() ) :

			wp_enqueue_script( 'tinymce' );
			wp_enqueue_script( 'moment-js' );
			wp_enqueue_script( 'daterangepicker' );
			wp_enqueue_script( 'datatables' );
			wp_enqueue_script( 'datatables-semantic-ui' );
			wp_enqueue_script( 'chartjs' );
			wp_enqueue_script( 'jsrender' );
			wp_enqueue_script( 'clipboardjs' );
			wp_enqueue_script( 'blockUI' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'jstree' );
			wp_enqueue_script( 'sejoli-member-area' );

		endif;


		if( sejolisa_is_member_area_page('affiliasi-network') )  :
			wp_enqueue_script( 'jstree' );
		endif;

		if($enable_semantic) :
			wp_enqueue_script( 'sejoli-member-area' );
			wp_enqueue_script( $this->plugin_name );
		endif;
	}

	/**
	 * Set messages
	 * Hooked via filter sejoli/set-messages, priority 999
	 * @param 	array 	$messages
	 * @param 	string  $info
	 * @return	array 	$messages
	 */
	public function set_messages($messages,$type = 'info')
	{
		global $sejoli;

		$this->messages[$type] += $messages;

		$sejoli['messages']	= $this->messages;
	}

	/**
	 * Add query vars for handle __sejoli_ajax_action.
	 * Hooked via filter query_vars, priority 100
	 * @return void
	 */
	public function add_query_vars($vars){
		$vars[] = '__sejoli_ajax_action';
		return $vars;
	}

	/**
	 * Add endpoint.
	 * Hooked via action init, priority 100
	 * @return void
	 */
	public function add_endpoint() {
		add_rewrite_rule('^sejoli-ajax/([^/]+)/?$','index.php?__sejoli_ajax_action=$matches[1]','top');
	}

	/**
	 * Add ajax action
	 * Hooked via action parse_request, priority 100
	 *
	 * @return void
	 */
	public function add_ajax_action()
	{
		global $wp;

		if ( ! isset( $wp->query_vars['__sejoli_ajax_action'] ) )
			return;

		do_action( 'sejoli_ajax_' . $wp->query_vars['__sejoli_ajax_action'] );

		exit;
	}

	/**
	 * Force user when access homepage to member area
	 * Hooked via action template_redirect, priority 100
	 * @since 	1.1.1
	 * @since 	1.4.3 	Set with no parameter in sejolisa_verify_checkout_page
	 * @return 	void
	 */
	public function redirect_to_member_area() {

		global $wp_query;

		$force_redirect = boolval(sejolisa_carbon_get_theme_option('sejoli_homepage_member_redirect'));

		if(
			$force_redirect &&
			( is_home() || is_front_page() ) &&
			! isset($wp_query->query['member']) &&
			! sejolisa_verify_checkout_page ( array( 'loading', 'renew', 'thank-you') )
		) :
			wp_redirect(home_url('/member-area/'));
			exit;
		endif;

	}

	/**
	 * Change redirect member-area into alihkan user setelah login url
	 * Hooked via action template_redirect, priority 100
	 * @since 	1.12.1
	 * @return 	void
	 */
	public function redirect_member_area_alihkan_url_setelah_login() {

		$keep_dashboard_statistic = boolval(sejolisa_carbon_get_theme_option('sejoli_keep_dashboard_statistic'));
    	$redirected_url = esc_url(sejolisa_carbon_get_theme_option('sejoli_after_login_redirect'));
    	$redirected_url = (!empty($redirected_url)) ? $redirected_url : '';

    	if( false === $keep_dashboard_statistic && !empty($redirected_url) ) :

		    if( is_user_logged_in() ) :

			    global $wp_query;

			    $get_member = isset($wp_query->query['member']) ? $wp_query->query['member'] : '';
			    $get_view   = isset($wp_query->query['view']) ? $wp_query->query['view'] : '';

			    if( $get_member === '1' && $get_view === "home" ):


			    	if( !empty($redirected_url) ):

				    	wp_redirect($redirected_url);
				    	exit;

			    	endif;

			    endif;

		   	endif;

		endif;

	}

	/**
	 * Check if current page is using sejoli-member-page.php
	 * Hooked via action template_redirect, priority 200
	 * @since 	1.1.7
	 * @since 	1.3.2 	Add conditional to check if current post type is able to use Sejoli Member Page template
	 * @since 	1.3.3 	Add more conditional by check if current page is a login or a checkout page
	 * @since 	1.4.3	Add conditiona by check if current page is a register page, add check if $post is_a WP_Post
	 * @return 	void
	 */
	public function check_using_member_page() {

		global $post;

		if(is_a($post, 'WP_Post') && property_exists($post, 'ID')) :

			$page_template = get_post_meta($post->ID, '_wp_page_template', true);

			// Return default template if we don't have a custom one defined
			if(
				!sejoli_is_a_member_page('login') &&
				!sejoli_is_a_member_page('register') &&
				!sejolisa_verify_checkout_page ( array( 'loading', 'renew', 'thank-you') ) &&
				'sejoli-member-page.php' === $page_template &&
			    !in_array($post->post_type, $this->exclude_post_types)) :

				if( !is_user_logged_in() ) :
					wp_die(
						sprintf(
							__('Anda harus login terlebih dahulu untuk mengakses halaman ini.<br /> Silahkan login melalui link <a href="%s">ini</a>', 'login'),
							site_url('member-area/login')
						),
						__('Anda tidak diperbolehkan mengakses halaman ini', 'sejoli')
					);

					exit;

				endif;

				$this->enable_semantic = true;
			endif;

		endif;
	}

	/**
	 * Check if current page is using sejoli-member-page.php
	 * Hooked via filter template_include, priority 10
	 * @since 	1.1.7
	 * @since 	1.3.0 	Change priority from 1 to 10
	 * @param  	string	$template	Template file
	 * @return 	string
	 */
	public function view_member_template($template) {

		global $post;

		// Return template if post is empty
		if ( ! $post ) :
			return $template;
		endif;

		// Return default template if we don't have a custom one defined
		if(false !== $this->enable_semantic) :

			$template = SEJOLISA_DIR . 'template/member-template.php';

			return $template;
		endif;

		return $template;
	}

	/**
	 * Enable semantic
	 * Hooked via filter sejoli/enable, priority 100
	 * @since 	1.1.7
	 * @return 	boolean
	 */
	public function enable_semantic($enable_semantic) {
		return (true === $enable_semantic) ? true : $this->enable_semantic;
	}

	/**
	 * Set body clasess for Sejoli Member Area template
	 * Hooked via filter body_class, priority 999
	 * @since 	1.5.4.1
	 * @param 	array $classes
	 * @return 	array
	 */
	public function set_body_classes( array $classes ) {

		if(is_page_template('sejoli-member-page.php')) :
			$classes[] = 'sejoli';
		endif;

		return $classes;
	}

	/**
	 * Add inline CSS script
	 * Hooked via action wp_head, priority 100
	 * @since 	1.2.0
	 * @since 	1.5.4.1
	 * @return 	boolean
	 */
	public function add_inline_style() {

		?>
		<style media="screen">
			/* #wpadminbar { background-color: #179822 !important; } */
		</style>

		<?php
		if(
			(
				!sejoli_is_a_member_page() &&
				!is_page_template('sejoli-member-page.php')
			) ||
			true !== boolval(sejolisa_carbon_get_theme_option('activate_custom_member_area_style'))
		) :
			return;
		endif;
		?>

		<style media="screen">
			body.sejoli {
				background-color: <?php echo sejolisa_carbon_get_theme_option('member_area_bg_color'); ?>!important;
			}
			body.sejoli .sejolisa-memberarea-menu.ui.inverted.menu { background-color: <?php echo sejolisa_carbon_get_theme_option('member_area_sidebar_bg_color'); ?>!important; }
			body.sejoli .ui.inverted.menu .item,
			body.sejoli .ui.inverted.menu .item>a:not(.ui) {
				color: <?php echo sejolisa_carbon_get_theme_option('member_area_sidebar_link_color'); ?>!important;
				background-color: <?php echo sejolisa_carbon_get_theme_option('member_area_sidebar_menu_bg_color'); ?>!important;
			}

			body.sejoli .ui.inverted.menu .item:not(.header-menu):hover {
				color: <?php echo sejolisa_carbon_get_theme_option('member_area_sidebar_link_hover_color'); ?>!important;
				background-color: <?php echo sejolisa_carbon_get_theme_option('member_area_sidebar_menu_hover_bg_color'); ?>!important;
			}

			body.sejoli .ui.inverted.menu .active.item {
				color: <?php echo sejolisa_carbon_get_theme_option('member_area_sidebar_link_active_color'); ?>!important;
				background-color: <?php echo sejolisa_carbon_get_theme_option('member_area_sidebar_menu_active_bg_color'); ?>!important;
			}

			body.sejoli .master-menu {
				border-bottom-width: 0;
			}

			body.sejoli .four.cards .ui.card.orange .content {
				background-color: <?php echo sejolisa_carbon_get_theme_option('statistic_lead'); ?>!important;
				color: <?php echo sejolisa_carbon_get_theme_option('statistic_lead_color'); ?>!important;
			}

			body.sejoli .four.cards .ui.card.orange .content .header { color: <?php echo sejolisa_carbon_get_theme_option('statistic_lead_color'); ?>!important; }

			body.sejoli .four.cards .ui.card.green .content {
				background-color: <?php echo sejolisa_carbon_get_theme_option('statistic_sale'); ?>!important;
				color: <?php echo sejolisa_carbon_get_theme_option('statistic_sale_color'); ?>!important;
			}

			body.sejoli .four.cards .ui.card.green .content .header { color: <?php echo sejolisa_carbon_get_theme_option('statistic_sale_color'); ?>!important; }

			body.sejoli .four.cards .ui.card.blue .content {
				background-color: <?php echo sejolisa_carbon_get_theme_option('statistic_omset'); ?>!important;
				color: <?php echo sejolisa_carbon_get_theme_option('statistic_omset_color'); ?>!important;
			}

			body.sejoli .four.cards .ui.card.blue .content .header {	color: <?php echo sejolisa_carbon_get_theme_option('statistic_omset_color'); ?>!important; }

			body.sejoli .four.cards .ui.card.light-green .content {
				background-color: <?php echo sejolisa_carbon_get_theme_option('statistic_komisi'); ?>!important;
				color: <?php echo sejolisa_carbon_get_theme_option('statistic_komisi_color'); ?>!important;
			}

			body.sejoli .ui.cards.information.daily .content.value 	{ background-color:white!important; color: black!important; }

			body.sejoli .four.cards .ui.card.light-green .content .header { color: <?php echo sejolisa_carbon_get_theme_option('statistic_komisi_color'); ?>!important; }

			<?php echo sejolisa_carbon_get_theme_option('member_area_css'); ?>

		</style>
		<?php
	}
}
