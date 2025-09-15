<?php

namespace SejoliSA\CLI;

class Affiliate
{
    /**
     * Get affiliate link
     *
     * <product_id>
     * The product ID
     *
     * <user_id>
     * The User ID
     *
     * ## EXAMPLES
     *
     *  wp sejolisa affliate link 10 13
     *
     * @when after_wp_load
     */
    public function link(array $args) {

        list( $product_id, $affiliate_id) = $args;

        __debug( sejolisa_get_affiliate_links($product_id, $affiliate_id) );
    }
}
