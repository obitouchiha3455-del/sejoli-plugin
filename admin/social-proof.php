<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

final class SocialProof {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.5.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.5.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.5.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     * Display product setting fields
     * Hooked via filter sejoli/product/fields, priority 90
     * @since   1.5.0
     * @since 	1.5.1.1 	Add display product photo instead buyer avatar
     * @since 	1.5.2 		Fixing bug in social code js code
     * @param   array  $fields   Container fields
     * @return  array
     */
    public function setup_product_setting_fields( array $fields ) {

        $fields[]   = array(

            'title'  => __('Social Proof', 'sejoli'),
            'fields' => array(

                Field::make('separator', 'sep_social_proof',    __('Pengaturan Social Proof', 'sejoli'))
					->set_classes('sejoli-with-help'),

                Field::make('html', 'social_proof_info')
                    ->set_html( __('Fitur social proof ini akan menampilkan popup terkait informasi pembeli produk', 'sejoli')),

                Field::make( 'checkbox',    'social_proof_enable',              __('Aktifkan social proof', 'sejoli')),

                Field::make( 'checkbox',    'social_proof_sensor_buyer_name',   __('Rahasiakan nama pembeli', 'sejoli'))
                    ->set_help_text( __('Nama pembeli akan disensor jika opsi ini diaktifkan', 'sejoli') ),

                Field::make( 'checkbox',    'social_proof_display_avatar',      __('Tampilkan avatar pembeli', 'sejoli'))
                    ->set_help_text( __('Jika pembeli sudah pernah mengupload photo di <a href="https://gravatar.com" target="_blank">gravatar.com</a>, maka photo tersebut akan ditampilkan', 'sejoli') ),

				Field::make( 'checkbox', 	'social_proof_display_product',		__('Tampilkan photo produk', 'sejoli'))
					->set_help_text( __('Tampilkan photo produk daripada avatar pembeli. Jika mengaktifkan fitur ini, pastikan anda sudah mengupload photo produk pada FEATURED IMAGE', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'social_proof_display_avatar',
							'value'	=> false
						)
					)),

				Field::make( 'text',		'social_proof_text',				__('Teks yang ditampilkan pada popup', 'sejoli') )
					->set_default_value('{{buyer_name}} telah membeli {{product_name}}')
					->set_help_text( __('Jika item ini adalah donasi, anda bisa mengganti dengan {{buyer_name}} telah berdonasi pada {{product_name}}','sejoli') ),

				Field::make( 'select', 		'social_proof_order_status',		__('Tampilkan data berdasarkan status order', 'sejoli'))
					->set_options(array(
						'on-hold'	=> __('Menunggu pembayaran', 'sejoli'),
						'completed'	=> __('Order selesai', 'sejoli'),
						'both'		=> __('Menunggu pembayaran dan order selesai', 'sejoli')
					)),

				Field::make( 'select',		'social_proof_position',		__('Posisi social proof popup', 'sejoli'))
					->set_options(array(
						'top left'      => __('Pojok kiri atas', 'sejoli'),
						'top center'    => __('Tengah atas', 'sejoli'),
						'top right'     => __('Pojok kanan atas', 'sejoli'),
						'bottom left'   => __('Pojok kiri bawah', 'sejoli'),
						'bottom center' => __('Tengah bawah', 'sejoli'),
						'bottom right'  => __('Pojok kanan bawah', 'sejoli')
					)),

                Field::make( 'select',      'social_proof_first',             __('Jeda popup pertama', 'sejoli'))
                    ->set_options(array(
                        0     => __('Langsung tampil', 'sejoli'),
                        1000  => __('2 Detik', 'sejoli'),
                        5000  => __('5 Detik', 'sejoli'),
                        10000 => __('10 Detik', 'sejoli')
                    ))
                    ->set_width( 33 ),

                Field::make( 'select',      'social_proof_display',         __('Waktu popup tampil', 'sejoli'))
                    ->set_options(array(
                        2000    => __('2 Detik', 'sejoli'),
                        5000    => __('5 Detik', 'sejoli'),
                        10000   => __('10 Detik', 'sejoli'),
                    ))
                    ->set_width( 34 ),

				Field::make( 'select',      'social_proof_delay',         __('Jeda antar popup', 'sejoli'))
                    ->set_options(array(
                        2000    => __('2 Detik', 'sejoli'),
                        5000    => __('5 Detik', 'sejoli'),
                        10000   => __('10 Detik', 'sejoli'),
                    ))
                    ->set_width( 33 ),

				Field::make('textarea',		'social_proof_code',		 __('Kode untuk integrasi dengan web lain', 'sejoli'))
					->set_default_value('')
					->set_attribute( 'readOnly', true)
					->set_help_text( __('Anda bisa copy code yang diatas dan letakkan di web yang anda gunakan tanpa sejoli. Untuk bisa mendapatkan kode ini, anda sudah SAVE halaman ini terlebih dahulu.', 'sejoli')),

            )
        );

        return $fields;
    }

	/**
	 * Add JS Code in product editor page
	 * Hooked via action admin_footer, priority 90
	 * @since 	1.5.2
	 * @return 	void
	 */
	public function add_js_code() {

		global $pagenow;

		if(
			'post.php' === $pagenow &&
			isset($_GET['action']) &&
			'edit' === $_GET['action']
		) :
		
			require_once( SEJOLISA_DIR . 'admin/partials/social-proof/js-code.php' );

		endif;

	}

}
