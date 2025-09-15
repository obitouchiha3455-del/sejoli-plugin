<?php

namespace SejoliSA\Front;

class Product
{
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
     * Construction
    */
    public function __construct( $plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Check access code by AJAX
     * Hooked via sejoli_ajax_check-checkout-code-access, priority 1
     * @since   1.1.6
     * @return  string  Json string
     */
    public function check_access_code_by_ajax() {

        $post_data = wp_parse_args($_POST, array(
            'code_access'       => NULL,
            'product_id'        => 0,
            'sejoli_ajax_nonce' => NULL
        ));

        $response = array(
            'valid'     => false,
            'message'   => __('Maaf, anda tidak bisa mengakses halaman checkout', 'sejoli')
        );

        if(
            sejoli_ajax_verify_nonce('sejoli-checkout-code-access') &&
            !empty($post_data['code_access']) &&
            !empty($post_data['product_id'])
        ):
            $code_access         = sanitize_text_field($post_data['code_access']);
            $product_id          = $post_data['product_id'];
            $product_code_access = sejolisa_carbon_get_post_meta($product_id, 'coupon_access_checkout');

            if($code_access === $product_code_access) :

                $cookie_name         = 'SEJOLI-ACCESS-PRODUCT-' . $product_id;
                $lifespan_cookie_day = time() + 1800;

                setcookie($cookie_name, $code_access, $lifespan_cookie_day, COOKIEPATH, COOKIE_DOMAIN);

                $response = array(
                    'valid'     => true,
                    'message'   => __('Anda akan dialihkan ke halaman checkout. Tunggu sebentar', 'sejoli')
                );
            endif;
        endif;

        wp_send_json($response);
    }
}
