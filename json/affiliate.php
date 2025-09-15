<?php
namespace SejoliSA\JSON;

Class Affiliate extends \SejoliSA\JSON
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
     * Get bonus affiliate content
     * Hooked via wp_ajax_sejoli-affiliate-get-bonus-content, priority 1
     * @since   1.0,0
     * @return  json
     */
    public function get_bonus_content() {

        $data = [];

        $post_data = wp_parse_args($_GET,[
            'nonce'      => NULL,
            'product_id' => NULL
        ]);

        if(
            (
                wp_verify_nonce( $post_data['nonce'], 'sejoli-affiliate-bonus-content') ||
                class_exists('WP_CLI')
            ) && !empty($post_data['product_id'])
        ) :
            $affiliate_id = get_current_user_id();

            $data = [
                'affiliate_id' => $affiliate_id,
                'product_id'   => $post_data['product_id'],
                'content'      => sejolisa_get_affiliate_bonus($affiliate_id, $post_data['product_id'])
            ];

        endif;


        if(class_exists('WP_CLI')) :
            __debug($data);
        else :
            wp_send_json($data);
        endif;
        exit;
    }

    /**
     * Update bonus affiliate content
     * Hooked via wp_ajax_sejoli-affiliate-update-bonus-content, priority 1
     * @since   1.0,0
     * @return  json
     */
    public function update_bonus_content() {

        $data = [
            'valid'    => false,
            'messages' => []
        ];

        $post_data = wp_parse_args($_POST,[
            'nonce'      => NULL,
            'product_id' => NULL,
            'content'    => NULL
        ]);

        if(
            (
                wp_verify_nonce( $post_data['nonce'], 'sejoli-affiliate-bonus-content') ||
                class_exists('WP_CLI')
            ) && !empty($post_data['product_id'])
        ) :
            $affiliate_id = get_current_user_id();

            if(!empty($post_data['content'])) :

                update_user_meta($affiliate_id, '_sejoli_bonus_affiliate_'.$post_data['product_id'], $post_data['content']);

                $data = [
                    'value' => true,
                    'messages' => [
                        __('Bonus konten berhasil diupdate', 'sejoli')
                    ]
                ];
            else :
                $data['messages'][] = __('Konten bonus wajib diisi', 'sejoli');
            endif;

        endif;


        if(class_exists('WP_CLI')) :
            __debug($data);
        else :
            wp_send_json($data);
        endif;
        exit;
    }

    /**
     * Get affiliate facebook pixel
     * Hooked via wp_ajax_sejoli-affiliate-get-facebook-pixel, priority 1
     * @since   1.0,0
     * @return  json
     */
    public function get_facebook_pixel() {

        $data = [];

        $post_data = wp_parse_args($_GET,[
            'nonce'      => NULL,
            'product_id' => NULL
        ]);

        if(
            (
                wp_verify_nonce( $post_data['nonce'], 'sejoli-affiliate-get-facebook-pixel') ||
                class_exists('WP_CLI')
            ) && !empty($post_data['product_id'])
        ) :
            $affiliate_id = get_current_user_id();

            $id_pixel = get_user_meta($affiliate_id, '_sejoli_id_pixel_affiliate_'.$post_data['product_id'],true);
            $links = sejolisa_get_product_fb_pixel_links($post_data['product_id']);

            $data = [
                'affiliate_id' => $affiliate_id,
                'product_id'   => $post_data['product_id'],
                'id_pixel'     => $id_pixel,
                'links'        => $links,
                'events'       => [
                ]
            ];

        endif;


        if(class_exists('WP_CLI')) :
            __debug($data);
        else :
            wp_send_json($data);
        endif;
        exit;
    }

    /**
     * Update affiliate facebook pixel
     * Hooked via wp_ajax_sejoli-affiliate-update-facebook-pixel, priority 1
     * @since   1.0,0
     * @return  json
     */
    public function update_facebook_pixel() {

        $data = [
            'valid'    => false,
            'messages' => []
        ];

        $post_data = wp_parse_args($_POST,[
            'nonce'      => NULL,
            'product_id' => NULL,
            'id_pixel'   => NULL
        ]);

        if(
            (
                wp_verify_nonce( $post_data['nonce'], 'sejoli-affiliate-update-facebook-pixel') ||
                class_exists('WP_CLI')
            ) && !empty($post_data['product_id'])
        ) :
            $affiliate_id = get_current_user_id();

            if(!empty($post_data['id_pixel'])) :

                update_user_meta($affiliate_id, '_sejoli_id_pixel_affiliate_'.$post_data['product_id'], $post_data['id_pixel']);

                $data = [
                    'value' => true,
                    'messages' => [
                        __('ID Pixel berhasil diupdate', 'sejoli')
                    ]
                ];
            else :
                $data['messages'][] = __('ID Pixel wajib diisi', 'sejoli');
            endif;

        endif;

        if(class_exists('WP_CLI')) :
            __debug($data);
        else :
            wp_send_json($data);
        endif;
        exit;
    }

    /**
     * Upload commission proof file to wordpress media
     * @since   1.0.0
     * @param   array           $upload [description]
     * @return  string|false    Path of file if upload success
     */
    protected function upload_commission_proof($upload) {

        $return = false;

        if(0 !== intval($upload['error'])) :
            return $return;
        endif;

        $file   = wp_upload_bits($upload['name'], null, file_get_contents($upload['tmp_name']));

        if(FALSE === $file['error']) :

            $type = '';

            if (!empty($file['type'])) :
                $type = $file['type'];
            else :
                $mime = wp_check_filetype($file['file']);
                if ($mime) :
                    $type = $mime['type'];
                endif;
            endif;

            $attachment = array(
                'post_title'     => basename($file['file']),
                'post_content'   => '',
                'post_type'      => 'attachment',
                'post_mime_type' => $type,
                'guid'           => $file['url']
            );

            $id = wp_insert_attachment($attachment, $file['file']);

            wp_update_attachment_metadata(
                $id,
                wp_generate_attachment_metadata(
                    $id,
                    $file['file']
                )
            );

            return $file['file'];
        endif;
    }

    /**
     * Update commission status
     * Hooked via action wp_ajax_sejoli-confirm_commission_transfer, priority 1
     * @since   1.0.0
     * @return  void
     */
    public function confirm_commission_transfer() {

        $response = [
            'valid'    => false,
            'messages' => []
        ];

        if(
            isset($_POST['sejoli-nonce']) &&
            wp_verify_nonce($_POST['sejoli-nonce'], 'sejoli-confirm-commission-transfer')
        ) :

            $files       = array();
            $commissions = explode(',', $_POST['commission_ids']);

            if(isset($_FILES['commission']) && 0 < count($_FILES['commission'])) :
                foreach($_FILES['commission'] as $key => $_file) :
                    foreach($_file as $id => $value) :

                        if(!isset($files[$id])) :
                            $files[$id] = array();
                        endif;

                        $files[$id][$key] = $value;

                    endforeach;
                endforeach;
            endif;

            $affiliates = sejolisa_get_all_unpaid_commissions($commissions);

            foreach($affiliates['commissions'] as $_affiliate ) :

                $affiliate_id = intval($_affiliate->affiliate_id);
                $affiliate    = sejolisa_get_user($affiliate_id);

                $commission_data = array(
                    'id'              => $affiliate_id,
                    'commission'      => sejolisa_price_format($_affiliate->total_commission),
                    'affiliate-name'  => $_affiliate->affiliate_name,
                    'affiliate-email' => $_affiliate->affiliate_email,
                    'affiliate-phone' => $affiliate->meta->phone,
                    'attachments'     => array()
                );

                if(isset($files[$affiliate_id])) :
                    $file = $this->upload_commission_proof($files[$affiliate_id]);
                    if(false !== $file) :
                        $commission_data['attachments'][] = $file;
                    endif;
                endif;

                do_action('sejoli/commission/set-status/paid', $commission_data);

                $response['messages'][]['message'] = sprintf(__('Notifikasi pembayaran komisi untuk %s sudah dikirimkan', 'sejoli'), $commission_data['affiliate-name']);

            endforeach;

            sejolisa_update_commission_paid_status(array(
                'ID'          => $commissions,
                'paid_status' => true
            ));

            $response['valid'] = true;


        endif;

        wp_send_json($response);
    }

    /**
     * Update single affiliate commission status
     * Hooked via action wp_ajax_sejoli-pay-single-affiliate-commission, priority 1
     * @since   1.0.0
     * @return  void
     */
    public function confirm_single_commission_transfer() {

        $response = [
            'valid'    => false,
            'messages' => []
        ];

        $post_data = wp_parse_args($_POST,[
            'sejoli-nonce'     => '',
            'affiliate_id'     => 0,
            'total_commission' => '',
            'current_time'     => current_time( 'mysql' ),
            'date_range'       => '',
        ]);


        if(
            isset($post_data['sejoli-nonce']) &&
            wp_verify_nonce($post_data['sejoli-nonce'], 'sejoli-pay-single-affiliate-commission')
        ) :

            $affiliate_id = intval($post_data['affiliate_id']);
            $affiliate    = sejolisa_get_user($post_data['affiliate_id']);

            if(is_a($affiliate, 'WP_User')) :

                $commission_data = array(
                    'id'              => $affiliate_id,
                    'commission'      => $post_data['total_commission'],
                    'affiliate-name'  => $affiliate->display_name,
                    'affiliate-email' => $affiliate->user_email,
                    'affiliate-phone' => $affiliate->meta->phone,
                    'attachments'     => array()
                );

                if(isset($_FILES['proof'])) :
                    $file = $this->upload_commission_proof($_FILES['proof']);
                    if(false !== $file) :
                        $commission_data['attachments'][] = $file;
                    endif;
                endif;

                sejolisa_update_single_affiliate_commission_paid_status(array(
                    'affiliate_id'  => $affiliate_id,
                    'paid_status'   => true,
                    'current_time'  => $post_data['current_time'],
                    'date_range'    => $post_data['date_range'],
                ));

                do_action('sejoli/commission/set-status/paid', $commission_data);

                $response['valid'] = true;
                $response['messages'][]['message'] = __('Status komisi sudah diupdate ke TELAH DIBAYAR', 'sejoli');

            else :
                $response['messages'][]['message'] = __('Affilisi tidak valid', 'sejoli');
            endif;

        endif;

        wp_send_json($response);
    }
}
