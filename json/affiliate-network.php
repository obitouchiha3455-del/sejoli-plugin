<?php
namespace SejoliSA\JSON;

Class AffiliateNetwork extends \SejoliSA\JSON
{
    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Translate data to jstree json data format
     * @since   1.0.0
     * @param   array   $list
     * @param   string  $parent
     * @param   string  $type
     * @return  array
     */
    protected function translate_for_jstree( $list, $parent = '#', $type = 'detail' ) {

        $data = array();

        foreach( $list as $i => $single ) :

            $display_name = $avatar_url = '';

            if( 'detail' === $type ) :

                $user         = get_user_by( 'id', $single['id'] );
                $display_name = $user->display_name;
                $avatar_url   = get_avatar_url( $user->ID );

                $data[] = array(
                    'id'       => $single['id'],
                    'parent'   => $parent,
                    'text'     => $display_name,
                    'icon'     => $avatar_url,
                    'a_attr'   => array(
                        'data-ID'   => $single['id']
                    )
                );

            else :

                $data[] = $single['id'];

            endif;

            if(
                is_array($single['downlines']) &&
                0 < count($single['downlines'])
            ) :
                $data = array_merge( $data, $this->translate_for_jstree( $single['downlines'], $single['id'], $type ) );
            endif;

        endforeach;

        return $data;
    }

    /**
     * Get network list
     * Hooked via action sejowoo-ajax/get-network-list, priority 1
     * @since   1.0.0
     * @return  void
     */
    public function get_network_list() {

        $response = array(

        );

        $args = wp_parse_args( $_GET, array(
            'node'  => 0,
            'nonce' => ''
        ));

       
        if(
            is_user_logged_in() &&
            wp_verify_nonce( $args['nonce'], 'sejoli-affiliate-get-network-list' )
        ) : 
        
            
            $data = sejolisa_user_get_downlines(
                        get_current_user_id(),
                        sejolisa_get_max_downline_tiers()
                    );


            if( false !== $data ) :
                $response = $this->translate_for_jstree( $data, '#' );
            endif;

        endif;

        wp_send_json( $response );

    }

    /**
     * Get user network list
     * Hooked via action sejowoo-ajax/get-user-network-list, priority 1
     * @return void
     */
    public function get_user_network_list(){
        $response = array(

        );

        $args = wp_parse_args( $_GET, array(
            'node'  => 0,
            'nonce' => '',
            'data_id' => 0
        ));

       
        if(
            is_user_logged_in() &&
            wp_verify_nonce( $args['nonce'], 'sejoli-affiliate-get-user-network-list' ) &&
            isset($args['data_id']) && !empty($args['data_id'])
        ) : 
        
            $check_user_id = $args['data_id'];


            $data_downline = sejolisa_admin_user_get_downlines(
                        $check_user_id,
                        sejolisa_get_max_downline_tiers()
                    );
            
            
            $data_user = sejolisa_admin_get_current_user_data(
                $check_user_id
            );

            $data_uplines = sejolisa_admin_user_get_uplines(
                $check_user_id,
                sejolisa_get_max_upline_tiers()               
            );


            if( false !== $data_downline ) :
                $data_downline = $this->translate_for_jstree( $data_downline, $check_user_id);
            endif;

            /** @var mixed $all_data  */
            $all_data = array_merge($data_uplines, $data_user, $data_downline);

            foreach ($all_data as $k_ad => $v_ad) {
                $all_data[$k_ad]['state'] = array(
                    'opened' => true
                );
            }


        endif;

        wp_send_json($all_data);

        
    }

    /**
     * Get single network detail
     * Hooked via action sejowoo-ajax/get-network-detail, priority 1
     * @since   1.0.0
     * @return  void
     */
    public function get_network_detail() {

        $response = array(
            'valid'     => true,
            'message'   => __('Maaf, anda tidak berhak untuk mengakses data affiliate.', 'sejolisa')
        );

        $args = wp_parse_args( $_GET, array(
            'id'    => 0,
            'nonce' => ''
        ));

        if(
            is_user_logged_in() &&
            wp_verify_nonce( $args['nonce'], 'sejoli-affiliate-get-network-detail' ) &&
            !empty($args['id'])
        ) :
            $data = sejolisa_user_get_downlines(
                        get_current_user_id(),
                        sejolisa_get_max_downline_tiers()
                    );

            if( false !== $data ) :

                $tree = $this->translate_for_jstree( $data, '#', 'simple' );

                if( in_array( $args['id'], $tree ) ) :

                    $detail_user_id = $args['id'];
                    $user_data = get_userdata($detail_user_id);

                    $show_affiliate_data = boolval( sejolisa_carbon_get_theme_option( 'sejoli_affiliate_tool_data_kontak_aff' ));
                    if( true !== $show_affiliate_data ) :
                        $response['user']   = array(
                            'name'       => $user_data->display_name,
                            'email'      => NULL,
                            'phone'      => NULL,
                            'address'    => get_user_meta( $detail_user_id, '_address', true),
                        );
                    else:
                        $response['user']   = array(
                            'name'       => $user_data->display_name,
                            'email'      => $user_data->user_email,
                            'phone'      => get_user_meta( $detail_user_id, '_phone', true),
                            'address'    => get_user_meta( $detail_user_id, '_address', true),
                        );
                    endif;



                endif;

            else :
                $response['valid']   = false;
                $response['message'] = __('Anda belum memiliki jaringan affiliasi.', 'sejoli');
            endif;

        else :

            $response['valid']  = false;

        endif;

        $response['args'] = $args;

        wp_send_json( $response );

    }

}
