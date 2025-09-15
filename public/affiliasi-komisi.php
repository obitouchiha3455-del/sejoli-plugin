<?php

namespace SejoliSA\Front;

class Affiliasi_Komisi
{

    /**
     * ajax get all commission by current user
     * hooked via action sejoli_ajax_get-commission, priority 999
     *
     * @return json
     */
    public function ajax_get_commission_current_user()
    {
        check_ajax_referer('ajax-nonce', 'security');

        $search = [];
        $order = [[
            'column' => 'ID',
            'sort' => 'desc',
        ]];

        $request = wp_parse_args($_POST, array(
            'start' => 0,
            'length' => 10,
            'draw' => 1,
        ));

        if (isset($request['search']) && 0 < count($request['search'])):

            foreach ($request['search'] as $_search):

                $search[$_search['name']] = [
                    'name' => $_search['name'],
                    'val' => $_search['val'],
                ];

            endforeach;

        endif;

        if (isset($request['order']) && 0 < count($request['order'])):
            $i = 0;
            foreach ($request['order'] as $_order):

                $order[$i]['sort'] = $_order['dir'];
                switch ($_order['column']):
            case 0:$order[$i]['column'] = 'created_at';
                break;
                endswitch;

                $i++;
            endforeach;
        endif;

        $filter = array(
            'start' => $request['start'],
            'length' => $request['length'],
            'search' => $search,
            'order' => $order,
        );

        $data = [];

        // $respond = \sejoli\Wallet::set_user_id( get_current_user_id() )
        //     ->set_label('commission')
        //     ->set_filter($filter)
        //     ->get_commission_user_list()
        //     ->get_commission_user_list_total()
        //     ->respond();

        // if ( $respond['valid'] ) :

        //     foreach ( $respond['commission'] as $key => $value ) :

        // if ( $value->type === 'in' ) :
        $type = '<a class="ui green label">Masuk</a>';
        // else:
        // $type = '<a class="ui orange label">Keluar</a>';
        // endif;

        // $data[] = array(
        //     'id'     => $value->ID,
        //     'date'   => date( "d-m-Y", strtotime( $value->created_at ) ),
        //     'note'   => $value->note,
        //     'amount' => wc_price( $value->value ),
        //     'type'   => $type,
        // );

        $data[] = array(
            'id' => 1,
            'date' => date("d-m-Y"),
            'note' => 'note 123',
            'amount' => sejolisa_price_format(10),
            'type' => $type,
        );

        //     endforeach;

        // endif;

        $recordsTotal = $recordsTotal = 1;

        $response = array(
            'draw' => $request['draw'],
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
        );

        wp_send_json($response);
    }

    /**
     * ajax get commission detail
     * hooked via action sejoli_ajax_get-commission-detail, priority 999
     *
     * @return json
     */
    public function ajax_get_commission_detail()
    {
            $commission = [];
            $commission_id = $_GET['commission_id'];

            // $respond = \sejoli\Wallet::set_id( $commission_id )
            //     ->get_commission_detail()
            //     ->respond();

            // if ( $respond['valid'] ) :

            // if ( $respond['commission']->type === 'in' ) :
            $type = '<a class="ui green label">Masuk</a>';
            // else:
            // $type = '<a class="ui orange label">Keluar</a>';
            // endif;

            // if ( $respond['commission']->product_id ) :
            //     $product = wc_get_product( $respond['commission']->product_id );
            //     $product_name = $product->get_title();
            // else:
            //     $product_name = '-';
            // endif;

            // $commission = array(
            //     'date'   => date( "d-m-Y", strtotime( $respond['commission']->created_at ) ),
            //     'product'=> $product_name,
            //     'amount' => wc_price( $respond['commission']->value ),
            //     'type'   => $type,
            //     'note'   => $respond['commission']->note,
            // );

            $commission = array(
                'date' => date("d-m-Y"),
                'product' => 'produk 1',
                'amount' => sejolisa_price_format(10),
                'type' => $type,
                'note' => 'note 123',
            );

            // endif;

            wp_send_json($commission);
    }
}
