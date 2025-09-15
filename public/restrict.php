<?php

namespace SejoliSA\Front;

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
     * Get protected product's ID
     * @since   1.0.0
     * @param   integer     $post_id    Current post ID
     * @return  array       Array of product ID
     */
    protected function get_product_protected($post_id) {

        $protected_products = [];
        $products           = sejolisa_carbon_get_post_meta($post_id, 'product_association');

        if(is_array($products) && 0 < count($products)) :
            foreach($products as $product) :
                $protected_products[] = $product['id'];
            endforeach;
        endif;

        return $protected_products;
    }

    /**
     * Check if current page is protected and user is able to access
     * Hooked via action wp priority 1
     * @since   1.0.0
     * @return  void
     */
    public function check_if_page_is_protected() {

        global $post;

        $able_to_access = true;
        $is_redirected  = false;

        if(is_singular()) :

            $is_protected          = boolval(sejolisa_carbon_get_post_meta($post->ID, 'restrict_content'));
            $is_redirected         = boolval(sejolisa_carbon_get_post_meta($post->ID, 'redirect_if_no_access'));
            $redirect_link         = esc_url(sejolisa_carbon_get_post_meta($post->ID, 'redirect_link'));
            $message               = sejolisa_carbon_get_post_meta($post->ID, 'message_no_access');
            $protected_by_products = $this->get_product_protected($post->ID);

            if(0 === $protected_by_products) :
                return;
            endif;

            if(false !== $is_protected) :
                if(!is_user_logged_in()) :
                    $able_to_access = false;
                else :
                    $bought_products = sejolisa_get_user_products_bought(get_current_user_id());
                    if( 0 === count(array_intersect($bought_products, $protected_by_products))) :
                        $able_to_access = false;
                    endif;
                endif;
            endif;

        endif;


        if(true !== $able_to_access) :

            if(false !== $is_redirected) :
                wp_safe_redirect($redirect_link);
            else :
                wp_die(
                    $message,
                    __('Anda tidak diizinkan mengakses halaman ini', 'sejoli')
                );
            endif;
            exit;
        endif;

    }
}
