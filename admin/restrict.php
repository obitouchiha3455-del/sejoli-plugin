<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Restrict {

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
	 * @access 	public
	 * @var 	array 	   $status 		Order status
	 */
	protected $status = [];

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
     * Setup custom fields for every public post types
     * Hooked via action carbon_fields_register_fields, priority 999
     * @since 	1.0.0
     * @return 	void
     */
    public function setup_carbon_fields() {
        $post_types = get_post_types(['public' => true ]);

        $conditionals = [
            'main' => [
                [
                    'field' => 'restrict_content',
                    'value' => true
                ]
            ],
            'redirect' => [
                [
                    'field' => 'restrict_content',
                    'value' => true
                ],[
                    'field' => 'redirect_if_no_access',
                    'value' => true,
                ]
            ],
            'message' => [
                [
                    'field' => 'restrict_content',
                    'value' => true
                ],[
                    'field' => 'redirect_if_no_access',
                    'value' => false,
                ]
            ]
        ];

        $container = Container::make('post_meta', __('Setup Akses', 'sejoli'))
            ->where( 'post_type', 'IN', $post_types)
            ->set_classes('sejoli-metabox')
            ->add_fields([
                Field::make( 'checkbox', 'restrict_content', __( 'Lindungi halaman ini', 'sejoli' ) )
                    ->set_option_value('yes')
                    ->set_help_text(__('Aktifkan opsi ini jika ingin membatasi akses halaman ini', 'sejoli')),

                Field::make( 'association', 'product_association', __( 'Produk', 'sejoli' ) )
                    ->set_types([
                        [
                            'type'      => 'post',
                            'post_type' => 'sejoli-product',
                        ]
                    ])
                    ->set_conditional_logic($conditionals['main'])
                    ->set_help_text(__('Isi akses ini hanya bisa dilihat oleh user yang telah membeli produk yang sesuai', 'sejoli'))
                    ->set_required(true),

                Field::make( 'checkbox', 'redirect_if_no_access', __( 'Aktifkan pengalihan user ke halaman lain', 'sejoli' ) )
                    ->set_option_value('yes')
                    ->set_help_text(__('Aktifkan user akan dialihkan ke halaman tertentu jika tidak memiliki hak akses', 'sejoli'))
                    ->set_conditional_logic($conditionals['main']),

                Field::make( 'text', 'redirect_link', __('Link halaman pengalihan', 'sejoli'))
                    ->set_required(true)
                    ->set_attribute('type', 'url')
                    ->set_conditional_logic($conditionals['redirect'])
                    ->set_help_text( __('Isi dengan link halaman. Wajib gunakan http:// atau https://', 'sejoli')),

                Field::make( 'rich_text', 'message_no_access', __('Pesan untuk user yang tidak punya akses', 'sejoli'))
                    ->set_required(true)
                    ->set_conditional_logic($conditionals['message'])
            ]);
    }
}
