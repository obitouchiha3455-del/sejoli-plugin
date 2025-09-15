<?php

namespace SejoliSA\CLI;

class Order
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
                'ID', 'created_at', 'updated_at', 'deleted_at', 'product_id',
                'user_id', 'affiliate_id', 'coupon_id', 'payment_gateway',
                'grand_total', 'quantity', 'status',
                'meta_data'
            ] : $fields;

        \WP_CLI\Utils\format_items(
            $view,
            $orders,
            $fields
        );
    }

    /**
     * Create new order
     *
     * ## OPTIONS
     *
     * <product_id>
     * : The product ID
     *
     * <user_id>
     * : The buyer ID
     *
     * [--affiliate_id=<affiliate_id>]
     * : The affiliate ID, default 0
     *
     * [--coupon_id=<coupon_id>]
     * : The coupon ID, default 0
     *
     * [--quantity=<quantity>]
     * : Quantity of item. default 1
     *
     * [--status=<status>]
     * : Order status, default on-hold
     *
     * [--payment_gateway=<payment_gateway>]
     * : Payment gateway for the order, default manual
     *
     * [--total=<total>]
     * : Grand total of the order, default 0
     *
     * ## EXAMPLES
     *
     *  wp sejolisa order create 10 1
     *
     * @when after_wp_load
     */
    public function create(array $args, array $assoc_args)
    {
        global $sejolisa;

        list($product_id, $user_id) = $args;

        $assoc_args['product_id']   = $product_id;
        $assoc_args['user_id']      = $user_id;

        $args = wp_parse_args($assoc_args, [
            'product_id'      => NULL,
            'user_id'         => NULL,
            'affiliate_id'    => NULL,
            'coupon_id'       => NULL,
            'quantity'        => 1,
            'status'          => 'on-hold',
            'payment_gateway' => 'manual',
            'total'           => NULL
        ]);

        do_action('sejoli/order/create', $args);

        $respond = sejolisa_get_respond();

        if(false !== $respond['order'][0]['valid']) :
            $order = $respond['order'][0]['order'];
            \WP_CLI::success( sprintf( __('Order created successfully. Order ID #%s', 'sejolisa'), $order['ID'] ) );

            if(isset($respond['commission']) && 0 < count($respond['commission'])) :
                foreach($respond['commission'] as $commission) :
                    $comm_data = $commission['commission'];
                    \WP_CLI::success(
                        sprintf(
                            __('Commission created for order ID #%s with commission %s for user id %s tier %s', 'sejolisa'),
                            $order['ID'],
                            $comm_data['commission'],
                            $comm_data['affiliate_id'],
                            $comm_data['tier']
                        )
                    );
                endforeach;
            endif;
        else :
            $messages = $respond['order'][0]['messages']['error'];
            \WP_CLI::error_multi_line($messages);
        endif;
    }

    /**
     * Get orders
     *
     * ## OPTIONS
     *
     * [--product_id=<product_id>]
     * : The product ID
     *
     * [--user_id=<user_id>]
     * : The buyer ID
     *
     * [--affiliate_id=<affiliate_id>]
     * : The affiliate ID, default 0
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
     * ## EXAMPLES
     *
     *  wp sejolisa order get --product_id
     *
     * @when after_wp_load
     */
    public function get(array $args, array $assoc_args) {

        $assoc_args = wp_parse_args($assoc_args,[
            'product_id'      => NULL,
            'user_id'         => NULL,
            'affiliate_id'    => NULL,
            'coupon_id'       => NULL,
            'payment_gateway' => NULL,
            'status'          => NULL
        ]);

        $respond = sejolisa_get_orders($assoc_args);

        if(
            isset($respond['orders']) &&
            is_array($respond['orders']) &&
            0 < count($respond['orders'])
        ) :

            $order_data = $respond['orders'];
            $this->render($order_data);
            \WP_CLI::success( sprintf( __('Total record : %s', 'sejolisa'), $respond['recordsTotal']));
        else :
            \WP_CLI::error( __('Data not found', 'sejolisa') );
        endif;
    }

    /**
     * Get orders by single column
     *
     * ## OPTIONS
     *
     * <column_name>
     * : Column name
     *
     * <column_value>
     * : Column value
     *
     * [--single=<single>]
     * : Get only single data (only first data)
     *
     * ## EXAMPLES
     *
     *  wp sejolisa order get_by user_id 1 --single=true
     *
     * @when after_wp_load
     */
    public function getby(array $args, array $assoc_args) {

        list($column, $value) = $args;
        $assoc_args = wp_parse_args($assoc_args, [
            'single' => NULL
        ]);

        if(NULL !== $assoc_args['single']) :
            $respond = sejolisa_get_order([
                $column => $value
            ]);
        else :
            $respond = sejolisa_get_orders([
                $column => $value
            ]);
        endif;

        if(!is_null($respond['orders'])) :
            if(NULL !== $assoc_args['single']) :
                __debug($respond['orders']);
                //$this->render([$respond['orders']],'yaml');
            else :
                $this->render($respond['orders']);
            endif;
        else :
            \WP_CLI::error( __('Data not found', 'sejolisa') );
        endif;
    }

    /**
     * Update order status
     *
     * ## OPTIONS
     *
     * <order_id>
     * : The order order
     *
     * <order_status>
     * : The order status
     *
     * ## EXAMPLES
     *
     *  wp sejolisa order update_status 12 completed
     *
     * @when after_wp_load
     */
    public function update_status(array $args) {
        list($order_id, $status) = $args;

        do_action('sejoli/order/update-status',[
            'ID'     => $order_id,
            'status' => $status,
        ]);

        $respond       = sejolisa_get_respond();
        $order_respond = $respond['order'][0];

        if(false !== $order_respond['valid']) :
            \WP_CLI::success($order_respond['messages']['success'][0]);


            if(isset($respond['subscription']) && 0 < count($respond['subscription'])) :
                foreach($respond['subscription'] as $subscription) :
                    foreach($subscription['messages'] as $type => $messages) :
                        if('error' === $type) :
                                \WP_CLI::error_multi_line($messages);
                        else :
                            foreach($messages as $message) :
                                if('success' === $type) :
                                    \WP_CLI::success($message);
                                endif;
                            endforeach;
                        endif;
                    endforeach;
                endforeach;
            endif;
        else :
            \WP_CLI::error_multi_line($order_respond['messages']['error']);
        endif;
    }

    /**
     * Update acquisition data
     *
     * ## OPTIONS
     *
     * <product_id>
     * : The product ID
     *
     * <affiliate_ids>
     * : The affiliate ID
     *
     * <source>
     * : Source
     *
     * <media>
     * : Media
     *
     * [--action=<action>]
     * : Action
     *
     * ## EXAMPLES
     *
     *  wp sejolisa order update_acquisition 351 1 fb test-1 --action=lead
     *
     * @when after_wp_load
     */
    public function update_acquisition(array $args, array $assoc_args) {

        list($product_id, $affiliate_id, $source, $media) = $args;

        $assoc_args = wp_parse_args($assoc_args,[
            'action'    => 'lead'
        ]);

        $response = sejolisa_update_acquisition_value([
            'affiliate_id' => $affiliate_id,
            'product_id'   => $product_id,
            'source'       => $source,
            'media'        => $media
        ], $assoc_args['action']);

        __debug($response);

    }
}
