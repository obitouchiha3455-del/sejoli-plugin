<?php

/**
 * Get all licenses
 * @param  array  $args
 * @param  array  $table
 * @return
 * - valid      boolean
 * - license     array
 * - messages   array
 */
function sejolisa_get_licenses(array $args, $table = array()) {

    $args = wp_parse_args($args, [
        'coupon_parent_id' => NULL,
        'status'           => NULL,
        'user_id'          => NULL
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => []
    ]);

    $query = SejoliSA\Model\License::reset()
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
 * Get licenses by order id
 * @since   1.0.0
 * @param   integer $order_id
 * @return
 * - valid      bool
 * - licenses   array
 * - messages   array
 */
function sejolisa_get_license_by_order($order_id) {

    $order_id = absint($order_id);
    $respond  = SejoliSA\Model\License::reset()
                    ->get_by('order_id', $order_id)
                    ->respond();

    return wp_parse_args($respond,[
        'valid'    => false,
        'licenses' => [],
        'messages' => []
    ]);
}

/**
 * Get licenses by code
 * @since   1.0.0
 * @param   string $code
 * @return
 * - valid      bool
 * - licenses   array
 * - messages   array
 */
function sejolisa_get_license_by_code($code) {

    if(!empty($code)) :
        $response  = SejoliSA\Model\License::reset()
                        ->get_by('code', $code, false)
                        ->respond();
    else :
        $response   = [
            'valid'     => false,
            'messages'  => [
                __('License code is empty', 'sejoli')
            ]
        ];
    endif;

    return wp_parse_args($response,[
        'valid'    => false,
        'licenses' => [],
        'messages' => []
    ]);
}

/**
 * Add new license
 * @since   1.0.0
 * @param   array $args
 * @return
 * - valid      bool
 * - license    array
 * - messages   array
 */
function sejolisa_add_license(array $args) {

    $args = wp_parse_args($args, [
        'order_id'   => NULL,
        'user_id'    => NULL,
        'product_id' => NULL,
        'code'       => NULL,
        'string'     => NULL,
        'status'     => 'active'
    ]);

    $respond = SejoliSA\Model\License::reset()
                    ->set_product_id($args['product_id'])
                    ->set_order_id($args['order_id'])
                    ->set_user_id($args['user_id'])
                    ->set_code($args['code'])
                    ->set_status($args['status'])
                    ->set_string($args['string'])
                    ->create()
                    ->respond();

    return wp_parse_args($respond,[
        'valid'    => false,
        'license'  => [],
        'messages' => []
    ]);
}

/**
 * Update string license
 * @since   1.0.0
 * @param   array $args
 * @return
 * - valid      bool
 * - license    array
 * - messages   array
 */
function sejolisa_update_string_license(array $args) {
    $args = wp_parse_args($args, [
        'ID'     => NULL,
        'string' => NULL
    ]);

    $response = SejoliSA\Model\License::reset()
                    ->set_id($args['ID'])
                    ->set_string($args['string'])
                    ->update_string()
                    ->respond();

    return wp_parse_args($response, [
        'valid'    => false,
        'license'  => [],
        'messages' => []
    ]);
}

/**
 * Update multiple license status
 * @since   1.0.0
 * @param   string  $status     Status of license
 * @param   array   $licenses   Array of license id
 * @return  array   Array of response
 */
function sejolisa_update_status_licenses($status, array $licenses) {

    $query = Sejolisa\Model\License::reset()
                ->set_status($status)
                ->set_filter('ID', $licenses);

    if(!current_user_can('manage_sejoli_licenses')) :
        $query = $query->set_filter('user_id', get_current_user_id());
    endif;

    $response = $query->update_status()
                    ->respond();

    return wp_parse_args($response,[
        'valid' => false
    ]);
}

/**
 * Update multiple license status by order
 * @since   1.0.0
 * @param   string  $status     Status of license
 * @param   array   $licenses   Array of license id
 * @return  array   Array of response
 */
function sejolisa_update_status_license_by_order($status, $order_id) {

    $query = Sejolisa\Model\License::reset()
                ->set_status($status)
                ->set_filter('order_id', $order_id);

    if(!current_user_can('manage_sejoli_licenses')) :
        $query = $query->set_filter('user_id', get_current_user_id());
    endif;

    $response = $query->update_status()
                    ->respond();

    return wp_parse_args($response,[
        'valid' => false
    ]);
}

/**
 * Reset multiple license string
 * @since   1.0.0
 * @param   string      $status         Status of license
 * @param   array       $licenses       Array of license id
 * @param   boolean     $force_reset    Force to reset license by ignore current user
 * @return  array       Array of response
 */
function sejolisa_reset_licenses(array $licenses, $force_reset = false) {

    $query = Sejolisa\Model\License::reset()
                ->set_filter('ID', $licenses);

    if(!current_user_can('manage_sejoli_licenses') && !$force_reset) :
        $query = $query->set_filter('user_id', get_current_user_id());
    endif;

    $response = $query->reset_string()
                    ->respond();

    return wp_parse_args($response,[
        'valid' => false
    ]);
}

/**
 * [sejolisa_check_own_license description]
 * @return boolean
 */
function sejolisa_check_own_license() {

    global $sejolisa;

    return boolval($sejolisa['license']['valid']);
}

/**
 * Get license by string
 * @since   1.0.0
 * @param   string  $string     The string code
 * @return  array
 */
function sejolisa_get_license_by_string($string) {

    $response = Sejolisa\Model\License::reset()
                    ->get_by('string', $string)
                    ->respond();

    return wp_parse_args($response,[
        'valid'    => false,
        'licenses' => NULL
    ]);
}

/**
 * Get license by order id
 * @since   1.0.0
 * @param   string $order_id Order ID of license
 * @return  array
 */
function sejolisa_get_license_by_order_id($order_id) {

    $query = Sejolisa\Model\License::get_license_by_order_id($order_id);

    return $query;

}

/**
 * Update license status by subscription is expired
 * @since   1.0.0
 * @param   string $order_id Order ID of license
 * @return  array Array of response
 */
function sejolisa_update_status_license_by_subscription_expired($order_id) {

    $query = Sejolisa\Model\License::reset()
                ->set_status('inactive')
                ->set_filter('order_id', $order_id);

    $response = $query->update_status()
                    ->respond();

    return wp_parse_args($response,[
        'valid' => false
    ]);
    
}