<?php

namespace SejoliSA;

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
class Admin {

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
	 * Container from carbonfield
	 * @since   1.0.0
	 * @access 	protected
	 * @var 	Container
	 */
	protected $container;

	/**
	 * Current admin page
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	bool
	 */
	protected $is_sejoli_page = false;

	/**
	 * Enable post type for CSS and HS
	 * @since 	1.1.9
	 * @access 	protected
	 * @var 	array
	 */
	protected $enabled_post_type = array(
		'sejoli-product', 'sejoli-coupon', 'sejoli-access', 'sejoli-user-group', 'sejoli-memmessage', 'sejoli-reward'
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

	}

	/**
	 * Get all available user roles
	 * @since 	1.5.0
	 * @return 	array
	 */
	public function get_user_roles() {

		global $wp_roles;

		$roles = array();

		foreach($wp_roles->roles as $role => $detail) :

			if(
				!in_array(
					$role,
					array( 'administrator', 'sejoli-manager' )
				)
			) :

				$roles[$role] = $detail['name'];
			endif;

		endforeach;

		return $roles;
	}

	/**
	 * Load carbon fields library
	 * Hooked via after_setup_theme, prioritas 999
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function load_carbon_fields() {
		\Carbon_Fields\Carbon_Fields::boot();
	}

	/**
	 * Setup custom fields for product
	 * Hooked via action carbon_fields_register_fields, priority 999
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function setup_carbon_fields() {

		global $sejolisa;

		if(false === sejolisa_check_own_license()) :
			return;
		endif;


		$fields = apply_filters( 'sejoli/general/fields', []);

		if(is_array($fields) && 0 < count($fields)) :
			$this->container = Container::make('theme_options', __('Sejoli', 'sejoli'))
									->set_icon( plugin_dir_url( __FILE__ ) . 'images/icon.png')
									->set_page_menu_position( 2 )
									->set_classes('sejoli-metabox');

			foreach($fields as $field) :
				$this->container->add_tab($field['title'], $field['fields']);
			endforeach;
		endif;
	}

	/**
	 * Get container main setting
	 * Hooked via filter sejoli/general/container, prirority 1
	 * @param  string $container
	 * @return Container
	 */
	public function get_container($container = '') {
		return $this->container;
	}

	/**
	 * Add general menu for main setting
	 * Hooked via filter sejoli/general/fields, priority 10
	 * @since  	1.0.0
	 * @since 	1.5.0	Add option to set available user roles to access admin page
	 * @since 	1.6.0	Add option to disable log
	 * @param  	array 	$fields
	 * @return 	array
	 */
	public function setup_main_setting_fields(array $fields) {

		$fields[] = [
			'title'		=> __('Umum', 'sejoli'),
			'fields'	=> [

				Field::make('separator', 'sep_sejoli_setting',	__('Identifikasi', 'sejoli')),

				Field::make('image', 'sejoli_setting_logo', __('Logo', 'sejoli'))
					->set_help_text(__('Dianjurkan panjang logo tidak melebihi 480px dengan tinggi tidak lebih dari 300px', 'sejoli')),

				Field::make('image', 'sejoli_setting_member_area_logo', __('Member area logo', 'sejoli'))
					->set_help_text(__('Dianjurkan panjang logo tidak melebihi 240px dengan tinggi tidak lebih dari 120px', 'sejoli')),

				Field::make('separator', 'sep_sejoli_registration',	__('Pendaftaran', 'sejoli')),

				Field::make('html', 'sejoli_registration_info', __('Informasi', 'sejoli'))
					->set_html(
						sprintf(
							__('Pengaturan ini hanya akan berfungsi di halaman registrasi : <strong>%s</strong>', 'sejoli'),
							home_url('/member-area/register')
						)
					),

				Field::make('checkbox', 'sejoli_enable_registration', __('Aktifkan pendaftaran', 'sejoli'))
					->set_default_value(true)
					->set_help_text(
						sprintf(
							__('Halaman pendaftaran : <strong>%s</strong>', 'sejoli'),
							home_url('/member-area/register')
						)
					),

				Field::make('checkbox', 'sejoli_registration_display_username', __('Tampilkan field username', 'sejoli'))
					->set_option_value('yes')
					->set_default_value('yes')
					->set_help_text(__('Jika tidak ditampilkan, email user digunakan sebagai username', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'sejoli_enable_registration',
							'value'	=> true
						)
					)),

				Field::make('checkbox', 'sejoli_registration_display_password', __('Tampilkan field password', 'sejoli'))
					->set_option_value('yes')
					->set_help_text(__('Jika tidak ditampilkan, password akan dibuat secara acak oleh sistem', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'sejoli_enable_registration',
							'value'	=> true
						)
					)),

				Field::make('separator', 'sep_sejoli_information',	__('Lainnya', 'sejoli')),

				Field::make( 'radio', 'sejoli_currency_type', __('Mata Uang', 'sejoli') )
				    ->add_options( array(
				        'IDR' => 'Indonesia Rupiah (IDR - Rp.)',
				        'MYR' => 'Malaysia Ringgit (MYR - RM)',
				        'USD' => 'Dolar Amerika Serikat (USD - $)',
				    ) )
				    ->set_default_value('IDR'),


				Field::make('text',	'sejoli_currency_thousand', __('Pemisah Ribuan', 'sejoli'))
					->set_default_value('.'),

				Field::make('text',	'sejoli_currency_decimal', __('Pemisah Desimal', 'sejoli'))
					->set_default_value(','),

				Field::make('text',	'sejoli_currency_number_of_decimals', __('Jumlah Desimal', 'sejoli'))
					->set_default_value('0'),

				Field::make('text',	'sejoli_ppn_price', __('PPN (%)', 'sejoli'))
					->set_attribute('type', 'number')
					->set_help_text(__('Masukan nilai PPN yang Anda inginkan', 'sejoli')),

				Field::make('checkbox', 'sejoli_affiliate_tool_data_kontak_buyer_order_detail', __('Tampilkan detail kontak pembeli di detail pesanan', 'sejoli'))
					->set_option_value('yes')
					->set_default_value(true)
					->set_help_text(__('Memunculkan data detail kontak pada masing-masing pembeli di detail pesanan', 'sejoli')),
				
				Field::make('text',	'sejoli_limit_product_ajax', __('Product Limit', 'sejoli'))
					->set_attribute('type', 'number')
					->set_default_value(200),

				Field::make('checkbox', 'sejoli_homepage_member_redirect', __('Alihkan user di homepage ke member area', "sejoli"))
					->set_help_text(__('Jika diaktifkan, user yang mengakses ke homepage akan dialihkan ke halaman member area', 'sejoli')),

				Field::make('text',	'sejoli_after_login_redirect', __('Alihkan user setelah login', 'sejoli'))
					->set_help_text(__('Jika dikosongkan, sistem akan mengalihkan user yang setelah login ke dashboard/home. <br />PASTIKAN link halaman yang digunakan valid!', 'sejoli'))
					->set_attribute('placeholder', 'https://'),

				Field::make('checkbox', 'sejoli_keep_dashboard_statistic', __('Tetap Munculkan Dashboard Statistik', "sejoli"))
					->set_help_text(__('Jika diaktifkan, sistem yang menggunakan alihkan user setelah login ke halaman khusus, tetap akan bisa menggunakan halaman dashboard statistik', 'sejoli')),

				/** @since 1.5.0 **/
				Field::make('multiselect', 'sejoli_user_roles_can_access_wp-admin', __('Tipe user yang diizinkan masuk ke dashboard WordPress', 'sejoli'))
					->add_options( array($this, 'get_user_roles') )
					->set_help_text( __('Tipe user yang dipilih bisa lebih dari satu. <br />Default user yang diizinkan adalah ADMINISTRATOR dan SEJOLI MANAGER', 'sejoli')),

				Field::make('text',	'sejoli_countdown_timer', __('Waktu mundur di invoice', 'sejoli'))
					->set_attribute('type', 'number')
					->set_required(true)
					->set_default_value(12)
					->set_help_text(__('Dalam satuan jam', 'sejoli')),

				Field::make('text', 'sejoli_autodelete_incomplete_order', __('Otomatis membatalkan order yang belum dibayar', 'sejoli'))
					->set_attribute('type', 'number')
					->set_default_value(0)
					->set_help_text(
						__('Kosongkan jika tidak ada otomatisasi penghapus order yang belum dibayar', 'sejoli') . '<br />' .
						__('Jika diisi, contohnya 10, maka sistem akan otomatis membatalkan order yang belum dibayar semenjak <strong>10 hari</strong> yang lalu', 'sejoli')
					),

				Field::make('text', 'sejoli_member_area_name', __('Nama member area', 'sejoli'))
					->set_required(true)
					->set_default_value(get_bloginfo('name'))
					->set_help_text( __('Ditampilkan di bagian sidebar member area', 'sejoli')),

				Field::make('checkbox', 'sejoli_enable_log', __('Aktifkan debug log', 'sejoli'))
					->set_default_value( false )
					->set_help_text( __('Aktifkan fitur ini jika anda ingin mengaktifkan fitur debug. Di beberapa hosting, ada kendala terkait penulisan log karena masalah izin penulisan. Jika anda mendapatkan masalah ketika checkout, harap nonaktifkan fitur ini', 'sejoli'))

			]
		];
		return $fields;
	}

	/**
	 * Add desain menu for main setting
	 * Hooked via filter sejoli/general/fields, priority 10
	 * @since  	1.0.0
	 * @param  	array 	$fields
	 * @return 	array
	 */
	public function setup_desain_setting_fields(array $fields) {
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
					->set_classes('sejoli-with-help'),

				Field::make('image',	'desain_logo',	   __('Logo', 'sejoli'))
					->set_help_text(__('Dianjurkan panjang logo tidak melebihi 480px dengan tinggi tidak lebih dari 300px', 'sejoli')),

				Field::make('image',	'desain_bg_image', __('Background Image', 'sejoli')),

				Field::make('color',	'desain_bg_color', __('Background Color', 'sejoli'))
					->set_default_value('#f2f3f8'),

				Field::make('select',	'desain_bg_position',__('Background Position', 'sejoli'))
					->set_options( array(
						'left top' => 'left top',
						'left center' => 'left center',
						'left bottom' => 'left bottom',
						'right top' => 'right top',
						'right center' => 'right center',
						'right bottom' => 'right bottom',
						'center top' => 'center top',
						'center center' => 'center center',
						'center bottom' => 'center bottom'
					) )
					->set_default_value('center top'),

				Field::make('select',	'desain_bg_repeat',__('Background Repeat', 'sejoli'))
					->set_options( array(
						'repeat' => 'repeat',
						'repeat-x' => 'repeat-x',
						'repeat-y' => 'repeat-y',
						'no-repeat' => 'no-repeat',
					) )
					->set_default_value('no-repeat'),

				Field::make('select',	'desain_bg_size',	__('Background Size', 'sejoli'))
					->set_options( array(
						'contain' => 'contain',
						'cover' => 'cover',
					) )
					->set_default_value('contain')
					->set_conditional_logic( $conditionals['desain_bg_size'] ),

				Field::make( 'separator', 'sep_member_area_color' , __('Pengaturan Warna Member Area', 'sejoli'))
					->set_classes('sejoli-with-help'),

				Field::make( 'checkbox', 'activate_custom_member_area_style', __('Aktifkan kustomisasi warna member area', 'sejoli'))
					->set_default_value(false),

				Field::make( 'color', 'member_area_bg_color', 			__('Warna background halaman', 'sejoli'))
					->set_default_value('#f8f8f8'),

				Field::make( 'separator', 'sep_member_area_sidebar', 	__('Warna sidebar', 'sejoli'))
					->set_width(40),

				Field::make( 'separator', 'sep_member_area_statitic', 	__('Warna statistik', 'sejoli'))
					->set_width(60),

				Field::make( 'color', 'member_area_sidebar_bg_color', 	__('Warna background', 'sejoli'))
					->set_width(40)
					->set_default_value('#000000'),

				Field::make( 'color', 'statistic_lead',		__('Warna statistik lead', 'sejoli'))
					->set_width(25)
					->set_default_value('#FF6600'),

				Field::make( 'color', 'statistic_lead_color',	__('Warna font statistik lead', 'sejoli'))
					->set_width(25)
					->set_default_value('#FFFFFF'),

				Field::make( 'color', 'member_area_sidebar_link_color', __('Warna link', 'sejoli'))
					->set_width(40)
					->set_default_value('#ffffff'),

				Field::make( 'color', 'statistic_sale',		__('Warna statistik sales', 'sejoli'))
					->set_width(25)
					->set_default_value('#179822'),

				Field::make( 'color', 'statistic_sale_color',	__('Warna font statistik sale', 'sejoli'))
					->set_width(25)
					->set_default_value('#FFFFFF'),

				Field::make( 'color', 'member_area_sidebar_link_hover_color', __('Warna link hover', 'sejoli'))
					->set_width(40)
					->set_default_value('#ffffff'),

				Field::make( 'color', 'statistic_omset',	__('Warna statistik omset', 'sejoli'))
					->set_width(25)
					->set_default_value('#162B9E'),

				Field::make( 'color', 'statistic_omset_color',	__('Warna font statistik omset', 'sejoli'))
					->set_width(25)
					->set_default_value('#FFFFFF'),

				Field::make( 'color', 'member_area_sidebar_link_active_color', __('Warna link aktif', 'sejoli'))
					->set_width(40)
					->set_default_value('#ffffff'),

				Field::make( 'color', 'statistic_komisi',	__('Warna statistik komisi', 'sejoli'))
					->set_width(25)
					->set_default_value('#40DF10'),

				Field::make( 'color', 'statistic_komisi_color',	__('Warna font statistik komisi', 'sejoli'))
					->set_width(25)
					->set_default_value('#FFFFFF'),

				Field::make( 'color', 'member_area_sidebar_menu_bg_color', __('Warna background link', 'sejoli'))
					->set_width(40)
					->set_default_value('#000000'),

				Field::make( 'color', 'graph_quantity', __('Warna grafik quantity', 'sejoli'))
					->set_width(25)
					->set_default_value('#179822'),

				Field::make( 'color', 'graph_omset', 	__('Warna grafik omset', 'sejoli'))
					->set_width(25)
					->set_default_value('#162B9E'),

				Field::make( 'color', 'member_area_sidebar_menu_hover_bg_color', __('Warna background link hover', 'sejoli'))
					->set_width(40)
					->set_default_value('#000000'),

				Field::make( 'html', 'sep_after_mas_hover')
					->set_html('&nbsp;')
					->set_width(60),

				Field::make( 'color', 'member_area_sidebar_menu_active_bg_color', __('Warna background link aktif', 'sejoli'))
					->set_width(40)
					->set_default_value('#000000'),

				Field::make( 'html', 'sep_after_mas_active')
					->set_html('&nbsp;')
					->set_width(60),


				Field::make('textarea', 'member_area_css', __('CSS Code', 'sejoli'))
					->set_help_text( __('Anda bisa menambahkan kode CSS khusus untuk halaman member area', 'sejoli'))
            ]
        ];

        return $fields;
	}

	/**
	 * Add affiliatel menu for main setting
	 * Hooked via filter sejoli/general/fields, priority 20
	 * @since  	1.0.0
	 * @since 	1.5.3.1		Add extra option to hide coupon menu
	 * @param  	array 		$fields
	 * @return 	array
	 */
	public function setup_affiliate_setting_fields(array $fields) {

		$fields[] = [
			'title'		=> __('Affiliasi', 'sejoli'),
			'fields'	=> [
				// Commission Setting
				Field::make('separator', 'sep_sejoli_affiliate_permission', __('Pembatasan', 'sejoli'))
					->set_classes('sejoli-with-help'),

				Field::make('checkbox', 'sejoli_no_access_affiliate', __('Fitur affiliasi tidak diaktifkan', 'sejoli'))
					->set_help_text( __('Dengan mengaktifkan fitur ini maka semua user tidak bisa mengakses ke menu affiliasi. <br />Anda bisa mengaktifkan affiliasi untuk user tertentu menggunakan <strong>User Groups</strong', 'sejoli')),

				Field::make('rich_text', 'sejoli_no_access_affiliate_text', __('Pesan untuk user tanpa fitur affiliasi', 'sejoli'))
					->set_help_text( __('Pesan ini akan ditampilkan di semua halaman affiliasi untuk user yang tidak memiliki fitur affilias.', 'sejoli'))
					->set_default_value('
						<p>Halaman ini hanya bisa diakses jika anda memiliki fitur affiliasi.</p>
			        	<p>Anda bisa menghubungi admin untuk hal ini.</p>
					'),

				Field::make('separator', 'sep_sejoli_cookie',	__('Cookie', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('setting-cookie') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make('checkbox', 'sejoli_cookie_is_general', __('General Cookie', 'sejoli'))
					->set_option_value('yes')
					->set_default_value('yes')
					->set_help_text(__('Dengan mengaktifkan ini, pembeli secara otomatis akan terdaftar sesuai affiliasi ke semua pembelian produk', 'sejoli')),

				Field::make('text', 'sejoli_cookie_age',	__('Umur Cookie', 'sejoli'))
					->set_default_value(0)
					->set_help_text(__('Umur cookie dalam satuan hari. Isi dengan 0 jika umur cookie selamanya', 'sejoli')),

				Field::make('checkbox', 'sejoli_permanent_affiliate', __('Kaitkan affiliasi', 'sejoli'))
					->set_option_value('yes')
					->set_default_value(false)
					->set_help_text(__('Dengan mengaktifkan ini, pembeli yang sudah pernah terdaftar atas affiliasi lain, untuk pembelian selanjutnya akan selalu berdasarkan affiliasi sebelumnya. <br />Penggunaan link affiliasi maupun kupon affiliasi tidak akan berpengaruh lagi', 'sejoli')),

				Field::make('separator', 'sep_sejoli_affiliate_tool', __('Tool', 'sejoli')),

				Field::make('checkbox', 'sejoli_affiliate_tool_help', __('Bantuan untuk Affiliasi', 'sejoli'))
					->set_option_value('yes')
					->set_default_value('yes')
					->set_help_text(__('Memunculkan menu bantuan untuk affiliasi', 'sejoli')),

				Field::make('checkbox', 'sejoli_affiliate_tool_coupon', __('Kupon Affiliasi', 'sejoli'))
					->set_option_value('yes')
					->set_default_value('yes')
					->set_help_text(__('Memunculkan menu kupon untuk affiliasi', 'sejoli')),

				Field::make('checkbox', 'sejoli_affiliate_tool_bonus', __('Bonus Editor', 'sejoli'))
					->set_option_value('yes')
					->set_default_value('yes')
					->set_help_text(__('Memunculkan editor pada masing-masing affiliate yang isinya akan ditampilkan kepada pembeli sesuai affiliatenya', 'sejoli')),

				Field::make('checkbox', 'sejoli_affiliate_tool_fb_pixel', __('Facebook Pixel', 'sejoli'))
					->set_option_value('yes')
					->set_help_text(__('Memunculkan isian ID facebook pixel pada masing-masing affiliate', 'sejoli')),
					
				Field::make('checkbox', 'sejoli_affiliate_tool_data_kontak_aff', __('Tampilkan detail kontak affiliate di pohoh jaringan', 'sejoli'))
					->set_option_value('yes')
					->set_help_text(__('Memunculkan data detail kontak pada masing-masing affiliate di pohon jaringan', 'sejoli')),

				Field::make('checkbox', 'sejoli_affiliate_tool_data_kontak_aff_order_detail', __('Tampilkan detail kontak affiliate di detail pesanan', 'sejoli'))
					->set_option_value('yes')
					->set_default_value(true)
					->set_help_text(__('Memunculkan data detail kontak pada masing-masing affiliate di detail pesanan', 'sejoli')),

				Field::make('separator', 'sep_sejoli_affiliate_coupon', __('Kupon', 'sejoli')),

				Field::make('checkbox', 'sejoli_affiliate_coupon_active', __('Kupon affiliasi langsung aktif', 'sejoli'))
					->set_option_value('yes')
					->set_default_value(true)
					->set_help_text(__('Dengan mengaktifkan kupon ini, maka semua permintaan kupon affiliasi akan langsung aktif', 'sejoli')),

				Field::make('separator', 'sep_sejolisa_affiliate_network_limit_upline', __('Jaringan Affiliasi (Upline)', 'sejoli')),

				Field::make('text', 'sejolisa_affiliate_network_limit_upline',	__('Batasan maksimal tampilan kedalaman jaringan affiliasi (Upline)', 'sejoli'))
					->set_default_value(1)
					->set_help_text(__('Tentukan maksimal kedalaman jaringan affiliasi (Upline) di menu Jaringan Affiliasi pada halaman member area', 'sejoli')),				


				Field::make('separator', 'sep_sejolisa_affiliate_network_limit', __('Jaringan Affiliasi (Downline)', 'sejoli')),
				Field::make('text', 'sejolisa_affiliate_network_limit',	__('Batasan maksimal tampilan kedalaman jaringan affiliasi (Downline)', 'sejoli'))
					->set_default_value(1)
					->set_help_text(__('Tentukan maksimal kedalaman jaringan affiliasi (Downline) di menu Jaringan Affiliasi pada halaman member area', 'sejoli')),

			]
		];
		return $fields;
	}

	/**
	 * Add recaptcha menu for product setting
	 * Hooked via filter sejoli/general/fields, priority 20
	 * @since  	1.0.0
	 * @since 	1.5.3.1		Add extra option to enable recaptcha in checkout form
	 * @param  	array 		$fields
	 * @return 	array
	 */
	public function setup_recaptcha_setting_fields(array $fields) {

		$fields[] = [
			'title'		=> __('reCAPTCHA', 'sejoli'),
			'fields'	=> [
				// Google reCAPTCHA Setting
				Field::make('separator', 'sep_sejoli_google_recaptcha', __('Google reCAPTCHA API Key', 'sejoli'))
					->set_classes('sejoli-with-help'),

				Field::make('checkbox', 'sejoli_google_recaptcha_enabled', __('Aktifkan Google reCAPTCHA', 'sejoli'))
					->set_help_text(__('Harap daftarkan domain Anda terlebih dahulu <a href="https://www.google.com/recaptcha/admin" target="_new">disini</a>, dapatkan kunci yang diperlukan dari Google <b>(wajib menggunakan reCAPTCHA v3)</b> dan simpan di bawah.', 'sejoli')),

				Field::make('text', 'sejoli_google_recaptcha_sitekey', __('Site Key', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'sejoli_google_recaptcha_enabled',
							'value'	=> true
						)
					)),

				Field::make('text', 'sejoli_google_recaptcha_secreetkey', __('Secret Key', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'sejoli_google_recaptcha_enabled',
							'value'	=> true
						)
					)),

				Field::make('text', 'sejoli_google_recaptcha_score_threshold',	__('Score Threshold', 'sejoli'))
					->set_default_value(0.9)
					->set_help_text(__('Skor nilai ambang batas keamanan ', 'sejoli')),

				Field::make('checkbox', 'sejoli_google_recaptcha_checkout_page', __('Aktifkan Google reCAPTCHA di Halaman Checkout', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'sejoli_google_recaptcha_enabled',
							'value'	=> true
						)
					)),

				Field::make('checkbox', 'sejoli_google_recaptcha_register_page', __('Aktifkan Google reCAPTCHA di Halaman Register', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'sejoli_google_recaptcha_enabled',
							'value'	=> true
						)
					)),
			]
		];

		return $fields;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $pagenow, $post;

		$is_sejoli_page = apply_filters('sejoli/admin/is-sejoli-page', false);

		wp_register_style( $this->plugin_name.'-carbonfields', SEJOLISA_URL . 'admin/css/carbonfields.css', 						 [], $this->version, 'all');
		wp_register_style( $this->plugin_name.'-dataTables',   SEJOLISA_URL . 'admin/css/dataTables.css', 							 [], $this->version, 'all');
		wp_register_style( $this->plugin_name.'-coupon',   	   SEJOLISA_URL . 'admin/css/coupon.css', 							 	 [], $this->version, 'all');
		wp_register_style( $this->plugin_name.'-product',      SEJOLISA_URL . 'admin/css/product.css', 							 	 [], $this->version, 'all');
		wp_register_style( $this->plugin_name.'-network-tree', SEJOLISA_URL . 'admin/css/network-tree.css', 						 [], $this->version, 'all');
		wp_register_style( 'select2',					'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css',  [], '4.0.7', 'all');
		wp_register_style( 'dataTables',				'https://cdn.datatables.net/1.10.18/css/jquery.dataTables.min.css', 		 [], '1.10.18', 'all');
		wp_register_style( 'semantic-ui', 				'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css', [], '2.4.1', 'all' );
		wp_register_style( 'dataTables-semantic-ui', 	'https://cdn.datatables.net/1.10.19/css/dataTables.semanticui.min.css', 	 ['dataTables', 'semantic-ui'], '1.10.19', 'all' );
		wp_register_style( 'daterangepicker',			'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',			 [], NULL, 'all');
		wp_register_style( 'chartjs',					'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.css',		 [], '2.8.0', 'all');
		wp_register_style( 'jstree', 					'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.10/themes/default/style.min.css', [], '3.3.10', 'all');

		wp_enqueue_style ( $this->plugin_name, 			SEJOLISA_URL . 'admin/css/sejoli-admin.css', 						 [], $this->version, 'all' );

		if(
			(in_array($pagenow, ['post-new.php', 'post.php']) && in_array($post->post_type, $this->enabled_post_type )) ||
			(
				in_array($pagenow, ['admin.php']) && isset($_GET['page']) &&
				in_array($_GET['page'], ['crb_carbon_fields_container_'.strtolower(__('Notifikasi', 'sejoli')).'.php', 'crb_carbon_fields_container_sejoli.php'])
			)
		) :
			wp_enqueue_style( $this->plugin_name.'-carbonfields');
		endif;

		if(
			in_array($pagenow, ['post-new.php', 'post.php', 'profile.php', 'user-edit.php']) &&
			(
				(is_a($post, 'WP_Post') && 'sejoli-product' === $post->post_type) ||
				isset($_GET['user_id']) ||
				'profile.php' === $pagenow
			)
		) :
			wp_enqueue_style( 'select2' );
		endif;

		if(in_array($pagenow, ['post-new.php', 'post.php']) && 'sejoli-coupon' === $post->post_type ) :
			wp_enqueue_style($this->plugin_name . '-coupon');
		endif;

		// load js tree style for admin

		if( in_array($pagenow, ['users.php'])) :

			$page = isset ($_GET['page']) ? $_GET['page']:'';

			if($page == 'detail-user-network'):
				wp_enqueue_style( 'jstree');
				wp_enqueue_style( $this->plugin_name.'-network-tree');
			endif;
					
		endif;


		if($is_sejoli_page) :
			wp_enqueue_style( $this->plugin_name . '-dataTables');
			wp_enqueue_style( 'daterangepicker');
			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'semantic-ui');
			wp_enqueue_style( 'dataTables-semantic-ui' );

			if(in_array($_GET['page'],['sejoli-orders', 'sejoli-commissions'] )):
				wp_enqueue_style('chartjs');
			endif;
		endif;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $pagenow, $post;

		$is_sejoli_page = apply_filters('sejoli/admin/is-sejoli-page', false);

		wp_register_script( 'select2', 			'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js', 					['jquery'], '4.0.7', true);
		wp_register_script( 'dataTables', 		'https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js', 							['jquery', $this->plugin_name], '1.10.18', true);
		wp_register_script( 'jquery-blockUI', 	'https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js', 		['jquery'], '2.70', true );
		wp_register_script( 'js-render', 		'https://cdnjs.cloudflare.com/ajax/libs/jsrender/0.9.91/jsrender.min.js', 					['jquery'], '0.9.91', true );
		wp_register_script( 'jquery-maskmoney', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js', 	['jquery'], '3.0.2', true );
		wp_register_script( 'moment',			'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',									['jquery'], NULL, true);
		wp_register_script( 'daterangepicker',	'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',						['moment'], NULL, true);
		wp_register_script( 'chartjs',			'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js',				[], '2.8.0', true);
		wp_register_script( 'semantic-ui',		'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js',					['jquery'], '2.4.1', true );
		wp_register_script( 'jstree',			'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.10/jstree.min.js', 						['jquery'], '3.3.10', true);
		wp_register_script( 'clipboardjs', 			 'https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js', [], NULL, false );

		wp_register_script( $this->plugin_name . '-hooks',	SEJOLISA_URL . 'admin/js/sejoli-hooks.js',										['jquery'], $this->version, true );
		wp_register_script( $this->plugin_name . '-coupon',	SEJOLISA_URL . 'admin/js/sejoli-coupon.js',										['jquery'], $this->version, true );
		wp_enqueue_script( 	$this->plugin_name, SEJOLISA_URL . 'admin/js/sejoli-admin.js', 													['jquery'], $this->version, true );

		$users = isset ($_GET['users']) ? $_GET['users']:'';
		if( wp_is_mobile() ){
			$interval = 14;
		} else {
			$interval = 29;
		}

		// localize data
		$admin_localize_data = apply_filters('sejoli/admin/js-localize-data', [
			'text' => [
				'main'         => __('Pengaturan', 'sejoli'),
				'notification' => __('Notifikasi', 'sejoli'),
				'currency' => sejolisa_currency_format(),
				'status'   => [
					'pending'  => __('Belum Aktif', 'sejoli'),
					'inactive' => __('Tidak Aktif', 'sejoli'),
					'active'   => __('Aktif', 'sejoli'),
					'expired'  => __('Berakhir', 'sejoli'),
				]
			],
			'color' => sejolisa_get_all_colors(),
			'countdown_text' => [
				'jam'   => __('Jam', 'sejoli'),
				'menit' => __('Menit', 'sejoli'),
				'detik' => __('Detik', 'sejoli'),
			],
			'network' => [
				'user' => [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-affiliate-get-user-network-list',
						'nonce' => wp_create_nonce('sejoli-affiliate-get-user-network-list'),
						'data_id' => $users
					], admin_url('admin-ajax.php')),
				]
			],
			'chart_daterange' => [
				'interval' => $interval
			],
		]);

		wp_localize_script( $this->plugin_name, 'sejoli_admin', $admin_localize_data);

		// If current page is product or profile
		if(
			in_array($pagenow, ['post-new.php', 'post.php', 'profile.php', 'user-edit.php']) &&
			(
				(is_a($post, 'WP_Post') && 'sejoli-product' === $post->post_type) ||
				isset($_GET['user_id']) ||
				'profile.php' === $pagenow
			)
		) :
			wp_enqueue_script( 'select2' );
		endif;

		if(
			in_array($pagenow, ['post-new.php', 'post.php' ]) &&
			'sejoli-coupon'	=== $post->post_type
		) :
			wp_enqueue_script ($this->plugin_name . '-coupon');
		endif;

		// load jstree script for admin

		if( in_array($pagenow, ['users.php'])) :

			$page = isset ($_GET['page']) ? $_GET['page']:'';

			if($page == 'detail-user-network'):
				wp_enqueue_script( 'jstree');
			endif;
					
		endif;
		

		// All sejoli option page
		if($is_sejoli_page) :

			wp_enqueue_script( 'daterangepicker');
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'dataTables' );
			wp_enqueue_script( 'jquery-blockUI');
			wp_enqueue_script( 'js-render');
			wp_enqueue_script( 'semantic-ui');
			wp_enqueue_script( 'clipboardjs' );
			wp_enqueue_script ($this->plugin_name . '-hooks');

			if(in_array($_GET['page'],['sejoli-orders', 'sejoli-commissions'] )):
				wp_enqueue_script('chartjs');
			endif;

			wp_localize_script ("dataTables","dataTableTranslation",array(
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
		endif;

	}

	/**
	 * Register custom cron schedule
	 * Hooked via filter cron_schedules, priority 999
	 * @since 	1.1.0
	 * @since 	1.3.3 	Add new schedule, quarterdaily and add conditiona check
	 * @param  	array 	$schedules 	Array of schedules
	 * @return 	array
	 */
	public function register_custom_cron($schedules) {

		if(!array_key_exists('fourth_hourly', $schedules)) :
			$schedules['fourth_hourly'] = array(
	        	'interval' => 15 * 60,
	        	'display'  => __('Fourth time hourly - Sejoli', 'sejoli')
    		);
		endif;

		if(!array_key_exists('twice_hourly', $schedules)) :
			$schedules['twice_hourly'] = array(
				'interval' => 30 * 60,
		        'display'  => __('Twice hourly - Sejoli', 'sejoli')
			);
		endif;

		if(!array_key_exists('every_10_min', $schedules)) :
			$schedules['every_10_min'] = array(
				'interval' => 10 * 60,
		        'display'  => __('Every 10 minutes - Sejoli', 'sejoli')
			);
		endif;

		if(!array_key_exists('every_30_min', $schedules)) :
			$schedules['every_30_min'] = array(
				'interval' => 30 * 60,
		        'display'  => __('Every 30 minutes - Sejoli', 'sejoli')
			);
		endif;

		if(!array_key_exists('quarterdaily', $schedules)) :
			$schedules['quarterdaily'] = array(
				'interval'	=> 6 * 60 * 60,
				'display'	=> __('Every Six Hours - Sejoli', 'sejoli')
			);
		endif;

		return $schedules;
	}

	/**
	 * Clean dashboard widgets those are not from SEJOLI
	 * Hooked via action wp_dashboard_setup, priority 999
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function remove_unneeded_widgets() {
		global $wp_meta_boxes;

		if(!current_user_can('manage_sejoli_orders')) :
			return;
		endif;

		if(isset($wp_meta_boxes['dashboard'])) :
			foreach($wp_meta_boxes['dashboard'] as $_side => $_types) :
				foreach($_types as $_type => $_cores) :
					foreach($_cores as $_key => $_widgets) :
						if(false === strpos($_key, 'sejoli-')) :
							unset($wp_meta_boxes['dashboard'][$_side][$_type][$_key]);
						endif;
					endforeach;
				endforeach;
			endforeach;
		endif;
	}

	/*
	* Display header help
	* Hooked via action admin_notices, priority 1
	* @since 	1.0.0
	* @return 	void
	*/
	public function display_help() {
	   require_once plugin_dir_path( __FILE__ ) . 'partials/debug-on.php';
	   require_once plugin_dir_path( __FILE__ ) . 'partials/header-help.php';
	}

	/**
	* Add member area link to admin bar
	* Hooked via admin_bar_menu, priority 9999
	* @since 	1.0.0
	* @return 	void
	*/
	public function add_member_area_link($admin_bar) {

		$admin_bar->add_menu([
		   'id'	=> 'sejoli-member-area',
		   'title'	=> 'Member Area',
		   'href'	=> home_url('member-area')
	   	]);

	   	if(!current_user_can('manage_options')) :
			$admin_bar->remove_node('wp-logo');
			$admin_bar->remove_node('site-name');
			$admin_bar->remove_node('new-content');
			$admin_bar->remove_node('query-monitor');
			$admin_bar->remove_node('edit-profile');
			$admin_bar->remove_node('search');
		endif;
	}

    /**
     * Check current admin page
     * Hooked via action admin_init, priority 999
     * @return void
     */
    public function check_page_request() {
        if(
            isset($_GET['page']) &&
            in_array($_GET['page'],[
                'sejoli-orders',
				'sejoli-commissions',
				'sejoli-affiliates',
				'sejoli-coupons',
				'sejoli-subscriptions',
				'sejoli-licenses',
				'sejoli-leaderboard',
				'sejoli-confirmation',
				'sejoli-reminder-log'
            ])
        ) :
            $this->is_sejoli_page = true;
        endif;
    }

	/**
	 * Set inline style for admin page
	 * Hooked via action admin_head, priority 999
	 * @since 	1.0.0
	 * @since 	1.5.4.1 	Add custom CSS
	 * @return  void
	 */
	public function set_inline_style() {

		global $pagenow;

		?>
		<style media="screen" type="text/css">
			.menu-icon-sejoli-coupon {
				display: none;
			}
		</style>
		<?php

		$screen = get_current_screen();

		if(
			'index.php' === $pagenow &&
			'dashboard' === $screen->base &&
			false !== boolval(sejolisa_carbon_get_theme_option('activate_custom_member_area_style'))
		) :
		?>
		<style media="screen" type='text/css'>
		body.wp-admin .three.cards .ui.card.orange .content {
			background-color: <?php echo sejolisa_carbon_get_theme_option('statistic_lead'); ?>!important;
			color: <?php echo sejolisa_carbon_get_theme_option('statistic_lead_color'); ?>!important;
		}

		body.wp-admin .three.cards .ui.card.orange .content .header { color: <?php echo sejolisa_carbon_get_theme_option('statistic_lead_color'); ?>!important; }

		body.wp-admin .three.cards .ui.card.green .content {
			background-color: <?php echo sejolisa_carbon_get_theme_option('statistic_sale'); ?>!important;
			color: <?php echo sejolisa_carbon_get_theme_option('statistic_sale_color'); ?>!important;
		}

		body.wp-admin .three.cards .ui.card.green .content .header { color: <?php echo sejolisa_carbon_get_theme_option('statistic_sale_color'); ?>!important; }

		body.wp-admin .three.cards .ui.card.blue .content {
			background-color: <?php echo sejolisa_carbon_get_theme_option('statistic_omset'); ?>!important;
			color: <?php echo sejolisa_carbon_get_theme_option('statistic_omset_color'); ?>!important;
		}

		body.wp-admin .three.cards .ui.card.blue .content .header {	color: <?php echo sejolisa_carbon_get_theme_option('statistic_omset_color'); ?>!important; }

		body.wp-admin .three.cards .ui.card.light-green .content {
			background-color: <?php echo sejolisa_carbon_get_theme_option('statistic_komisi'); ?>!important;
			color: <?php echo sejolisa_carbon_get_theme_option('statistic_komisi_color'); ?>!important;
		}

		body.wp-admin .ui.cards.information.daily .content.value 	{ background-color:white!important; color: black!important; }

		body.wp-admin .three.cards .ui.card.light-green .content .header { color: <?php echo sejolisa_carbon_get_theme_option('statistic_komisi_color'); ?>!important; }
		</style>
		<?php
		endif;
	}

    /**
     * Check if current admin page is a sejoli
     * Hooked via filter sejoli/admin/is-sejoli-page, priority 999
     * @param  boolean $is_sejoli_page
     * @return boolean
     */
    public function is_sejoli_page($is_sejoli_page = false) {
        return $this->is_sejoli_page;
    }

    /**
     * Saving user preferencing to show or hide use of sejoli widget
     * Hooked via action wp_ajax_sejoli_save_user_panel_preference, priority 999
     * @return true
     */
    public function sejoli_save_user_panel_preference() {

	    $user_id = get_current_user_id();
	    
	    if (!$user_id) return;

	    if (isset($_POST['sejoli_hide_widget_use_of_sejoli'])) :
	        update_user_meta($user_id, 'sejoli_hide_widget_use_of_sejoli', $_POST['sejoli_hide_widget_use_of_sejoli']);
	    endif;

	}

}
