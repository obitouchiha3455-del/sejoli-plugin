<?php
/**
 * Check if current page is sejoli checkout built-in page, included thank-you and loading
 * @since   1.0.0
 * @since   1.4.3   Check pages as array, not as single page anymore
 * @param   string  $page
 * @return  boolean
 */
function sejolisa_verify_checkout_page( $page = '' ) {

    $valid = false;

    global $wp, $sejolisa, $wp_query;

    $pages = (array) $page;
    $pages = ( 0 === count($pages) ) ? array( 'loading', 'thank-you', 'renew' ) : $pages;

    if (
        isset( $wp->query_vars['sejolisa_checkout_page'], $_GET['order_id'] ) &&
        in_array( $wp->query_vars['sejolisa_checkout_page'], $pages )
    ) :

        $order_id = intval($_GET['order_id']);

        $response = sejolisa_get_order([
            'ID' => $order_id
        ]);

        if ( false !== $response['valid'] ) :
            $sejolisa['order']  = $response['orders'];
        endif;

        $valid = true;

    endif;

    return $valid;

}
