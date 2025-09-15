<?php

namespace SejoliSA\Front;

class Affiliasi_Help
{

    /**
     * ajax get affilaite link
     * hooked via action sejoli_ajax_get-affiliate-help, priotrity 100
     *
     * @return void
     */
    public function ajax_get_affiliate_help()
    {
        check_ajax_referer('ajax-nonce', 'security');

        $product_id = $_POST['product_id'];

        // $respond = \sejoli\Affiliate::set_product_id( $product_id )
        //             ->get_help_list()
        //             ->respond();

        $data = array();

        // if ( $respond['valid'] && is_array( $respond['help'] ) ) :

        //     foreach ( $respond['help'] as $key => $value ) :

                $data[] = array(
                    'title' => 'Help 1',
                    'product_id'  => $product_id,
                    'key'   => 'help1',
                );

        //     endforeach;

        // endif;

        wp_send_json($data);
    }   

    /**
     * ajax get affilaite link
     * hooked via action sejoli_ajax_get-affiliate-help-detail, priotrity 100
     *
     * @return void
     */
    public function ajax_get_affiliate_help_detail()
    {
        check_ajax_referer('ajax-nonce', 'security');

        $product_id = $_POST['product_id'];
        $key = $_POST['key'];

        // $respond = \sejoli\Affiliate::set_product_id( $product_id )
        //             ->get_help_detail( $key )
        //             ->respond();

        // $data = array();

        // if ( $respond['valid'] && is_array( $respond['help'] ) ) :

            // $data = $respond['help'];

            $data = [
                'help_title' => 'help 1',
                'help_image' => 'https://via.placeholder.com/600x600.png?text=Sejoli',
            ];

        // endif;

        wp_send_json($data);
    }

}