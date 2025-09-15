<?php

namespace SejoliSA\CLI;

class Checkout
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
     * Do checkout
     *
     * ## OPTIONS
     *
     * <product_id>
     * : Product ID
     *
     * [--user_id=<user_id>]
     * : User ID
     *
     * [--coupon=<coupon>]
     * : Coupon
     *
     * [--affiliate_id=<affiliate_id>]
     * : The affiliate ID, default 0
     *
     * [--payment_gateway=<payment_gateway>]
     * : Payment gateway, default manual
     *
     * [--quantity=<quantity>]
     * : Quantity
     *
     * [--user_email=<user_email>]
     * : User email
     *
     * [--user_name=<user_name>]
     * : User fullname
     *
     * [--user_password=<user_password>]
     * : User password
     *
     * [--user_phone=<user_phone>]
     * : User Phone
     *
     * [--shipment=<shipment>]
     * : Shipment data, the format is COURIER:::SERVICE:::COST
     *
     * [--address=<address>]
     * : Address
     *
     * [--variants=<variants>]
     * : Product variants
     *
     * ## EXAMPLES
     *
     *  wp sejolisa checkout do_it 56 --user_id=1 --coupon=ASIKSIk --payment_gateway=bca --quantity=3
     *
     * @when after_wp_load
     */
    public function do_it (array $args, array $assoc_args) {

        list($product_id) = $args;

        $args = wp_parse_args($assoc_args,[
            'user_id'         => NULL,
            'affiliate_id'    => NULL,
            'coupon'          => NULL,
            'payment_gateway' => 'manual',
            'quantity'        => 1,
            'user_email'      => NULL,
            'user_name'       => NULL,
            'user_password'   => NULL,
            'user_phone'      => NULL,
            'district_id'     => NULL,
            'courier'         => NULL,
            'product_id'      => $product_id,
            'variants'        => NULL
        ]);

        $args['variants'] = explode(',', $args['variants']);

        do_action('sejoli/checkout/do', $args);

        $order    = sejolisa_get_respond('order');
        $checkout = sejolisa_get_respond('checkout');

        if(false === $checkout['valid']) :
            \WP_CLI::error_multi_line($checkout['messages']['error']);
        elseif(false !== $order['valid']) :

            $d_order = $order['order'];

            \WP_CLI::success( sprintf( __('Order created successfully. Order ID #%s', 'sejolisa'), $d_order['ID'] ) );

            if(0 < count($order['messages']['warning'])) :
                foreach($order['messages']['warning'] as $message) :
                    \WP_CLI::line('WARNING :: ' . $message);
                endforeach;
            endif;

            if(0 < count($order['messages']['info'])) :
                foreach($order['messages']['info'] as $message) :
                    \WP_CLI::line('INFO :: ' . $message);
                endforeach;
            endif;

            $respond = sejolisa_get_respond();

            if(isset($respond['commission']) && 0 < count($respond['commission'])) :
                foreach($respond['commission'] as $commission) :
                    $comm_data = $commission['commission'];
                    \WP_CLI::success(
                        sprintf(
                            __('Commission created for order ID #%s with commission %s for user id %s tier %s', 'sejolisa'),
                            $d_order['ID'],
                            $comm_data['commission'],
                            $comm_data['affiliate_id'],
                            $comm_data['tier']
                        )
                    );
                endforeach;
            endif;

            if(isset($respond['subscription']) && 0 < count($respond['subscription'])) :
                foreach($respond['subscription'] as $subscription) :
                    $_data = $subscription['subscription'];
                    \WP_CLI::success(
                        sprintf(
                            __('Subscription created for order ID #%s with type %s for user id %s and will be ended at %s', 'sejolisa'),
                            $_data['order_id'],
                            $_data['type'],
                            $_data['user_id'],
                            $_data['end_date']
                        )
                    );
                endforeach;
            endif;
        endif;
    }

    /**
     * Renew subscription
     *
     * ## OPTIONS
     *
     * <order_id>
     * : Order ID
     *
     * [--coupon=<coupon>]
     * : Coupon
     *
     * [--affiliate_id=<affiliate_id>]
     * : The affiliate ID, default 0
     *
     * [--payment_gateway=<payment_gateway>]
     * : Payment gateway, default manual
     *
     * ## EXAMPLES
     *
     *  wp sejolisa checkout renew 102 --coupon=asiksik --affiliate_id=3 --payment_gateway=bca
     *
     * @when after_wp_load
     */
    public function renew(array $args, array $assoc_args) {

        list($order_id) = $args;

        $args = wp_parse_args($assoc_args,[
            'order_id'        => $order_id,
            'coupon'          => NULL,
            'affiliate_id'    => NULL,
            'payment_gateway' => 'manual'
        ]);

        do_action('sejoli/checkout/renew', $args);

        $order    = sejolisa_get_respond('order');
        $checkout = sejolisa_get_respond('checkout');

        if(false === $checkout['valid']) :
            \WP_CLI::error_multi_line($checkout['messages']['error']);
        elseif(false !== $order['valid']) :

            $d_order = $order['order'];

            \WP_CLI::success( sprintf( __('Order created successfully. Order ID #%s', 'sejolisa'), $d_order['ID'] ) );

            $respond = sejolisa_get_respond();

            if(isset($respond['commission']) && 0 < count($respond['commission'])) :
                foreach($respond['commission'] as $commission) :
                    $comm_data = $commission['commission'];
                    \WP_CLI::success(
                        sprintf(
                            __('Commission created for order ID #%s with commission %s for user id %s tier %s', 'sejolisa'),
                            $d_order['ID'],
                            $comm_data['commission'],
                            $comm_data['affiliate_id'],
                            $comm_data['tier']
                        )
                    );
                endforeach;
            endif;

            if(isset($respond['subscription']) && 0 < count($respond['subscription'])) :
                foreach($respond['subscription'] as $subscription) :
                    $_data = $subscription['subscription'];
                    \WP_CLI::success(
                        sprintf(
                            __('Subscription created for order ID #%s with type %s for user id %s and will be ended at %s', 'sejolisa'),
                            $_data['order_id'],
                            $_data['type'],
                            $_data['user_id'],
                            $_data['end_date']
                        )
                    );
                endforeach;
            endif;
        endif;
    }

    /**
     * Do calculation
     *
     * ## OPTIONS
     *
     * <product_id>
     * : Product ID
     *
     * [--coupon=<coupon>]
     * : Coupon Code
     *
     * [--quantity=<quantity>]
     * : Item quantity, default 1
     *
     * [--payment_gateway=<payment_gateway>]
     * : Payment gateway, default manual
     *
     * [--shipment=<shipment>]
     * : Shipment value, default manual
     *
     * [--variants=<variants>]
     * : Product variants
     *
     * ## EXAMPLES
     *
     *  wp sejolisa checkout calculate 102 --coupon=asiksik --quantity=3 --payment_gateway=bca
     *
     * @when after_wp_load
     */
    public function calculate(array $args, array $assoc_args) {

        list($product_id) = $args;

        $args = wp_parse_args($assoc_args,[
            'product_id'      => $product_id,
            'coupon'          => NULL,
            'quantity'        => 1,
            'type'            => 'regular',
            'payment_gateway' => 'manual',
            'shipment'        => NULL,
            'variants'        => NULL,
            'address'
        ]);

        $args['variants'] = explode(',', $args['variants']);

        do_action('sejoli/checkout/calculate', $args);

        $respond  = sejolisa_get_respond('total');
        $checkout = sejolisa_get_respond('checkout');

        if(!isset($checkout['valid'])) :

            \WP_CLI::success(sprintf(__('Total pembayaran IDR %s', 'sejoli'), $respond['total']));

            if(0 < count($respond['messages']['warning'])) :
                foreach($respond['messages']['warning'] as $message) :
                    \WP_CLI::line('WARNING :: ' . $message);
                endforeach;
            endif;

            if(0 < count($respond['messages']['info'])) :
                foreach($respond['messages']['info'] as $message) :
                    \WP_CLI::line('INFO :: ' . $message);
                endforeach;
            endif;
        else :
            \WP_CLI::error_multi_line($checkout['messages']['error']);
        endif;
    }

    /**
     * Do shipping calculation
     *
     * ## OPTIONS
     *
     * <product_id>
     * : Product ID
     *
     * <district_id>
     * : Kecamatan ID
     *
     * [--quantity=<quantity>]
     * : Item quantity, default 1
     *
     * [--variants=<variants>]
     * : Product variants
     *
     * ## EXAMPLES
     *
     *  wp sejolisa checkout shipping 87 jne --quantity=3
     *
     * @when after_wp_load
     */
    public function shipping(array $args, array $assoc_args) {

        list($product_id, $district_id) = $args;

        $args = wp_parse_args($assoc_args, [
            'product_id'  => $product_id,
            'district_id' => $district_id,
            'quantity'    => 1,
            'variants'    => NULL,
        ]);

        $args['variants'] = explode(',', $args['variants']);

        do_action('sejoli/checkout/shipment-calculate', $args);

        $available_couriers = apply_filters('sejoli/shipment/available-couriers', []);

		if(
			false !== $available_couriers &&
			is_array($available_couriers) &&
			0 < count($available_couriers)
		) :
			$couriers = implode(':', array_keys($available_couriers));

            \WP_CLI::line(sprintf(__('Available couriers : %s', 'sejoli'), $couriers));
            $shipment_data = sejolisa_get_respond('shipment');

            \WP_CLI::line($shipment_data['messages']['info'][0]);

            if(false !== $shipment_data['valid']) :
                foreach($shipment_data['shipment'] as $key => $label) :
                    \WP_CLI::line($key.' = '.$label);
                endforeach;
            else :
                \WP_CLI::error_multi_line($shipment_data['messages']['error']);
            endif;
        else :
            \WP_CLI::error(__('There is no available couriers in your setting options', 'sejoli'));
        endif;
    }
}
