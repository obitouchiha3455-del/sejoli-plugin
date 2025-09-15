<?php

namespace SejoliSA\CLI;

class License
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
     * Create coupon
     *
     * ## OPTIONS
     *
     * <order_id>
     * : Order ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa license create 31
     *
     * @when after_wp_load
     */
    public function create(array $args) {

        list($order_id) = $args;

        do_action('sejoli/license/create', $order_id);

        $respond = sejolisa_get_respond('license');

        if(false !== $respond['valid']) :
            $license = $respond['license'];
            \WP_CLI::success(sprintf(__('License for order %d created successfull. The code is %s', 'sejoli'), $license['order_id'], $license['code']));
        else :
            \WP_CLI::error_multi_line($respond['messages']['error']);
        endif;
    }

    /**
     * Check license
     *
     * ## OPTIONS
     *
     * <code>
     * : License code
     *
     * <email>
     * : User email
     *
     * <password>
     * : User password
     *
     * <string>
     * : String
     *
     * ## EXAMPLES
     *
     *  wp sejolisa license check 0001572721-Y7ZD7-YD32K-1UJXS-K95NH hi@orangerdigiart 123456 test.com
     *
     * @when after_wp_load
     */
    public function check( array $args ) {
        list(
            $license_code,
            $user_email,
            $user_pass,
            $string
        ) = $args;

        $data = [
            'user_email' => $user_email,
			'user_pass'  => $user_pass,
			'license'    => $license_code,
			'string'     => $string
        ];

        $available = apply_filters('sejoli/license/availability', NULL, $data);

        if(false === $available['valid']) :
            \WP_CLI::error_multi_line($available['messages']);
        else :
            foreach($available['messages'] as $message) :
                \WP_CLI::success($message);
            endforeach;
        endif;
    }
}
