<?php

namespace SejoliSA\CLI;

class Ajax
{

    /**
     * List coupon parent
     *
     * ## OPTIONS
     * <user_id>
     * : User ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa ajax list_parent_coupons 1
     *
     * @when after_wp_load
     */
    public function list_parent_coupons(array $args) {

        list($user_id)  = $args;

        $_POST = [
            'nonce' => wp_create_nonce('sejoli-list-parent-coupons')
        ];

        wp_set_current_user($user_id);

        do_action('wp_ajax_sejoli-list-coupons');
    }

    /**
     * Create affiliate coupon
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User id
     *
     * <coupon_parent_id>
     * : Coupon parent ID
     *
     * <coupon_code>
     * : Coupon code
     *
     * ## EXAMPLES
     *
     *  wp sejolisa ajax create_coupon 10 12 codecoupone
     *
     * @when after_wp_load
     */
    public function create_coupon( array $args ) {

        list($user_id, $coupon_parent_id, $coupon_code) = $args;

        $_POST = [
            'code'             => $coupon_code,
            'coupon_parent_id' => $coupon_parent_id,
            'nonce'            => wp_create_nonce('sejoli-create-affiliate-coupon')
        ];

        wp_set_current_user($user_id);

        do_action('wp_ajax_sejoli-create-coupon');
    }

    /**
     * List orders
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User id
     *
     * [--product_id=<product_id>]
     * : The product ID
     *
     * [--coupon_id=<coupon_id>]
     * : The coupon ID, default 0
     *
     * [--status=<status>]
     * : Order status, default on-hold
     *
     * [--payment_gateway=<payment_gateway>]
     * : Payment gateway for the order, default manual
     *
     * [--date_range=<date_range>]
     * : Date range filter
     *
     * ## EXAMPLES
     *
     *  wp sejolisa ajax list_orders 1 --product_id=87
     *
     * @when after_wp_load
     */
    public function list_orders( array $args )  {

        list($user_id) = $args;

        $_POST = [
            'product_id'      => NULL,
            'coupon_id'       => NULL,
            'payment_gateway' => NULL,
            'status'          => NULL,
            'type'            => NULL,
            'date_range'      => NULL,
            'search'          => NULL,
            'nonce'           => wp_create_nonce('sejoli-list-orders')
        ];

        wp_set_current_user($user_id);

        do_action('wp_ajax_sejoli-order-table');
    }

    /**
     * List affiliate orders
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User id
     *
     * [--product_id=<product_id>]
     * : The product ID
     *
     * [--coupon_id=<coupon_id>]
     * : The coupon ID, default 0
     *
     * [--status=<status>]
     * : Order status, default on-hold
     *
     * [--payment_gateway=<payment_gateway>]
     * : Payment gateway for the order, default manual
     *
     * [--date_range=<date_range>]
     * : Date range filter
     *
     * ## EXAMPLES
     *
     *  wp sejolisa ajax list_orders 1 --product_id=87
     *
     * @when after_wp_load
     */
    public function list_affiliate_orders( array $args )  {

        list($user_id) = $args;

        $_POST = [
            'product_id'      => NULL,
            'coupon_id'       => NULL,
            'payment_gateway' => NULL,
            'status'          => NULL,
            'type'            => NULL,
            'date_range'      => NULL,
            'search'          => NULL,
            'nonce'            => wp_create_nonce('sejoli-list-orders')
        ];

        wp_set_current_user($user_id);

        do_action('wp_ajax_sejoli-affiliate-order-table');
    }

    /**
     * List affiliate product link
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User id
     *
     * <product_id>
     * : Product id
     *
     * ## EXAMPLES
     *
     *  wp sejolisa ajax list_product_affiliate_link 1 87
     *
     * @when after_wp_load
     */
    public function list_product_affiliate_link( array $args ) {

        list($user_id, $product_id) = $args;

        $_POST = [
            'product_id'    => $product_id,
            'nonce'         => wp_create_nonce('sejoli-list-product-affiliate-link')
        ];

        wp_set_current_user($user_id);

        do_action('wp_ajax_sejoli-product-affiliate-link-list');

    }

    /**
     * List affiliate product help content
     *
     * ## OPTIONS
     *
     * <product_id>
     * : Product id
     *
     * ## EXAMPLES
     *
     *  wp sejolisa ajax list_product_affiliate_link 1 87
     *
     * @when after_wp_load
     */
    public function list_product_affiliate_help( array $args ) {

        list($product_id) = $args;

        $_POST = [
            'product_id'    => $product_id,
            'nonce'         => wp_create_nonce('sejoli-list-product-affiliate-help')
        ];

        do_action('wp_ajax_sejoli-product-affiliate-help-list');

    }

    /**
     * Get affiliate bonus content
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User id
     *
     * <product_id>
     * : Product id
     *
     * ## EXAMPLES
     *
     *  wp sejolisa ajax get_bonus_content 6 87
     *
     * @when after_wp_load
     */
    public function get_bonus_content(array $args) {
        list($user_id, $product_id) = $args;

        $_GET = [
            'product_id'    => $product_id,
            'nonce'         => wp_create_nonce('sejoli-affiliate-get-bonus-content')
        ];

        wp_set_current_user($user_id);

        do_action('wp_ajax_sejoli-affiliate-get-bonus-content');
    }

    /**
     * Update affiliate bonus content
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User id
     *
     * <product_id>
     * : Product id
     *
     * <content>
     * : Content
     *
     * ## EXAMPLES
     *
     *  wp sejolisa ajax update_bonus_content 6 87 'Asik aja situ sih'
     *
     * @when after_wp_load
     */
    public function update_bonus_content(array $args) {
        list($user_id, $product_id, $content) = $args;

        $_POST = [
            'product_id' => $product_id,
            'content'    => $content,
            'nonce'      => wp_create_nonce('sejoli-affiliate-update-bonus-content')
        ];

        wp_set_current_user($user_id);

        do_action('wp_ajax_sejoli-affiliate-update-bonus-content');
    }
}
