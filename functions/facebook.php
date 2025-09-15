<?php
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

function sejoli_facebook_tracker( $order_data, $eventString, $value = 0, $source = "", $test_code = "" ) {
    
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
        $phone        = $users->meta->phone;
        $email        = $users->user_email;
        $user_id      = $order['user_id'];

        $fb_conversion_active       = boolval(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_active'));
        $fb_conversion_id           = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_id'));
        $fb_conversion_access_token = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_access_token'));
        $fb_conversion_currency     = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_currency'));
        $fb_conversion_test_code    = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_test_code'));

    else:

        $product_id   = $order_data;
        $products     = sejolisa_get_product($product_id);
        $value        = $products->price;
        $product_name = $products->post_title;
        $product_qty  = 1;
        $users        = sejolisa_get_user(get_current_user_id());
        $id           = $product_id.'-'.get_current_user_id().'-'.date("Y-m-d-H:i:s");
        $phone        = $users->meta->phone;
        $email        = $users->user_email;
        $user_id      = get_current_user_id();

        $fb_conversion_active       = boolval(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_active'));
        $fb_conversion_id           = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_id'));
        $fb_conversion_access_token = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_access_token'));
        $fb_conversion_currency     = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_currency'));
        $fb_conversion_test_code    = esc_attr(sejolisa_carbon_get_post_meta($product_id, 'fb_conversion_test_code'));

    endif;

    if( $fb_conversion_active === false ) :
    
        return array(
            "valid" => false,
            "response" => "Facebook is disabled"
        );
    
    endif;

    if ( $source === "" ) :
        $source = ActionSource::WEBSITE;
    endif;

    // Configuration.
    $access_token = $fb_conversion_access_token;
    $pixel_id     = $fb_conversion_id;
    $currency     = $fb_conversion_currency;
    $userAgent    = $_SERVER['HTTP_USER_AGENT'];
    $ip           = $_SERVER['REMOTE_ADDR'];

    if (is_null($access_token) || is_null($pixel_id)) {
        throw new Exception(
            'You must set your access token and pixel id before executing'
        );
    }

    // Initialize
    Api::init( null, null, $access_token );
    $api = Api::instance();
    $api->setLogger(new CurlLogger());

    $user_data = (new UserData())
        ->setClientIpAddress( $ip )
        ->setClientUserAgent( $userAgent );

    if( !empty( $phone ) ) :

      $user_data->setPhones( [hash('sha256', preg_replace('/[^0-9.]+/', '', $phone))] );
    
    endif;

    if( !empty( $email ) ) :

      $user_data->setEmail( [hash('sha256', $email)] );
    
    endif;

    $user_data->setExternalId( hash('sha256', preg_replace('/[^0-9.]+/', '', $user_id)) );

    $content = (new Content())
        ->setProductId( 'product'.$product_id )
        ->setQuantity( $product_qty )
        ->setItemPrice( $value )
        ->setTitle( $product_name );

    $custom_data = new CustomData();
    $custom_data->setContentCategory( 'product' );
    $custom_data->setContentType( 'product' );
    $custom_data->setContentName( $product_name );
    $custom_data->setContentIds( [$product_id] );
    $custom_data->setContents( array( $content ) );

    if( !empty( $currency ) && !empty( $value ) && $value > 0 ) :
    
        $custom_data->setValue( $value )->setCurrency( $currency );
    
    endif;

    $events = array();
    $event = new Event();

    $event->setEventSourceUrl( home_url( $wp->request ) );
    $event->setActionSource( $source );
    $event->setEventTime( time() );

    switch( $eventString ) :
        
        case "ViewContent":
            $event->setEventId('ViewContent-' . $id);
            $event->setEventName('ViewContent');
            break;

        case "Contact":
            $event->setEventId("Contact-" . $id);
            $event->setEventName('Contact');
            break;

        case "InitiateCheckout":
            $event->setEventId("InitiateCheckout-" . $id);
            $event->setEventName('InitiateCheckout');
            break;

        case "Purchase":
            $event->setEventId("Purchase-" . $id);
            $event->setEventName('Purchase');
            break;

        default:
            $event->setEventId($eventString . '-' . $id);
            $event->setEventName($eventString);
            break;

    endswitch;

    $event->setUserData( $user_data );
    $event->setCustomData( $custom_data );

    array_push( $events, $event );

    do_action('qm/info', array("event" => $event));

    try {

        $request = ( new EventRequest( $pixel_id ) )
            ->setEvents( $events );

        if( !empty( $fb_conversion_test_code ) ):
            $request = $request->setTestEventCode( $fb_conversion_test_code );
        endif;

        $response = $request->execute();

        return array(
            "valid"    => true,
            "response" => $response->getFbTraceId(),
        );

    } catch ( \Exception $e ) {

        return array(
            "valid"    => false,
            "response" => $e->getMessage()
        );

    }

    return;

}