<?php

/**
 * Create order
 * @param  array  $args
 * @return array
 * - valid      bool
 * - order ID   integer     if the order created successfully, it will return the order ID, beside that the value is NULL
 * - messages   array
 */
function sejolisa_create_order(array $args) {

    $args   = wp_parse_args($args, [
        'product_id'      => NULL,
        'order_parent_id' => NULL,
        'user_id'         => NULL,
        'affiliate_id'    => NULL,
        'coupon_id'       => NULL,
        'quantity'        => 1,
        'status'          => 'on-hold',
        'payment_gateway' => 'manual',
        'type'            => 'regular',
        'grand_total'     => 0,
        'meta_data'       => []
    ]);

    $payment_module = apply_filters('sejoli/payment/module', $args['payment_gateway']);

    $respond = SejoliSA\Model\Order::reset()
                    ->set_product_id($args['product_id'])
                    ->set_order_parent_id($args['order_parent_id'])
                    ->set_user_id($args['user_id'])
                    ->set_affiliate_id($args['affiliate_id'])
                    ->set_coupon_id($args['coupon_id'])
                    ->set_quantity($args['quantity'])
                    ->set_status($args['status'])
                    ->set_type($args['type'])
                    ->set_payment_gateway( $payment_module )
                    ->set_total($args['grand_total'])
                    ->set_meta_data($args['meta_data'])
                    ->create()
                    ->respond();

    if(false !== $respond['valid']) :
        $respond['order']['meta_data'] = maybe_unserialize($respond['order']['meta_data']);
    endif;

    return wp_parse_args($respond,[
        'valid'    => false,
        'order'    => NULL,
        'messages' => []
    ]);
}

/**
 * Update order status
 * @param  array  $args
 * @return array
 * - valid      bool
 * - order      array
 * - messages   array
 */
function sejolisa_update_order_status(array $args) {

    $args = wp_parse_args($args,[
        'ID'     => NULL,
        'status' => NULL
    ]);

    $respond = SejoliSA\Model\Order::reset()
                    ->set_id($args['ID'])
                    ->set_status($args['status'])
                    ->update_status()
                    ->respond();

    return wp_parse_args($respond,[
        'valid'    => false,
        'orders'   => NULL,
        'messages' => []
    ]);
}

/**
 * Update order meta data
 * @since   1.0.0
 * @param   integer  $order_id
 * @param   array    $meta_data
 * @return  array
 * - valid      bool
 * - order      array
 * - messages   array
 */
function sejolisa_update_order_meta_data($order_id, array $meta_data) {

    $response = SejoliSA\Model\Order::reset()
                    ->set_id($order_id)
                    ->set_meta_data($meta_data)
                    ->update_meta_data()
                    ->respond();

    return wp_parse_args($response,[
        'valid'    => false,
        'order'    => NULL,
        'messages' => []
    ]);
}

/**
 * Get list of orders
 * @param  array  $args
 * @param  array  $table
 * @return array
 * - valid      bool
 * - order      array
 * - messages   array
 */
function sejolisa_get_orders(array $args, $table = array()) {

    $args = wp_parse_args($args,[
        'product_id'      => NULL,
        'user_id'         => NULL,
        'affiliate_id'    => NULL,
        'coupon_id'       => NULL,
        'payment_gateway' => NULL,
        'status'          => NULL,
        'type'            => NULL
    ]);

    $table = wp_parse_args($table, [
        'start'  => NULL,
        'length' => NULL,
        'order'  => NULL,
        'filter' => NULL
    ]);

    if(isset($args['date-range']) && !empty($args['date-range'])) :
        $table['filter']['date-range'] = $args['date-range'];
        unset($args['date-range']);
    endif;

    $query = SejoliSA\Model\Order::reset()
                ->set_filter_from_array($args)
                ->set_data_start($table['start']);

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query = $query->set_filter('created_at', $start.' 00:00:00', '>=')
                    ->set_filter('created_at', $end.' 23:59:59', '<=');
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

    foreach($respond['orders'] as $i => $order) :
        $respond['orders'][$i]->product   = sejolisa_get_product( intval($order->product_id) );
        $respond['orders'][$i]->meta_data = apply_filters('sejoli/order/table/meta-data', maybe_unserialize($order->meta_data), $respond['orders'][$i]);
    endforeach;

    return wp_parse_args($respond,[
        'valid'    => false,
        'orders'   => NULL,
        'messages' => []
    ]);
}

/**
 * Get single order by latest
 * @since   1.0.0
 * @param  array  $args
 * @return array
 * - valid      bool
 * - order      array
 * - messages   array
 */
function sejolisa_get_order(array $args) {

    reset($args);

    $column  = key($args);
    $value   = $args[$column];
    $respond = SejoliSA\Model\Order::reset()
                    ->get_by($column, $value )
                    ->respond();

    if(false !== $respond['valid']) :
        $respond['orders']['meta_data'] = maybe_unserialize($respond['orders']['meta_data']);
        $respond['orders'] = apply_filters('sejoli/order/order-detail', $respond['orders']);
        $respond['orders']['confirm_detail'] = maybe_unserialize($respond['orders']['confirm_detail']);        
    endif;

    return wp_parse_args($respond,[
        'valid'     => false,
        'orders'    => NULL,
        'messages'  => []
    ]);
}

/**
 * Get previous order from a user with selected product
 * @since   1.5.3.3
 * @param   integer     $product_id
 * @param   integer     $user_id
 * @return  array
 */
function sejolisa_get_renewall_order($order_parent_id, $user_id) {

    global $wpdb;
    
    $respond = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."sejolisa_orders WHERE order_parent_id = %s AND user_id = %s AND status = 'completed' ORDER BY ID DESC LIMIT 1", $order_parent_id, $user_id));

    $get_id = isset($respond[0]->ID) ? $respond[0]->ID : null;
    
    return $get_id;

}

/**
 * Get single on-hold order by amount
 * @since   1.0.0
 * @param   float   $amount
 * @return array
 * - valid      bool
 * - order      array
 * - messages   array
 */
function sejolisa_get_order_by_amount($amount) {

    $respond = SejoliSA\Model\Order::reset()
                    ->set_filter_from_array(array(
                        'status'    => 'on-hold'
                    ))
                    ->get_by('grand_total', $amount )
                    ->respond();

    if(false !== $respond['valid']) :
        $respond['orders']['meta_data'] = maybe_unserialize($respond['orders']['meta_data']);
        $respond['orders'] = apply_filters('sejoli/order/order-detail', $respond['orders']);
    endif;

    return wp_parse_args($respond,[
        'valid'    => false,
        'orders'   => NULL,
        'messages' => []
    ]);
}

/**
 * Get all products by order by user id
 * @since   1.0.0
 * @param   integer     $user_id
 * @return  array
 * - valid
 * - products
 * - messages
 */
function sejolisa_get_product_by_orders($user_id) {

    $respond = SejoliSA\Model\Order::reset()
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
 * Get all affiliates by order by user id
 * @since   1.0.0
 * @param   integer     $user_id
 * @return  array
 * - valid
 * - products
 * - messages
 */
function sejolisa_get_affiliate_by_orders($user_id) {

    $respond = SejoliSA\Model\Order::reset()
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
 * Get all products with physical type
 * @since   1.0.0
 * @param   array     $order_id
 * @return  array
 * - valid
 * - products
 * - messages
 */
function sejolisa_get_orders_with_physical_product($order_id) {

    $response = SejoliSA\Model\Order::reset()
                        ->set_orders($order_id)
                        ->get_by_physical_product()
                        ->respond();

    return wp_parse_args($response, [
        'valid'    => false,
        'orders'   => NULL,
        'messages' => []
    ]);
}

/**
 * Get total order by filter
 * @since   1.0.0
 * @param   array $args [description]
 * @return  array
 * - valid
 * - total
 * - messages
 */
function sejolisa_get_total_order($args) {

    global $sejolisa;

    $args = wp_parse_args($args,[
        'product_id'   => NULL,
        'status'       => NULL,
        'affiliate_id' => NULL
    ]);

    if(!is_null($args['product_id'])) :
        $key = $args['product_id'];

        if(!is_null($args['status'])) :
            $key = $key.'-'.$args['status'];
        endif;
    else :
        $key = false;
    endif;

    if(false !== $key && isset($sejolisa['total-order'][$key])) :
        $response = $sejolisa['total-order'][$key];

        do_action( 'sejoli/log/write', 'load total order', [
            'from'     => 'memory',
            'response' => $response
        ]);
    else :

        $query = SejoliSA\Model\Order::reset()
                        ->set_product_id($args['product_id'])
                        ->set_status($args['status']);

        if(!empty($args['affiiliate_id'])) :
            $query = $query->set_affiliate_id($args['affiliate_id']);
        endif;

        $response = $query->get_total_order()
                        ->respond();

        $sejolisa['total-order'][$key] = $response;

        do_action( 'sejoli/log/write', 'load total order', [
            'from'     => 'query',
            'response' => $response
        ]);
    endif;

    return wp_parse_args($response, [
        'valid'    => false,
        'total'    => 0,
        'messages' => []
    ]);
}

/**
 * Get all products that user bought
 * @since   1.0.0
 * @param   integer $user_id    Given user id
 * @return  array   All products ID that user bought
 */
function sejolisa_get_user_products_bought($user_id) {
    $products = [];
    $orders   = sejolisa_get_product_by_orders($user_id);

    if(false !== $orders['valid']) :
        foreach($orders['products'] as $product) :
            if(!in_array($product->product_id, $products)) :
                $products[] = $product->product_id;
            endif;
        endforeach;
    endif;

    $subscriptions = sejolisa_get_product_by_subscriptions($user_id);

    if(false !== $subscriptions['valid']) :
        foreach($subscriptions['products'] as $product) :
            if(!in_array($product->product_id, $products)) :
                $products[] = $product->product_id;
            endif;
        endforeach;
    endif;

    return $products;
}

/**
 * Get orders for bulk actions
 * @since   1.0.0
 * @param   array    $args     Query arguments
 * @return  array
 */
function sejolisa_get_orders_for_bulks(array $args) {

    $data = [
        'orders' => [],
        'total'  => 0
    ];

    $args = wp_parse_args($args,[
        'product_id' => 0,
        'date-range' => date('Y-m-d',strtotime('-30day')) . ' - ' . date('Y-m-d'),
        'status'     => 'on-hold'
    ]);

    $product           = absint($args['product_id']);
    list($start, $end) = explode(' - ', $args['date-range']);

    $response   = SejoliSA\Model\Order::reset()
                    ->set_product_id($product)
                    ->set_status($args['status'])
                    ->set_filter('created_at', $start.' 00:00:00', '>=')
                    ->set_filter('created_at', $end.' 23:59:59', '<=')
                    ->get_for_bulks()
                    ->respond();

    if(false !== boolval($response['valid'])) :

        foreach($response['orders'] as $_order) :
            $data['orders'][] = $_order->ID;
        endforeach;

        $data['total'] = $response['recordsTotal'];
    endif;

    return $data;

}

/**
 * Get previous order from a user with selected product
 * @since   1.5.3.3
 * @param   integer     $product_id
 * @param   integer     $user_id
 * @return  array
 */
function sejolisa_get_previous_order( $product_id, $user_id = 0 ) {

    $user_id = ( empty($user_id) ) ? get_current_user_id() : $user_id;

    $limit_minute = intval( sejolisa_carbon_get_post_meta( $product_id, 'limit_buy_time' ) );
    $unix_limit   = current_time( 'timestamp' ) - ( $limit_minute * 60 );
    $day_limit    = date('Y-m-d H:i:s', $unix_limit);

    $order   = false;
    $respond = SejoliSA\Model\Order::reset()
                    ->set_user_id( $user_id )
                    ->set_product_id( $product_id )
                    ->set_last_time( $day_limit )
                    ->get_last_bought()
                    ->respond();

    if( false !== $respond['valid'] ) :
        $order = $respond['order'];
    endif;

    return $order;
}

/**
 * Get status log
 * @since   1.5.3.3
 * @param   integer     $product_id
 * @param   integer     $user_id
 * @return  array
 */
function sejolisa_get_status_log( $order_status ) {

    switch ($order_status) {
        case 'on-hold':
            $status = __('Menunggu pembayaran', 'sejoli');
            break;
        case 'payment-confirm':
            $status = __('Pembayaran dikonfirmasi', 'sejoli');
            break;
        case 'in-progress':
            $status = __('Pesanan diproses', 'sejoli');
            break;
        case 'shipping':
            $status = __('Proses pengiriman', 'sejoli');
            break;
        case 'completed':
            $status = __('Selesai', 'sejoli');
            break;
        case 'refund':
            $status = __('Refund', 'sejoli');
            break;
        default:
            $status = __('Batal', 'sejoli');
            break;
    }

    return $status;

}