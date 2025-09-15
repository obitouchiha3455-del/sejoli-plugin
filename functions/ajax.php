<?php

function sejoli_ajax_verify_nonce( $action ) {

    $valid = false;

    if ( isset( $_REQUEST['sejoli_ajax_nonce'] ) && 
        wp_verify_nonce($_REQUEST['sejoli_ajax_nonce'], $action) ) :

        $valid = true;

    endif;

    return $valid;

}

function sejoli_is_ajax_request() {

    $valid = false;

    if ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 
        strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' ) :
    
        $valid = true;
    
    endif;

    return $valid;

}