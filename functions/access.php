<?php

use Carbon\Carbon;

/**
 * Get all user access products
 * @since   1.0.0
 * @param   integer $user_id    User ID
 * @return  array               Array of all access product
 */
function sejolisa_get_user_access_products($user_id) {

    $access        = [];
    $orders        = sejolisa_get_product_by_orders($user_id);
    $subscriptions = sejolisa_get_product_by_subscriptions($user_id);

    if(false !== boolval($orders['valid'])) :
        foreach($orders['products'] as $order) :

            $start        = (0 < strtotime($order->updated_at)) ? $order->updated_at : $order->created_at;
            $start_carbon = new Carbon($start);
            $access[$order->product_id] = [
                'start'         => $start,
                'start_day'     => Carbon::now()->diffInDays($start_carbon),
                'end_active'    => NULL,
                'name'          => $order->product_name
            ];
        endforeach;
    endif;

    if(false !== boolval($subscriptions['valid'])) :
        foreach($subscriptions['products'] as $order) :

            if(!isset($access[$order->product_id])) :
                $start        = (0 < strtotime($order->updated_at)) ? $order->updated_at : $order->created_at;
                $start_carbon = new Carbon($start);
                $access[$order->product_id] = [
                    'start'         => $start,
                    'start_day'     => Carbon::now()->diffInDays($start_carbon),
                    'end_active'    => NULL,
                    'name'          => $order->product_name
                ];
            endif;

            $access[$order->product_id]['end_active'] = $order->end_date;

        endforeach;
    endif;

    return $access;
}

/**
 * Does user has access to the product
 * @since   1.0.0
 * @param   integer     $user_id        Obviously it's USER ID
 * @param   integer     $product_id     Obviously it's PRODUCT ID
 * @return  boolean
 */
function sejolisa_does_user_have_access($user_id, $product_id) {

    return apply_filters('sejoli/access/has-access', false, $user_id, $product_id);

}

/**
 * Get default member area url, the option can be overwriten from admin > sejoli > pengaturan, 'Alihkan user setelah login'
 * @since   1.5.0
 * @return  string  Basic member area url
 */
function sejolisa_get_default_member_area_url() {

    $redirected_url = esc_url(sejolisa_carbon_get_theme_option('sejoli_after_login_redirect'));
    $redirected_url = (!empty($redirected_url)) ? $redirected_url : sejoli_get_endpoint_url('home');

    return $redirected_url;
}
