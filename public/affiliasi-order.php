<?php

namespace SejoliSA\Front;

class Affiliasi_Order
{

    /**
     * Ajax get affiliate order
     * hooked via action sejoli_ajax_get-affiliate-order, priority 100
     * @since 0.6
     *
     * @return void
     */
    public function ajax_get_affiliate_order()
    {
        check_ajax_referer('ajax-nonce', 'security');
  
        $request = wp_parse_args( $_POST, array(
            'start'  => 0,
            'length' => 10,
            'draw'	 => 1,
        ) );
    
        $args = [
            's_affiliate' => get_current_user_id(),
            'orderby'  => 'date',
            'order'    => 'DESC',
            'limit'    => $request['length'],
            'offset'   => $request['start'],
            'paginate' => true,
        ];

        if ( isset( $request['search'] ) && 0 < count( $request['search'] ) ) :
  
            foreach ( $request['search'] as $key => $value ) :

                if ( !empty( $value['val'] ) ) :

                    $name = $value['name'];
                    $val = $value['val']; 

                    if ( $name === 'date_range' ) :
                        $name = 'date_created';
                        $date = explode(' - ',$val);
                        $val = date('Y-m-d', strtotime($date[0])).'...'.date('Y-m-d', strtotime($date[1]));
                    elseif( $name === 'status' ) :
                        $val = safe_str_replace('wc-','',$val);
                    endif;

                    $args[$name] = $val;

                endif;

            endforeach;
  
        endif;
        
        $orders = [
            'data' => [],
            'total' => 0,
        ];

        // $data = wc_get_orders( $args );

        // if ( is_a( $data, 'stdClass' ) && $data->total > 0 ) :
    
        //     foreach ( $data->orders as $key => $value ) :
        
        //         $orders['data'][] = [
        //             $value->get_date_created()->format ('d-m-Y'),
        //             [
        //                 'invoice_number'   => $value->get_id(),
        //                 'customer_name'    => $value->get_billing_first_name().' '.$value->get_billing_last_name(),
        //                 'commission_total' => 'total komisi',
        //             ],
        //             'referal',
        //             $value->get_status(),
        //             $value->get_id()
        //         ];

                $orders['data'][] = [
                    date('d-m-Y'),
                    [
                        'invoice_number'   => 1,
                        'customer_name'    => 'Jhon Doe',
                        'commission_total' => 'total komisi',
                    ],
                    'referal',
                    'on hold',
                    1
                ];
    
        //     endforeach;

        //     $orders['total'] = $data->total;
        
        // endif;

        $response = array(
            'draw' => $request['draw'],
            'recordsTotal' => $orders['total'],
            'recordsFiltered' => $orders['total'],
            'data' => $orders['data'],
        );
    
        wp_send_json($response);   
    }   

    /**
     * Ajax get order detail
     * hooked via action sejoli_ajax_get-order-detail, priority 100
     * @since 0.6
     *
     * @return void
     */
    public function ajax_get_order_detail()
    {
        check_ajax_referer('ajax-nonce', 'security');
        
        $data = [];

        $order_id = intval($_GET['id']);

        // $order = wc_get_order( $order_id );

        // if ( is_a( $order, 'WC_Order' ) ) :

            // $data = [
            //     'date'             => $order->get_date_created()->format ('d-m-Y'),
            //     'invoice_number'   => $order->get_id(),
            //     'customer_name'    => $order->get_billing_first_name().' '.$order->get_billing_last_name(),
            //     'commission_total' => 'total komisi',
            //     'referal'          => 'referal',
            //     'status'           => $order->get_status(),
            // ];

            $data = [
                'date'             => date('d-m-Y'),
                'invoice_number'   => '1',
                'customer_name'    => 'Jhon Doe',
                'commission_total' => 'total komisi',
                'referal'          => 'referal',
                'status'           => 'on hold',
            ];

        // endif;
                        
        return wp_send_json($data);
    }
}