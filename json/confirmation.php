<?php
namespace SejoliSA\JSON;

Class Confirmation extends \SejoliSA\JSON
{
    /**
     * Set confirmation table data
     * Hooked via action wp_ajax_sejoli-confirmation-table, priority 1
     * @since   1.1.6
     * @return  json
     */
    public function set_for_table() {

        $table = $this->set_table_args($_POST);

        $data    = [];

        if(isset($_POST['backend']) && current_user_can('manage_sejoli_orders')) :

        else :
            $table['filter']['user_id'] = get_current_user_id();
        endif;

        $response = sejolisa_get_confirmations($table['filter'], $table);

        if(false !== $response['valid']) :
            $data = $response['confirmations'];
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
     * Get single order data
     * Hooked via wp_ajax_sejoli-order_detail, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function get_detail() {

        $data = false;

        if(wp_verify_nonce($_GET['nonce'], 'sejoli-render-confirmation-detail')) :

            $response = sejolisa_get_confirmation(intval($_GET['id']));

            if(false !== $response['valid']) :
                $data = $response['confirmation'];
                $data->detail     = maybe_unserialize($data->detail);
                $data->created_at = date('Y M d', strtotime($data->created_at));
            endif;
        endif;

        echo wp_send_json($data);
        exit;
    }
}
