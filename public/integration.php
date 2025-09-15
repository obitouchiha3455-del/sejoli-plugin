<?php

namespace SejoliSA\Front;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Integration {

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
	 * Initialize the class and set its properties.
	 *
	 * @since   1.3.2
	 * @param   string    $plugin_name      The name of the plugin.
	 * @param   string    $version    		The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

    /**
     * Get current page facebook pixel data
     * @since   1.3.
     * @param   integer     $post_id
     * @return  false|array
     */
    protected function get_facebook_pixel_setup($post_id) {

        $setup  = false;

        if(false !== boolval(sejolisa_carbon_get_post_meta($post_id, 'fb_pixel_active')) ):

            $setup = array(
                'active'           => boolval(sejolisa_carbon_get_post_meta($post_id, 'fb_pixel_active')),
                'affiliate_active' => boolval(sejolisa_carbon_get_post_meta($post_id, 'fb_pixel_affiliate_active')),
                'id'               => trim(sejolisa_carbon_get_post_meta($post_id, 'fb_pixel_id')),
                // 'content_category' => trim(sejolisa_carbon_get_post_meta($post_id, 'fb_pixel_content_category')),
                // 'content_type'     => trim(sejolisa_carbon_get_post_meta($post_id, 'fb_pixel_content_type')),
                'links'            => sejolisa_get_product_fb_pixel_links($post_id)
            );

        endif;

        return $setup;
    }

    /**
     * Get affiliate pixel ID
     * @since   1.3.2
     * @param   integer     $product_id
     * @param   array       $setup
     * @param   string      $type
     * @return  false|string;
     */
    protected function get_affiliate_pixel_id($product_id, array $setup, $type = 'checkout') {

        $affiliate_id = $pixel_id = false;

        if('checkout' === $type) :
            $cookie     = sejolisa_get_affiliate_cookie();

            if(isset($cookie['product']) && isset($cookie['product'][$product_id])) :
                $affiliate_id = $cookie['product'][$product_id];
            elseif(isset($cookie['general']) && !empty($cookie['general'])) :
                $affiliate_id = $cookie['general'];
            endif;

		elseif('redirect' === $type) :

			global $sejolisa;

			$order 	= $sejolisa['order'];

			if(isset($order['affiliate_id'])) :
				$affiliate_id = intval($order['affiliate_id']);
			endif;
        endif;

        if(
            $affiliate_id &&
            false !== $setup['affiliate_active']
        ) :
            $pixel_id = sejolisa_get_affiliate_facebook_pixel_id($affiliate_id, $product_id);
        endif;

        return $pixel_id;
    }

    /**
     * Set facebook pixel in local data
     * Hooked via action wp_enqueue_scripts, priority 888
     * @since   1.3.2
     * @since 	1.5.1.1 	Remove content_type and content_category
     * @return  void
     */
    public function set_localize_js_vars() {

        $product_id = 0;
		$setup      = false;
		if(
			sejolisa_verify_checkout_page('loading') ||
			sejolisa_verify_checkout_page('thank-you') ||
			sejolisa_verify_checkout_page('renew')
		) :

			global $sejolisa;

			if(isset($sejolisa['order'])) :

				$order          = $sejolisa['order'];
				$product_id     = intval($order['product_id']);

				if(is_numeric($product_id) && 0 !== $product_id) :

					$product        = sejolisa_get_product($product_id);
		            $setup 			= $this->get_facebook_pixel_setup($order['product_id']);
		            $type  			= sejolisa_verify_checkout_page('thank-you') ? 'invoice' : 'redirect';
					$value          = $order['grand_total'];

					if($setup) :
						$affiliate_id 	= (0 < $order['affiliate_id']) ?
							sejolisa_get_affiliate_facebook_pixel_id($order['affiliate_id'], $product_id) :
							NULL;
					endif;

				endif;

			endif;

        elseif(sejolisa_is_checkout_page() && !sejolisa_verify_page('confirm')) :

            global $post;

            $product_id 	= $post->ID;
			$product        = sejolisa_get_product($product_id);

			if(is_numeric($product_id) && 0 !== $product_id) :

	            $setup      	= $this->get_facebook_pixel_setup($product_id);
	            $type       	= 'checkout';
				$value          = $product->price;
				$key            = 'sejoli-checkout';

				if($setup) :
					$affiliate_id   = $this->get_affiliate_pixel_id($product_id, $setup, $type);
				endif;

			endif;

        endif;

        if( ! $setup) :
            return;
        endif;

        $currency_type = sejolisa_carbon_get_theme_option('sejoli_currency_type');

        $sejoli_fb_pixel = [
		    'id'               => $setup['id'],
		    'affiliate_id'     => $affiliate_id,
		    'affiliate_active' => boolval(sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_affiliate_active')),
		    'product_id'       => $product_id,
		    'current_event'    => $type,
		    'value'            => $value,
		    'currency'         => $currency_type,
		    'event'            => array(
		        'checkout'       => $setup['links']['checkout']['type'],
		        'submit'         => $setup['links']['submit']['type'],
		        'redirect'       => $setup['links']['redirect']['type'],
		        'invoice'        => $setup['links']['invoice']['type']
		    )
		];

        if ($affiliate_id <= 0) {
		    unset($sejoli_fb_pixel['affiliate_id']);
		}

        wp_localize_script('sejoli-checkout', 'sejoli_fb_pixel', $sejoli_fb_pixel);

    }
}
