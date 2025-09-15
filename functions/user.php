<?php
global $sejoliCache;

/**
 * Get multiple user data
 * @since   1,0.0
 * @param   array  $multiple_user_ids [description]
 * @return  array
 */
function sejolisa_get_users(array $multiple_user_ids) {
    $user_data = [];

    $users = get_users([
        'include'   => $multiple_user_ids
    ]);

    foreach( $users as $user ) :

        $user_data[$user->ID] = $user;
        $user_data[$user->ID]->meta = new stdClass();
        $user_data[$user->ID]->meta = apply_filters('sejoli/user/meta-data', $user);

    endforeach;

    return $user_data;
}

/**
 * Get user data, user meta will be extended by hook
 * @since   1.0.0
 * @since   1.5.2    Change maximal strlen from 5 to 10
 * @param   mixed    $by_value accepted values are email, id and phone number
 * @return  mixed    WP_Post or false
 */
function sejolisa_get_user($by_value) {

    global $sejolisa;

    $user = false;
    $get_from_temp = false;

    if(is_email($by_value)) :

        $user = get_user_by('email', $by_value);

    elseif(0 < absint($by_value) && 10 > strlen($by_value)) :

        if(isset($sejolisa['users'][$by_value])) :
            $user          = $sejolisa['users'][$by_value];
            $get_from_temp = true;
        else :
            $user = get_user_by('id', $by_value);
        endif;

    elseif(!empty($by_value) && !is_email($by_value)) :
        $users = get_users([
            'number'     => 1,
            'meta_key'   => '_phone',
            'meta_value' => trim($by_value)
        ]);

        $user = (is_array($users) && 0 < count($users)) ? $users[0] : $user;
    endif;

    if(false === $get_from_temp && is_a($user, 'WP_User')) :
        $user->meta = new stdClass();
        $sejolisa['users'][$user->ID] = $user = apply_filters('sejoli/user/meta-data', $user);
    endif;

    return $user;
}

/**
 * Get upline list of an user
 * @param  integer $user_id [description]
 * @param  integer $limit
 * @param  string  $return
 * @return array   $users
 */
function sejolisa_user_get_uplines($user_id = 0,$limit = 0,$return = 'id')
{
    $user_id = intval($user_id);
    $user_id = (0 === $user_id) ? get_current_user_id() : $user_id;
    $limit   = intval(apply_filters('sejoli/affiliate/max-upline',$limit));

    $respond = SejoliSA\Model\AffiliateTree::reset()
                    ->set_user_id($user_id)
                    ->set_upline_tier($limit)
                    ->get_uplines()
                    ->respond();

    if(true === $respond['valid']) :
        return apply_filters('sejoli/user/uplines',$respond['uplines']);
    endif;

    return false;
}

/**
 * Get user uplines data for user page
 * hooked via filter sejoli/user/uplines
 * @param int $user_id
 * @param int $limit
 * @param string $return
 * @return void
 */
function sejolisa_admin_user_get_uplines($user_id = 0,$limit = 0,$return = 'id')
{
    $user_id = intval($user_id);
    $user_id = (0 === $user_id) ? get_current_user_id() : $user_id;
    $limit   = intval(apply_filters('sejoli/affiliate/max-upline',$limit));

    $respond = SejoliSA\Model\AffiliateTree::reset()
                    ->set_user_id($user_id)
                    ->set_upline_tier($limit)
                    ->get_user_uplines()
                    ->respond();

    if(true === $respond['valid']) :
        return apply_filters('sejoli/user/uplines',$respond['uplines']);
    endif;

    return false;
}

function sejolisa_admin_get_current_user_data($user_id){

    $user_id = intval($user_id);
    $user_id = (0 === $user_id) ? get_current_user_id() : $user_id;


    $parent_id = '';
    $has_parent = get_user_meta( $user_id, sejolisa_get_affiliate_key() , true );

    if($has_parent){
        $parent_id = $has_parent;
    }else{
        $parent_id = '#';
    }


    $user_info = get_userdata($user_id);
    $display_name = $user_info->display_name;
    $avatar_url   = get_avatar_url( $user_id );

    $data_current_user = array();

    $data = array(
            'id'       => $user_id,
            'parent'   => $parent_id,
            'text'     => '<span class="checked-user">'.$display_name.'</span>',
            'icon'     => $avatar_url,
            'a_attr'   => array(
                'data-ID'   => $user_id
            )
    );

    $data_current_user[] = $data;
    return apply_filters('sejoli/user/current',$data_current_user);
}

function sejolisa_admin_user_get_downlines($user_id = 0,$limit = 0,$return = 'id')
{
    $user_id = intval($user_id);
    $user_id = (0 === $user_id) ? get_current_user_id() : $user_id;
    $limit   = intval(apply_filters('sejoli/affiliate/max-downline',$limit));


    $respond = SejoliSA\Model\AffiliateTree::reset()
                    ->set_user_id($user_id)
                    ->set_downline_tier($limit)
                    ->get_user_downlines()
                    ->respond();

    if(true === $respond['valid']) :
        return apply_filters('sejoli/user/downlines',$respond['downlines']);
    endif;

    return false;

}

/**
 * Get downline list of an user
 * @param  integer $user_id [description]
 * @param  integer $limit
 * @param  string  $return
 * @return array   $users
 */
function sejolisa_user_get_downlines($user_id = 0,$limit = 0,$return = 'id')
{
    $user_id = intval($user_id);
    $user_id = (0 === $user_id) ? get_current_user_id() : $user_id;
    $limit   = intval(apply_filters('sejoli/affiliate/max-downline',$limit));


    $respond = SejoliSA\Model\AffiliateTree::reset()
                    ->set_user_id($user_id)
                    ->set_downline_tier($limit)
                    ->get_downlines()
                    ->respond();

    if(true === $respond['valid']) :
        return apply_filters('sejoli/user/downlines',$respond['downlines']);
    endif;

    return false;

}

/**
 * Get user_group data as array for select options
 * @since   1.3.3
 * @return  array
 */
function sejolisa_get_user_group_options() {

    global $sejolisa;

    if(
        !array_key_exists('user-groups', $sejolisa) ||
        0 === count($sejolisa['user-groups'])
    ) :

        $user_group_data = [];

        $user_groups = new WP_Query([
            'posts_per_page'         => -1,
            'post_status'            => 'publish',
            'post_type'              => SEJOLI_USER_GROUP_CPT,
            // 'meta_key'               => '_priority',
            // 'orderby'                => 'meta_value',
            'order'                  => 'ASC',
            'cache_results'          => false, // do not cache the result
            'update_post_meta_cache' => false, // do not cache the result
            'update_post_term_cache' => false, // do not cache the result
        ]);

        foreach( (array) $user_groups->posts as $user_group ) :

            $user_group_data[ $user_group->ID ] = sprintf(
                                                    __('%s ( LVL %s )', 'sejoli'),
                                                    $user_group->post_title,
                                                    get_post_meta($user_group->ID, '_priority', true)
                                                );

        endforeach;

        $sejolisa['user-groups'] = $user_group_data;

    endif;

    return $sejolisa['user-groups'];
}
/**
 * Get user group priority
 * @since   1.3.0
 * @return  array
 */
function sejolisa_get_user_group_priority() {

    global $sejolisa;

    if(
        !array_key_exists('user-group-priority', $sejolisa) ||
        0 < count($sejolisa['user-group-priority'])
    ) :

        $user_group_data = [];

        $user_groups = get_posts([
            'posts_per_page' => -1,
            'post_type'      => SEJOLI_USER_GROUP_CPT,
            'meta_key'       => '_priority',
            'orderby'        => 'meta_value',
            'order'          => 'ASC'
        ]);

        foreach( (array) $user_groups as $user_group ) :

            $user_group_data[ $user_group->ID ] = intval(sejolisa_carbon_get_post_meta($user_group->ID, 'priority'));

        endforeach;

        $sejolisa['user-group-priority'] = $user_group_data;

    endif;

    return $sejolisa['user-group-priority'];
}

/**
 * Update user group, set force true if there is no check group level
 * If force is false then current user's group will be checked based on $action_type
 * @since   1.3.0
 * @param   integer  $user_id
 * @param   integer  $group_id
 * @param   boolean  $force
 * @param   string   $action_type   Values are upgrade and downgrade
 * @return  true|WP_error
 */
function sejolisa_update_user_group($user_id, $group_id, $force = false, $action_type = 'upgrade') {

    $need_update = false;

    // Need checking first before update
    if(false === $force) :

        $current_user_group = intval(sejolisa_carbon_get_user_meta($user_id, 'user_group'));

        // User group is not set
        if(0 === $current_user_group) :
            $need_update = true;
        else :

            $group_priority          = sejolisa_get_user_group_priority();
            $current_group_priority  = (int) (array_key_exists($current_user_group, $group_priority)) ? $group_priority[$current_user_group] : 0;
            $selected_group_priority = (int) (array_key_exists($group_id, $group_priority)) ? $group_priority[$group_id] : 0;

            if(0 === $current_group_priority) :
                $need_update = true;
            elseif('upgrade' === $action_type && $current_group_priority < $selected_group_priority ) :
                $need_update = true;
            elseif('downgrade' === $action_type && $current_group_priority > $selected_group_priority ) :
                $need_update = true;
            endif;

        endif;

    // Force to update user group and ignore group level
    else :

        $need_update = true;

    endif;

    if(false !== $need_update) :

        update_user_meta($user_id, '_user_group', $group_id);
        return true;

    else :
        $error = new \WP_Error();
        $error->add($type, $message);

        return $error;
    endif;
}

/**
 * Check user group with product
 * @since  1.3.0
 * @param  WP_Post|integer  $product
 * @param  integer          $user_id
 * @return array
 */
function sejolisa_check_user_permission_by_product_group($product, $user_id = 0) {

    $allow_buy      = true;
    $disallowed     = NULL;
    $error          = new WP_Error();
    $user_id        = (0 === $user_id) ? get_current_user_id() : $user_id;
    $user           = sejolisa_get_user($user_id);
    $product        = (is_a($product, 'WP_Post')) ? $product : sejolisa_get_product($product);
    $group_options  = sejolisa_get_user_group_options();
    $group_priority = sejolisa_get_user_group_priority();

    if(
        false !== $product->group['buy_group'] &&
        !is_a($user, 'WP_User')
    ) :

        $error->add('disallowed', __('Not valid user', 'sejoli'));

        return array(
            'allow' => false,
            'error' => $error->get_error_messages()
        );

    elseif(
        false !== $product->group['buy_group'] &&
        property_exists($user->meta, 'group_id') &&
        !empty($user->meta->group_id) &&
        !in_array($user->meta->group_id, $product->group['buy_group_list'])
    ) :

        $allow_buy = false;
        $disallowed  = 'not-in-group';

        $error->add(
            'disallowed',
            sprintf(
                __('Product group id %s, user group id %s', 'sejoli'),
                implode(',', $product->group['buy_group_list']),
                $user->meta->group_id
            )
        );

    elseif(
        false !== $product->group['buy_group'] &&
        (
            !property_exists($user->meta, 'group_id') ||
            empty($user->meta->group_id)
        )
    ) :

        $allow_buy = false;
        $disallowed  = 'not-in-group';

        $error->add(
            'disallowed',
            sprintf(
                __('Product group id %s, user group id %s', 'sejoli'),
                implode(',', $product->group['buy_group_list']),
                $user->meta->group_id
            )
        );

    endif;

    return array(
        'allow' => $allow_buy,
        'error' => array(
            'type'    => $disallowed,
            'message' => $error->get_error_messages()
        )
    );
}

/**
 * Check if current user is able to updated to product user group
 * @since   1.3.0
 * @param   integer|WP_Post  $product
 * @param   integer          $user_id
 * @return  array
 */
function sejolisa_check_update_user_group_by_product($product, $user_id = 0) {

    $update         = false;
    $disallowed     = NULL;
    $error          = new WP_Error();
    $user_id        = (0 === $user_id) ? get_current_user_id() : $user_id;
    $user           = sejolisa_get_user($user_id);
    $product        = (is_a($product, 'WP_Post')) ? $product : sejolisa_get_product($product);
    $group_options  = sejolisa_get_user_group_options();
    $group_priority = sejolisa_get_user_group_priority();

    if( false !== $product->group['update_group'] ) :

        if(
            property_exists($user->meta, 'group_id') &&
            !empty($user->meta->group_id) &&

            false !== $product->group['update_group_condition'] &&
            !in_array($user->meta->group_id, $product->group['update_group_list'])
        ) :

            $update = false;
            $disallowed = array(
                'type'          => 'not-in-group',
                'product_group' => $product->group['update_group_list'],
                'user_group'    => $user->meta->group_id
            );

            $error->add(
                'disallowed',
                sprintf(
                    __('Product group id %s, user group id %s', 'sejoli'),
                    implode(',', $product->group['update_group_list']),
                    $user->meta->group_id
                )
            );

        else :

            $update = true;

        endif;
    endif;

    return array(
        'update'    => $update,
        'group'     => $product->group['update_group_to'],
        'error'     => array(
            'type'    => $disallowed,
            'message' => $error->get_error_messages()
        )
    );
}

/**
 * Set group commission detail
 * @since   1.3.0
 * @param   array $commissions
 * @return  array
 */
function sejolisa_set_group_commission(array $commissions) {

    $commission_data = array();

    if(is_array($commissions) && 0 < count($commissions)) :
        foreach($commissions as $i => $_detail) :
            $tier = $i + 1;
            $commission_data[$tier] = array(
                'tier'  => $tier,
                'fee'   => floatval($_detail['number']),
                'type'  => $_detail['type']
            );
        endforeach;
    endif;

    return $commission_data;
}

/**
 * Get user group detail
 * @since   1.3.0
 * @since   1.4.0       Add extra conditional to check if no group for current user
 * @param   integer     $group_id
 * @return  array|false
 */
function sejolisa_get_group_detail($group_id) {

    global $sejolisa;

    $group_data = false;

    if(
        !empty($group_id) &&
        array_key_exists('group', $sejolisa) &&
        array_key_exists($group_id, $sejolisa['group'])
    ) :
        $group_data = $sejolisa['group'][$group_id];
    endif;

    if(false === $group_data && !empty($group_id)) :
        $group = get_post($group_id);

        $group_data = array(
            'name'                => $group->post_title,
            'affiliate'           => boolval(sejolisa_carbon_get_post_meta($group_id, 'can_view_affiliate')),
            'priority'            => sejolisa_carbon_get_post_meta($group_id, 'priority'),
            'enable_discount'     => sejolisa_carbon_get_post_meta($group_id, 'group_discount_enable'),
            'discount_price'      => floatval(sejolisa_carbon_get_post_meta($group_id, 'group_discount_price')),
            'discount_price_type' => sejolisa_carbon_get_post_meta($group_id, 'group_discount_price_type'),
            'commissions'         => array(),
            'per_product'         => array()
        );

        $commissions = sejolisa_carbon_get_post_meta($group_id, 'group_commissions');
        $per_product = sejolisa_carbon_get_post_meta($group_id, 'group_setup_per_product');

        $group_data['commissions'] = sejolisa_set_group_commission($commissions);

        if(is_array($per_product) && 0 < count($per_product)) :

            foreach($per_product as $detail) :

                $group_data['per_product'][$detail['product']] = array(
                    'enable_discount'     => $detail['discount_enable'],
                    'discount_price'      => floatval($detail['discount_price']),
                    'discount_price_type' => $detail['discount_price_type'],
                    'commissions'         => array()
                );

                $per_product_commissions    = $detail['commission'];

                $group_data['per_product'][$detail['product']]['commissions'] = sejolisa_set_group_commission($per_product_commissions);

                //
                // I put comment block here since i'm afraid it will reduce the system performance
                //
                // $group_data['per_product'][$detail['product']] = apply_filters(
                //                                                     'sejoli/user-group/per-product/detail',
                //                                                     $group_data['per_product'][$detail['product']],
                //                                                     $detail
                //                                                  );

            endforeach;
        endif;

        $group_data = apply_filters('sejoli/user-group/detail', $group_data, $group_id, $commissions, $per_product);

    endif;

    return $group_data;
}

/**
 * Get group detail by user
 * @since   1.3.3
 * @param   integer $user_id
 * @return  array   Group detail
 */
function sejolisa_get_user_group($user_id = 0) {

    $user_id = ( 0 === $user_id ) ? get_current_user_id() : $user_id;
    $user_group_id = sejolisa_carbon_get_user_meta($user_id, 'user_group');

    return sejolisa_get_group_detail($user_group_id);
}

/**
 * Check if user can access affiliate page
 * @since   1.4.0
 * @return  boolean
 */
function sejolisa_check_user_can_access_affiliate_page() {

    if(!is_user_logged_in()) :
        return false;
    endif;

    $current_user_group  = sejolisa_get_user_group();
    $no_access_affiliate = boolval(sejolisa_carbon_get_theme_option('sejoli_no_access_affiliate'));

    // Need to be factored later
    if(
        // User has no group
        (
            false !== $no_access_affiliate &&
            false === $current_user_group
        )
            ||

        // User has group
        (
            is_array($current_user_group) &&
            array_key_exists('affiliate', $current_user_group) &&
            false === $current_user_group['affiliate']
        )
    ) :

        return false;

    endif;

    return true;
}

/**
 * Check if current user can access wp-admin or no
 * @since   1.5.0
 * @param   NULL|WP_User $user  Set null if need to check current user
 * @return  boolean             Return true if current user can access wp-admin
 */
function sejolisa_user_can_access_wp_admin( $user = NULL ) {

    if( !is_user_logged_in() ) :

        return false;

    elseif(
        current_user_can('manage_sejoli_orders') ||
        current_user_can('sejoli_user_can_access_admin')
    ) :

        return true;

    else :

        $user            = ( !is_a($user, 'WP_User') ) ? wp_get_current_user() : $user;
        $available_roles = (array) sejolisa_carbon_get_theme_option( 'sejoli_user_roles_can_access_wp-admin' );


        if( 0 < count( array_intersect( $user->roles, $available_roles ) ) ) :

            return true;

        endif;

    endif;

    return false;

}

/**
 * Check if user has already bought product
 * @since   1.5.2
 * @param   integer     $product_id
 * @param   integer     $user_id
 * @return  boolean
 */
function sejolisa_check_if_user_has_bought_product( $product_id, $user_id = 0 ) {

    $user_id = ( 0 === $user_id ) ? get_current_user_id() : $user_id;

    $respond = SejoliSA\Model\Order::reset()
                    ->set_product_id( $product_id )
                    ->set_user_id( $user_id )
                    ->get_user_bought()
                    ->respond();

    if( true === $respond['valid'] ) :
        return true;
    endif;

    return false;
}
