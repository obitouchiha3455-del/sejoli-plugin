<?php
namespace SejoliSA\JSON;

Class Order extends \SejoliSA\JSON
{
    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Set user options
     * @since   1.0.0
     * @return  json
     */
    public function set_for_options() {

    }

    /**
     * Set table data
     * Hooked via action wp_ajax_sejoli-order-table, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

		$table = $this->set_table_args($_POST);

		$data  = [];

        if(isset($_POST['backend']) && current_user_can('manage_sejoli_orders')) :

        else :
            $table['filter']['user_id'] = get_current_user_id();
        endif;

		$respond = sejolisa_get_orders($table['filter'], $table);

		if(false !== $respond['valid']) :
			$data = $respond['orders'];
		endif;

        if(class_exists('WP_CLI')) :
            __debug([
    			'table'           => $table,
    			'draw'            => $table['draw'],
    			'data'            => $data,
    			'recordsTotal'    => $respond['recordsTotal'],
    			'recordsFiltered' => $respond['recordsTotal'],
    		]);
        else :
    		echo wp_send_json([
    			'table'           => $table,
    			'draw'            => $table['draw'],
    			'data'            => $data,
    			'recordsTotal'    => $respond['recordsTotal'],
    			'recordsFiltered' => $respond['recordsTotal'],
    		]);
        endif;
		exit;
    }

    /**
     * Set table data
     * Hooked via action wp_ajax_sejoli-affiliate-order-table, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_affiliate_table() {

        $table = $this->set_table_args($_POST);

        $data  = [];

        if(isset($_POST['backend']) && current_user_can('manage_sejoli_orders')) :

        else :
            $table['filter']['affiliate_id'] = get_current_user_id();
        endif;

        $respond = sejolisa_get_orders($table['filter'], $table);

        if(false !== $respond['valid']) :
            $data = $respond['orders'];
        endif;

        if(class_exists('WP_CLI')) :
            __debug([
    			'table'           => $table,
    			'draw'            => $table['draw'],
    			'data'            => $data,
    			'recordsTotal'    => $respond['recordsTotal'],
    			'recordsFiltered' => $respond['recordsTotal'],
    		]);
        else :
    		echo wp_send_json([
    			'table'           => $table,
    			'draw'            => $table['draw'],
    			'data'            => $data,
    			'recordsTotal'    => $respond['recordsTotal'],
    			'recordsFiltered' => $respond['recordsTotal'],
    		]);
        endif;

        exit;
    }

    /**
     * Set chart data
     * Hooked via wp_ajax_sejoli-order-chart, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_chart() {

        $start_date = $end_date = $chart = NULL;
        $type       = $_GET['type'];
        $filter     = $this->set_filter_args($_GET['data']);

        if(isset($filter['date-range']) && !empty($filter['date-range'])) :
            list($start_date, $end_date) = explode(' - ', $filter['date-range']);
            unset($filter['date-range']);

        endif;

        $query = \SejoliSA\Model\Order::set_chart_start_date($start_date)
                        ->set_chart_end_date($end_date);

        if(is_array($filter) && 0 < count($filter)) :
            $query = $query->set_filter_from_array($filter);
        endif;

        $respond = $query->set_for_chart($type)
                        ->respond();

        $order_status = apply_filters('sejoli/order/status', []);
        $chart        = $this->set_chart_data($respond['data'], $respond['chart'], $order_status);

        echo wp_send_json(wp_parse_args($chart,[
            'labels'   => NULL,
            'datasets' => NULL
        ]));
        exit;
    }

    /**
     * Get single order data
     * Hooked via wp_ajax_sejoli-order_detail, priority 1
     * @since   1.0.0
     * @since   1.6.1   Add restriction data based on current user
     * @return  json
     */
    public function get_detail() {

        $data = false;

        if(wp_verify_nonce($_GET['nonce'], 'sejoli-order-detail')) :

            $response = sejolisa_get_order(['ID' => $_GET['order_id'] ]);

            if(false !== $response['valid']) :

                $status_logs = (isset($response['orders']['meta_data']['status_log']) ? $response['orders']['meta_data']['status_log'] : '');

                if ( !is_array( $status_logs ) ) : $status_logs = []; endif;
                if (array_filter($status_logs)) {

                    foreach ($status_logs as $key => $status_log) {

                        $status_log_data = '';
                        if ( is_array( $status_log ) ) : 
                            $status_log_data = $status_log;
                        endif;

                        if(!empty($status_log_data)){

                            if($status_log > 0) {

                                $get_status_log[] = [
                                    'update_date' => date( "d M Y H:i:s", strtotime( $status_log['update_date'] ) ),
                                    'old_status'  => sejolisa_get_status_log($status_log['old_status']),
                                    'new_status'  => sejolisa_get_status_log($status_log['new_status']),
                                    'updated_by'  => $status_log['updated_by']
                                ];

                            }

                        }

                    }

                    $response['orders']['status_log'] = (isset($get_status_log) ? $get_status_log : '');

                } else {

                    $response['orders']['status_log'] = null;

                }

                $show_affiliate_data = boolval( sejolisa_carbon_get_theme_option( 'sejoli_affiliate_tool_data_kontak_aff_order_detail' ));
                if( true !== $show_affiliate_data ) :
                    $response['orders']['affiliate'] = null;
                endif;

                $show_buyer_data = boolval( sejolisa_carbon_get_theme_option( 'sejoli_affiliate_tool_data_kontak_buyer_order_detail' ));
                if( true !== $show_buyer_data ) :
                    $response['orders']['user']->data->user_email = null;
                    $response['orders']['user']->data->meta->phone = null;
                endif;

                // Get payment data info
                $response['orders']['payment_data'] = apply_filters('sejoli/payment/data', $response['orders']['meta_data']);

                $payment_gateway = $response['orders']['payment_gateway'];

                $unique_code = '';
                if(isset($response['orders']['meta_data'][$payment_gateway]['unique_code'])):
                    $unique_code = $response['orders']['meta_data'][$payment_gateway]['unique_code'];
                    $response['orders']['meta_data']['unique_code'] = sejolisa_price_format($unique_code);
                endif;

                $total_wt_additionalfee = $response['orders']['grand_total'];
                if(isset($response['orders']['meta_data'][$payment_gateway]['unique_code'])):
                    $total_wt_additionalfee = $response['orders']['grand_total'] - $response['orders']['meta_data'][$payment_gateway]['unique_code'];
                elseif(isset($response['orders']['meta_data']['shipping_data']['cost'])):
                    $total_wt_additionalfee = $response['orders']['grand_total'] - $response['orders']['meta_data']['shipping_data']['cost'];
                elseif(isset($response['orders']['meta_data']['shipping_data']['cost']) && isset($response['orders']['meta_data'][$payment_gateway]['unique_code'])):
                    $total_wt_additionalfee = $response['orders']['grand_total'] - $response['orders']['meta_data']['shipping_data']['cost'] - $response['orders']['meta_data'][$payment_gateway]['unique_code'];
                endif;

                $enable_ppn = sejolisa_carbon_get_post_meta( $response['orders']['product_id'], 'enable_ppn' );
                if(true === $enable_ppn && isset($response['orders']['meta_data']['ppn'])) :
                    $price_without_ppn = ($total_wt_additionalfee / (1 + $response['orders']['meta_data']['ppn'] / 100));
                    $value_ppn         = $price_without_ppn * $response['orders']['meta_data']['ppn'] / 100;
                    $response['orders']['meta_data']['ppn'] = number_format($response['orders']['meta_data']['ppn'], 2, ',', ' ');;
                    $response['orders']['meta_data']['ppn_total'] = sejolisa_price_format($value_ppn);
                endif;
                
                $data = $response['orders'];

            endif;

            if(!current_user_can('manage_sejoli_sejoli')) :

                $user_id = get_current_user_id();

                if(
                    $user_id !== intval($data['affiliate_id']) &&
                    $user_id !== intval($data['user_id'])
                ) :
                    $data = false;
                endif;

            endif;

        endif;

        echo wp_send_json( sejolisa_remove_harm_data($data) );
        exit;
    }

    /**
     * Check if given order product is physical or not
     * Hooked via wp_ajax_sejoli-order-shipping, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function check_for_shipping() {

        $data = false;

        if(wp_verify_nonce($_POST['nonce'], 'sejoli-order-shipping')) :

            $response = sejolisa_get_orders_with_physical_product($_POST['orders']);

            if(false !== $response['valid']) :

                $orders = $response['orders'];
                $temp = [];

                foreach($orders as $i => $order) :
                    $temp[$i]                = $order;
                    $temp[$i]->meta_data     = $meta_data = maybe_unserialize($order->meta_data);
                    $temp[$i]->need_shipment = (isset($meta_data['need_shipment'])) ? boolval($meta_data['need_shipment']) : false;
                    $temp[$i]->shipping_data = isset($meta_data['shipping_data']) ? $meta_data['shipping_data'] : false;
                endforeach;

                $response['orders'] = $temp;

            endif;

            $data = $response;
        endif;

        echo wp_send_json($data);
        exit;
    }

    /**
     * Update order resi
     * Hooked via wp_ajax_sejoli-order-input-resi, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function update_resi() {
        $data = false;

        $post_data = wp_parse_args($_POST,[
            'nonce' => NULL,
            'data'  => []
        ]);

        if(wp_verify_nonce($post_data['nonce'], 'sejoli-order-input-resi')) :

            if(isset($post_data['data']['order_resi']) && 0 < count($post_data['data']['order_resi'])) :

                foreach($post_data['data']['order_resi'] as $order_id => $resi_number) :

                    $resi_number = sanitize_text_field(trim($resi_number));

                    if(!empty($resi_number)) :

                        $response = sejolisa_update_order_meta_data(
                            $order_id,
                            [
                                'shipping_data' => [
                                    'resi_number' => $resi_number
                                ]
                            ]);

                        do_action('sejoli/order/update-status', [
                            'ID'          => $order_id,
                            'status'      => 'shipping'
                        ]);

                        if(false !== $response['valid']) :
                            if (!is_array($data)) :
                                $data = [];
                            endif;

                            $data[] = sprintf( __('Order %s updated to shipping with resi number %s', 'sejoli'), $order_id, $resi_number);
                        endif;
                    endif;

                endforeach;

            endif;

        endif;

        echo wp_send_json($data);
        exit;
    }

    /**
     * Prepare for exporting order data
     * Hooked via wp_ajax_sejoli-order-export-prepare, priority 1
     * @since   1.0.2
     * @return  void
     */
    public function prepare_for_exporting() {

        $response = [
            'url'  => admin_url('/'),
            'data' => [],
        ];

        $post_data = wp_parse_args($_POST,[
            'data'    => array(),
            'nonce'   => NULL,
            'backend' => false
        ]);

        if(wp_verify_nonce($post_data['nonce'], 'sejoli-order-export-prepare')) :

            $request = array();

            foreach($post_data['data'] as $_data) :
                if(!empty($_data['val'])) :
                    $request[$_data['name']]    = $_data['val'];
                endif;
            endforeach;

            if(false !== $post_data['backend']) :
                $request['backend'] = true;
            endif;

            $response['data'] = $request;
            $response['url']  = wp_nonce_url(
                                    add_query_arg(
                                        $request,
                                        site_url('/sejoli-ajax/sejoli-order-export')
                                    ),
                                    'sejoli-order-export',
                                    'sejoli-nonce'
                                );
        endif;

        echo wp_send_json($response);
        exit;
    }

   /*
    * Check order for bulk notification
    * Hooked via action wp_ajax_sejoli-bulk-notification-order, priority 1
    * @return [type] [description]
    */
   public function check_order_for_bulk_notification() {

       $data      = false;
       $post_data = wp_parse_args($_GET,[
           'nonce'      => false,
           'product'    => NULL,
           'date-range' => date('Y-m-d',strtotime('-30day')) . ' - ' . date('Y-m-d'),
           'status'     => 'on-hold'
       ]);

       if(
           wp_verify_nonce($post_data['nonce'], 'sejoli-bulk-notification-order') &&
           !empty($post_data['product'])
       ) :

           $data = sejolisa_get_orders_for_bulks([
               'date-range' => $post_data['date-range'],
               'product_id' => $post_data['product'],
               'status'     => $post_data['status']
           ]);
       endif;

       echo wp_send_json($data);

       exit;
   }

   /**
    * Get order data for confirmation process
    * Hooked via action sejoli_ajax_check-order-for-confirmation, priority 1
    * @since    1.1.6
    * @since    1.5.0   Enchance the confirmation process
    * @return   void
    */
   public function get_order_confirmation() {

       $response = array(
           'valid'   => false,
           'order'   => null,
           'message' => __('Order berdasarkan invoice yang anda masukkan tidak ditemukan', 'sejoli')
       );

       $post_data = wp_parse_args($_GET, array(
           'order_id'          => 0,
           'sejoli_ajax_nonce' => NULL
       ));

       if(sejoli_ajax_verify_nonce('sejoli-check-order-for-confirmation') && !empty($post_data['order_id'])) :

           $order_id = trim(preg_replace('/[^0-9]/', '', $post_data['order_id']));
           $order_id = safe_str_replace('INV','', $order_id);
           $order_response = sejolisa_get_order(['ID' => $order_id]);

           // // Order not found by invoice ID, then  we will check by the amount
           // if(false === $order_response['valid']) :
           //     $order_response = sejolisa_get_order_by_amount($order_id);
           // endif;

           if(false !== $order_response['valid']) :

                switch ($order_response['orders']['status']) :

                    case 'in-progress' :
                    case 'shipping' :
                    case 'completed' :
                        $response['message'] = __('Order berdasarkan invoice yang anda masukkan sudah diproses', 'sejoli');
                        break;

                    case 'refunded' :
                    case 'cancelled' :
                        $response['message'] = __('Order berdasarkan invoice yang anda masukkan sudah dibatalkan', 'sejoli');
                        break;

                    case 'on-hold' :
                    case 'payment-confirm' :
                        $product_id = intval($order_response['orders']['product_id']);
                        $product    = get_post($product_id);

                        $response['valid']  = true;
                        $response['order']  = array(
                            'invoice_id' => $order_response['orders']['ID'],
                            'product_id' => $product_id,
                            'product'    => $product->post_title,
                            'total'      => $order_response['orders']['grand_total']
                        );
                        $response['message']= __('Order ditemukan', 'sejoli');
                        break;

                    endswitch;

                endif;
         endif;

         echo wp_send_json($response);
       exit;
   }
}
