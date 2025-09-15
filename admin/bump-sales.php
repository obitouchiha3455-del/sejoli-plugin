<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

final class BumpSales {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.11.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.11.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.11.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Get bump product options
	 * @since 	1.11.0
	 * @param  	array  $options 	City options
	 * @return 	array
	 */
	public function get_bump_product($options = array()) {

		$options = [];

		$args = array(
            'post_type'  => 'sejoli-product',
            'meta_query' => array(
            	'relation'  => 'AND',
                array(
                    'key'   => '_product_type',
                    'value' => 'digital',
                    'compare' => '='
                ),
                array(
                    'key'   => '_product_format',
                    'value' => 'bump-product',
                    'compare' => '='
                ),
            ),
            'posts_per_page'         => -1,
	        'no_found_rows'          => true,
	        'update_post_meta_cache' => false,
	        'update_post_term_cache' => false
        );

        $query = new \WP_Query($args);

        if ( $query->have_posts() ) :

            while ( $query->have_posts() ) : $query->the_post();

                $options[get_the_ID()] = get_the_title();

	        endwhile;

	        wp_reset_postdata();

        endif;

        asort($options);

		return $options;

	}

    /**
     * Display product setting fields
     * Hooked via filter sejoli/product/fields, priority 90
     * @since   1.11.0
     * @param   array  $fields   Container fields
     * @return  array
     */
    public function setup_product_bump_sales_setting_fields( array $fields ) {

    	global $post;

		$product_id = (is_object($post) && property_exists($post, 'ID')) ? $post->ID : 0;

        $fields[]   = array(

            'title'  => __('Penawaran', 'sejoli'),
            'fields' => array(

                Field::make( 'separator', 'sep_bump_sales' , __('Bump Sales', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('shipping') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make('html', 'html_info_bump_sales')
                    ->set_html('<div class="sejoli-html-message info"><p>'. __('Pengaturan ini hanya akan muncul jika tipe produk adalah produk digital dan format produk adalah main produk', 'sejoli') . '</p></div>'),

                Field::make( 'radio', 'bump_product_type', __('Jenis Bump Produk', 'sejoli'))
					->set_options([
						'bump-sale-offer'	 => __('Bump Sale Offer' ,'sejoli'),
						// 'up-down-sale-offer' => __('Upsale / Downsale Offer', 'sejoli')
					])
					->set_default_value('bump-sale-offer')
					->set_width(50)
					->set_conditional_logic(array(
						'relation' => 'AND',
						array(
							'field'	=> 'product_type',
							'value'	=> 'digital'
						),
						array(
							'field' => 'product_format',
							'value' => 'main-product'
						)
					)),
                Field::make('multiselect', 'sejoli_bump_sales', __('Produk', 'sejoli'))
                    ->set_options(array($this, 'get_bump_product'))
                    ->set_conditional_logic(array(
						'relation' => 'AND',
						array(
							'field'	=> 'product_type',
							'value'	=> 'digital'
						),
						array(
							'field' => 'product_format',
							'value' => 'main-product'
						)
					))
                    ->set_help_text(__('Produk Bump Sales yang akan digunakan pada pembelian produk ini', 'sejoli'))

            )
        );

        return $fields;

    }

	/**
	 * Modify product title
	 * Hooked via filter display_post_states, priority 11
	 * @since 	1.11.0
	 * @param 	array 	$post_states
	 * @return 	array
	 */
	public function display_product_states(array $post_states) {

		global $post;

		if(is_a($post, 'WP_Post')) :

			$product_type = sejolisa_carbon_get_post_meta($post->ID, 'product_type');

			if('digital' === $product_type) :

				$product_format = sejolisa_carbon_get_post_meta($post->ID, 'product_format');

				if('bump-product' === $product_format) :
					$post_states[]	= __('Bump Sale', 'sejoli');
				endif;
			endif;

		endif;

		return $post_states;
	}
}
