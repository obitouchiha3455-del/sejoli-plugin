<?php

namespace SejoliSA\Front;

class Affiliasi_Link
{

    /**
     * ajax get affilaite link
     * hooked via action sejoli_ajax_get-affiliate-link, priotrity 999
     *
     * @return void
     */
    public function ajax_get_affiliate_link()
    {
        check_ajax_referer('ajax-nonce', 'security');

        $product_id = $_POST['product_id'];

        // $respond = \sejoli\Affiliate::set_product_id( $product_id )
        //             ->get_link_list()
        //             ->respond();

        $data = array();

        // if ( $respond['valid'] && is_array( $respond['link_list'] ) ) :

        //     foreach ( $respond['link_list'] as $key => $value ) :

                // $data[] = array(
                //     'title' => $value['title'],
                //     'description' => $value['description'],
                //     'link'  => $value['link'],
                //     'key'   => $key
                // );

                $data[] = array(
                    'title' => 'Link 1',
                    'description' => 'Desc link 1',
                    'link'  => 'link.com',
                    'key'   => 'link1'
                );

        //     endforeach;

        // endif;

        wp_send_json($data);
    }

}