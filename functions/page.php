<?php
/**
 * Verify if current page is sejoli built-in page
 * @since   1.0.0
 * @param   string  $page
 * @return  boolean
 */
function sejolisa_verify_page( $page = '' ) {

    $valid = false;

    global $wp;

    if ( !empty( $page ) &&
        isset( $wp->query_vars['sejolisa_page'] ) &&
        $wp->query_vars['sejolisa_page'] === $page ) :

        $valid = true;

    endif;

    return $valid;

}

/**
 * Check if current pagi is sejoli-product post type
 * @since   1.0.0
 * @return  boolean Return valid value
 */
function sejolisa_is_checkout_page() {

    $valid = false;

    global $wp;

    if ( is_singular( 'sejoli-product' ) ||

        ( isset( $wp->query_vars['sejolisa_page'] ) && !empty( $wp->query_vars['sejolisa_page'] ) ) ||
        ( isset( $wp->query_vars['sejolisa_checkout_page'] ) && !empty( $wp->query_vars['sejolisa_checkout_page'] ) ) ) :

        $valid = true;

    endif;

    return $valid;

}

/**
 * Check current sejoli member page
 * @since   1.0.0
 * @param   string  $view       View value
 * @param   string  $action     Action value
 * @return  boolean             Value if condition met
 */
function sejolisa_is_member_area_page($view = '', $action = '') {

    $valid = false;

    global $wp_query;

    if ( isset( $wp_query->query_vars['member'] ) &&
        intval( $wp_query->query_vars['member'] ) === 1 ) :

        $current_view   = get_query_var('view');
        $current_action = get_query_var('action');

        if(!empty($view) && $current_view !== $view) :
            return false;
        endif;

        if(!empty($action) && $current_action !== $action) :
            return false;
        endif;

        $valid = true;

    endif;

    return $valid;

}
