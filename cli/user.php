<?php

namespace SejoliSA\CLI;

class User
{
    /**
     * Get user data
     *
     * ## OPTIONS
     *
     * <user_data>
     * : User value, can be ID, email or phone number
     *
     * ## EXAMPLES
     *
     *  wp sejolisa user get 08112411111
     *
     * @when after_wp_load
     */
    public function get(array $args) {

        list($user_data) = $args;

        __debug(sejolisa_get_user($user_data));
    }

    /**
     * Register user
     *
     * ## OPTIONS
     *
     * <username>
     * : Username
     *
     * <email>
     * : Email
     *
     * <phonenumber>
     * : Phone number
     *
     * [--user_password=<user_password>]
     * : Passwords
     *
     * ## EXAMPLES
     *
     *  wp sejolisa user register Dylan test@gmail.com 0823232323 --password=123232
     *
     * @when after_wp_load
     */
    public function register(array $args, array $assoc_args) {

        list($name, $email, $phone) = $args;

        $args = wp_parse_args($assoc_args,[
            'user_email'      => $email,
            'user_name'       => $name,
            'user_password'   => NULL,
            'user_phone'      => $phone,
        ]);

        do_action('sejoli/user/register', $args);

        $user_data = sejolisa_get_user($args['user_email']);
    }

    /**
     * Get upline data
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User Id
     *
     * [--limit=<limit>]
     * : Limit of upper line, default NULL
     *
     * ## EXAMPLES
     *
     *  wp sejolisa user upline 1 --limit=2
     *
     * @when after_wp_load
     */
    public function upline(array $args, array $assoc_args) {
        list($user_id) = $args;
        $args = wp_parse_args($assoc_args,[
            'user_id' => $user_id,
            'limit'   => 0
        ]);

        __debug(sejolisa_user_get_uplines($args['user_id'], $args['limit']));
    }

    /**
     * Get downline data
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User Id
     *
     * [--limit=<limit>]
     * : Limit of depth line, default NULL
     *
     * ## EXAMPLES
     *
     *  wp sejolisa user downline 1 --limit=2
     *
     * @when after_wp_load
     */
    public function downline(array $args, array $assoc_args) {
        list($user_id) = $args;
        $args = wp_parse_args($assoc_args,[
            'user_id' => $user_id,
            'limit'   => 0
        ]);

        __debug(sejolisa_user_get_downlines($args['user_id'], $args['limit']));
    }

    /**
     * Get group detail
     *
     * ## OPTIONS
     *
     * <group_id>
     * : Group ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa user group_detail 701
     *
     * @when after_wp_load
     */
    public function group_detail(array $args) {
        list($group_id) = $args;

        __debug(sejolisa_get_group_detail($group_id));
    }

    /**
     * Simulate update group by order
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User ID
     *
     * <product_id>
     * : Product ID
     *
     *
     * ## EXAMPLES
     *
     *  wp sejolisa user group_order 14 701
     *
     * @when after_wp_load
     */
    public function group_order(array $args) {

        list($user_id, $product_id) = $args;

        $response_allow_buy = sejolisa_check_user_permission_by_product_group($product_id, $user_id);
        $update_user_group  = sejolisa_check_update_user_group_by_product($product_id, $user_id);

        __debug(
            array(
                'allow'  => $response_allow_buy,
                'update' => $update_user_group
            )
        );
    }

    /**
     * Get product price with user group simulation
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User ID
     *
     * <product_id>
     * : Product ID
     *
     *
     * ## EXAMPLES
     *
     *  wp sejolisa user check_price 14 701
     *
     * @when after_wp_load
     */
    public function check_price(array $args) {

        list($user_id, $product_id) = $args;

        wp_set_current_user ( $user_id );

        $product = sejolisa_get_product($product_id);

        if(is_a($product, 'WP_Post')) :
            \WP_CLI::success( sprintf( __('Product %s price is %s', 'sejoli'), $product->post_title, $product->price));
        else :
            \WP_CLI::error(__('Data not found', 'sejoli'));
        endif;
    }

    /**
     * Simulate how to update user group based on product group setting
     *
     * ## OPTIONS
     *
     * <user_id>
     * : User ID
     *
     * <product_id>
     * : Product ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa user update_user_group 14 701
     *
     * @when after_wp_load
     */
    public function update_user_group(array $args, array $assoc_args) {

        list($user_id, $product_id) = $args;

        $order_data = array(
            'user_id'    => $user_id,
            'product_id' => $product_id
        );

        __debug(
            sejolisa_check_update_user_group_by_product($order_data['product_id'], $order_data['user_id'])
        );
    }

}
