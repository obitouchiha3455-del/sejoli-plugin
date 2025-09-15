<?php
/**
 * Validating google reCaptcha
 * @since   1.0.0
 * @return  array
 */
function sejolisa_validating_g_recaptcha($request, bool $valid, $type, $header, $errorType) {

    if ( !empty($request['recaptcha_response']) && false !== $valid ) :

        require SEJOLISA_DIR . '/third-parties/recaptcha/src/autoload.php';
        
        $g_recaptcha_secretekey = esc_attr(sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_secreetkey' ));
        $g_recaptcha_score_threshold = esc_attr(sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_score_threshold' ));

        $recaptcha = new \ReCaptcha\ReCaptcha( $g_recaptcha_secretekey );
        $resp      = $recaptcha->setExpectedHostname( $_SERVER['SERVER_NAME'] )
                               ->setExpectedAction( $type )
                               ->setScoreThreshold( $g_recaptcha_score_threshold )
                               ->verify( $request['recaptcha_response'], $_SERVER['REMOTE_ADDR'] );

        if( $header === 'yes' ):

            header('Content-type:application/json');

        endif;

        if ( $resp->isSuccess() ) :

            // Verified!
            $valid = true;

        else:

            $valid  = false;
            $errors = $resp->getErrorCodes();
            $errors[] = __('reCaptcha not valid!', 'sejoli');
            
            if( $errorType === 'json' ):

                sejolisa_set_respond([
                    'valid' => false,
                    'messages' => [
                        'error' => $errors
                    ]
                ], 'checkout');

            endif;

            if( $errorType === 'action' ):

                $messages = $errors;
                do_action('sejoli/set-messages',$messages,'error');

            endif;

        endif;

    endif;

    return $valid;

}