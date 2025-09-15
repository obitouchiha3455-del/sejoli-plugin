<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Access {

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
	 * All products data saved here
	 *
	 * @since 	1.0.0
	 * @access 	public
	 * @var 	array 	   $product 		Product data
	 */
	protected $products = [];

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
     * Register access post type
     * Hooked via action init, priority 1009
     * @return void
     */
    public function register_post_type() {

		if(false === sejolisa_check_own_license()) :
			return;
		endif;

		$labels = [
    		'name'               => _x( 'Access', 'post type general name', 'sejoli' ),
    		'singular_name'      => _x( 'Access', 'post type singular name', 'sejoli' ),
    		'menu_name'          => _x( 'Access', 'admin menu', 'sejoli' ),
    		'name_admin_bar'     => _x( 'Access', 'add new on admin bar', 'sejoli' ),
    		'add_new'            => _x( 'Add New', 'access', 'sejoli' ),
    		'add_new_item'       => __( 'Add New Access', 'sejoli' ),
    		'new_item'           => __( 'New Access', 'sejoli' ),
    		'edit_item'          => __( 'Edit Access', 'sejoli' ),
    		'view_item'          => __( 'View Access', 'sejoli' ),
    		'all_items'          => __( 'All Access', 'sejoli' ),
    		'search_items'       => __( 'Search Access', 'sejoli' ),
    		'parent_item_colon'  => __( 'Parent Access:', 'sejoli' ),
    		'not_found'          => __( 'No access found.', 'sejoli' ),
    		'not_found_in_trash' => __( 'No access found in Trash.', 'sejoli' )
    	];

    	$args = [
    		'labels'             => $labels,
            'description'        => __( 'Description.', 'sejoli' ),
    		'public'             => true,
    		'publicly_queryable' => true,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
			'exclude_from_search'=> true,
    		'rewrite'            => [ 'slug' => 'member-content' ],
    		'capability_type'    => 'sejoli_access',
			'capabilities'		 => array(
				'publish_posts'      => 'publish_sejoli_accesses',
				'edit_posts'         => 'edit_sejoli_accesses',
				'edit_others_posts'  => 'edit_others_sejoli_accesses',
				'read_private_posts' => 'read_private_sejoli_accesses',
				'edit_post'          => 'edit_sejoli_access',
				'delete_posts'       => 'delete_sejoli_access',
				'read_post'          => 'read_sejoli_access'
			),
    		'has_archive'        => false,
    		'hierarchical'       => false,
    		'menu_position'      => null,
    		'supports'           => [ 'title', 'editor', 'page-attributes' ],
			'menu_icon'			 => plugin_dir_url( __FILE__ ) . 'images/icon.png'
    	];

    	register_post_type( SEJOLI_ACCESS_CPT, $args );
    }

	/**
	 * Set product options
	 * @since 	1.0.0
	 * @return 	void
	 */
    public function set_product_options() {
        $options = [];

        $products = get_posts([
            'post_type' => SEJOLI_PRODUCT_CPT
        ]);

        foreach($products as $product) :
            $options[$product->ID] = $product->post_title;
        endforeach;

        return $options;
    }

    /**
     * Setup custom fields for product
     * Hooked via action carbon_fields_register_fields, priority 1009
     * @since 	1.0.0
     * @return 	void
     */
    public function setup_carbon_fields() {

        $container = Container::make('post_meta', 'sejoli_access', __('Setup Akses', 'sejoli'))
            ->where( 'post_type', '=', 'sejoli-access')
            ->set_classes('sejoli-metabox')
            ->add_tab( __('Pembatasan', 'sejoli'),[
				Field::make( 'separator', 'sep_access_configuration', __('Pengaturan Pembatasan', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('access') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make( 'text',		'drip_day', __('Hari konten dirilis (Drip Content)', 'sejoli'))
					->set_default_value(0)
					->set_attribute('type', 'number')
					->set_help_text(__('Dengan mengisi isian ini, pembeli akan bisa mengakses konten setelah X hari pembelian', 'sejoli')),

                Field::make( 'association', 'product_association', __( 'Produk', 'sejoli' ) )
                    ->set_types([
                        [
                            'type'      => 'post',
                            'post_type' => 'sejoli-product',
                        ]
                    ])
                    ->set_help_text(__('Isi akses ini hanya bisa dilihat oleh user yang telah membeli produk yang sesuai', 'sejoli'))
			])
			->add_tab( __('Informasi', 'sejoli'),[

				Field::make( 'separator', 'sep_block_access', __('Informasi ke pengakses', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('product-type') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make( 'checkbox',   'access_redirect_active', __('Alihkan pengakses ke halaman lain', 'sejoli')),

				Field::make( 'text',	   'access_redirect_link',	 __('Link pengalihan untuk pengakses yang tidak diizinkan', 'sejoli'))
					->set_attribute('placeholder', 'https://')
					->set_help_text( __('Diisi dengan format link yang benar', 'sejolu') )
					->set_conditional_logic([
						[
							'field'	=> 'access_redirect_active',
							'value'	=> true
						]
					]),

				Field::make( 'rich_text',  'access_block_message',	 __('Pesan bagi pengakses yang tidak diizinkan', 'sejoli'))
					->set_default_value('<p>Maaf, anda tidak bisa mengakses halaman ini dikarenakan untuk bisa mengakses halaman ini anda wajib untuk membeli produk {diisi dengan nama produk} terlebih dahulu</p>')
					->set_conditional_logic([
						[
							'field'	=> 'access_redirect_active',
							'value'	=> false
						]
					]),

				Field::make( 'rich_text',  'access_expired_message',	 __('Pesan bagi pengakses yang sudah berakhir masa aksesnya', 'sejoli'))
					->set_default_value('<p>Maaf, anda tidak bisa mengakses halaman ini dikarenakan waktu akses produk {diisi dengan nama produk} telah habis. Silahkan perpanjang akses produk tersebut.</p>')
					->set_conditional_logic([
						[
							'field'	=> 'access_redirect_active',
							'value'	=> false
						]
					]),

				Field::make( 'rich_text',  'access_drip_day_message',	 __('Pesan bagi pengakses yang tidak sesuai hari rilis konten (Drip Content)', 'sejoli'))
					->set_default_value('<p>Maaf, anda tidak bisa mengakses halaman ini dikarenakan anda harus menunggu waktu akses hari ini setelah {lama hari} hari setelah invoice selesai</p>')
					->set_conditional_logic([
						[
							'field'	=> 'access_redirect_active',
							'value'	=> false
						],[
							'field'   => 'drip_day',
							'value'   => 0,
							'compare' => '>'
						]
					])
			]);
    }


	/**
	 * Add product column to access columns
	 * Hooked via filter manage_sejoli-access_posts_columns, priority 100
	 * @since 	1.0.0
	 * @param	array $columns
	 * @return 	array
	 */
	public function add_access_columns(array $columns) {

		unset($columns['date']);

		$columns['sejoli-product']	= __('Produk', 'sejoli');
		$columns['sejoli-drip-day']	= __('Hari', 'sejoli');

		return $columns;
	}

	/**
	 * Display product by current access
	 * @since	1.0.0
	 * @param  	integer 	$post_id 	Given post ID
	 * @return	array
	 */
	protected function render_product_protection_data($post_id) {

		$content = [];
		$products = sejolisa_carbon_get_post_meta($post_id, 'product_association');

		if(0 < count($products)) :
			foreach($products as $product) :

				if(!isset($this->products[$product['id']])) :
					$post = get_post($product['id']);

					$this->products[$product['id']]	= [
						'name'	=> $post->post_title,
						'link'	=> get_permalink($post->ID)
					];
				endif;

				$data = $this->products[$product['id']];

				ob_start();
				?><a href='<?php echo $data['link']; ?>'><?php echo $data['name']; ?></a><?php
				$content[] = ob_get_contents();
				ob_end_clean();

			endforeach;
		endif;

		echo implode(" &bull; ", $content);

	}

	/**
	 * Display product data to access product column
	 * Hooked via manage_posts_custom_column, priority 100
	 * @since 	1.0.0
	 * @param  	string 		$column
	 * @param  	integer 	$post_id
	 * @return 	void
	 */
	public function display_product_protection_data($column, $post_id) {

		switch ( $column ) :

			case 'sejoli-drip-day' :
				$day = intval(sejolisa_carbon_get_post_meta($post_id, 'drip_day'));

				if(0 === $day) :
					echo __('Langsung Aktif', 'sejoli');
				else :
					printf(__('Hari ke-%d', 'sejoli'), $day);
				endif;
				break;

			case 'sejoli-product' :
				$this->render_product_protection_data($post_id);
				break;

		endswitch;
	}
}
