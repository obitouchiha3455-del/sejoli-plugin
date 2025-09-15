<?php

function sejoli_tiktok_tracker( $order_data, $eventString, $value = 0, $source = "", $test_code = "" ) {
    
    global $sejolisa, $wp;

    if( is_array( $order_data ) ) :
    
        $order        = $order_data;
        $product_id   = $order['product_id'];
        $id           = $product_id.'-'.$order['ID'].'-'.$order['user_id'].'-'.date("Y-m-d-H:i:s");
        $value        = $order['grand_total'];
        $products     = get_post($product_id);
        $product_name = $products->post_title;
        $product_qty  = $order['quantity'];
        $users        = sejolisa_get_user($order['user_id']);
        $user_id      = $order['user_id'];
        $phone        = $users->meta->phone;
        $email        = $users->user_email;
        $user_id      = $order['user_id'];

        $tiktok_conversion_active       = boolval(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_active'));
        $tiktok_conversion_id           = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_id'));
        $tiktok_conversion_access_token = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_access_token'));
        $tiktok_conversion_currency     = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_currency'));
        $tiktok_conversion_test_code    = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_test_code'));

    else:

        $product_id   = $order_data;
        $products     = sejolisa_get_product($product_id);
        $value        = $products->price;
        $product_name = $products->post_title;
        $product_qty  = 1;
        $users        = sejolisa_get_user(get_current_user_id());
        $user_id      = get_current_user_id();
        $id           = $product_id.'-'.get_current_user_id().'-'.date("Y-m-d-H:i:s");
        $phone        = $users->meta->phone;
        $email        = $users->user_email;
        $user_id      = get_current_user_id();

        $tiktok_conversion_active       = boolval(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_active'));
        $tiktok_conversion_id           = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_id'));
        $tiktok_conversion_access_token = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_access_token'));
        $tiktok_conversion_currency     = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_currency'));
        $tiktok_conversion_test_code    = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'tiktok_conversion_test_code'));

    endif;

    if ( $tiktok_conversion_active === false ) :

        return array(
            "valid" => false,
            "response" => "TikTok is disabled"
        );

    endif;

    $source = (empty($source)) ? "PIXEL_EVENTS" : $source;

    // Configuration.
    $access_token = $tiktok_conversion_access_token;
    $pixel_id     = $tiktok_conversion_id;
    $currency     = $tiktok_conversion_currency;
    $userAgent    = $_SERVER['HTTP_USER_AGENT'];
    $ip           = $_SERVER['REMOTE_ADDR'];

    if (is_null($access_token) || is_null($pixel_id)) {
        throw new Exception(
            'You must set your access token and pixel id before executing'
        );
    }

    $body = array(
        "pixel_code"       => $pixel_id,
        "event"            => $eventString,
        "event_id"         => $id,
        "limited_data_use" => true,
        "opt_out"          => false,
        "data_processing_options" => array(
            "options1"
        ),
        "data_processing_options_country" => 1,
        "data_processing_options_state"   => 0,
        "timestamp" => date("Y-m-d\TH:i:s.Z\Z", current_time('timestamp')),
        "context"   => array(
            "page"  => array(
                "url"     => home_url( $wp->request ),
                "referer" => home_url( $wp->request ),
            ),
            'user' => array(
                "lead_event_source" => 'web',
                "lead_id"           => hash('sha256', preg_replace('/[^0-9.]+/', '', $user_id)),
                "external_id"       => hash('sha256', preg_replace('/[^0-9.]+/', '', $user_id)),
                "phone_number"      => hash('sha256', preg_replace('/[^0-9.]+/', '', $phone)),
                "email"             => hash('sha256', preg_replace('/[^0-9.]+/', '', $email))
            ),
            'user_agent' => $userAgent,
            'ip'         => $ip,
        ),
        "properties"   => array(),
        "event_source" => $source,
    ); 

    $body["context"]["ad"]             = array();
    $body["context"]["ad"]["callback"] = get_bloginfo('url');

    if( $value > 0 ) :
        $body["properties"] = array(
            "contents" => array(
                array(
                    "price"        => $value,
                    "quantity"     => $product_qty,
                    'content_type' => 'product',
                    'content_name' => $product_name,
                    'content_id'   => (string) $product_id,
                )
            ),
            "currency" => $currency,
            "value"    => $value,
        );
    endif;

    if( count( $body['properties'] ) === 0 ) :
        unset($body['properties']);
    endif;

    if( !empty( $tiktok_conversion_test_code ) ):
        $test_code = $tiktok_conversion_test_code;
    endif;

    if( $test_code !== "" ) :
        $body["test_event_code"] = $test_code;
    endif;

    try {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://business-api.tiktok.com/open_api/v1.3/pixel/track/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode( $body ),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Access-Token: ' . $access_token
            )
        ));

        $response      = curl_exec( $curl );
        $response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        $res = json_decode( $response );

        if( $response_code == 200 && $res->code === 0 ) :

            return array( 
                "valid"    => true,
                "response" => $res->request_id
            );

        elseif( $response_code == 200 && $res->code != 0 ) :

            throw new Exception("TikTok Pixel Error: " . $res->message);

        else :

            throw new Exception("TikTok Pixel Error: " . $response_code);

        endif;

    } catch ( \Exception $e ) {

        return array(
            "valid" => false,
            "response" => $e->getMessage()
        );

    }

}