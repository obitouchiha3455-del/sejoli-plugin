<?php

/**
 * Get multiple products by id
 * @since   1.0.0
 * @param   array  $multiple_product_ids [description]
 * @return  array
 */
function sejolisa_get_products( array $multiple_product_ids ) {

    $product_data = [];

    $products = get_posts([
        'posts_per_page' => count( $multiple_product_ids ),
        'post_type'      => SEJOLI_PRODUCT_CPT,
        'include'        => $multiple_product_ids
    ]);

    foreach( (array) $products as $product ) :

        $product_data[ $product->ID ] = $product;

    endforeach;

    return $product_data;
}

/**
 * Get product data as array for select options
 * @since   1.3.3
 * @since   1.11.1  Add caching
 * @return  array
 */
function sejolisa_get_product_options() {

    global $sejolisa;

    $options = isset($sejolisa['options']) ? $sejolisa['options'] : '';

    //check if data is not cached
    if(!array_key_exists('product', (array)$options)) :

        $product_data = [];

        $products = get_posts([
            'posts_per_page'         => -1,
            'post_type'              => SEJOLI_PRODUCT_CPT,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        ]);

        foreach( (array) $products as $product ) :

            $product_data[ $product->ID ] = $product->post_title;

        endforeach;

        $sejolisa['options']['product-options'] = $product_data;

    endif;

    return $sejolisa['options']['product-options'];
}

/**
 * Get product detail
 * @since   1.0.0
 * @since   1.3.4   Add parameter $renew to get fresh product data, not from global
 * @param   int     $product_id
 * @param   boolean $renew          Set true if not get the product data from global
 * @return  mixed
 */
function sejolisa_get_product(int $product_id, $renew = false) {

    global $sejolisa;

    if(!isset($sejolisa['products'][$product_id]) || $renew) :

        $product = get_post($product_id);

        if(is_a($product, 'WP_Post') && SEJOLI_PRODUCT_CPT === $product->post_type) :

            $product = apply_filters('sejoli/product/meta-data', $product, $product->ID);
            $sejolisa['products'][$product->ID] = $product;

            return $product;

        endif;

    else :
        return $sejolisa['products'][$product_id];
    endif;

    return new WP_Error('broke', __('Not valid product ID', 'sejoli'));

}

/**
 * Check if product is physic
 * @since   1.0.0
 * @param   integer  $product_id
 * @return  boolean
 */
function is_sejolisa_product_physical($product_id) {

    global $sejolisa;

    $product_id = intval($product_id);
    $product    = sejolisa_get_product($product_id);

    return ('physical' === $product->type) ? true : false;
}

/**
 * Get product facebook pixel setup
 * @since   1.0.0
 * @param   integer $product_id [description]
 * @return  array
 */
function sejolisa_get_product_fb_pixel_setup($product_id) {

    $active                 = boolval(sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_active'));
    $current_user_affiliate = sejolisa_get_user_affiliate();

    $data = [
        'active'                 => $active,
        'affiliate_active'       => boolval(sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_affiliate_active')),
        'id'                     => preg_replace("/[^0-9]/", "", (sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_id'))),
        'affiliate_id'           => NULL,
        'event_on_checkout_page' => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_event_load_checkout_page'),
        'event_on_submit_button' => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_event_submit_checkout_button'),
        'event_on_redirect_page' => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_event_load_redirect_page'),
        'event_on_invoice_page'  => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_event_load_invoice_page'),
        'currency'               => 'IDR'
    ];

    if(false !== $current_user_affiliate) :
        $data['affiliate_id']   = preg_replace("/[^0-9]/", "", (get_user_meta($current_user_affiliate, '_fb_pixel_id_' . $product_id, true)));
    endif;

    return $data;
}

/**
 * Get product facebook pixel product link
 * @since   1.0.0
 * @since   1.5.1.1     Remove content_type and content_category
 * @param   integer $product_id [description]
 * @return  array
 */
function sejolisa_get_product_fb_pixel_links($product_id) {

    return [
        // 'detail'    => [
        //     'content_category' => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_content_category'),
        //     'content_type'     => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_content_type'),
        // ],
        'checkout'  => [
            'title'  => __('Event pada Checkout Page', 'sejoli'),
            'detail' => 'URL Equals',
            'type'   => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_event_load_checkout_page'),
            'link'   => get_permalink($product_id)
        ],
        'submit'    => [
            'title'  => __('Event pada saat menekan Beli Sekarang', 'sejoli'),
            'detail' => NULL,
            'type'   => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_event_submit_checkout_button'),
            'link'   => NULL,
        ],
        'redirect' => [
            'title'  => __('Event pada Redirect Page', 'sejoli'),
            'detail' => 'URL Contains',
            'type'   => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_event_load_redirect_page'),
            'link'   => home_url('/checkout/loading/')
        ],
        'invoice' => [
            'title'  => __('Event pada Invoice', 'sejoli'),
            'detail' => 'URL Contains',
            'type'   => sejolisa_carbon_get_post_meta($product_id, 'fb_pixel_event_load_invoice_page'),
            'link'   => home_url('/checkout/thank-you')
        ]
    ];
}

/**
 * Check if product is closed
 * @since   1.1.6
 * @param   integer     $product_id
 * @return  boolean
 */
function sejolisa_is_product_closed($product_id = 0) {

    $closed = false;

    if (0 === $product_id) :
        global $post;
        $product = $post;
    else :
        $product = get_post($product_id);
    endif;

    $_enable_sale = $product->_enable_sale;

    if ( 'yes' !== $_enable_sale ) :
        $closed = true;
    endif;

    if ( !empty( $product->_disable_sale_time ) ) :

        $_disable_sale_time = new \DateTime($product->_disable_sale_time);
        $date_time_now      = new \DateTime( current_time( 'mysql' ) );

        if ( $date_time_now > $_disable_sale_time ) :
            $closed = true;
        endif;

    endif;

    if(false !== $closed) :

        $cookie_name = 'SEJOLI-ACCESS-PRODUCT-' . $product_id;

        if(isset($_COOKIE[$cookie_name])) :

            $code_access         = sanitize_text_field($_COOKIE[$cookie_name]);
            $product_code_access = sejolisa_carbon_get_post_meta($product_id, 'coupon_access_checkout');

            $closed = ($code_access !== $product_code_access) ? true : false;

        endif;
    endif;

    return $closed;
}
