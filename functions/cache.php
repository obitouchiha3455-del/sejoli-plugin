<?php

global $sejoliCache;

$sejoliCache = [
    'post' => array(),
    'user'  => array(),
    'theme' => array()
];

/**
 * Get value from carbon_get_post_meta to prevent duplicate call directly to database
 * @since   1.11.1
 * @param   int     $post_id
 * @param   string  $key
 * @return  mixed
 */
function sejolisa_carbon_get_post_meta( $post_id, string $key ) {

    global $sejoliCache;

    if( empty($post_id) || is_null($post_id)) :
        return null;
    endif;

    if(!array_key_exists($post_id, $sejoliCache['post'])) :
        $sejoliCache['post'][$post_id] = array();
    endif;

    if(!array_key_exists($key, $sejoliCache['post'][$post_id])) :
        $sejoliCache['post'][$post_id][$key] = carbon_get_post_meta($post_id, $key);
    endif;

    return $sejoliCache['post'][$post_id][$key];
}

/**
 * Get value from carbon_get_theme_option to prevent duplicate call directly to database
 * @since   1.11.0
 * @param   string  $key
 * @return  mixed
 */
function sejolisa_carbon_get_theme_option( string $key ) {
    global $sejoliCache;

    if(!array_key_exists($key, $sejoliCache['theme'])) :
        $sejoliCache['theme'][$key] = carbon_get_theme_option($key);
    endif;

    return $sejoliCache['theme'][$key];
}

/**
 * Get value from carbon_get_user_meta to prevent duplicate call directly to database
 * @since   1.11.0
 * @param   int     $user_id
 * @param   string  $key
 * @return  mixed
 */
function sejolisa_carbon_get_user_meta( int $user_id, string $key ) {
    global $sejoliCache;

    if(!array_key_exists($user_id, $sejoliCache['user'])) :
        $sejoliCache['user'][$user_id] = [];
    endif;

    if(!array_key_exists($key, $sejoliCache['user'][$user_id])) :
        $sejoliCache['user'][$user_id][$key] = carbon_get_user_meta($user_id, $key);
    endif;

    return $sejoliCache['user'][$user_id][$key];
}
