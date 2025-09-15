<?php

use Delight\Cookie\Cookie;

/**
 * Get affiliate key name for user meta
 * @since 1.0.0
 * @return string
 */
function sejolisa_get_affiliate_key() {
    return '_affiliate_id';
}

/**
 * Get affiliate data by user
 * @param  WP_User|Int  $user
 * @param  string       $return     type of return, default will return the id only
 * @return mixed
 */
function sejolisa_get_affiliate($user,$return = 'id')
{
    global $sejolisa;

    $affiliate_id = $affiliate = NULL;
    $user_id      = get_current_user_id();

    if(is_a($user,'WP_User')) :
        $user_id = $user->ID;
    else :
        $user_id = intval($user);
    endif;

    $affiliate_id = apply_filters('sejoli/user/affiliate', $affiliate_id, $user_id);

    if(!is_null($affiliate_id) || 0 !== $affiliate_id) :

        if('id' === $return) :

            $affiliate = intval($affiliate_id);

        elseif('wp_user' === $return) :

            if(isset($sejolisa['affiliates']) && isset($sejolisa['affiliates'][$affiliate_id])) :

                $affiliate = $sejolisa['affiliates'][$affiliate_id];

            else :

                $sejolisa['affiliates'][$affiliate_id] = $affiliate  = get_user_by('id',$affiliate_id);

            endif;

        endif;

    endif;

    return $affiliate;
}

/**
 * Add commission data
 * @param  array  $commission_data
 * @return array
 * - valid      bool
 * - commission array     if the commission added successfully, it will return all commission data
 * - messages   array
 */
function sejolisa_add_commission(array $commission_data) {

    $args   = wp_parse_args($commission_data, [
        'order_id'     => NULL,
        'affiliate_id' => NULL,
        'product_id'   => NULL,
        'tier'         => 1,
        'commission'   => 0,
        'status'       => 'pending'
    ]);

    $respond = SejoliSA\Model\Affiliate::reset()
                    ->set_product_id($args['product_id'])
                    ->set_order_id($args['order_id'])
                    ->set_user_id($args['affiliate_id'])
                    ->set_tier($args['tier'])
                    ->set_commission($args['commission'])
                    ->set_status($args['status'])
                    ->create()
                    ->respond();

    return $respond;
}

/**
 * Get list of commission
 * @since  1.0.0
 * @param  array  $args
 * @return array
 * - valid          bool
 * - commission      array
 * - messages       array
 */
function sejolisa_get_commissions(array $args, $table = array()) {

    $args = wp_parse_args($args,[
        'product_id'   => NULL,
        'user_id'      => NULL,
        'affiliate_id' => NULL,
        'tier'         => NULL,
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => NULL
    ]);

    if(isset($args['order_id'])) :
        $args['order_id'] = explode(',', $args['order_id']);
    endif;

    if(isset($args['date-range']) && !empty($args['date-range'])) :
        unset($args['date-range']);
    endif;

    $query = SejoliSA\Model\Affiliate::reset()
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

    return wp_parse_args($respond,[
        'valid'       => false,
        'commissions' => NULL,
        'messages'    => []
    ]);
}

/**
 * Get commission by its ID
 * @param  integer  $commission_id
 * @return array
 */
function sejolisa_get_commission($commission_id) {

    $respond = SejoliSA\Model\Affiliate::reset()
                    ->set_id($commission_id)
                    ->first()
                    ->respond();

    return wp_parse_args($respond,[
        'valid'      => false,
        'commission' => NULL,
        'messages'   => []
    ]);
}

/**
 * Update status of commission
 * @since   1.0.0
 * @param   array  $args \
 * @return  array
 * - valid          boolean
 * - commission     array
 * - messages       array
 */
function sejolisa_update_commission_status(array $args) {

    $args = wp_parse_args($args, [
        'ID'     => NULL,
        'status' => NULL
    ]);

    $respond = SejoliSA\Model\Affiliate::reset()
                    ->set_multiple_id($args['ID'])
                    ->set_status($args['status'])
                    ->update_status()
                    ->respond();

    return $respond;

}

/**
 * Update paid status of commission
 * @since   1.0.0
 * @param   array  $args
 * @return  array
 * - valid          boolean
 * - commission     array
 * - messages       array
 */
function sejolisa_update_commission_paid_status(array $args) {

    $args = wp_parse_args($args, [
        'ID'          => NULL,
        'paid_status' => false
    ]);

    $response = SejoliSA\Model\Affiliate::reset()
                    ->set_multiple_id($args['ID'])
                    ->set_paid_status($args['paid_status'])
                    ->update_paid_status()
                    ->respond();

    return $response;

}

/**
 * Get checkout affiliate data
 * @since   1.0.0
 * @return  array
 */
function sejolisa_get_affiliate_checkout() {
    return apply_filters('sejoli/checkout/affiliate-data', []);
}

/**
 * Get checkout affiliate detail data
 * @since   1.0.0
 * @since   1.5.3   Add $product parameter
 * @return  array   Affiliate data
 */
function sejolisa_get_affiliate_detail_checkout( \WP_Post $product ) {

    $affiliate_id = $affiliate      = false;

    $affiliate_data = wp_parse_args(sejolisa_get_affiliate_checkout(),[
        'user_meta' => NULL,
        'link'      => NULL,
        'coupon'    => NULL
    ]);

    if(!is_null($affiliate_data['user_meta'])) :
        $affiliate_id = intval($affiliate_data['user_meta']);
    endif;

    if(false === $affiliate_id) :
        if(!is_null($affiliate_data['coupon'])) :
            $affiliate_id = intval($affiliate_data['coupon']);
        elseif(!is_null($affiliate_data['link'])) :
            $affiliate_id = intval($affiliate_data['link']);
        endif;
    endif;

    if(is_user_logged_in() && $affiliate_id === get_current_user_id()) :

        $affiliate = false;

    elseif(false !== $affiliate_id) :

        /**
         * @since   1.5.3
         */
        if( true === sejolisa_user_can_affiliate_the_product($product->ID, $affiliate_id ) ) :

            $user = get_user_by('id', $affiliate_id);
            $affiliate = is_a($user, 'WP_user') ? $user->display_name : '';

        endif;

    endif;


    return $affiliate;
}

/*
 * Get all unpaid commissions per affiliate
 * @since   1.0.0
 * @param   array  $commission_ids
 * @return  array
 */
function sejolisa_get_all_unpaid_commissions(array $commission_ids) {

    $response = SejoliSA\Model\Affiliate::reset()
                    ->set_paid_status(0)
                    ->calculate_commission_per_affiliate($commission_ids)
                    ->respond();

    return $response;
}

/**
 * Get all affiliate links based on product id
 * @since   1.0.0
 * @param   integer $product_id
 * @param   integer $affiliate_id
 * @return  array
 */
function sejolisa_get_affiliate_links($product_id, $affiliate_id) {

    $i     = 0;
    $links = [];
    $args  = [
        'user_id'    => $affiliate_id,
        'product_id' => $product_id
    ];

    $main_link   = sejolisa_carbon_get_post_meta($product_id, 'sejoli_landing_page');
    $other_links = sejolisa_carbon_get_post_meta($product_id, 'sejoli_affiliate_links');

    if( !empty($main_link) ) :
        $links[$i] = [
            'link'           => esc_url($main_link),
            'affiliate_link' => apply_filters('sejoli/affiliate/link', '', $args),
            'title'       => __('Sales Page', 'sejoli'),
            'description' => __('Halaman penjualan', 'sejoli')
        ];

        $i++;
    endif;

    foreach( (array) $other_links as $link ) :
        $key         = $i .'-'.sanitize_title($link['title']);
        $links[$key] = [
            'link'           => esc_url($link['link']),
            'affiliate_link' => apply_filters('sejoli/affiliate/link', '', $args, $key),
            'title'          => $link['title'],
            'description'    => $link['description']
        ];

        $i++;
    endforeach;

    return $links;
}

/**
 * Get cookie name
 * @since   1.0.0
 * @return  string
 */
function sejolisa_get_cookie_name() {
    $tokens           = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $auth_key         = AUTH_KEY;
    $secure_auth_key  = SECURE_AUTH_KEY;
    $logged_in_key    = LOGGED_IN_KEY;
    $nonce_key        = NONCE_KEY;
    $auth_salt        = AUTH_SALT;
    $secure_auth_salt = SECURE_AUTH_SALT;
    $logged_in_salt   = LOGGED_IN_SALT;
    $nonce_salt       = NONCE_SALT;

    $i = [
        0 => absint(ord(strtolower($auth_key[0])) - 48),
        1 => absint(ord(strtolower($secure_auth_key[0])) - 96),
        2 => absint(ord(strtolower($logged_in_key[0])) - 96),
        3 => absint(ord(strtolower($nonce_key[0])) - 48),
        4 => absint(ord(strtolower($auth_salt[0])) - 96),
        5 => absint(ord(strtolower($secure_auth_salt[0])) - 96),
        6 => absint(ord(strtolower($logged_in_salt[0])) - 48),
        7 => absint(ord(strtolower($nonce_salt[0])) - 96),
    ];

    $key = '';

    foreach($i as $_i) :
        $key .= $tokens[$_i];
    endforeach;

    return 'SEJOLI-'.$key;
}

/**
 * Get cookie value
 * @since   1.0.0
 * @return  array
 */
function sejolisa_get_affiliate_cookie() {

    $data        = [];
    $cookie_name = sejolisa_get_cookie_name();

    if(isset($_COOKIE[$cookie_name])) :
        $data = maybe_unserialize(stripslashes($_COOKIE[$cookie_name]));
    endif;

    return $data;
}

/**
 * Get current user affiliate
 * @since  1.0.0
 * @return false|int
 */
function sejolisa_get_user_affiliate() {

    global $post;

    $cookie_affiliate = $user_meta_affiliate = $affiliate = false;
    $is_affiliate_permanent = boolval(sejolisa_carbon_get_theme_option('sejoli_permanent_affiliate'));

    if(is_user_logged_in()) :
        $user_id             = get_current_user_id();
        $user_meta_affiliate = intval( get_user_meta( $user_id, sejolisa_get_affiliate_key(), true) );
    endif;

    if(false === $user_meta_affiliate || false === $is_affiliate_permanent ) :
        $cookie_name = sejolisa_get_cookie_name();
        $cookie_data = wp_parse_args(
                            maybe_unserialize(
                                stripslashes( Cookie::get($cookie_name) )
                            ),
                            [
                                'general' => false,
                                'product' => []
                            ]
                        );

        if(is_a($post, 'WP_Post') && 'sejoli-product' === $post->post_type && isset($cookie_data['product'][$post->ID])) :
            $affiliate = $cookie_data['product'][$post->ID];
        else :
            $affiliate = $cookie_data['general'];
        endif;
    else :
        $affiliate = $user_meta_affiliate;
    endif;

    return $affiliate;
}

/**
 * Get affiliate bonus based on product
 * @since  1.0.0
 * @param  int  $affiliate_id
 * @param  int  $product_id
 * @return false|string
 */
function sejolisa_get_affiliate_bonus($affiliate_id, $product_id) {

    $affiliate_id = intval($affiliate_id);
    $product_id   = intval($product_id);

    return get_user_meta($affiliate_id, '_sejoli_bonus_affiliate_'.$product_id, true);
}

/**
 * Get all affiliate commission info, include waiting, unpaid and paid
 * @since   1.1.3
 * @return  array
 */
function sejolisa_get_affiliate_commission_info( $table = array() ) {

    $query = SejoliSA\Model\Affiliate::reset();

    if ( isset( $table['filter']['date-range'] ) && ! empty( $table['filter']['date-range'] ) ) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query->set_filter('created_at', $start.' 00:00:00', '>=')
                ->set_filter('created_at', $end.' 23:59:59', '<=');
    endif;

    if ( isset( $table['filter']['affiliate_id'] ) && ! empty( $table['filter']['affiliate_id'] ) ) :
        $query->set_filter('affiliate_id', $table['filter']['affiliate_id'], '=');
    endif;

    $response = $query->get_affiliate_commission_info()
                        ->respond();

    return wp_parse_args($response, array(
        'valid'       => false,
        'commissions' => array()
    ));
}

/**
 * Get all total commission info, include waiting, unpaid and paid
 * @since   1.3.2
 * @return  array
 */
function sejolisa_get_total_affiliate_commission_info( $table = array() ) {

    $return   = array(
        'pending_commission' => 0,
        'unpaid_commission'  => 0,
        'paid_commission'    => 0
    );

    $query = SejoliSA\Model\Affiliate::reset();

    if ( isset( $table['filter']['date-range'] ) && ! empty( $table['filter']['date-range'] ) ) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query->set_filter('created_at', $start.' 00:00:00', '>=')
                ->set_filter('created_at', $end.' 23:59:59', '<=');
    endif;

    if ( isset( $table['filter']['affiliate_id'] ) ) :
        $query->set_filter('affiliate_id', $table['filter']['affiliate_id'], '=');
    endif;

    $response = $query->get_total_affiliate_commission_info()
                        ->respond();

    if(false !== $response['valid']) :
        $return = wp_parse_args((array) $response['commissions'],  $return);
    endif;

    return array_map('floor', $return);
}

/**
 * Get single affiliate commission info, include waiting, unpaid and paid
 * @since   1.1.3
 * @param   integer     $affiliate_id   Affiliate ID
 * @return  array
 */
function sejolisa_get_single_affiliate_commission_info($affiliate_id, $args = []) {

    $query = SejoliSA\Model\Affiliate::reset()
                    ->set_user_id($affiliate_id);

    if ( isset( $args['date_range'] ) && ! empty( $args['date_range'] ) ) :
        list($start, $end) = explode(' - ', $args['date_range']);
        $query->set_filter('created_at', $start.' 00:00:00', '>=')
                ->set_filter('created_at', $end.' 23:59:59', '<=');
    endif;

    $response = $query->get_single_affiliate_commission_info()
                        ->respond();

    if(false !== $response['valid']) :

        $affiliate = sejolisa_get_user($affiliate_id);

        if(is_a($affiliate, 'WP_User')) :
            $response['affiliate']->info                   = get_user_meta($affiliate_id, '_bank_info', true);
            $response['affiliate']->user_email             = $affiliate->user_email;
            $response['affiliate']->user_phone             = $affiliate->meta->phone;
            $response['affiliate']->avatar                 = get_avatar_url($affiliate->user_email);
            $response['affiliate']->unpaid_commission_html = sejolisa_price_format($response['affiliate']->unpaid_commission);
            $response['affiliate']->current_time           = current_time( 'mysql' );
        endif;

    endif;

    return wp_parse_args($response, array(
        'valid'     => false,
        'affiliate' => array()
    ));
}

/**
 * Update single affiliate commission paid status
 * @since   1.1.3
 * @param   array  $args
 * @return  array
 */
function sejolisa_update_single_affiliate_commission_paid_status(array $args) {

    $args   = wp_parse_args($args, array(
        'affiliate_id'  => 0,
        'paid_status'   => NULL,
        'current_time'  => current_time( 'mysql' ),
        'date_range'    => '',
    ));

    $query = SejoliSA\Model\Affiliate::reset()
                ->set_user_id( $args['affiliate_id'] )
                ->set_paid_status( $args['paid_status'] )
                ->set_paid_time( $args['current_time'] );

    if ( isset( $args['date_range'] ) && ! empty( $args['date_range'] ) ) :
        list($start, $end) = explode(' - ', $args['date_range']);
        $query->set_filter('created_at', $start.' 00:00:00', '>=')
                ->set_filter('created_at', $end.' 23:59:59', '<=');
    endif;

    $response = $query->update_single_affiliate_commission_paid_status()
                      ->respond();

    return $response;
}

/**
 * Get affiliate product facebook ID
 * @since   1.3.2
 * @param   integer     $affiliate_id
 * @param   integer     $product_id
 * @return  null|string
 */
function sejolisa_get_affiliate_facebook_pixel_id($affiliate_id, $product_id) {

    $meta_key = '_sejoli_id_pixel_affiliate_'. $product_id;
    return trim(sanitize_text_field(get_user_meta( $affiliate_id, $meta_key, true)));

}

/**
 * Check if user can affiliate the product
 * @since   1.5.3
 * @param   integer     $product_id
 * @param   integer     $affiliate_id
 * @return  boolean
 */
function sejolisa_user_can_affiliate_the_product($product_id, $affiliate_id ) {

    $enable_affiliate           = boolval( sejolisa_carbon_get_post_meta( $product_id, 'sejoli_enable_affiliate'));
    $enable_affiliate_if_bought = boolval( sejolisa_carbon_get_post_meta( $product_id, 'sejoli_enable_affiliate_if_already_bought') );
    if( true !== $enable_affiliate ) :
        return false;
    endif;

    if( true === $enable_affiliate_if_bought ) :

        return sejolisa_check_if_user_has_bought_product( $product_id, $affiliate_id );

    endif;

    return true;
}

/**
 * Get total commission per order
 * @since   1.5.3.4
 * @param   integer     $order_id
 * @param   boolean     $valid_only
 * @return  float
 */
function sejolisa_get_total_commission_by_order( $order_id, $valid_only = true ) {

    $total = 0;

    $filter = array(
        'order_id'  => $order_id
    );

    if( true === $valid_only ) :
        $filter['status'] = 'added';
    endif;

    $response = SejoliSA\Model\Affiliate::reset()
                    ->set_filter_from_array($filter)
                    ->get_total_commission()
                    ->respond();

    if(false !== $response['valid']) :
        $total = floor( $response['total'] );
    endif;

    return $total;

}

/**
 * Get max downline tiers
 * @since   1.0.0
 * @param   integer     $user_id
 * @return  integer
 */

// old function [activate when need it]
// function sejolisa_get_max_downline_tiers( $user_id = 0 ) {

//     $max_tiers = absint( sejolisa_carbon_get_theme_option( 'sejoli_affiliate_network_limit' ) );

//     $user_id       = ( empty($user_id) ) ? get_current_user_id() : $user_id;
//     $user_group_id = sejolisa_get_current_user_group( $user_id );

//     if( !empty( $user_group_id) ) :
//         $max_tiers = absint( sejolisa_carbon_get_post_meta( $user_group_id, 'affiliate_network_limit') );        
//     endif;

//     return ( 0 >= $max_tiers ) ? 1 : $max_tiers;
// }

function sejolisa_get_max_downline_tiers( $user_id = 0 ) {

    $max_tiers = absint( sejolisa_carbon_get_theme_option( 'sejolisa_affiliate_network_limit' ) );

    $user_id       = ( empty($user_id) ) ? get_current_user_id() : $user_id;
    $user_group_id = sejolisa_get_current_user_group( $user_id );

    if( !empty( $user_group_id) ) :
        //$max_tiers = absint( sejolisa_carbon_get_post_meta( $user_group_id, 'affiliate_network_limit') );        
        $max_tiers = absint( sejolisa_carbon_get_theme_option( 'sejolisa_affiliate_network_limit' ) );
    endif;

    return ( 0 >= $max_tiers ) ? 1 : $max_tiers;
}

/**
 * Get max upline tiers
 *
 * @param int $user_id 
 * @return integer
 */
function sejolisa_get_max_upline_tiers( $user_id = 0 ) {

    $max_tiers = absint( sejolisa_carbon_get_theme_option( 'sejolisa_affiliate_network_limit_upline' ) );

    $user_id       = ( empty($user_id) ) ? get_current_user_id() : $user_id;
    $user_group_id = sejolisa_get_current_user_group( $user_id );

    if( !empty( $user_group_id) ) :
        //$max_tiers = absint( sejolisa_carbon_get_post_meta( $user_group_id, 'affiliate_network_limit') );        
        $max_tiers = absint( sejolisa_carbon_get_theme_option( 'sejolisa_affiliate_network_limit_upline' ) );
    endif;

    return ( 0 >= $max_tiers ) ? 1 : $max_tiers;
}
