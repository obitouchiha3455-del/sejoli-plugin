<?php 

/**
 * Get current user group ID
 * @since   1.0.0
 * @param   integer         $user_id
 * @return  false|array
 */
function sejolisa_get_current_user_group($user_id = 0) {

    $user_id       = ( 0 === $user_id ) ? get_current_user_id() : $user_id;
    $user_group_id = intval(sejolisa_carbon_get_user_meta($user_id, 'user_group'));

    if( 0 === $user_group_id ) :
        return false;
    endif;

    return $user_group_id;

}

/**
 * Get current user group data based on user ID
 * @since   1.0.0
 * @param   integer         $user_id
 * @return  false|array
 */
function sejolisa_get_current_user_group_data($user_id = 0) {

    $user_id       = ( 0 === $user_id ) ? get_current_user_id() : $user_id;
    $user_group_id = intval(sejolisa_carbon_get_user_meta($user_id, 'user_group'));

    if( 0 === $user_group_id ) :
        return false;
    endif;

    return sejolisa_get_user_group_data($user_group_id);
}


/**
 * Get user group data
 * @since   1.0.0
 * @param   integer         $user_group_id
 * @return  false|stdClass
 */
function sejolisa_get_user_group_data($user_group_id = 0) {

    $group = get_post($user_group_id);

    if(
        is_a($group, 'WP_Post') &&
        SEJOLI_USER_GROUP_CPT === $group->post_type
    ) :

        $group_data = new stdClass;

        $group_data->id          = $group->ID;
        $group_data->name        = $group->post_title;
        $group_data->commissions = array();
        $group_data->per_product = array();

        $group_data->cashback = array(
            'enabled'    => boolval( sejolisa_carbon_get_post_meta( $group->ID, 'group_cashback_enable') ),
            'value'      => floatval( sejolisa_carbon_get_post_meta( $group->ID, 'group_cashback_value') ),
            'type'       => sejolisa_carbon_get_post_meta( $group->ID, 'group_cashback_type' ),
            'refundable' => boolval( sejolisa_carbon_get_post_meta( $group->ID, 'group_cashback_refundable' ) )
        );

        $group_data->discount = array(
            'enabled'   => boolval( sejolisa_carbon_get_post_meta( $group->ID, 'group_discount_enable') ),
            'value'     => floatval( sejolisa_carbon_get_post_meta( $group->ID, 'group_discount_price') ),
            'type'      => sejolisa_carbon_get_post_meta( $group->ID, 'group_discount_price_type' )
        );

        $commissions = sejolisa_carbon_get_post_meta( $group->ID, 'group_commissions' );

        foreach( (array) $commissions as $tier => $setup ) :

            $group_data->commissions[$tier] = array(
                'value' => floatval( $setup['number'] ),
                'type'  => $setup['type']
            );

        endforeach;

        $products = sejolisa_carbon_get_post_meta( $group->ID, 'group_setup_per_product' );

        foreach( (array) $products as $product => $setup ) :

            $configuration = array();

            $configuration['cashback'] = array(
                'enabled'    => boolval( $setup[ 'cashback_enable'] ),
                'value'      => floatval( $setup[ 'cashback_value'] ),
                'type'       => $setup[ 'cashback_type' ],
                'refundable' => boolval( $setup[ 'cashback_refundable'] )
            );

            $configuration['discount'] = array(
                'enabled'   => boolval( $setup[ 'discount_enable'] ),
                'value'     => floatval( $setup[ 'discount_price'] ),
                'type'      => $setup[ 'discount_price_type' ]
            );

            $configuration['commissions'] = array();

            $commissions = $setup[ 'commission' ];

            foreach( (array) $commissions as $tier => $commission ) :

                $configuration['commissions'][$tier] = array(
                    'value' => floatval( $commission['number'] ),
                    'type'  => $commission['type']
                );

            endforeach;

            $group_data->per_product[$setup['product']] = $configuration;

        endforeach;

        return $group_data;

    endif;

    return false;
}


