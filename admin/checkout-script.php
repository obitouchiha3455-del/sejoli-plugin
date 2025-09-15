<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class CheckoutScript {

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
		$this->version     = $version;

	}

	/**
	 * Get only all main product options
	 * @since 	1.11.0
	 * @param  	array  $options 	product options
	 * @return 	array
	 */
	public function get_default_product($options = array()) {

		$options = [];

		$args = array(
            'post_type'  => 'sejoli-product',
            'meta_query' => array(
                array(
                    'key'     => '_product_format',
                    'value'   => 'main-product',
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
     * Display checkout script setting fields
     * Hooked via action admin_init
     * @since   1.13.14
     * @param   array  $fields
     * @return  array
     */
	public function sejoli_register_settings() {

	    register_setting('sejoli_checkout_script_group', 'sejoli_checkout_script');

	    add_settings_section(
	        'sejoli_checkout_settings',
	        __('Checkout Script Settings', 'sejoli'),
	        null,
	        'sejoli_checkout_script'
	    );

	    add_settings_field(
	        'product_dropdown',
	        __('Produk', 'sejoli'),
	        [$this, 'sejoli_product_dropdown_render'],
	        'sejoli_checkout_script',
	        'sejoli_checkout_settings'
	    );

	    add_settings_field(
	        'form_width',
	        __('Set Lebar Form', 'sejoli'),
	        [$this, 'sejoli_form_width_render'],
	        'sejoli_checkout_script',
	        'sejoli_checkout_settings'
	    );

	    add_settings_field(
	        'form_height',
	        __('Set Tinggi Form', 'sejoli'),
	        [$this, 'sejoli_form_height_render'],
	        'sejoli_checkout_script',
	        'sejoli_checkout_settings'
	    );

	   	add_settings_field(
	        'form_scrolling',
	        __('Scrolling', 'sejoli'),
	        [$this, 'sejoli_form_scrolling_render'],
	        'sejoli_checkout_script',
	        'sejoli_checkout_settings'
	    );


	    add_settings_field(
	        'enable_product_description',
	        __('Enable Product Description', 'sejoli'),
	        [$this, 'sejoli_enable_product_description_render'],
	        'sejoli_checkout_script',
	        'sejoli_checkout_settings'
	    );

	    add_settings_field(
	        'checkout_design',
	        __('Desain', 'sejoli'),
	        [$this, 'sejoli_checkout_design_render'],
	        'sejoli_checkout_script',
	        'sejoli_checkout_settings'
	    );

	    add_settings_field(
	        'checkout_script_display',
	        __('Tampilan Halaman Checkout', 'sejoli'),
	        [$this, 'sejoli_checkout_display_render'],
	        'sejoli_checkout_script',
	        'sejoli_checkout_settings'
	    );

	    add_settings_field(
	        'checkout_script_code',
	        __('Script', 'sejoli'),
	        [$this, 'sejoli_checkout_script_code_render'],
	        'sejoli_checkout_script',
	        'sejoli_checkout_settings'
	    );

	}

	/**
     * Display product dropdown in checkout script setting fields
     * @since   1.13.14
     * @param   $fields
     * @return  fields
     */
	public function sejoli_product_dropdown_render() {

	    $options  = get_option('sejoli_checkout_script');
	    $products = $this->get_default_product(); // Custom function to fetch products

	    echo '<select name="sejoli_checkout_script[product_dropdown]">';
	    foreach ($products as $product_id => $product_name) :
	        $selected = isset($options['product_dropdown']) && $options['product_dropdown'] == $product_id ? 'selected' : '';
	        echo '<option value="' . esc_attr($product_id) . '" ' . $selected . '>' . esc_html($product_name) . '</option>';
	    endforeach;
	    echo '</select>';

	}

	/**
     * Display form width in checkout script setting fields
     * @since   1.13.14
     * @param   $fields
     * @return  fields
     */
	public function sejoli_form_width_render() {

	    $options = get_option('sejoli_checkout_script');
	    echo '<input type="number" name="sejoli_checkout_script[form_width]" value="' . esc_attr($options['form_width'] ?? '') . '" min="0" max="100" /> %';
	
	}

	/**
     * Display form height in checkout script setting fields
     * @since   1.13.14
     * @param   $fields
     * @return  fields
     */
	public function sejoli_form_height_render() {

	    $options = get_option('sejoli_checkout_script');
	    echo '<input type="number" name="sejoli_checkout_script[form_height]" value="' . esc_attr($options['form_height'] ?? '') . '" min="0" max="2000" /> px';
	
	}

	/**
     * Display form scrolling in checkout script setting fields
     * @since   1.13.14
     * @param   $fields
     * @return  fields
     */
	public function sejoli_form_scrolling_render() {

	   	$options  = get_option('sejoli_checkout_script');

	    echo '<select name="sejoli_checkout_script[form_scrolling]">';
        $selected = isset($options['form_scrolling']) && $options['form_scrolling'] == $product_id ? 'selected' : '';
        echo '<option value="yes" ' . $selected . '>Yes</option>';
        echo '<option value="no" ' . $selected . '>No</option>';
	    echo '</select>';
	
	}

	/**
     * Display enabled product description in checkout script setting fields
     * @since   1.13.14
     * @param   $fields
     * @return  fields
     */
	public function sejoli_enable_product_description_render() {

	    $options = get_option('sejoli_checkout_script');

	    if (!$options) {
	        $options = [
	            'enable_product_description' => 1
	        ];
	        update_option('sejoli_checkout_script', $options);
	    }

	    $checked = isset($options['enable_product_description']) && $options['enable_product_description'] ? 'checked' : '';

	    echo '<input type="checkbox" name="sejoli_checkout_script[enable_product_description]" ' . $checked . ' />';

	}

	/**
     * Display checkout design in checkout script setting fields
     * @since   1.13.14
     * @param   $fields
     * @return  fields
     */
	public function sejoli_checkout_design_render() {

	    $options = get_option('sejoli_checkout_script');
	    $designs = [
	        'default'   => __('Legacy', 'sejoli'),
	        'version-2' => __('Versi 2', 'sejoli'),
	        'modern'    => __('Modern', 'sejoli'),
	        'compact'   => __('Compact', 'sejoli'),
	        'less'      => __('Less is More', 'sejoli'),
	        'smart'     => __('Smart', 'sejoli')
	    ];

	    echo '<select name="sejoli_checkout_script[checkout_design]">';
	    foreach ($designs as $key => $label) :
	        $selected = isset($options['checkout_design']) && $options['checkout_design'] == $key ? 'selected' : '';
	        echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
	    endforeach;
	    echo '</select>';

	}

	/**
     * Display checkout script code in checkout script setting fields
     * @since   1.13.14
     * @param   $fields
     * @return  fields
     */
	public function sejoli_checkout_script_code_render() {

	    $options         = get_option('sejoli_checkout_script');
	    $checkout_script = $options['checkout_script_code'] ?? '';

	    echo '<textarea id="checkout-script-iframe" readonly style="width:100%; height:150px;">' . $checkout_script . '</textarea>';

	    echo '<span class="ui teal right labeled icon button copy-btn" data-clipboard-target="#checkout-script-iframe"><i class="copy icon"></i>'.__('Copy Script', 'sejoli').'</span>';

	    echo "<script>
		    jQuery(document).ready(function($){

		        if (typeof ClipboardJS === 'function') {

		            var clipboard = new ClipboardJS('.copy-btn' );
		            clipboard.on('success', function(e) {
		                // e.preventDefault();
		                if ( e.text !== '' ) {
		                    alert('Copied!');
		                }
		            });
		            clipboard.on('error', function(e) {
		                if ( e.text !== '' ) {
		                    alert('Press Ctrl+C to copy');
		                }
		            });
		        }

		        $('select[name=\"sejoli_checkout_script[product_dropdown]\"]').select2();

		    });
		</script>";

	}

	/**
     * Display checkout display in checkout script setting fields
     * @since   1.13.14
     * @param   $fields
     * @return  fields
     */
	public function sejoli_checkout_display_render() {

	    $options              = get_option('sejoli_checkout_script');
	    $checkout_script_code = $options['checkout_script_code'] ?? '';

		if(isset($checkout_script_code)) :

			$checkout_layout = $checkout_script_code;

		else:

			$checkout_layout = "<p>"._('Script belum dibuat! Silahkan generate script dahulu!.', 'sejoli')."</p>";

		endif;

		echo $checkout_layout;

	}

	/**
     * Register checkout script menu under SEJOLI
     * Hooked via action admin_menu, priority 1010
     * @return  void
     */
	public function sejoli_add_admin_menu() {

	    add_submenu_page(
            'crb_carbon_fields_container_sejoli.php',
            __('Checkout Script', 'sejoli'),
            __('Checkout Script', 'sejoli'),
            'manage_sejoli_orders',
            'sejoli-checkout-script',
            [$this, 'sejoli_checkout_script_page']
        );

	}

	/**
     * Display checkout script page settings
     * @return  void
     */
	public function sejoli_checkout_script_page() {
	    ?>
	    <div class="wrap">
	        <h1><?php _e('Checkout Script Settings', 'sejoli'); ?></h1>
	        <form action="options.php" method="post">
	            <?php
	            settings_fields('sejoli_checkout_script_group');
	            do_settings_sections('sejoli_checkout_script');
	            submit_button();
	            ?>
	        </form>
	    </div>
	    <?php
	}

	/**
     * Generate checkout script
     * Hooked via filter pre_update_option_sejoli_checkout_script, priority 10, 3
     * @return  void
     */
	public function sejoli_generate_checkout_script( $new_value, $old_value, $option ) {

	    if (isset($new_value['product_dropdown'], $new_value['form_width'], $new_value['form_height'], $new_value['form_scrolling'], $new_value['checkout_design'])) :

	        $product_id                 = $new_value['product_dropdown'];
	        $product                    = get_post_field('post_name', $product_id);
	        $form_width                 = $new_value['form_width'];
	        $form_height                = $new_value['form_height'];
	        $form_scrolling             = $new_value['form_scrolling'];
	        $enable_product_description = !empty($new_value['enable_product_description']);
	        $checkout_design            = $new_value['checkout_design'];

	        $script = '<iframe
	            class="iframe-sejoli"
	            src="' . get_home_url() . '/product/' . esc_attr($product) . '/?design=' . esc_attr($checkout_design) . '&description=' . esc_attr($enable_product_description) . '"
	            width="' . esc_attr($form_width) . '%"
	            height="' . esc_attr($form_height) . '"
	            frameborder="0"
	            scrolling="' . esc_attr($form_scrolling) . '"
	            style="max-width: 100vw; height:' . esc_attr($form_height) . 'px;"
	            allowfullscreen></iframe>
	            ';

	        $new_value['checkout_script_code'] = $script;
	    
	    endif;

	    return $new_value;

	}

}