<?php
/**
 * Update acquisition view value
 * @since   1.0.0
 * @param   array   $args   Array of arguments
 * @param   string  $type   Data type that will be updated, can be view, lead and sales
 * @return  array
 */
function sejolisa_update_acquisition_value(array $args, $type = 'view') {

    $args = wp_parse_args($args, [
        'product_id'    => NULL,
        'affiliate_id'  => NULL,
        'source'        => NULL,
        'media'         => NULL
    ]);

    switch($type) :

        case 'sales'    :
            $update_action  = 'update_sales';
            break;

        case 'lead'    :
            $update_action  = 'update_lead';
            break;

        default    :
            $update_action  = 'update_view';
            break;

    endswitch;


    $response   =  SejoliSA\Model\Acquisition::reset()
                        ->set_affiliate($args['affiliate_id'])
                        ->set_product_id($args['product_id'])
                        ->set_source($args['source'])
                        ->set_media($args['media'])
                        ->$update_action()
                        ->respond();

    // do_action('sejoli/log/write', 'acquisition data', $args);
    // do_action('sejoli/log/write', 'acquisition-'.$update_action, $response);

    return wp_parse_args($response,[
        'valid'    => false,
        'data'     => NULL,
        'messages' => NULL
    ]);
}

/**
 * Add order acquisition data
 * @since   1.0.0
 * @param   array   $args   Array of arguments
 * @return  array   Response data
 */
function sejolisa_add_order_acquisition(array $args) {

    $args = wp_parse_args($args,[
        'order_id' => NULL,
        'source'   => NULL,
        'media'    => NULL
    ]);

    $response   =  SejoliSA\Model\Acquisition::reset()
                        ->set_order_id($args['order_id'])
                        ->set_source($args['source'])
                        ->set_media($args['media'])
                        ->add_order()
                        ->respond();

    return wp_parse_args($response, [
        'valid'     => false,
        'messages'  => NULL
    ]);
}

/**
 * Get acquisition data
 * @since   1.0.0
 * @param   array   $args   Array of arguments
 * @param  array    $table  Array of table
 * @return  array   Response data
 */
function sejolisa_get_acquisition_data(array $args, $table = array()) {

    $respond = array();
    $args = wp_parse_args($args,[
        'product_id'   => NULL,
        'affiliate_id' => NULL,
        'source'       => NULL,
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => NULL
    ]);

    if(isset($args['date-range']) && !empty($args['date-range'])) :
        unset($args['date-range']);
    endif;


    $query = SejoliSA\Model\Acquisition::reset()
                ->set_filter_from_array($args)
                ->set_data_start($table['start']);

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query = $query->set_filter('created_at', $start.' 00:00:00', '>=')
                    ->set_filter('created_at', $end.' 23:59:59', '<=');
    endif;

    if(0 < $table['length']) :
        $query->set_data_length($table['length']);
    endif;

    if(!is_null($table['order']) && is_array($table['order'])) :
        foreach($table['order'] as $order) :
            $query->set_data_order($order['column'], $order['sort']);
        endforeach;
    endif;

    $statistic_data = $query->get()->respond();
    $order_data     = $query->get_total_order()->respond();

    if( false !== boolval($statistic_data['valid']) ) :

        $platforms = sejolisa_get_acquisition_platforms();
        
        $respond['valid'] = true;
        if(isset($respond['acquisitions'])){
            $respond['acquisitions'] = [];
        }

        foreach($statistic_data['acquisitions'] as $_data) :
            $respond['acquisitions'][$_data->source] = [
                'source' => $_data->source,
                'label'  => (array_key_exists($_data->source, $platforms)) ? $platforms[$_data->source] : $_data->source,
                'view'   => $_data->total_view,
                'lead'   => $_data->total_lead,
                'sales'  => $_data->total_sales,
                'value'  => 0
            ];
        endforeach;

        if( false !== boolval($order_data['valid']) ) :

            foreach($order_data['acquisitions'] as $_data) :
                $respond['acquisitions'][$_data->source]['value']   = sejolisa_price_format($_data->total_order);
            endforeach;

        endif;
    endif;

    return wp_parse_args($respond,[
        'valid'        => false,
        'acquisitions' => NULL,
        'messages'     => []
    ]);

}

/**
 * Get acquisition member data
 * @since   1.0.0
 * @param   array   $args   Array of arguments
 * @param  array    $table  Array of table
 * @return  array   Response data
 */
function sejolisa_get_acquisition_member_data(array $args, $table = array()) {

    $respond = [];

    $args = wp_parse_args($args,[
        'product_id'   => NULL,
        'source'       => NULL,
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => NULL
    ]);

    if(isset($args['date-range']) && !empty($args['date-range'])) :
        unset($args['date-range']);
    endif;


    $query = SejoliSA\Model\Acquisition::reset()
                ->set_filter_from_array($args)
                ->set_data_start($table['start'])
                ->set_filter('affiliate_id', get_current_user_id());

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query = $query->set_filter('created_at', $start.' 00:00:00', '>=')
                    ->set_filter('created_at', $end.' 23:59:59', '<=');
    endif;

    if(0 < $table['length']) :
        $query->set_data_length($table['length']);
    endif;

    if(!is_null($table['order']) && is_array($table['order'])) :
        foreach($table['order'] as $order) :
            $query->set_data_order($order['column'], $order['sort']);
        endforeach;
    endif;

    $statistic_data = $query->get_member()->respond();
    $order_data     = $query->get_total_member_order()->respond();

    if( false !== boolval($statistic_data['valid']) ) :

        $platforms = sejolisa_get_acquisition_platforms();

        $respond['valid'] = true;
        if(isset($respond['acquisitions'])){
            $respond['acquisitions'] = [];
        }

        foreach($statistic_data['acquisitions'] as $_data) :
            $respond['acquisitions'][$_data->source][$_data->media] = [
                'source' => $_data->source,
                'media'  => $_data->media,
                'label'  => (array_key_exists($_data->source, $platforms)) ? $platforms[$_data->source] : $_data->source,
                'view'   => $_data->total_view,
                'lead'   => $_data->total_lead,
                'sales'  => $_data->total_sales,
                'value'  => 0
            ];
        endforeach;

        if( false !== boolval($order_data['valid']) ) :

            foreach($order_data['acquisitions'] as $_data) :
                $respond['acquisitions'][$_data->source][$_data->media]['value']     = sejolisa_price_format($_data->total_order);
                $respond['acquisitions'][$_data->source][$_data->media]['tmp_value'] = $_data->total_order;
            endforeach;

        endif;
    endif;

    return wp_parse_args($respond,[
        'valid'        => false,
        'acquisitions' => NULL,
        'messages'     => []
    ]);

}

/**
 * Get acquisition platforms
 * @since   1.0.0
 * @return  array
 */
function sejolisa_get_acquisition_platforms() {
    $platforms      = [];
    $data_platforms = explode(PHP_EOL, sejolisa_carbon_get_theme_option('sejoli_acquisition_platform'));

    foreach($data_platforms as $_data) :
        list($key, $label)  = explode(';', $_data);
        $platforms[$key]    = $label;
    endforeach;

    return $platforms;
}
