<?php

namespace SejoliSA\Front;

class Profile
{

    /**
     * ajax add affilaite coupon user
     * hooked via action sejoli_ajax_update-profile, priotrity 100
     *
     * @return void
     */
    public function ajax_update_profile()
    {
		if ( ! wp_verify_nonce( $_POST['security'], 'ajax-nonce' ) ) :
			die( 'invalid nonce' );
		endif;

        $request = wp_parse_args($_POST, [
            'name' => '',
            'real_email' => '',
            'phone' => '',
            'address' => '',
            'kecamatan' => '',
            'kecamatan_name' => '',
            '_bank_info' => '',
            'password_baru' => '',
            'konfirmasi_password_baru' => '',
        ]);

        error_log(print_r($request, true));

        $errors = [];

        if ( empty( $request['name'] ) ) :
            $errors[] = __('Nama wajib diisi');
        endif;

        if ( empty( $request['real_email'] ) ) :
            $errors[] = __('Alamat Email wajib diisi');
        endif;

        if ( empty( $request['phone'] ) ) :
            $errors[] = __('No Handphone wajib diisi');
        endif;

        $phoneNumber = preg_replace('/\D/', '', $request['phone']);
        
        if (strlen($phoneNumber) < 9) :
            $errors[] = __('No Handphone tidak valid','sejoli');
        endif;

        if ( !empty( $request['password_baru'] ) ) :
            if ( empty( $request['konfirmasi_password_baru'] ) ) :
                $errors[] = __('Konfirmasi Password baru wajib diisi');
            elseif ( $request['password_baru'] !== $request['konfirmasi_password_baru'] ) :
                $errors[] = __('Password baru dan Konfirmasi Password baru harus sama');
            endif;
        endif;

        if ( empty( $errors ) ) :

            $userdata = [
                'ID' => get_current_user_id(),
                'first_name' => $request['name'],
                'display_name' => $request['name'],
                'user_email' => $request['real_email'],
                'user_pass'  => $request['password_baru'],
            ];

            $user_id = wp_update_user( $userdata );

            if ( !is_wp_error( $user_id ) ) :

                if ( !empty( $request['phone'] ) ) :
                    update_user_meta( $user_id, '_phone', $request['phone'] );
                endif;

                if ( !empty( $request['address'] ) ) :
                    update_user_meta( $user_id, '_address', $request['address'] );
                endif;

                if ( !empty( $request['kecamatan'] ) ) :
                    update_user_meta( $user_id, '_destination', $request['kecamatan'] );
                endif;

                if ( !empty( $request['kecamatan_name'] ) ) :
                    update_user_meta( $user_id, '_destination_name', $request['kecamatan_name'] );
                endif;

                if ( !empty( $request['_bank_info'] ) ) :
                    update_user_meta( $user_id, '_bank_info', $request['_bank_info'] );
                endif;

                $response = [
                    'valid' => true,
                    'messages' => [ __( 'Profile berhasil diperbarui', 'sejoli' ) ]
                ];
                wp_send_json($response);

            else:

                $errors[] = __( 'Profile gagal diperbarui', 'sejoli' );

            endif;

        endif;

        $response = [
            'valid' => false,
            'messages' => $errors,
        ];
        wp_send_json($response);

    }

}
