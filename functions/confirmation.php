<?php
/**
 * Add or update existing confirmation data
 * @since   1.1.6
 * @param   array $args Arguments
 * @return  array
 */
function sejolisa_add_payment_confirmation_data( $args) {

    $args = wp_parse_args($args, array(
        'order_id'   => 0,
        'product_id' => 0,
        'user_id'    => get_current_user_id(),
        'detail'     => array()
    ));

    $args['detail'] = wp_parse_args($args['detail'], array(
        'sender'         => NULL,
        'account_number' => NULL,
        'total'          => NULL,
        'bank_sender'    => NULL,
        'bank_recipient' => NULL,
        'proof'          => NULL,
        'note'           => NULL,
    ));

    $response  = SejoliSA\Model\Confirmation::reset()
                    ->set_order_id($args['order_id'])
                    ->set_product_id($args['product_id'])
                    ->set_user_id($args['user_id'])
                    ->set_detail($args['detail'])
                    ->insert()
                    ->respond();

    return $response;
}

/**
 * Get all confirmations
 * @since   1.1.6
 * @param   array  $args
 * @param   array  $table
 * @return
 * - valid          boolean
 * - confirmation   array
 * - messages       array
 */
function sejolisa_get_confirmations(array $args, $table = array()) {

    $args = wp_parse_args($args, [
        'order_id'   => NULL,
        'user_id'    => NULL,
        'product_id' => NULL
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => []
    ]);

    $query = SejoliSA\Model\Confirmation::reset()
                ->set_filter_from_array($args)
                ->set_data_start($table['start']);

    if(0 < $table['length']) :
        $query->set_data_length($table['length']);
    endif;

    if(!is_null($table['order']) && is_array($table['order'])) :
        foreach($table['order'] as $order) :
            $query->set_data_order($order['column'], $order['sort']);
        endforeach;
    endif;

    $response = $query->get()->respond();

    $i    = 0;
    $temp = array();

    foreach((array) $response['confirmations'] as $data) :
        $temp[$i]             = $data;
        $temp[$i]->created_at = date('Y M d', strtotime($data->created_at));
        $temp[$i]->detail     = maybe_unserialize($data->detail);
        $temp[$i]->total      = sejolisa_price_format($temp[$i]->detail['total']);
        $i++;
    endforeach;

    $response['confirmations'] = $temp;

    return $response;
}

/**
 * Get single confirmation data
 * @since   1.1.6
 * @param   integer $id  data ID
 * @return  array
 */
function sejolisa_get_confirmation($id) {

    return SejoliSA\Model\Confirmation::reset()
                    ->set_id($id)
                    ->single()
                    ->respond();
}
