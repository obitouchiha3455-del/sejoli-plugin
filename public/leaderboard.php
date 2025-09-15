<?php

namespace SejoliSA\Front;

class Leaderboard
{

        /**
     * ajax get leaderboard
     * hooked via action sejoli_ajax_get-leaderboard, priotrity 100
     *
     * @return void
     */
    public function ajax_get_leaderboard()
    {   
        check_ajax_referer('ajax-nonce', 'security');

        $filter = array();

        foreach ( $_POST['filter'] as $key => $value ) :

            if ( $value['val'] ) :

                $filter[$value['name']] = $value['val'];
        
            endif;

        endforeach;

        // $respond = \sejoli\Leaderboard::set_filter( $filter )
        //             ->get_list()
        //             ->respond();

        $data = array();

        // if ( $respond['valid'] && is_object( $respond['leaderboard'] ) ) :

        //     foreach ( $respond['leaderboard'] as $key => $value ) :

        //         $user = get_userdata( $value->user_id );

                // $data[] = array(
                //     'id'    => $key+1,
                //     'name'  => $user->display_name,
                //     'avatar'=> get_avatar_url( $value->user_id ),
                //     'sales' => $value->sales,
                // );

                $data[] = array(
                    'id'    => 1,
                    'name'  => 'Jhon Doe',
                    'avatar'=> get_avatar_url(),
                    'sales' => 23,
                );

        //     endforeach;

        // endif;

        wp_send_json($data);
    }

}