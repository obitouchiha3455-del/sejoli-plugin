<?php

namespace SejoliSA\CLI;

class Commission
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
                'ID', 'created_at', 'updated_at', 'deleted_at', 'order_id',
                'product_id', 'affiliate_id', 'tier', 'commission',
                'status'
            ] : $fields;

        \WP_CLI\Utils\format_items(
            $view,
            $orders,
            $fields
        );
    }

    /**
     * Get commission
     *
     * ## OPTIONS
     *
     * [--order_id=<order_id>]
     * : The order ID
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
     * [--status=<status>]
     * : Order status, default on-hold
     *
     * [--tier=<tier>]
     * : The tier of commission
     *
     * ## EXAMPLES
     *
     *  wp sejolisa commission get --product_id=56
     *
     * @when after_wp_load
     */
    public function get(array $args, array $assoc_args) {

        $assoc_args = wp_parse_args($assoc_args,[
            'product_id'   => NULL,
            'order_id'     => NULL,
            'affiliate_id' => NULL,
            'coupon_id'    => NULL,
            'tier'         => NULL,
            'status'       => NULL
        ]);

        $respond = sejolisa_get_commissions($assoc_args);

        if(
            isset($respond['commissions']) &&
            is_array($respond['commissions']) &&
            0 < count($respond['commissions'])
        ) :

            $commission_data = $respond['commissions'];
            $this->render($commission_data);
            \WP_CLI::success( sprintf( __('Total record : %s', 'sejolisa'), $respond['recordsTotal']));
        else :
            \WP_CLI::error( __('Data not found', 'sejolisa') );
        endif;
    }

    /**
     * Get all affiliate commission info
     *
     * ## OPTIONS
     *
     * ## EXAMPLES
     *
     *  wp sejolisa commission affiliate_info
     *
     * @when after_wp_load
     */
    public function affiliate_info() {

        $response = sejolisa_get_affiliate_commission_info();
        __debug($response);
    }

    /**
     * Get single affiliate commission info
     *
     * ## OPTIONS
     *
     * <affiliate_id>
     * : Affiliate ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa commission single_affiliate_info 10
     *
     * @when after_wp_load
     */
    public function single_affiliate_info(array $args) {

        list( $affiliate_id) = $args;

        $response = sejolisa_get_single_affiliate_commission_info($affiliate_id);

        __debug($response);
    }

    /**
     * Get list affiliate commission by product
     *
     * ## OPTIONS
     *
     * <product_id>
     * : Product ID
     *
     * <affiliate_id>
     * : Affiliate ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa commission render_list 6 14
     *
     * @when after_wp_load
     */
    public function render_list(array $args, array $assoc_args) {

        list($product_id, $affiliate_id) = $args;

        $affiliate_commissions = array();
        $product               = sejolisa_get_product($product_id);
        $commission_data       = array();
        $commissions           = sejolisa_carbon_get_post_meta($product_id, 'sejoli_commission');

		foreach((array) $commissions as $i => $commission) :

            $tier = $i + 1;

            $commission_data[$tier] = [
				'tier'	=> $tier,
				'fee'	=> floatval($commission['number']),
				'type'	=> $commission['type']
			];

		endforeach;

        $order_data = array(
            'product_id'    => $product_id,
            'grand_total'   => $product->price,
            'quantity'      => 1
        );

		if(0 < count($commission_data)) :

			$users      = [];
			$max_tier   = count($commission_data);
			$affiliates = apply_filters('sejoli/affiliate/uplines', array(), $affiliate_id, $max_tier);

			foreach($affiliates as $tier => $affiliate_id) :

				if(isset($commission_data[$tier])) :

                    $affiliate = sejolisa_get_user($affiliate_id);

					$commission_set = $commission_data[$tier];
					$commission     = apply_filters( 'sejoli/order/commission', 0, $commission_set, $order_data, $tier, $affiliate_id);

                    $affiliate_commissions[] = array(
                        'tier'       => $tier,
                        'affiliate'  => $affiliate->display_name,
                        'commission' => $commission

                    );

				endif;

			endforeach;

		endif;

        $this->render(
            $affiliate_commissions,
            'table',
            array(
                'tier', 'affiliate', 'commission'
            )
        );
    }
}
