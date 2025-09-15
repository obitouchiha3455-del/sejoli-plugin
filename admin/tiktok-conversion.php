<?php
namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class TiktokConversion {

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
		$this->version     = $version;

	}

    /**
	 * Setup tiktok pixel fields for product
	 * Hooked via filter sejoli/product/fields, priority 60
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_tiktok_conversion_setting_fields( array $fields ) {

        $tiktok_conversion_events = [
            ''                     => __('Pilih Tiktok Conversion API Event', 'sejoli'),
            'AddPaymentInfo'       => 'Add payment info',
            'AddToCart'            => 'Add to cart',
            'AddToWishlist'        => 'Add to wishlist',
            'CompleteRegistration' => 'Complete Registration',
            'Contact'              => 'Contact',
            'CustomizeProduct'     => 'Customize Product',
            'Donate'               => 'Donate',
            'Find Location'        => 'FindLocation',
            'InitiateCheckout'     => 'InitiateCheckout',
            'Lead'                 => 'Lead',
            'Purchase'             => 'Purchase',
            'Schedule'             => 'Schedule',
            'Search'               => 'Search',
            'StartTrial'           => 'Start Trial',
            'SubmitApplication'    => 'Submit Application',
            'Subscribe'            => 'Subscribe',
            "ClickButton" 		   => "Click Button",
            "CompletePayment"      => "Complete Payment",
            "PlaceAnOrder"         => "Place An Order",
            "SubmitForm"  		   => "Submit Form",
            'ViewContent'          => 'View Content'
        ];

        $fields[] = [
			'title'	 => __('Tiktok Conversion API', 'sejoli'),
            'fields' =>  [
                Field::make('separator', 'sep_sejoli_tiktok_conversion', __('Pengaturan Tiktok Conversion API', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('facebook-pixel') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),
                Field::make('checkbox', 'tiktok_conversion_active', __('Aktifkan Tiktok Conversion API untuk produk ini', 'sejoli')),
                Field::make('text', 'tiktok_conversion_id', __('ID Tiktok Pixel', 'sejoli'))
                	->set_required(true)
                	->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),
                Field::make('textarea', 'tiktok_conversion_access_token', __('Access Token', 'sejoli'))
                	->set_required(true)
                	->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),
                Field::make('text', 'tiktok_conversion_currency', __('Currency', 'sejoli'))
                	->set_required(true)
                	->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),
                Field::make('text', 'tiktok_conversion_test_code', __('Test Event Code', 'sejoli'))
                	->set_help_text('Gunakan ini jika Anda perlu menguji acara sisi server. Harap hapus setelah pengujian dilakukan untuk setelahnya menggunakan live code.', 'sejoli')
                	->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),
                Field::make('select', 'tiktok_conversion_event_on_checkout_page', __('Event pada halaman checkout', 'sejoli'))
                    ->add_options( $tiktok_conversion_events )
                    ->set_help_text(__('Event ketika user mengunjungi halaman checkout', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),
                Field::make('select', 'tiktok_conversion_event_submit_checkout_button', __('Event pada tombol submit di halaman checkout', 'sejoli'))
                    ->add_options( $tiktok_conversion_events )
                    ->set_help_text(__('Event ketika user menekan tombol BELI SEKARANG', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),
                Field::make('select', 'tiktok_conversion_event_on_invoice_page', __('Event pada halaman invoice', 'sejoli'))
                    ->add_options( $tiktok_conversion_events )
                    ->set_help_text(__('Event ketika user mengunjungi halaman invoice', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),
                Field::make('select', 'tiktok_conversion_event_change_order_status_page', __('Event pada perubahan status order', 'sejoli'))
                    ->add_options( $tiktok_conversion_events )
                    ->set_help_text(__('Event ketika terjadi perubahan status order menjadi "Selesai"', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),
                Field::make('select', 'tiktok_conversion_event_click_link_access_page', __('Event pada user klik akses pertama kali', 'sejoli'))
                    ->add_options( $tiktok_conversion_events )
                    ->set_help_text(__('Event ketika user klik link akses pertama kali', 'sejoli'))
                    ->set_conditional_logic([
                        [
                            'field' => 'tiktok_conversion_active',
                            'value' => true
                        ]
                    ]),

            ]
        ];

        return $fields;
    }

}