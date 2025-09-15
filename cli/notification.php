<?php

namespace SejoliSA\CLI;

class Notification
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
                'ID', 'created_at', 'updated_at', 'deleted_at',
                'order_id', 'product_id', 'user_id',
                'code', 'string', 'status',
                'meta_data'
            ] : $fields;

        \WP_CLI\Utils\format_items(
            $view,
            $orders,
            $fields
        );
    }
    /**
     * Simulate sending notification for order
     *
     * ## OPTIONS
     *
     * <order_id>
     * : Order ID
     *
     * [--status=<status>]
     * : The order status, default null. If null the status is the order status
     *
     * ## EXAMPLES
     *
     *  wp sejolisa notification order 31 --status=in-progress
     *
     * @when after_wp_load
     */
    public function order(array $args, array $assoc_args) {

        list($order_id) = $args;

        $args = wp_parse_args($assoc_args,[
            'status' => NULL,
            'ID'     => $order_id
        ]);

        $respond = sejolisa_get_order(['ID' => $args['ID']]);

        if(false !== $respond['valid']) :

            $order  = $respond['orders'];
            $status = (is_null($args['status'])) ? $order['status'] : $args['status'];

            do_action('sejoli/notification/order/' . $status, $order);

        else :
        endif;

    }

    /**
     * Simulate sending notification for user
     *
     * ## OPTIONS
     *
     * <user_id>
     * : Order ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa notification registration 1
     *
     * @when after_wp_load
     */
    public function registration(array $args) {

        list($user_id) = $args;

        $user = sejolisa_get_user($user_id);

        $data = [
            'user_name'     => $user->display_name,
            'user_email'    => $user->user_email,
            'user_phone'    => $user->meta->phone,
            'user_password' => 123456
        ];

        do_action('sejoli/notification/registration', $data);
    }
}
