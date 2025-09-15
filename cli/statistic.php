<?php

namespace SejoliSA\CLI;

class Statistic
{
    /**
     * Render data
     * @param  array    $orders
     * @param  string   $view
     * @param  mixed    $fields
     * @return void
     */
    protected function render($data, $view = 'table', $fields = NULL) {

        $fields = (!is_array($fields)) ?
            [
                'ID', 'created_at', 'updated_at', 'deleted_at', 'product_id',
                'user_id', 'affiliate_id', 'coupon_id', 'payment_gateway',
                'grand_total', 'quantity', 'status',
                'meta_data'
            ] : $fields;

        \WP_CLI\Utils\format_items(
            $view,
            $data,
            $fields
        );
    }

    /**
     * Get affiliate statistics
     *
     * ## OPTIONS
     *
     * [--product_id=<product_id>]
     * : The given product id
     *
     * [--affiliate_id=<affiliate_id>]
     * : The given affiliate id
     *
     * [--calculate=<calculate>]
     * : Calculate by order or commission
     *
     * [--order_status=<order_status>]
     * : Order status
     *
     * [--commission_status=<commission_status>]
     * : Commission status
     *
     * [--start_date=<start_date>]
     * : Start date
     *
     * [--end_date=<end_date>]
     * : End date
     *
     * [--sort=<sort>]
     * : Sort data
     *
     * ## EXAMPLES
     *
     *  wp sejolisa statis
     *
     * @when after_wp_load
     */
    public function affiliate(array $args, array $assoc_args) {

        $args = wp_parse_args($assoc_args,[
            'product_id'        => NULL,
            'affiliate_id'      => NULL,
            'calculate'         => 'order',
            'order_status'      => NULL,
            'commission_status' => NULL,
            'start_date'        => NULL,
            'end_date'          => NULL,
            'sort'              => NULL,
        ]);

        $args['product_id']        = (NULL !== $args['product_id']) ? explode(',',$args['product_id']) : NULL;
        $args['affiliate_id']      = (NULL !== $args['affiliate_id']) ? explode(',',$args['affiliate_id']) : NULL;
        $args['order_status']      = (NULL !== $args['order_status']) ? explode(',',$args['order_status']) : NULL;
        $args['commission_status'] = (NULL !== $args['commission_status']) ? explode(',',$args['commission_status']) : NULL;

        $response = sejolisa_get_affiliate_statistic($args);

        if($response['statistic']) :
            $data = [];

            foreach($response['statistic'] as $commission) :
                $data[] = [
                    'user_name' => '('.$commission->ID.') ' . $commission->user_name,
                    'total'     => ('omset' === $args['calculate']) ? sejolisa_price_format($commission->total) : $commission->total
                ];
            endforeach;

            $this->render($data, 'table', [
                'user_name', 'total'
            ]);
        else :
            \WP_CLI::error( __('Found no data', 'sejoli') );
        endif;
    }

    /**
     * Get product statistics
     *
     * ## OPTIONS
     *
     * [--product_id=<product_id>]
     * : The given product id
     *
     * [--calculate=<calculate>]
     * : Calculate by order or total
     *
     * [--order_status=<order_status>]
     * : Order status
     *
     * [--start_date=<start_date>]
     * : Start date
     *
     * [--end_date=<end_date>]
     * : End date
     *
     * [--sort=<sort>]
     * : Sort data
     *
     * ## EXAMPLES
     *
     *  wp sejolisa statis
     *
     * @when after_wp_load
     */
    public function product(array $args, array $assoc_args) {

        $args = wp_parse_args($assoc_args,[
            'product_id'        => NULL,
            'calculate'         => 'order',
            'order_status'      => NULL,
            'start_date'        => NULL,
            'end_date'          => NULL,
            'sort'              => NULL,
        ]);

        $args['product_id']        = (NULL !== $args['product_id']) ? explode(',',$args['product_id']) : NULL;
        $args['order_status']      = (NULL !== $args['order_status']) ? explode(',',$args['order_status']) : NULL;

        $response = sejolisa_get_product_statistic($args);

        if($response['statistic']) :
            $data = [];
            $total = 0;
            foreach($response['statistic'] as $order) :
                $data[] = [
                    'product_name' => '('.$order->ID.') ' . $order->product_name,
                    'total'     => ('omset' === $args['calculate']) ? sejolisa_price_format($order->total) : $order->total
                ];
                $total += $order->total;
            endforeach;

            $data[] = [
                'product_name'  => 'Total',
                'total' => ('omset' === $args['calculate']) ? sejolisa_price_format($total) : $total
            ];

            $this->render($data, 'table', [
                'product_name', 'total'
            ]);
        else :
            \WP_CLI::error( __('Found no data', 'sejoli') );
        endif;
    }


        /**
         * Get buyer statistics
         *
         * ## OPTIONS
         *
         * [--user_id=<user_id>]
         * : The given product id
         *
         * [--product_id=<product_id>]
         * : The given product id
         *
         * [--calculate=<calculate>]
         * : Calculate by order or total
         *
         * [--order_status=<order_status>]
         * : Order status
         *
         * [--start_date=<start_date>]
         * : Start date
         *
         * [--end_date=<end_date>]
         * : End date
         *
         * [--sort=<sort>]
         * : Sort data
         *
         * ## EXAMPLES
         *
         *  wp sejolisa statis
         *
         * @when after_wp_load
         */
        public function buyer(array $args, array $assoc_args) {

            $args = wp_parse_args($assoc_args,[
                'product_id'   => NULL,
                'user_id'      => NULL,
                'calculate'    => 'order',
                'order_status' => NULL,
                'start_date'   => NULL,
                'end_date'     => NULL,
                'sort'         => NULL,
            ]);

            $args['product_id']   = (NULL !== $args['product_id']) ? explode(',',$args['product_id']) : NULL;
            $args['user_id']      = (NULL !== $args['user_id']) ? explode(',',$args['user_id']) : NULL;
            $args['order_status'] = (NULL !== $args['order_status']) ? explode(',',$args['order_status']) : NULL;

            $response = sejolisa_get_buyer_statistic($args);

            if($response['statistic']) :
                $data = [];
                $total = 0;
                foreach($response['statistic'] as $order) :
                    $data[] = [
                        'user_name' => '('. $order->ID .') ' . $order->user_name,
                        'total'     => ('omset' === $args['calculate']) ? sejolisa_price_format($order->total) : $order->total
                    ];
                    $total += $order->total;
                endforeach;

                $data[] = [
                    'user_name'  => 'Total',
                    'total' => ('omset' === $args['calculate']) ? sejolisa_price_format($total) : $total
                ];

                $this->render($data, 'table', [
                    'user_name', 'total'
                ]);
            else :
                \WP_CLI::error( __('Found no data', 'sejoli') );
            endif;
        }

}
