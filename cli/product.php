<?php

namespace SejoliSA\CLI;

class Product {

    /**
     * Check product price
     *
     * <product_id>
     * : The product id
     *
     * ## EXAMPLES
     *
     *  wp sejolisa product check_price 56
     *
     * @when after_wp_load
     */
    public function check_price(array $args) {

        list($product_id) = $args;

        $product = sejolisa_get_product($product_id);

        if(is_a($product, 'WP_Post')) :
            \WP_CLI::success( sprintf( __('Product %s price is %s', 'sejoli'), $product->post_title, $product->price));
        else :
            \WP_CLI::error(__('Data not found', 'sejoli'));
        endif;
    }

    /**
     * Check product data
     *
     * <product_id>
     * : The product id
     *
     * ## EXAMPLES
     *
     *  wp sejolisa product get 56
     *
     * @when after_wp_load
     */
    public function get(array $args) {
        list($product_id)   = $args;
        __debug(sejolisa_get_product($product_id));
    }


    /**
     * Check facebook pixel setup
     *
     * <product_id>
     * : The product id
     *
     * [--user_id=<user_id>]
     * : User ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa product get_facebook_pixel 56
     *
     * @when after_wp_load
     */
    public function get_facebook_pixel(array $args, array $assoc_args) {

        list($product_id)   = $args;

        $assoc_args = wp_parse_args($assoc_args,[
            'user_id'  => NULL
        ]);

        if(!is_null($assoc_args['user_id'])) :
            wp_set_current_user($assoc_args['user_id']);
        endif;

        __debug(sejolisa_get_product_fb_pixel_setup($product_id));
    }

    /**
     * Check facebook pixel link
     *
     * <product_id>
     * : The product id
     *
     * [--user_id=<user_id>]
     * : User ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa product get_facebook_pixel 56
     *
     * @when after_wp_load
     */
    public function get_facebook_pixel_link(array $args, array $assoc_args) {

        list($product_id)   = $args;

        __debug(sejolisa_get_product_fb_pixel_links($product_id));
    }
}
