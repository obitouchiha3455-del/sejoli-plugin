<?php
/**
 * Render default sejoli member area menu
 * @since   1.1.0
 * @since   1.5.3.1 Add option to disable some affiliate menu
 * @return  array
 */
function sejolisa_get_member_area_menu() {

    $value = [
        'dashboard' => [
            'link'  => site_url('member-area/'),
            'label' => __('Dashboard','sejoli'),
            'icon'  => 'tachometer alternate icon',
            'class' => 'item',
            'submenu' => [

            ]
        ],
        'affiliate' => [
            'link'    => 'javascript:void(0)',
            'label'   => __('Affiliasi','sejoli'),
            'icon'    => 'bullhorn icon',
            'class'   => 'item',
            'submenu' => [
                'commission' => [
                    'link' => site_url('member-area/affiliasi-komisi'),
                    'label' => __('Komisi','sejoli'),
                    'icon' => '',
                    'class' => 'item',
                    'submenu' => [

                    ]
                ],
                'link' => [
                    'link' => site_url('member-area/affiliasi-link'),
                    'label' => __('Link','sejoli'),
                    'icon' => '',
                    'class' => 'item',
                    'submenu' => [

                    ]
                ],
                'help' => [
                    'link' => site_url('member-area/affiliasi-bantuan'),
                    'label' => __('Marketing Kit','sejoli'),
                    'icon' => '',
                    'class' => 'item',
                    'submenu' => [

                    ]
                ],
                'coupon' => [
                    'link' => site_url('member-area/affiliasi-kupon'),
                    'label' => __('Kupon','sejoli'),
                    'icon' => '',
                    'class' => 'item',
                    'submenu' => [

                    ]
                ],
                'order' => [
                    'link' => site_url('member-area/affiliasi-order'),
                    'label' => __('Order','sejoli'),
                    'icon' => '',
                    'class' => 'item',
                    'submenu' => [

                    ]
                ],
                'bonus-editor' => [
                    'link' => site_url('member-area/affiliasi-bonus-editor'),
                    'label' => __('Bonus Editor','sejoli'),
                    'icon' => '',
                    'class' => 'item',
                    'submenu' => [

                    ]
                ],
                'facebook-pixel' => [
                    'link' => site_url('member-area/affiliasi-facebook-pixel'),
                    'label' => __('Facebook Pixel','sejoli'),
                    'icon' => '',
                    'class' => 'item',
                    'submenu' => [

                    ]
                ],
                // add menu for network tree
                'network-tree' => [
                    'link' => site_url('member-area/affiliasi-network'),
                    'label' => __('Jaringan Anda','sejoli'),
                    'icon' => '',
                    'class' => 'item',
                    'submenu' => [

                    ]
                ],
            ]
        ],
        'leaderboard' => [
            'link' => site_url('member-area/leaderboard'),
            'label' => __('Leaderboard','sejoli'),
            'icon' => 'trophy icon',
            'class' => 'item',
            'submenu' => [

            ]
        ],
        'user-order' => [
            'link' => site_url('member-area/order'),
            'label' => __('Order','sejoli'),
            'icon' => 'shopping cart icon',
            'class' => 'item',
            'submenu' => []
        ],
        'user-subscription' => [
            'link' => site_url('member-area/subscription'),
            'label' => __('Langganan','sejoli'),
            'icon' => 'stopwatch icon',
            'class' => 'item',
            'submenu' => []
        ],
        'download' => [
            'link' => site_url('member-area/akses/'),
            'label' => __('Akses','sejoli'),
            'icon' => 'download icon',
            'class' => 'item',
            'submenu' => [

            ]
        ],
        'lisensi' => [
            'link' => site_url('member-area/license/'),
            'label' => __('Lisensi','sejoli'),
            'icon' => 'key icon',
            'class' => 'item',
            'submenu' => [

            ]
        ],
        'profile' => [
            'link' => site_url('member-area/profile/'),
            'label' => __('Profile','sejoli'),
            'icon' => 'user icon',
            'class' => 'item',
            'submenu' => [

            ]
        ],
        'logout' => [
            'link' => wp_logout_url( site_url('member-area/login/') ),
            'label' => __('Logout','sejoli'),
            'icon' => 'sign-out icon',
            'class' => 'item',
            'submenu' => [

            ]
        ],
    ];

    if( true !== boolval( sejolisa_carbon_get_theme_option( 'sejoli_affiliate_tool_help' )) ) :
        unset($value['affiliate']['submenu']['help']);
    endif;

    if( true !== boolval( sejolisa_carbon_get_theme_option( 'sejoli_affiliate_tool_coupon' )) ) :
        unset($value['affiliate']['submenu']['coupon']);
    endif;

    if( true !== boolval( sejolisa_carbon_get_theme_option( 'sejoli_affiliate_tool_bonus' )) ) :
        unset($value['affiliate']['submenu']['bonus-editor']);
    endif;

    if( true !== boolval( sejolisa_carbon_get_theme_option( 'sejoli_affiliate_tool_fb_pixel' )) ) :
        unset($value['affiliate']['submenu']['facebook-pixel']);
    endif;

    // Check if affiliate network disabled
    if ( 0 === absint( sejolisa_carbon_get_theme_option( 'sejolisa_affiliate_network_limit') ) && 0 === absint( sejolisa_carbon_get_theme_option( 'sejolisa_affiliate_network_limit_upline') ) ) :
        unset($value['affiliate']['submenu']['network-tree']);
    endif;

    $menu = apply_filters('sejoli/member-area/menu', $value);

    return $menu;

}

/**
 * Get current member page
 * @since   1.1.7
 * @return  string
 */
function sejolisa_get_current_member_page() {
    global $wp_query;

    $current_page = false;

    if(
        isset($wp_query->query['member']) &&
        true === boolval($wp_query->query['member'])
    ) :
        return $wp_query->query['view'];
    endif;

    return $current_page;
}
