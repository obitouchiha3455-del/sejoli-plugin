<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class MemberMessage {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.3.2
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.2
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Product holder
	 * @since	1.0.0
	 * @var 	array
	 */
	protected $products = array();

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.3.2
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

    }

    /**
	 * Register member message post type
	 * Hooked via action init, priority 999
	 *
	 * @return 	void
	 */
	public function register_post_type() {

		if(false === sejolisa_check_own_license()) :
			return;
		endif;

        $labels = [
    		'name'               => _x( 'Member Messages', 'post type general name', 'sejoli' ),
    		'singular_name'      => _x( 'Member Message', 'post type singular name', 'sejoli' ),
    		'menu_name'          => _x( 'Messages', 'admin menu', 'sejoli' ),
    		'name_admin_bar'     => _x( 'Member Message', 'add new on admin bar', 'sejoli' ),
    		'add_new'            => _x( 'Add New', 'member message', 'sejoli' ),
    		'add_new_item'       => __( 'Add Member Message', 'sejoli' ),
    		'new_item'           => __( 'Add Member Message', 'sejoli' ),
    		'edit_item'          => __( 'Edit Member Message', 'sejoli' ),
    		'view_item'          => __( 'View Member Message', 'sejoli' ),
    		'all_items'          => __( 'All Member Messages', 'sejoli' ),
    		'search_items'       => __( 'Search Member Messages', 'sejoli' ),
    		'parent_item_colon'  => __( 'Parent Member Messages:', 'sejoli' ),
    		'not_found'          => __( 'No member messages found.', 'sejoli' ),
    		'not_found_in_trash' => __( 'No member messages found in Trash.', 'sejoli' )
    	];

    	$args = [
    		'labels'             => $labels,
            'description'        => __( 'Description.', 'sejoli' ),
    		'public'             => false,
    		'publicly_queryable' => false,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
    		'rewrite'            => [ 'slug' => 'member_message' ],
    		'capability_type'    => 'sejoli_member_message',
			'capabilities'		 => array(
				'publish_posts'       => 'publish_sejoli_content',
				'edit_posts'          => 'edit_sejoli_content',
				'edit_others_posts'   => 'edit_others_sejoli_content',
				'read_private_posts'  => 'read_private_sejoli_content',
				'edit_post'           => 'edit_sejoli_content',
				'delete_post'         => 'delete_sejoli_content',
				'delete_posts'        => 'delete_sejoli_content',
				'delete_others_posts' => 'delete_sejoli_content',
				'read_post'           => 'read_sejoli_content'
			),
    		'has_archive'        => true,
    		'hierarchical'       => false,
    		'menu_position'      => null,
    		'supports'           => ['title'],
			'menu_icon'			 => plugin_dir_url( __FILE__ ) . 'images/icon.png'
    	];

    	register_post_type( SEJOLI_MESSAGE_CPT, $args );
	}

	/**
	 * Set style options
	 * @since 1.0.0
	 */
	public function set_style_options() {

		return array(
			'blue'   => __('Blue', 'sejoli'),
			'red'    => __('Red', 'sejoli'),
			'orange' => __('Orange', 'sejoli'),
			'yellow' => __('Yellow', 'sejoli'),
			'olive'  => __('Olive', 'sejoli'),
			'green'  => __('Green', 'sejoli'),
			'teal'   => __('Teal', 'sejoli'),
			'violet' => __('Violet', 'sejoli'),
			'purple' => __('Purple', 'sejoli'),
			'pink'   => __('Pink', 'sejoli'),
			'brown'  => __('Brown', 'sejoli')
		);

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

        $container = Container::make('post_meta', __('Setup Pesan', 'sejoli'))
            ->where( 'post_type', '=', 'sejoli-memmessage')
            ->set_classes('sejoli-metabox')
			->add_tab( __('Pesan', 'sejoli'),[

				Field::make( 'separator', 'sep_message_content', __('Pengaturan Pesan', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="#" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make('html', 'html_sejoli_message_info', __('Informasi', 'sejoli'))
					->set_html( __('Pesan ini akan muncul di halaman member-area user.<br />Untuk pengaturan kondisi penampilan pesan, bisa diatur di tab \'Pengaturan Pembatasan\'')),

				Field::make( 'rich_text',	'message', __('Pesan', 'sejoli'))
					->set_required(true),

				Field::make( 'date_time',	'end_show', __('Akhir pesan ditampilkan', 'sejoli'))
					->set_help_text(__('Kosongkan jika pesan akan terus ditampilkan', 'sejoli')),

				Field::make( 'select',		'style', __('Warna pesan', 'sejoli'))
					->add_options(array($this, 'set_style_options')),

				Field::make('text',			'icon', __('Icon pesan', 'sejoli'))
					->set_default_value('coffee')
					->set_help_text( __('Icon bisa didapatkan di <a href="https://semantic-ui.com/elements/icon.html" target="_blank">https://semantic-ui.com/elements/icon.html</a>.<br />Bisa dikosongkan jika tidak ingin menggunakan icon', 'sejoli'))
			])
            ->add_tab( __('Pembatasan', 'sejoli'),[
				Field::make( 'separator', 'sep_message_condition', __('Pengaturan Pembatasan', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="#" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make( 'association', 'product_association', __( 'Produk', 'sejoli' ) )
                    ->set_types([
                        [
                            'type'      => 'post',
                            'post_type' => 'sejoli-product',
                        ]
                    ])
                    ->set_help_text(__('Isi pesan ini hanya bisa dilihat oleh user yang telah membeli produk yang sesuai.<br /> Jika tidak diisi, maka pesan bisa dilihat oleh SEMUA member.', 'sejoli'))
			]);
    }

	/**
	 * Add product column to access columns
	 * Hooked via filter manage_sejoli-memmessage_posts_columns, priority 100
	 * @since 	1.0.0
	 * @param	array $columns
	 * @return 	array
	 */
	public function add_table_columns(array $columns) {

		unset($columns['date']);

		$columns['sejoli-message-style']    = __('Tampilan', 'sejoli');
		$columns['sejoli-message-end-date'] = __('Batas Waktu', 'sejoli');
		$columns['sejoli-message-product']  = __('Keterkaitan Produk', 'sejoli');

		return $columns;
	}

	protected function display_product_lists($product_list) {

		$products = array();

		foreach($product_list as $product) :
			if(array_key_exists($product['id'], $this->products)) :
				$products[]	= $this->products[$product['id']];
			else :
				$_product = get_post($product['id']);
				$products[]	= $this->products[$product['id']] = '<a href="' . get_permalink($product['id']) . '">' . $_product->post_title . '</a>';
			endif;
		endforeach;

		return $products;

	}

	/**
	 * Display product data to access product column
	 * Hooked via manage_posts_custom_column, priority 100
	 * @since 	1.0.0
	 * @param  	string 		$column
	 * @param  	integer 	$post_id
	 * @return 	void
	 */
	public function display_column_data($column, $post_id) {

		switch ( $column ) :

			case 'sejoli-message-style' :

				$options = $this->set_style_options();
				$style   = sejolisa_carbon_get_post_meta($post_id, 'style');

				if(array_key_exists($style, $options)) :
					echo $options[$style];
				endif;

				break;

			case 'sejoli-message-end-date' :
				$end_date = sejolisa_carbon_get_post_meta($post_id, 'end_show');

				echo (empty($end_date)) ? '-' : date('d F Y, H:i', strtotime($end_date));

				break;

			case 'sejoli-message-product' :

				$products = sejolisa_carbon_get_post_meta($post_id, 'product_association');

				if(!is_array($products) || 0 === count($products)) :
					echo '-';
				else :
					echo implode('<br />', $this->display_product_lists($products));
				endif;
				break;

		endswitch;
	}
}
