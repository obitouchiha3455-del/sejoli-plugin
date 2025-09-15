<?php
/**
 * Get shipment data
 * @since   1.0.0
 * @param   array   $shipment_data
 * @return  array
 */
function sejolisa_get_shipment_cost(array $shipment_data) {
    $args = wp_parse_args($shipment_data,[
        'destination_id' => NULL,
        'origin_id'      => NULL,
        'weight'         => NULL,
        'courier'        => NULL,
        'quantity'       => 1
    ]);

    $response = SejoliSA\Model\Shipment::reset()
                ->set_origin($args['origin_id'])
                ->set_destination($args['destination_id'])
                ->set_courier($args['courier'])
                ->set_weight($args['weight'])
                ->set_courier($args['courier'])
                ->set_quantity($args['quantity'])
                ->get_cost()
                ->respond();

    $response['messages']['info'][]  = sprintf( __('Total berat per produk : %s gram', 'sejoli'), $args['weight']);

    return wp_parse_args($response,[
        'valid'    => false,
        'shipment' => NULL,
        'messages' => []
    ]);
}

/**
 * Get subdistrict detail by its ID
 * @since   1.5.3
 * @param   integer     $subdistrict_id
 * @return  array|null
 */
function sejolise_get_subdistrict_detail( $subdistrict_id ) {

    global $sejolisa;

    if( 0 !== intval($subdistrict_id) ) :

        if( !isset($sejolisa['subdistricts'] ) || ! is_array($sejolisa['subdistricts'] ) ) :

            ob_start();
            require SEJOLISA_DIR . 'json/subdistrict.json';
            $json_data = ob_get_contents();
            ob_end_clean();

            $sejolisa['subdistricts'] = $subdistricts = json_decode($json_data, true);
        else :
            $subdistricts = $sejolisa['subdistricts'];
        endif;

        $key                 = array_search($subdistrict_id, array_column($subdistricts, 'subdistrict_id'));
        $current_subdistrict = $subdistricts[$key];

        return $current_subdistrict;

    endif;

    return 	NULL;
}
