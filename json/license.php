<?php
namespace SejoliSA\JSON;

Class License extends \SejoliSA\JSON
{
    /**
     * Many license IDs
     * @since   1.0.0
     * @var     array
     */
    protected $licenses = array();

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
     * Hooked via action wp_ajax_sejoli-license-table, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

        $table = $this->set_table_args($_POST);

        $data    = [];

        if(isset($_POST['backend']) && current_user_can('manage_sejoli_licenses')) :

        else :
        
            $table['filter']['user_id'] = get_current_user_id();
        
        endif;

        $response = sejolisa_get_licenses($table['filter'], $table);

        if(false !== $response['valid']) :
        
            $data = $response['licenses'];        
        
        endif;

        echo wp_send_json([
            'table'           => $table,
            'draw'            => $table['draw'],
            'data'            => $data,
            'recordsTotal'    => $response['recordsTotal'],
            'recordsFiltered' => $response['recordsTotal'],
        ]);

        exit;

    }

    /**
     * Update multiple licenses
     * Hooked via action wp_ajax_sejoli-license-update, priority 1
     * @return  void
     */
    public function update_licenses() {

        $post_data = wp_parse_args($_POST,[
            'licenses' => NULL,
            'status'   => NULL,
            'nonce'    => NULL
        ]);

        if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-license-update') &&
            is_array($post_data['licenses'])
        ) :
    
            if(in_array($post_data['status'], ['active', 'pending', 'inactive'])) :
    
                sejolisa_update_status_licenses($post_data['status'], $post_data['licenses']);
    
            else :
    
                sejolisa_reset_licenses($post_data['licenses']);
    
            endif;
    
        endif;
    
        exit;
    
    }

    /**
     * Updating License Status to Inactive Based on Subscription Status is Expired
     * Hooked via filter cron_schecules, priority 1
     *
     * @since 1.0.0
     */
    public function sejoli_update_license_status_cron_schedules($schedules) {

        $schedules['license_status_to_inactive'] = array(
            'interval' => 300, 
            'display'  => 'Update Status License to Inactive Based on Subscription Expired Once every 5 minutes'
        );

        return $schedules;

    }

    /**
     * Set Schedule Event for Updating License Status to Inactive Based on Subscription Status is Expired
     * Hooked via action admin_init, priority 1
     *
     * @since 1.0.0
     */
    public function schedule_update_license_status_to_inactive_based_on_subscription_status() {

        // Schedule an action if it's not already scheduled
        if ( ! wp_next_scheduled( 'update_status_license_to_inactive' ) ) {
            
            wp_schedule_event( time(), 'license_status_to_inactive', 'update_status_license_to_inactive' );
        
        }

    }

    /**
     * Process Updating Subscription Status to Expired Based on Subscription Time is Expired
     * Hooked via action update_status_license_to_inactive, priority 1
     *
     * @since    1.0.0
     */
    public function update_license_status_to_inactive_based_on_subscription_status() {
        
        $check_subscriptions = sejolisa_get_expired_subscription_data(); 

        if (is_array($check_subscriptions) || is_object($check_subscriptions)) :

            foreach( $check_subscriptions['subscriptions'] as $check_subscription ) :

                $subscription_order_id = $check_subscription->order_id;
                $check_licenses        = sejolisa_get_license_by_order_id( $subscription_order_id ); 
                
                if( !empty($check_licenses) ) :
                    $license_order_id      = $check_licenses[0];
                    $license_status        = $check_licenses[1];

                    if( $license_order_id === $subscription_order_id && $license_status === 'active' ):
                            
                        sejolisa_update_status_license_by_subscription_expired( $license_order_id );
                        
                    endif;

                endif;
          
            endforeach;

        endif;

    }

}