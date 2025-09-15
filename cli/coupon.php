<?php

namespace SejoliSA\CLI;

class Coupon
{

    /**
     * Render data
     * @param  array    $orders
     * @param  string   $view
     * @param  mixed    $fields
     * @return void
     */
    protected function render($orders, $view = 'table', $fields = NULL) {

        $fields = (!is_array($fields)) ?
            [
                'ID', 'created_at', 'updated_at', 'deleted_at', 'user_id',
                'coupon_parent_id', 'code', 'rule', 'discount',
                'usage', 'limit_use', 'limit_date',
                'status'
            ] : $fields;

        \WP_CLI\Utils\format_items(
            $view,
            $orders,
            $fields
        );
    }

    /**
     * Create coupon
     *
     * ## OPTIONS
     *
     * <coupon>
     * : Coupon code
     *
     * <user_id>
     * : The coupon owner, user_id
     *
     * [--coupon_parent_id=<coupon_parent_id>]
     * : Parent coupon ID
     *
     * [--discount=<discount>]
     * : Number of discount, this will be ignored when coupon_parent_id is set
     *
     * [--discount_type=<discount_type>]
     * : Discount type, default fixed,  this will be ignored when coupon_parent_id is set
     *
     * [--discount_usage=<discount_usage>]
     * : Discount applied based on event, default per_item,  this will be ignored when coupon_parent_id is set
     *
     * [--limit_use=<limit_use>]
     * : Number of limit use,  this will be ignored when coupon_parent_id is set
     *
     * [--limit_day=<limit_day>]
     * : Number of limit day, will be counted as how many days since the coupon created,  this will be ignored when coupon_parent_id is set
     *
     * [--status=<status>]
     * : Coupon status, default pending
     *
     * ## EXAMPLES
     *
     *  wp sejolisa coupon create sejoli-asik 1 --parent_coupon=10 --discount=13 --discount_type=percentage --discount_usage=grand_total --limit_use=100 --limit_day=21 --status=active
     *
     * @when after_wp_load
     */
    public function create(array $args, array $assoc_args) {

        list($code, $user_id) = $args;

        $coupon_data = $assoc_args = wp_parse_args($assoc_args,[
            'coupon_parent_id' => NULL,
            'discount'         => 10,
            'discount_type'    => 'percentage',
            'discount_usage'   => 'per_item',
            'limit_use'        => 0,
            'limit_day'        => 0,
            'status'           => 'pending'
        ]);

        $coupon_data['code']       = $code;
        $coupon_data['user_id']    = $user_id;
        $coupon_data['limit_date'] = (0 !== $coupon_data['limit_day']) ? date('Y-m-d H:i:s',strtotime('+' . $coupon_data['limit_day'] . 'day')) : NULL;


        do_action('sejoli/coupon/create', $coupon_data);

        $respond = sejolisa_get_respond('coupon');

        if(false !== $respond['valid']) :
            \WP_CLI::success( sprintf( __('Coupon %s created successfully', 'sejoli'), $coupon_data['code']));
        else :
            \WP_CLI::error_multi_line($respond['messages']['error']);
        endif;
    }

    /**
     * Get coupon by coupon
     *
     * ## OPTIONS
     *
     * <coupon>
     * : Coupon code
     *
     * ## EXAMPLES
     *
     *  wp sejolisa coupon get coupon-code
     *
     * @when after_wp_load
     */
    public function check(array $args) {
        list($code) = $args;

        $respond    = sejolisa_get_coupon_by_code($code);

        $this->render([$respond['coupon']], 'yaml');
    }


    /**
     * Update coupon
     *
     * ## OPTIONS
     *
     * <coupon_id>
     * : Coupon ID
     *
     * [--discount=<discount>]
     * : Number of discount, this will be ignored when coupon_parent_id is set
     *
     * [--discount_type=<discount_type>]
     * : Discount type, default fixed,  this will be ignored when coupon_parent_id is set
     *
     * [--discount_usage=<discount_usage>]
     * : Discount applied based on event, default per_item,  this will be ignored when coupon_parent_id is set
     *
     * [--limit_use=<limit_use>]
     * : Number of limit use,  this will be ignored when coupon_parent_id is set
     *
     * [--limit_day=<limit_day>]
     * : Number of limit day, will be counted as how many days since the coupon created,  this will be ignored when coupon_parent_id is set
     *
     * [--status=<status>]
     * : Coupon status, default pending
     *
     * ## EXAMPLES
     *
     *  wp sejolisa coupon update 1 --discount=13 --discount_type=percentage --discount_usage=grand_total --limit_use=100 --limit_day=21 --status=active
     *
     * @when after_wp_load
     */
    public function update(array $args, array $assoc_args) {
        list($coupon_id) = $args;

        $coupon_data = $assoc_args = wp_parse_args($assoc_args,[
            'discount'         => 10,
            'discount_type'    => 'percentage',
            'discount_usage'   => 'per_item',
            'limit_use'        => 0,
            'limit_day'        => 0,
            'status'           => 'pending'
        ]);

        $coupon_data['ID']         = $coupon_id;
        $coupon_data['limit_date'] = (0 !== $coupon_data['limit_day']) ? date('Y-m-d H:i:s',strtotime('+' . $coupon_data['limit_day'] . 'day')) : NULL;

        do_action('sejoli/coupon/update', $coupon_data);

        $respond = sejolisa_get_respond('coupon');

        if(false !== $respond['valid']) :
            \WP_CLI::success( sprintf( __('Coupon %s updated successfully', 'sejoli'), $respond['coupon']['code']));
            $this->render([$respond['coupon']], 'yaml');
        else :
            \WP_CLI::error_multi_line($respond['messages']['error']);
        endif;
    }

    /**
     * Update coupon
     *
     * ## OPTIONS
     *
     * <coupon_id>
     * : Coupon ID
     *
     * <user_id>
     * : Owner of coupon
     *
     * <status>
     * : Coupon status
     *
     * ## EXAMPLES
     *
     *  wp sejolisa coupon update_status 10 1 active
     *
     * @when after_wp_load
     */
    public function update_status(array $args, array $assoc_args) {

        list($coupon_id, $user_id, $status) = $args;

        $coupon_data = [
            'ID'      => $coupon_id,
            'user_id' => $user_id,
            'status'  => $status,
        ];

        do_action('sejoli/coupon/update-status', $coupon_data);

        $respond = sejolisa_get_respond('coupon');

        if(false !== $respond['valid']) :
            \WP_CLI::success( sprintf( __('Coupon %s status updated successfully', 'sejoli'), $respond['coupon']['code']));
            $this->render([$respond['coupon']], 'yaml');
        else :
            \WP_CLI::error_multi_line($respond['messages']['error']);
        endif;
    }

    /**
     * Get coupon
     *
     * ## OPTIONS
     *
     * [--coupon_parent_id=<coupon_parent_id>]
     * : Coupon parent ID
     *
     * [--status=<status>]
     * : Coupon status
     *
     * [--user_id=<user_id>]
     * : Owner of user id
     *
     * ## EXAMPLES
     *
     *  wp sejolisa coupon get --status=active --coupon_parent_id=10
     *
     * @when after_wp_load
     */
    public function get(array $args, array $assoc_args) {
        $assoc_args = wp_parse_args($assoc_args, [
            'coupon_parent_id' => NULL,
            'status'           => NULL,
            'user_id'          => NULL
        ]);

        $respond = sejolisa_get_coupons($assoc_args);

        if(!is_null($respond['coupons'])) :
            $this->render($respond['coupons']);
        else :
            \WP_CLI::error( __('Data not found', 'sejolisa') );
        endif;
    }

    /**
     * Update coupon usage
     *
     * ## OPTIONS
     *
     * <coupon>
     * : Coupon Code
     *
     * ## EXAMPLES
     *
     *  wp sejolisa coupon update_usage couponcode
     *
     * @when after_wp_load
     */
    public function update_usage(array $args) {
        list($code) = $args;

        do_action('sejoli/coupon/update-usage', $code);

        $respond = sejolisa_get_respond('coupon');

        if(false !== $respond['valid']) :
            \WP_CLI::success( __('Kupon berhasi dipakai', 'sejoli'));
            $this->render([$respond['coupon']], 'yaml');
        else :
            \WP_CLI::error_multi_line($respond['messages']['error']);
        endif;
    }

    /**
     * Check affiliate coupon avaibility
     *
     * ## OPTIONS
     *
     * <coupon_id>
     * : Coupon ID
     *
     * <affiliate_id>
     * : Affiliate ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa coupon check_affiliate_coupon 2 12
     *
     * @when after_wp_load
     */
    public function check_affiliate_coupon_availibility(array $args) {
        list($coupon_parent_id, $user_id) = $args;

        $available = sejolisa_is_affiliate_coupon_available(array(
            'coupon_parent_id' => $coupon_parent_id,
            'user_id'          => $user_id
        ));

        if(false !== $available) :
            \WP_CLI::success( sprintf(__('Affiliate %s for coupon %s available', 'sejoli'), $user_id, $coupon_parent_id) );
        else :
            \WP_CLI::error( sprintf( __('Affiliate %s for coupon %s not available', 'sejoli'), $user_id, $coupon_parent_id) );
        endif;
    }
}
