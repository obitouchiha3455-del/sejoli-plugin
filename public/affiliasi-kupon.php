<?php

namespace SejoliSA\Front;

class Affiliasi_Kupon
{

    /**
     * ajax get affilaite coupon user
     * hooked via action sejoli_ajax_get-affiliate-coupon-user, priotrity 999
     *
     * @return void
     */
    public function ajax_get_affiliate_coupon_user()
    {
		$_post = json_decode( file_get_contents('php://input'), true );

		if ( ! wp_verify_nonce( $_post['security'], 'ajax-nonce' ) ) :
			die( 'invalid nonce' );
		endif;

        $request = wp_parse_args($_post,[
            'start'  => 0,
            'length' => 10,
            'draw'	 => 1,
        ]);

        $search = [];

        $order = [[
            'column'=> 'ID',
            'sort'	=> 'desc'
        ]];

        if ( isset( $request['search'] ) && 0 < count( $request['search'] ) ) :
			foreach( $request['search'] as $_search ) :
                $search[$_search['name']] = [
					'name' => $_search['name'],
					'val'  => $_search['val']
				];
			endforeach;
        endif;

		if( isset( $request['order'] ) && 0 < count( $request['order'] ) ) :
			$i = 0;
			foreach( $request['order'] as $_order ) :
				$order[$i]['sort']	= $_order['dir'];
				switch( $_order['column'] ) :
					case 0 	: $order[$i]['column'] = 'created_at'; break;
				endswitch;
				$i++;
			endforeach;
		endif;

        $filter = [
            'start'  => $request['start'],
            'length' => $request['length'],
            'search' => $search,
            'order'  => $order,
        ];

        $affiliate_id = get_current_user_id();

        // $respond = \sejoli\Coupon::set_affiliate_id( $affiliate_id )
        //                 ->set_filter( $filter )
        //                 ->get_list_by_user()
        //                 ->respond();

        $coupons = [];
        $recordsTotal = 0;

        // if ( $respond['valid'] && is_object( $respond['coupons'] ) ) :

        //     foreach ( $respond['coupons'] as $key => $value ):

        //         $coupon_parent = '';
        //         $coupon = new \WC_Coupon( $value->coupon_parent_id );
        //         if ( 0 !== $coupon->get_id() ) {
        //             $coupon_parent = $coupon->get_code();
        //         }

		// 	if ( 1 === intval($value->active) ) :
		// 		$active = '<a class="ui green label">aktif</a>';
		// 	else:
				$active = '<a class="ui red label">tidak aktif</a>';
			// endif;

                // $coupons[] = array(
                //     date('d-m-Y', strtotime($value->created_at)),
                //     $coupon_parent,
                //     $value->coupon_code,
                //     $value->usage,
                //     $active,
                // );

                $coupons[] = array(
                    date('d-m-Y', strtotime($value->created_at)),
                    '123',
                    'tes123',
                    10,
                    $active,
                );

            // endforeach;

        //     $recordsTotal = $respond['recordsTotal'];

        // endif;

        $response = array(
			'data'            => $coupons,
			'draw'            => $request['draw'],
		    'recordsTotal'    => $recordsTotal,
		    'recordsFiltered' => $recordsTotal,
        );

        wp_send_json($response);
    }    
    
    /**
     * get coupon parent list
     * hooked via action, sejoli_ajax_get-affiliate-coupon-parent-list, priority 100
     *
     * @return void
     */
    public function ajax_get_coupon_parent_list_select2()
    {
        check_ajax_referer( 'ajax-nonce', 'security' );

        $search = sanitize_text_field( $_POST['search'] );

        // $respond = \sejoli\Coupon::get_parent_list( $search )
        //                 ->respond();

        $coupon_parent = [];
        $recordsTotal = 0;

        // if ( $respond['valid'] && is_array( $respond['coupon_parent'] ) ) :

        //     foreach ( $respond['coupon_parent'] as $key => $value ):

        //         $coupon_parent[] = array(
        //             'id'   => $value['ID'],
        //             'text' => $value['code'],
        //         );

                $coupon_parent[] = array(
                    'id'   => 1,
                    'text' => '123',
                );

        //     endforeach;

        // endif;

        wp_send_json($coupon_parent);
    }

    /**
     * ajax add affilaite coupon user
     * hooked via action sejoli_ajax_add-affiliate-coupon-user, priotrity 100
     *
     * @return void
     */
    public function ajax_add_affiliate_coupon_user()
    {
		if ( ! wp_verify_nonce( $_POST['security'], 'ajax-nonce' ) ) :
			die( 'invalid nonce' );
		endif;

		$args = array(
			'user_id' => get_current_user_id()
		);

        // if ( isset( $_POST['data'] ) && is_array( $_POST['data'] ) ) :

        //     foreach ( $_POST['data'] as $key => $value ) :

		// 		$name = $value['name'];
        //         $args[$name] = $value['val'];

        //     endforeach;

		// endif;

		// $add_coupon = sejoli_add_coupon_affiliate($args);

        // if ( $add_coupon['valid'] ) :

        //     $coupon = $add_coupon['coupon'];

        //     if ( isset( $coupon['ID'] ) && !empty( $coupon['ID'] ) ) :

                wp_send_json_success( [ __( 'Kupon berhasil ditambahkan', 'sejoli' ) ] );

        //     else:

        //         wp_send_json_error( [ __( 'Kupon gagal ditambahkan', 'sejoli' ) ] );

        //     endif;

        // else:

        //     wp_send_json_error( $add_coupon['messages'] );

        // endif;
	}
}