<?php
/**
 * Create new coupon
 * @param  array  $coupon_data
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_create_coupon(array $coupon_data) {

    $coupon_data = wp_parse_args($coupon_data,[
        'code'             => NULL,
        'user_id'          => 0,
        'coupon_parent_id' => NULL,
        'discount'         => 10,
        'discount_type'    => 'percentage',
        'discount_usage'   => 'per_item',
        'free_shipping'    => false,
        'limit_use'        => 0,
        'rule'             => [],
        'limit_date'       => NULL,
        'status'           => (false !== sejolisa_carbon_get_theme_option('sejoli_affiliate_coupon_active')) ? 'active' : 'pending'
    ]);

    $respond = SejoliSA\Model\Coupon::reset()
                ->set_code($coupon_data['code'])
                ->set_user_id($coupon_data['user_id'])
                ->set_coupon_parent_id($coupon_data['coupon_parent_id'])
                ->set_discount([
                    'value' => $coupon_data['discount'],
                    'type'  => $coupon_data['discount_type'],
                    'usage' => $coupon_data['discount_usage'],
                    'free_shipping' => boolval($coupon_data['free_shipping'])
                ])
                ->set_limit_use($coupon_data['limit_use'])
                ->set_limit_date($coupon_data['limit_date'])
                ->set_rule($coupon_data['rule'])
                ->set_status($coupon_data['status'])
                ->create()
                ->respond();

    return $respond;
}

/**
 * Check total affiliate coupon data
 * @since   1.1.9
 * @param   array  $coupon_data Coupon data
 * @return  array
 */
function sejolisa_check_affiliate_coupon_availability(array $coupon_data) {

    $coupon_data = wp_parse_args($coupon_data, array(
                        'user_id'          => NULL,
                        'coupon_parent_id' => NULL
                   ));

    $response = SejoliSA\Model\Coupon::reset()
                    ->set_user_id($coupon_data['user_id'])
                    ->set_coupon_parent_id($coupon_data['coupon_parent_id'])
                    ->get_total_affiliate_coupon()
                    ->respond();

    return wp_parse_args($response, array(
        'valid' => false,
        'total' => 0
    ));
}

/**
 * Check if affiliate can create coupon
 * @since   1.1.9
 * @param   array   $coupon_data    Coupon data
 * @return  boolean
 */
function sejolisa_is_affiliate_coupon_available(array $coupon_data) {

    $available = true;
    $coupon_data = wp_parse_args($coupon_data, array(
                        'user_id'          => NULL,
                        'coupon_parent_id' => NULL,
                        'limit'            => 0
                   ));

    $limit    = $coupon_data['limit'];
    $response = sejolisa_check_affiliate_coupon_availability($coupon_data);

    if(false !== $response['valid'] ) :
        if($limit !== 0 && $limit <= $response['total']) :
            $available = false;
        endif;
    endif;

    return $available;
}

/**
 * Create new affiliate coupon
 * @param  array  $coupon_data
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_create_affiliate_coupon(array $coupon_data) {

    $coupon_data = wp_parse_args($coupon_data,[
        'code'             => NULL,
        'user_id'          => NULL,
        'coupon_parent_id' => NULL,
        'status'           => (false !== sejolisa_carbon_get_theme_option('sejoli_affiliate_coupon_active')) ? 'active' : 'pending'
    ]);

    $response = SejoliSA\Model\Coupon::reset()
                ->set_code($coupon_data['code'])
                ->set_user_id($coupon_data['user_id'])
                ->set_coupon_parent_id($coupon_data['coupon_parent_id'])
                ->set_discount([
                    'value' => isset($coupon_data['discount']) ? $coupon_data['discount'] : 0,
                    'type'  => isset($coupon_data['discount_type']) ? $coupon_data['discount_type'] : 'percentage',
                    'usage' => isset($coupon_data['discount_usage']) ? $coupon_data['discount_usage'] : 0,
                    'free_shipping' => isset($coupon_data['free_shipping']) ? boolval($coupon_data['free_shipping']) : false
                ])
                ->set_limit_use($coupon_data['limit_use'])
                ->set_limit_date($coupon_data['limit_date'])
                ->set_rule($coupon_data['rule'])
                ->set_status($coupon_data['status'])
                ->create()
                ->respond();

    return $response;
}

/**
 * Update existing coupon
 * @param  array  $coupon_data
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_update_coupon(array $coupon_data) {

    $coupon_data = wp_parse_args($coupon_data,[
        'ID'               => false,
        'coupon_parent_id' => NULL,
        'discount'         => 10,
        'discount_type'    => 'percentage',
        'discount_usage'   => 'per_item',
        'free_shipping'    => false,
        'limit_use'        => 0,
        'rule'             => [],
        'limit_date'       => NULL,
        'status'           => 'pending'
    ]);

    $respond = SejoliSA\Model\Coupon::reset()
                ->set_id($coupon_data['ID'])
                ->set_discount([
                    'value' => $coupon_data['discount'],
                    'type'  => $coupon_data['discount_type'],
                    'usage' => $coupon_data['discount_usage'],
                    'free_shipping' => boolval($coupon_data['free_shipping'])
                ])
                ->set_limit_use($coupon_data['limit_use'])
                ->set_limit_date($coupon_data['limit_date'])
                ->set_rule($coupon_data['rule'])
                ->set_status($coupon_data['status'])
                ->update()
                ->respond();

    return $respond;
}

/**
 * Update existing status coupon
 * @param  array  $coupon_data
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_update_coupon_status(array $coupon_data) {

    $coupon_data = wp_parse_args($coupon_data,[
        'ID'      => NULL,
        'user_id' => NULL,
        'pending' => NULL,
    ]);

    $respond = SejoliSA\Model\Coupon::reset()
                ->set_id($coupon_data['ID'])
                ->set_user_id($coupon_data['user_id'])
                ->set_status($coupon_data['status'])
                ->update_status()
                ->respond();

    return $respond;
}

/**
 * Delete coupon
 * @param  integer  $coupon_id
 * @return
 * - valid      boolean
 * - messages   array
 */
function sejolisa_delete_coupons(array $coupon_ids) {

    $respond = SejoliSA\Model\Coupon::reset()
                ->set_multiple_id($coupon_ids)
                ->delete()
                ->respond();

    return $respond;
}

/**
 * Get coupon by coude
 * @param  string  $coupon_data
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_get_coupon_by_code($coupon_code) {

    $respond = SejoliSA\Model\Coupon::reset()
                ->set_code($coupon_code)
                ->single()
                ->respond();

    return $respond;
}

/**
 * Get coupon by id
 * @param  string  $coupon_data
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_get_coupon_by_id($coupon_id) {

    $respond = SejoliSA\Model\Coupon::reset()
                ->set_id($coupon_id)
                ->single()
                ->respond();

    if(false === $respond['valid']) :
        $respond['messages']['error'][] = __('Kupon tidak ditemukan', 'sejoli');
    endif;

    return $respond;
}

/**
 * Get all coupons
 * @param  array  $args
 * @param  array  $table
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_get_coupons(array $args, $table = array()) {

    $args = wp_parse_args($args, [
        'coupon_parent_id' => NULL,
        'status'           => NULL,
        'user_id'          => NULL,
        'limit_date'       => NULL
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => []
    ]);

    $query = SejoliSA\Model\Coupon::reset()
                ->set_filter_from_array($args)
                ->set_data_start($table['start']);

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
 * Update coupon usage
 * @param  string $coupon_code
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_update_coupon_usage($coupon_code) {

    $respond = sejolisa_get_coupon_by_code($coupon_code);

    if(false !== $respond['valid']) :

        $coupon = $respond['coupon'];

        if(!in_array($coupon['status'], ['pending', 'need-approve'])) :

            $respond = SejoliSA\Model\Coupon::reset()
                            ->set_id($coupon['ID'])
                            ->set_limit_use($coupon['limit_use'])
                            ->set_limit_date($coupon['limit_date'])
                            ->set_usage($coupon['usage'])
                            ->update_usage()
                            ->respond();

        else :

            $respond['valid'] = false;
            $respond['messages']['error'][] = sprintf( __('Kupon %s tidak aktif', 'sejoli'), $coupon['code'] );

        endif;

        $respond['coupon'] = $coupon;

    endif;

    return $respond;
}

/**
 * Update coupon usage by coupon id
 * @param  integer $coupon_id
 * @return
 * - valid      boolean
 * - coupon     array
 * - messages   array
 */
function sejolisa_update_coupon_usage_by_id($coupon_id) {

}

/**
 * Update multiple coupon status
 * @since   1.0.0
 * @param   string  $status     Status of coupon
 * @param   array   $coupons   Array of coupon id
 * @return  array   Array of response
 */
function sejolisa_update_multiple_coupons_status($status, array $coupons) {

    $query = Sejolisa\Model\Coupon::reset()
                ->set_status($status)
                ->set_filter('ID', $coupons);

    if(!current_user_can('manage_sejoli_coupons')) :
        $query = $query->set_filter('user_id', get_current_user_id());
    endif;

    $response = $query->update_status_multiple()
                    ->respond();

    return wp_parse_args($response,[
        'valid' => false
    ]);
}

/**
 * Get total use from all coupons
 * @since   1.1.4
 * @return  array   response
 */
function sejolisa_get_total_use_all_coupons() {

    $response   = Sejolisa\Model\Coupon::reset()
                    ->get_total_use_all_coupons()
                    ->respond();

    return $response;
}

/**
 * Update total usage per coupon
 * @since   1.1.4
 * @param   array    $args       Parameters and arguments
 * @return  array    response
 */
function sejolisa_update_total_usage_coupon(array $args) {

    $args   = wp_parse_args($args, array(
        'id'    => 0,
        'usage' => 0
    ));

    $response    = Sejolisa\Model\Coupon::reset()
                    ->set_id($args['id'])
                    ->set_usage($args['usage'])
                    ->update_total_usage()
                    ->respond();

    return $response;
}

/**
 * Get all available affiliate coupons
 * @since   1.5.1
 * @return  array
 */
function sejolisa_get_affiliate_coupons() {

    $coupons = array();
    $args    = array(
        'user_id' => get_current_user_id(),
    );

    $response = sejolisa_get_coupons($args);

    if( $response['valid'] ) :

        foreach( $response['coupons'] as $coupon ) :

            if(
                is_array($coupon['rule']) &&
                array_key_exists('use_by_affiliate', $coupon['rule']) &&
                false === $coupon['rule']['use_by_affiliate']
            ) :
                continue;
            endif;

            $coupons[$coupon['ID']] = $coupon['code'];

        endforeach;

    endif;

    return $coupons;
}
