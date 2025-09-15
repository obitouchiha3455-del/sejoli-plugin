<?php

namespace SejoliSA\Front;

class Register
{

    /**
     * enqueue scripts
     * hooked via action wp_enqueue_scripts
     *
     * @return void
     */
    public function enqueue_scripts()
    {

        $g_recaptcha          = boolval(sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_enabled' ));
        $g_recaptcha_register = boolval( sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_register_page' ) );
        $g_recaptcha_sitekey  = esc_attr(sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_sitekey' ));

        if( sejoli_is_a_member_page('register') && true === $g_recaptcha && !empty($g_recaptcha_sitekey) && true === $g_recaptcha_register ) :
            wp_register_script( 'g-recaptcha',       'https://www.google.com/recaptcha/api.js?render='.$g_recaptcha_sitekey, [], null, true );
            wp_enqueue_script('g-recaptcha');
        endif;

    }

    /**
     * register user
     * Hooked via action sejoli/register
     * @return void
     */
    public function submit_register() {

        $enable_registration = boolval(sejolisa_carbon_get_theme_option('sejoli_enable_registration'));

        if(false === $enable_registration || is_user_logged_in()) :

            wp_redirect( home_url('member-area/login') );
            
            exit;

        endif;

        if(isset($_POST['sejoli-nonce']) && wp_verify_nonce($_POST['sejoli-nonce'],'user-register')) :

            $valid   = true;
            $request = wp_parse_args($_POST, [
                'username'           => '',
                'full_name'          => '',
                'email'              => '',
                'password'           => '',
                'confirm_password'   => '',
                'wa_phone'           => '',
                'recaptcha_response' => ''
            ]);

            $errors = [];

            $display_username = boolval(sejolisa_carbon_get_theme_option('sejoli_registration_display_username'));
            $display_password = boolval(sejolisa_carbon_get_theme_option('sejoli_registration_display_password'));

            if(true === $display_username) :

                if ( empty( $request['user_login'] ) ) :

                    $valid = false;

                    $errors[] = __('Nama Pengguna wajib diisi','sejoli');

                endif;

            endif;

            if ( empty( $request['full_name'] ) ) :

                $valid = false;

                $errors[] = __('Nama Lengkap wajib diisi','sejoli');

            endif;

            if ( empty( $request['email'] ) ) :

                $valid = false;

                $errors[] = __('Email wajib diisi','sejoli');

            endif;

            if(true === $display_password) :
                
                if ( empty( $request['password'] ) || empty($request['confirm_password']) ) :

                    $valid = false;

                    $errors[] = __('Password dan Konfirmasi Password wajib diisi','sejoli');

                elseif( $request['password'] !== $request['confirm_password'] ):

                    $valid = false;

                    $errors[] = __('Password dan Konfirmasi Password harus sama','sejoli');

                endif;

            endif;

            if ( empty( $request['wa_phone'] ) ) :

                $valid = false;

                $errors[] = __('Nomor WhatsApp wajib diisi','sejoli');

            endif;

            $phoneNumber = preg_replace('/\D/', '', $request['wa_phone']);
        
            if (strlen($phoneNumber) < 9) :

                $valid = false;

                $errors[] = __('Nomor WhatsApp tidak valid','sejoli');

            endif;

            $reCaptcha = sejolisa_validating_g_recaptcha($request, $valid, 'register', 'no', 'action');

            if ( ($errors && (empty($valid) || false === $valid)) || (empty($valid) || false === $valid) ) :

                $messages = $errors;
                do_action('sejoli/set-messages',$messages,'error');

            else:

                if( empty($request['password']) ) :

                    $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!!!';
                    $charactersLength = strlen($characters);
                    $password         = '';

                    for ($i = 0; $i < 8; $i++) :
                        $password .= $characters[rand(0, $charactersLength - 1)];
                    endfor;

                    $set_password = $password;

                endif;
                $request['username'] =  $request['user_login'];
                $username           = isset($request['username']) ? $request['username'] : '';
                $user_login         = isset($request['user_login']) ? $request['user_login'] : '';
                $useremail          = $request['email'];
                $username_prefix    = rand(1, 999);
                $get_short_email    = substr($useremail, 0, strpos($useremail, '@'));
                $set_username_email = $get_short_email.$username_prefix;

                $userdata = array(
                    'user_email'    => $request['email'],
                    'username'      => (true === $display_username) ? $username : $set_username_email,
                    'user_login'    => (true === $display_username) ? $user_login : $set_username_email,
                    'first_name'    => $request['full_name'],
                    'display_name'  => $request['full_name'],
                    'user_pass'     => (true === $display_password) ? $request['password'] : $set_password,
                    'role'          => 'sejoli-member',
                );

                $user_id = wp_insert_user( $userdata );

                if ( ! is_wp_error( $user_id ) ) :

                    update_user_meta($user_id, '_phone', $request['wa_phone']);
                    // wp_new_user_notification( $user_id, null, 'both' );
                    
                    $user_data = array(
                        'user_login'    => (true === $display_username) ? $username : $set_username_email,
                        'user_email'    => $request['email'],
                        'user_name'     => $request['full_name'],
                        'user_password' => (true === $display_password) ? $request['password'] : $set_password,
                        'user_phone'    => $request['wa_phone'],
                        'product_id'    => ''
                    );

                    do_action('sejoli/notification/registration', $user_data);

                    $messages = [
                        sprintf(__('Selamat! pendaftaran berhasil, Info akses <a href="%s">Login</a> sudah dikirim ke Email','sejoli'),sejoli_get_endpoint_url('login')),
                    ];

                    do_action('sejoli/set-messages',$messages,'success');

                else:

                    if ( $user_id->get_error_message() ) :

                        $messages = [
                            $user_id->get_error_message()
                        ];

                        error_log(print_r($messages, true));

                    else:

                        $messages = [
                            sprintf(__('Pendaftaran gagal, silahkan coba lagi','sejoli'),sejoli_get_endpoint_url('login'))
                        ];

                    endif;

                    do_action('sejoli/set-messages',$messages,'error');

                endif;

            endif;

        endif;
    }

}
