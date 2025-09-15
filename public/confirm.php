<?php

namespace SejoliSA\Front;

class Confirm
{

    /**
     * Register custom query variables
     * Hooked via filter query_vars, priority 100
     * @since   1.0.0
     * @param   array $vars
     * @return  array
     */
    public function custom_query_vars( $vars )
    {

        $vars[] = "sejolisa_page";
        return $vars;

    }

    /**
     * Display confirm page
     * Hooked via action parse_request, priority 999
     * @since   1.0.0   Initalization
     * @since   1.1.6   Hide products those are closed
     * @return  void
     */
    public function display_confirm_page()
    {

        if ( sejolisa_verify_page( 'confirm' ) && file_exists( SEJOLISA_DIR . 'template/checkout/confirm.php' ) ) :

            $products = [];

            $args = [
                'post_type' => 'sejoli-product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
            ];

            $query = new \WP_Query( $args );

            if ( $query->have_posts() ) :

                foreach ( $query->posts as $key => $product ) :

                    if(!sejolisa_is_product_closed($product->ID)) :
                        $products[$product->ID] = $product->post_title;
                    endif;

                endforeach;

            endif;

            include SEJOLISA_DIR . 'template/checkout/confirm.php';
            exit;
        endif;

    }

    /**
     * sejoli confirm by ajax
     * hooked via action parse_request
     * @since   1.5.1.1     Delete attachment file update email sent
     * @since   1.5.2       Remove attachment deleting, system will remove all confirmation attachment files with cron
     * @return json
     */
    public function confirm_by_ajax()
    {
        if ( sejoli_ajax_verify_nonce( 'sejoli-checkout-ajax-confirm' ) ) :

            $request = wp_parse_args( $_POST,[
                'invoice_id'         => NULL,
                'product'            => NULL,
                'nama_pengirim'      => NULL,
                'no_rekening_anda'   => NULL,
                'jumlah_nominal'     => NULL,
                'bank_asal_transfer' => NULL,
                'bank_transfer'      => NULL
            ]);

            $errors = [];

            if ( empty( $request['invoice_id'] ) ) :
                $errors[] = __('Invoice id wajib diisi');
            endif;

            if ( empty( $request['product'] ) ) :
                $errors[] = __('Produk wajib diisi');
            endif;

            if ( empty( $request['nama_pengirim'] ) ) :
                $errors[] = __('Nama pengirim wajib diisi');
            endif;

            if ( empty( $request['no_rekening_anda'] ) ) :
                $errors[] = __('Nomor rekening anda wajib diisi');
            endif;

            if ( empty( $request['jumlah_nominal'] ) ) :
                $errors[] = __('Jumlah nominal wajib diisi');
            endif;

            if ( empty( $request['bank_asal_transfer'] ) ) :
                $errors[] = __('Bank asal transfer wajib diisi');
            endif;

            if ( empty( $request['bank_transfer'] ) ) :
                $errors[] = __('Bank tujuan transfer wajib diisi');
            endif;

            // if ( empty( $request['keterangan'] ) ) :
            //     $errors[] = __('keterangan id wajib diisi');
            // endif;

            if ( $_FILES["bukti_transfer"]["error"] != 0 ) :
                $errors[] = __('Bukti Transfer wajib diisi');
            else:
                $maxsize    = wp_max_upload_size();
                $acceptable = array(
                    'application/pdf',
                    'image/jpeg',
                    'image/jpg',
                    'image/gif',
                    'image/png'
                );

                if ( ( $_FILES['bukti_transfer']['size'] >= $maxsize ) ||
                    ( $_FILES['bukti_transfer']["size"] == 0 ) ) :

                    $errors[] = 'File terlalu besar. File harus kurang dari '.number_format($maxsize / 1048576, 1).' MB.';

                endif;

                if ( ( !in_array($_FILES['bukti_transfer']['type'], $acceptable ) ) &&
                    ( !empty( $_FILES['bukti_transfer']["type"] ) ) ) :

                    $errors[] = 'Jenis file tidak valid. Hanya tipe PDF, JPG, GIF, dan PNG yang diterima.';

                endif;
            endif;

            if ( empty( $errors ) ) :

                $attachments = [];

                if ( ! function_exists( 'wp_handle_upload' ) ) :
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                endif;

                $uploadedfile = $_FILES['bukti_transfer'];

                $upload_overrides = array( 'test_form' => false );

                $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

                if ( $movefile && ! isset( $movefile['error'] ) ) :

                    $attachments[] = $movefile['file'];

                    $filename    = basename( $movefile['file'] );
                    $wp_filetype = wp_check_filetype($filename, null );
                	$attachment  = array(
                		'post_mime_type' => $wp_filetype['type'],
                		'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
                		'post_content'   => '',
                		'post_status'    => 'inherit'
                	);

                    $attachment_id = wp_insert_attachment( $attachment, $movefile['file'] );

                    if (!is_wp_error($attachment_id)) :

                		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                        
                		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $movefile['file'] );
                		wp_update_attachment_metadata( $attachment_id,  $attachment_data );

                    endif;

                endif;

                $to         = [];
                $recipients = sejolisa_carbon_get_theme_option('notification_confirmation_recipients');

                if(empty($recipients)) :
                    $to[] = get_option('admin_email');
                else :
                    $to = explode(',', $recipients);
                endif;

                $product = sejolisa_get_product( $request['product'] );

                $subject = sprintf(__('Konfirmasi Pembayaran dari %s untuk produk %s', 'sejoli'), get_bloginfo('name'), $product->post_title );

                $message = 'Nomor Invoice: '.$request['invoice_id'].'<br>';
                $message .= 'Produk: '.$product->post_title.'<br>';
                $message .= 'Nama pengirim: '.$request['nama_pengirim'].'<br>';
                $message .= 'Nomor rekening buyer: '.$request['no_rekening_anda'].'<br>';
                $message .= 'Bank asal transfer: '.$request['bank_asal_transfer'].'<br>';
                $message .= 'Jumlah nominal: '.sejolisa_price_format($request['jumlah_nominal']).'<br>';
                $message .= 'Bank tujuan transfer: '.$request['bank_transfer'].'<br>';
                $message .= 'Keterangan: '.$request['keterangan'].'<br>';

                $headers = array('Content-Type: text/html; charset=UTF-8');

                wp_mail( $to, $subject, $message, $headers, $attachments );

                $response = sejolisa_add_payment_confirmation_data(array(
                    'order_id'   => $request['invoice_id'],
                    'product_id' => $request['product'],
                    'user_id'    => get_current_user_id(),
                    'detail'     => array(
                        'sender'         => $request['nama_pengirim'],
                        'account_number' => $request['no_rekening_anda'],
                        'total'          => $request['jumlah_nominal'],
                        'bank_sender'    => $request['bank_asal_transfer'],
                        'bank_recipient' => $request['bank_transfer'],
                        'note'           => $request['keterangan'],
                        'proof'          => $movefile['url'],
                        'proof_id'       => $attachment_id,
                    )
                ));

                do_action('sejoli/order/update-status', array(
                    'ID'     => intval($request['invoice_id']),
                    'status' => 'payment-confirm'
                ));

                $response = [
                    'valid'    => true,
                    'messages' => $response['messages'],
                ];

                wp_send_json($response);

            endif;

            $response = [
                'valid' => false,
                'messages' => $errors,
            ];
            wp_send_json($response);

        endif;
    }

}
