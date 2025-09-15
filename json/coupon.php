<?php
namespace SejoliSA\JSON;

Class Coupon extends \SejoliSA\JSON
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
     * Hooked via action wp_ajax_sejoli-coupon-table, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

		$table = $this->set_table_args($_POST);

		$data    = [];

        if(isset($_POST['backend']) && current_user_can('manage_sejoli_commissions')) :

        else :
            $table['filter']['user_id'] = get_current_user_id();
        endif;

		$respond = sejolisa_get_coupons($table['filter'], $table);

		if(false !== $respond['valid']) :

			$data = [];

            foreach( $respond['coupons'] as $coupon) :

                if(
                    !isset($_POST['backend']) &&
                    is_array($coupon['rule']) &&
                    array_key_exists('use_by_affiliate', $coupon['rule']) &&
                    false === $coupon['rule']['use_by_affiliate']
                ) :
                    continue;
                endif;

                if(!empty($coupon['parent_discount'])) :
                    $coupon['discount']   = unserialize($coupon['parent_discount']);
                    $coupon['limit_date'] = $coupon['parent_limit_date'];
                    $coupon['limit_use']  = $coupon['parent_limit_use'];
                endif;

                $discount = '';

                if('fixed' === $coupon['discount']['type']) :
                    $discount .= sejolisa_price_format($coupon['discount']['value']);

                    $discount .= ('per_item' === $coupon['discount']['usage']) ?
                                    ' (' . __('per item', 'sejoli') . ')' :
                                    ' (' . __('total', 'sejoli') . ')';
                else :

                    $discount .= $coupon['discount']['value'] . '%';

                endif;

                $data[] = [
                    'ID'          => $coupon['ID'],
                    'code'        => $coupon['code'],
                    'limit'       => [
                        'date'  => $coupon['limit_date'],
                        'use'   => $coupon['limit_use']
                    ],
                    'username'       => $coupon['owner_name'],
                    'discount'       => $discount,
                    'parent_code'    => safe_strtoupper($coupon['parent_code']),
                    'usage'          => $coupon['usage'],
                    'status'         => $coupon['status'],
                    'affiliate_use'  => isset($coupon['rule']['use_by_affiliate']) ? boolval($coupon['rule']['use_by_affiliate']) : null,
                    'free_shipping'  => isset($coupon['discount']['free_shipping']) ? boolval($coupon['discount']['free_shipping']) : false,
                    'renewal_coupon' => isset($coupon['rule']['renewal_coupon']) ? boolval($coupon['rule']['renewal_coupon']) : null,
                ];

            endforeach;

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
     * Check the coupon availability
     * Hooked via action wp_ajax_sejoli-coupon-check, priority 999
     * @return  1.0.0
     * @return  json
     */
    public function check_coupon_availability() {
        $coupon_code = $_GET['code'];
        $respond     = sejolisa_get_coupon_by_code($coupon_code);

        echo wp_send_json($respond);
        exit;
    }

    /**
     * Render product IDS from carbon field association
     * @since   1.0.0
     * @param   mixed  $data
     * @return  array
     */
    protected function render_product_id_from_association($data) {

        $post_ids = [];

        foreach((array) $data as $_data) :
            $post_ids[] = $_data['id'];
        endforeach;

        return $post_ids;

    }

    /**
     * Create affiliate coupon
     * Hooked via action wp_ajax_sejoli-create-coupon, priority 1
     * @since   1.0.0
     * @return json
     */
    public function create_affiliate_coupon() {

        $response = [
            'valid'    => false,
            'messages' => []
        ];
        $valid    = false;

        if(
            (
                class_exists('WP_CLI') ||
                wp_verify_nonce($_POST['nonce'], 'sejoli-create-affiliate-coupon')
            ) &&
            (
                current_user_can('manage_sejoli_own_coupons') ||
                current_user_can('manage_sejoli_coupons')
            )
        ) :

            $args               = $_POST;
            $args['user_id']    = get_current_user_id();
            $response_by_id     = sejolisa_get_coupon_by_id($args['coupon_parent_id']);
            $response_by_code   = sejolisa_get_coupon_by_code($args['code']);
            $renewal_coupon     = isset($response_by_id['coupon']['rule']['renewal_coupon']) ? $response_by_id['coupon']['rule']['renewal_coupon'] : null;
            $args['limit_use']  = sejolisa_carbon_get_post_meta($args['coupon_parent_id'], 'limit_use');
            $args['limit_date'] = sejolisa_carbon_get_post_meta($args['coupon_parent_id'], 'limit_date');
            $args['rule']       = [
                'apply_only'             => $this->render_product_id_from_association(sejolisa_carbon_get_post_meta($args['coupon_parent_id'], 'apply_only_on')),
                'cant_apply'             => $this->render_product_id_from_association(sejolisa_carbon_get_post_meta($args['coupon_parent_id'], 'cant_apply_only_on')),
                'max_discount'           => floatval(sejolisa_carbon_get_post_meta($args['coupon_parent_id'], 'max_discount_number')),
                'use_by_affiliate'       => sejolisa_carbon_get_post_meta($args['coupon_parent_id'], 'use_by_affiliate'),
                'limit_affiliate_coupon' => absint(sejolisa_carbon_get_post_meta($args['coupon_parent_id'], 'limit_affiliate_coupon')),
                'renewal_coupon'         => $renewal_coupon
            ];


            if(false !== $response_by_id['valid']) :

                // Check parent coupon is valid
                if(
                    0 !== intval($response_by_id['coupon']['coupon_parent_id']) ||
                    'active' !== $response_by_id['coupon']['status']
                ) :
                    $response = [
                        'valid' => false,
                        'messages' => [
                            'error' => [
                                __('Kupon asli tidak bisa digunakan', 'sejoli')
                            ]
                        ]
                    ];
                else :

                    // Check if coupon code exists
                    if(false !== $response_by_code['valid']) :
                        $response = [
                            'valid' => false,
                            'messages' => [
                                'error' => [
                                    sprintf( __('Kupon %s sudah digunakan. Ganti dengan yang lain', 'sejoli'), $args['code'] )
                                ]
                            ]
                        ];
                    else :
                        $args['limit']  = intval($response_by_id['coupon']['rule']['limit_affiliate_coupon']);
                        $coupon_affiliate_available = sejolisa_is_affiliate_coupon_available($args);

                        if(false != $coupon_affiliate_available) :
                            $response = sejolisa_create_affiliate_coupon($args);
                        else :
                            $response = [
                                'valid' => false,
                                'messages'  => array(
                                    'error' => array(
                                        __('Jumlah kupon yang anda buat sudah mencapai batas kepemilikan kupon per affiliasi', 'sejoli')
                                    )
                                )
                            ];
                        endif;
                    endif;
                endif;
            endif;
            $valid = $response['valid'];

        else :
            $response['messages']['error'] = __('Anda tidak berhak mengakses fungsi ini', 'sejoli');
        endif;;

        if($valid) :
            wp_send_json_success([
                'coupon'   => $response['coupon']
            ]);
        else :
            wp_send_json_error([
                'messages' => $response['messages']['error']
            ]);
        endif;
    }

    /**
     * List all available parent coupons
     * Hooked via wp_ajax_sejoli-list-coupons, priority 1
     * @since  1.0.0
     * @return void
     */
    public function list_parent_coupons() {

        $data    = [];
        $options = [];

        if(
            wp_verify_nonce($_POST['nonce'], 'sejoli-list-parent-coupons') ||
            class_exists('WP_CLI')
        ) :
            $response = sejolisa_get_coupons([
                'coupon_parent_id' => '0',
                'status'           => 'active'
            ]);


            if( false !== $response['valid'] ) :

                $coupons = [];

                foreach( $response['coupons'] as $_coupon ) :

                    if(isset($_coupon['rule']['use_by_affiliate']) && false !== $_coupon['rule']['use_by_affiliate']) :
                        
                        if ( (safe_strtotime($_coupon['limit_date']) > strtotime(date('Y-m-d-H-i-s')))
                            || $_coupon['limit_date'] === null
                        ) {
                            $coupons[] = $_coupon;
                            $options[] = [
                                'id'   => $_coupon['ID'],
                                'text' => $_coupon['code']
                            ];
                        }

                    endif;

                endforeach;

                $data = [
                    'valid'   => true,
                    'coupons' => $coupons
                ];

            endif;

        endif;

        if(class_exists('WP_CLI')) :
            __debug(wp_parse_args($data,[
                'valid'           => false,
                'coupons'         => NULL
            ]));
        else :
            wp_send_json(wp_parse_args($data,[
                'results' => $options,
            ]));
        endif;
        exit;
    }

    /**
     * Update multiple coupons
     * Hooked via action wp_ajax_sejoli-coupon-update, priority 1
     * @return  void
     */
    public function update_coupons() {

        $post_data = wp_parse_args($_POST,[
            'coupons'  => NULL,
            'status'    => NULL,
            'nonce'     => NULL
        ]);

        if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-coupon-update') &&
            is_array($post_data['coupons'])
        ) :
            if(in_array($post_data['status'], ['active', 'pending'])) :
                sejolisa_update_multiple_coupons_status($post_data['status'], $post_data['coupons']);
            endif;
        endif;
        exit;
    }

    /**
     * Delete multiple coupons
     * Hooked via action wp_ajax_sejoli-coupon-delete, priority 1
     * @since   1.1.10
     * @return  json
     */
    public function delete_coupons() {

        $post_data = wp_parse_args($_POST, [
            'coupons'
        ]);

        if(
            wp_verify_nonce($post_data['nonce'], 'sejoli-coupon-delete') &&
            is_array($post_data['coupons']) &&
            current_user_can('manage_sejoli_coupons')
        ) :

            $response = sejolisa_delete_coupons($post_data['coupons']);
            wp_send_json($response);
        endif;
        exit;
    }
}
