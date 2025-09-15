<?php
/**
 * Get affiliate statistic
 * @since   1.0.0
 * @param   array  $args [description]
 * @return  array
 */
function sejolisa_get_affiliate_statistic(array $args) {
    $args = wp_parse_args($args,[
        'product_id'        => NULL,
        'affiliate_id'      => NULL,
        'calculate'         => 'order',
        'order_status'      => NULL,
        'commission_status' => NULL,
        'start_date'        => NULL,
        'end_date'          => NULL,
        'sort'              => NULL,
    ]);

    $response = SejoliSA\Model\Statistic::reset()
                    ->set_product($args['product_id'])
                    ->set_affiliate($args['affiliate_id'])
                    ->set_count_by($args['calculate'])
                    ->set_order_status($args['order_status'])
                    ->set_commission_status($args['commission_status'])
                    ->set_start_date($args['start_date'])
                    ->set_end_date($args['end_date'])
                    ->set_sort($args['sort'])
                    ->calculate_by_affiliate()
                    ->respond();

    return $response;
}

/**
 * Get product statistic
 * @since   1.0.0
 * @param   array  $args [description]
 * @return  void
 */
function sejolisa_get_product_statistic(array $args) {
    $args = wp_parse_args($args,[
        'product_id'        => NULL,
        'calculate'         => 'order',
        'order_status'      => NULL,
        'start_date'        => NULL,
        'end_date'          => NULL,
        'sort'              => NULL,
        'affiliate_id'      => NULL
    ]);

    $query = SejoliSA\Model\Statistic::reset()
                    ->set_product($args['product_id'])
                    ->set_count_by($args['calculate'])
                    ->set_order_status($args['order_status'])
                    ->set_start_date($args['start_date'])
                    ->set_end_date($args['end_date'])
                    ->set_sort($args['sort']);

    if(!empty($args['affiliate_id'])) :
        $query = $query->set_affiliate($args['affiliate_id']);
    endif;

    $response = $query->calculate_by_product()
                    ->respond();

    return $response;
}

/**
 * Get buyert statistic
 * @since   1.0.0
 * @param   array  $args [description]
 * @return  void
 */
function sejolisa_get_buyer_statistic(array $args) {
    $args = wp_parse_args($args,[
        'user_id'           => NULL,
        'product_id'        => NULL,
        'calculate'         => 'order',
        'order_status'      => NULL,
        'start_date'        => NULL,
        'end_date'          => NULL,
        'sort'              => NULL,
    ]);

    $response = SejoliSA\Model\Statistic::reset()
                    ->set_product($args['product_id'])
                    ->set_user($args['user_id'])
                    ->set_count_by($args['calculate'])
                    ->set_order_status($args['order_status'])
                    ->set_start_date($args['start_date'])
                    ->set_end_date($args['end_date'])
                    ->set_sort($args['sort'])
                    ->calculate_by_buyer()
                    ->respond();

    return $response;
}

/**
 * Get commission statistic
 * @since   1.0.0
 * @param   array  $args    Arguments
 * @return  array
 */
function sejolisa_get_commission_statistic(array $args) {
    $args = wp_parse_args($args,[
        'status'       => NULL,
        'affiliate_id' => NULL,
        'start_date'   => NULL,
        'end_date'     => NULL,
        'sort'         => NULL,
    ]);

    $response = SejoliSA\Model\Statistic::reset()
                    ->set_affiliate($args['affiliate_id'])
                    ->set_commission_status($args['status'])
                    ->set_start_date($args['start_date'])
                    ->set_end_date($args['end_date'])
                    ->set_sort($args['sort'])
                    ->calculate_commission_by_product()
                    ->respond();

    return $response;
}
