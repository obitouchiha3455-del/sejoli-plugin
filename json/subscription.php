<?php
namespace SejoliSA\JSON;

use Carbon\Carbon;

Class Subscription extends \SejoliSA\JSON
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
     * Hooked via action wp_ajax_sejoli-subscription-table, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

		$table = $this->set_table_args($_POST);

		$data    = [];

        if(isset($_POST['backend']) && current_user_can('manage_sejoli_subscriptions')) :

        else :
            $table['filter']['subscription.user_id'] = get_current_user_id();
        endif;

		$respond = sejolisa_get_subscriptions($table['filter'], $table);

		if(false !== $respond['valid']) :

            $data = $respond['subscriptions'];
            $temp = array();

            $i = 0;

            foreach($data as $_dt) :

                $temp[$i]       = $_dt;
                $temp[$i]->link = add_query_arg( array( 'order_id' => $_dt->order_id) , home_url('checkout/renew/') );

                if( strtotime($_dt->end_date) > current_time('timestamp')) :
                    $temp[$i]->day_left = Carbon::createFromDate($_dt->end_date)->diffInDays(Carbon::now());
                    $temp[$i]->expired  = false;
                    $temp[$i]->renewal  = true;
                else :
                    $temp[$i]->day_left = 0;
                    $temp[$i]->expired  = true;
                    $temp[$i]->renewal  = true;
                    $temp[$i]->status   = 'expired';

                    $max_renewal_day = absint( sejolisa_carbon_get_post_meta( $_dt->product_id, 'subscription_max_renewal_days') );

                    if(
                        0 < $max_renewal_day &&
                        $max_renewal_day < sejolisa_get_difference_day( strtotime($_dt->end_date) )
                    ) :
                        $temp[$i]->renewal = false;
                        $temp[$i]->link    = get_permalink( $_dt->product_id );
                    endif;

                endif;
                $i++;
            endforeach;

            $data = $temp;
		endif;

		echo wp_send_json([
			'table'           => $table,
			'draw'            => $table['draw'],
			'data'            => $data,
			'recordsTotal'    => $respond['recordsTotal'],
			'recordsFiltered' => $respond['recordsTotal'],
		]);

		exit;
    }

    /**
     * Prepare for exporting data
     * Hooked via action wp_ajax_sejoli-subscription-export, priority 1
     * @since   1.5.3
     * @return  void
     */
    public function prepare_for_exporting() {

        $response = [
            'url'   => admin_url('/'),
            'data'  => [],
        ];

        $post_data = wp_parse_args($_POST,[
            'data'    => array(),
            'nonce'   => NULL,
            'backend' => false
        ]);

        if(wp_verify_nonce($post_data['nonce'], 'sejoli-do-subscription-export')) :

            $request          = array();

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
                                        site_url('/sejoli-ajax/sejoli-subscription-export')
                                    ),
                                    'sejoli-subscription-export',
                                    'sejoli-nonce'
                                );
        endif;

        echo wp_send_json($response);

        exit;
    }

    /**
     * Updating Subscription Status to Expired Based on Subscription Status is Active
     * Hooked via filter cron_schecules, priority 1
     *
     * @since 1.0.0
     */
    public function sejoli_update_subscription_to_expired_cron_schedules($schedules) {

        $schedules['subscription_status_to_expired'] = array(
            'interval' => 300, 
            'display'  => 'Update Status Subscription to Expired Based on Subscription time is Expired Once every 5 minutes'
        );

        return $schedules;

    }

    /**
     * Set Schedule Event for Updating Subscription Status to Expired Based on Subscription Time is Expired
     * Hooked via action admin_init, priority 1
     *
     * @since 1.0.0
     */
    public function schedule_update_subscription_status_to_expired_based_on_subscription_is_expired() {

        // Schedule an action if it's not already scheduled
        if ( ! wp_next_scheduled( 'update_status_subscription_to_expired' ) ) {
            
            wp_schedule_event( time(), 'subscription_status_to_expired', 'update_status_subscription_to_expired' );
        
        }

    }

    /**
     * Update status subscription to expired
     * @since   1.0.0
     * @param   array   $order_data [description]
     */
    public function set_subcription_expired() {

        $check_subscriptions = sejolisa_get_subscription_by_status('active');

        foreach( $check_subscriptions as $check_subscription ){

            $subscription_order_id = $check_subscription;
            $get_subscription = sejolisa_get_subscription_by_order( $subscription_order_id ); 
            $max_renewal_day = absint( sejolisa_carbon_get_post_meta( $get_subscription['subscription']->product_id, 'subscription_max_renewal_days') );

            $expired_renewal_date = date( 'Y-m-d H:i:s', current_time( 'timestamp') - ( $max_renewal_day * DAY_IN_SECONDS ) );
            if( strtotime($get_subscription['subscription']->end_date) < strtotime($expired_renewal_date)) :
                $args = [
                    'ID'     => $get_subscription['subscription']->ID,
                    'status' => 'expired'
                ];

                $respond = sejolisa_update_subscription_status($args);
                sejolisa_set_respond($respond, 'subscription');
            endif;
          
        }

    }

    /**
     * Update multiple subscriptions
     * Hooked via action wp_ajax_sejoli-subscription-update, priority 1
     * @return  void
     */
    public function update_subscriptions() {

        $post_data = wp_parse_args($_POST,[
            'subscriptions' => NULL,
            'status'   => NULL,
            'nonce'    => NULL
        ]);

        if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-subscription-update') &&
            is_array($post_data['subscriptions'])
        ) :
    
            if(in_array($post_data['status'], ['pending', 'active', 'inactive', 'expired'])) :
    
                sejolisa_update_status_subscriptions($post_data);
    
            else :
    
                sejolisa_reset_subscriptions($post_data['subscriptions']);
    
            endif;
    
        endif;
    
        exit;
    
    }
    
}
