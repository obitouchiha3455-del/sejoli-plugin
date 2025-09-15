<?php
/**
 * Add first subscription
 * @since   1.0.0
 * @param   array   $args
 * @return  array
 */
function sejolisa_add_subscription(array $args) {

    $args = wp_parse_args($args, [
        'order_id'        => NULL,
        'user_id'         => NULL,
        'product_id'      => NULL,
        'type'            => 'regular',
        'order_parent_id' => NULL,
        'end_time'        => 0,
        'status'          => 'active'
    ]);

    $respond = SejoliSA\Model\Subscription::reset()
                    ->set_product_id($args['product_id'])
                    ->set_order_id($args['order_id'])
                    ->set_order_parent_id($args['order_parent_id'])
                    ->set_user_id($args['user_id'])
                    ->set_type($args['type'])
                    ->set_end_time($args['end_time'])
                    ->set_status($args['status'])
                    ->create()
                    ->respond();

    return wp_parse_args($respond, [
        'valid'        => false,
        'subscription' => NULL,
        'messages'     => NULL
    ]);
}

/**
 * Get latest subscription by order id
 * @since   1.0.0
 * @param   integer $order_id
 * @return  array
 */
function sejolisa_get_subscription_by_order($order_id) {
    $respond = SejoliSA\Model\Subscription::reset()
                ->set_order_id($order_id)
                ->get_by_order()
                ->respond();

    return wp_parse_args($respond, [
        'valid'        => false,
        'subscription' => NULL,
        'messages'     => NULL
    ]);
}

/**
 * Check subscription by order or order parent
 * @since   1.0.0
 * @param   integer     $order_id
 * @return  array
 */
function sejolisa_check_subscription($order_id) {

    $respond = SejoliSA\Model\Subscription::reset()
                ->set_order_id($order_id)
                ->set_order_parent_id($order_id)
                ->check_subscription()
                ->respond();

    return wp_parse_args($respond, [
        'valid'        => false,
        'subscription' => NULL,
        'messages'     => NULL
    ]);
}

/**
 * Update subscription status
 * @since   1.0.0
 * @param   array  $args
 * @return  array
 */
function sejolisa_update_subscription_status(array $args) {
    $args = wp_parse_args($args,[
        'ID'     => null,
        'status' => 'pending'
    ]);

    $respond    = SejoliSA\Model\Subscription::reset()
                    ->set_id($args['ID'])
                    ->set_status($args['status'])
                    ->update_status()
                    ->respond();

    return wp_parse_args($respond, [
        'valid'        => false,
        'subscription' => NULL,
        'messages'     => NULL
    ]);
}

/**
 * Get all subscriptions
 * @param  array  $args
 * @param  array  $table
 * @return
 * - valid          boolean
 * - subscriptions  array
 * - messages       array
 */
function sejolisa_get_subscriptions(array $args, $table = array()) {

    $args = wp_parse_args($args, [
        'type'       => NULL,
        'status'     => NULL,
        'user_id'    => NULL,
        'product_id' => NULL
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => []
    ]);

    if(isset($args['ID'])) :
        $args['order_id'] = $args['ID'];
        unset($args['ID']);
    endif;

    if(isset($args['date-range']) && !empty($args['date-range'])) :
        $table['filter']['date-range'] = $args['date-range'];
        unset($args['date-range']);
    endif;

    $query = SejoliSA\Model\Subscription::reset()
                ->set_filter_from_array($args)
                ->set_data_start($table['start']);

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query = $query->set_filter('subscription.created_at', $start.' 00:00:00', '>=')
                    ->set_filter('subscription.created_at', $end.' 23:59:59', '<=');
    endif;

    if(0 < $table['length']) :
        $query->set_data_length($table['length']);
    endif;

    if(!is_null($table['order']) && is_array($table['order'])) :
        foreach($table['order'] as $order) :
            $query->set_data_order($order['column'], $order['sort']);
        endforeach;
    endif;

    $respond = $query->get()->respond();

    return $respond;
}

/**
 * Check subscription status by order
 * @since   1.0.0
 * @param   array   $order_data
 * @return  boolean
 */
function sejolisa_subscription_is_active($order_data) {

    $order_data = wp_parse_args($order_data, [
        'ID'              => NULL,
        'type'            => 'regular',
        'order_parent_id' => 0,
    ]);

    $active = true;

    if('regular' !== $order_data['type']) :
        $active = apply_filters('sejoli/subscription/is-active', $active, $order_data);
    endif;

    return $active;
}

/**
 * Get all products by subscription by user id
 * @since   1.0.0
 * @param   integer     $user_id
 * @return  array
 * - valid
 * - products
 * - messages
 */
function sejolisa_get_product_by_subscriptions($user_id) {

    $respond = SejoliSA\model\Subscription::reset()
                    ->set_user_id($user_id)
                    ->get_products()
                    ->respond();

    return wp_parse_args($respond, [
        'valid'    => false,
        'products' => NULL,
        'messages' => []
    ]);
}

/**
 * Get all affiliates by subscription by user id
 * @since   1.0.0
 * @param   integer     $user_id
 * @return  array
 * - valid
 * - products
 * - messages
 */
function sejolisa_get_affiliate_by_subscriptions($user_id) {

    $respond = SejoliSA\model\Subscription::reset()
                    ->set_user_id($user_id)
                    ->get_affiliates()
                    ->respond();

    return wp_parse_args($respond, [
        'valid'      => false,
        'affiliates' => NULL,
        'messages'   => []
    ]);
}

/**
 * Get expired subscriptions
 * @since   1.5.3
 * @return  array
 * - valid
 * - subscriptions
 * - messages
 */
function sejolisa_get_expired_subscriptions( $max_renewal_day = 0 ) {

    $respond = SejoliSA\model\Subscription::reset()
                    ->set_max_renewal_day( $max_renewal_day )
                    ->get_expired_subscriptions()
                    ->respond();

    return wp_parse_args($respond, [
        'valid'         => false,
        'subscriptions' => NULL,
        'messages'      => array()
    ]);

}

/**
 * Get difference day
 * @since   1.5.3
 * @param   integer     $timestamp           unix timestamp
 * @param   integer     $current_time
 * @return  integer
 */
function sejolisa_get_difference_day( $timestamp, $given_time = NULL ) {

    $given_time = empty( $given_time ) ? current_time('timestamp') : $given_time;

    return round(
        ( $given_time - $timestamp ) /
        DAY_IN_SECONDS
    );

}

/**
 * Get subscription by status is expired
 * @since   1.0.0
 * @param   string $status Status of license
 * @return  array
 */
function sejolisa_get_expired_subscription_data() {

    $respond = SejoliSA\model\Subscription::reset()
                    ->get_expired_subscriptions()
                    ->respond();

    return wp_parse_args($respond, [
        'valid'         => false,
        'subscriptions' => NULL,
        'messages'      => array()
    ]);

}

function sejolisa_get_subscription_by_status($status) {

    $query = Sejolisa\Model\Subscription::get_subscription_expired($status);

    return $query;

}

/**
 * Reset multiple subscription string
 * @since   1.0.0
 * @param   string      $status         Status of subscription
 * @param   array       $subscriptions       Array of subscription id
 * @param   boolean     $force_reset    Force to reset subscription by ignore current user
 * @return  array       Array of response
 */
function sejolisa_reset_subscriptions(array $subscriptions, $force_reset = false) {

    $query = Sejolisa\Model\Subscription::reset()
                ->set_filter('ID', $subscriptions);

    if(!current_user_can('manage_sejoli_subscriptions') && !$force_reset) :
        $query = $query->set_filter('user_id', get_current_user_id());
    endif;

    $response = $query->reset_string()
                    ->respond();

    return wp_parse_args($response,[
        'valid' => false
    ]);
}

/**
 * Update multiple subscription status
 * @since   1.0.0
 * @param   string  $status     Status of subscription
 * @param   array   $subscriptions   Array of subscription id
 * @return  array   Array of response
 */
function sejolisa_update_status_subscriptions(array $args) {

    $args = wp_parse_args($args,[
        'subscriptions' => null,
        'status' => 'pending'
    ]);

    $respond    = SejoliSA\Model\Subscription::reset()
                    ->set_filter('ID', $args['subscriptions'])
                    ->set_status($args['status'])
                    ->update_status_multiple()
                    ->respond();

    return wp_parse_args($respond, [
        'valid'        => false,
        'subscription' => NULL,
        'messages'     => NULL
    ]);

}