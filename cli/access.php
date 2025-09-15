<?php

namespace SejoliSA\CLI;

class Access
{
    /**
     * Get all active subscription and product
     *
     * <user_id>
     * The User ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa access list_user_bought 1
     *
     * @when after_wp_load
     */
    public function list_user_bought(array $args) {

        list( $user_id ) = $args;

        wp_set_current_user($user_id);

        $_GET = [
            'nonce' => wp_create_nonce('sejoli-access-list-by-product')
        ];

        do_action('wp_ajax_sejoli-access-list-by-product');
    }
}
