<?php

namespace SejoliSA\NotificationMedia;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WooWa extends WhatsApp {
    /**
     * Construction
     */
    public function __construct() {
        add_filter('sejoli/whatsapp/setup-fields', [$this, 'setup_fields'], 1);
    }

    /**
     * Get name of service
     * @return string
     */
    public function get_label() {
        return 'woowa';
    }

    /**
     * Add setup fields to whatsapp fields
     * Hooked via filter sejoli/whatsapp/setup-fields, priority 1
     * @since   1.0.0
     * @param   array  $fields
     * @return  array
     */
    public function setup_fields(array $fields) {

        $setup_fields = [
            Field::make('text', 'woowa_api_key',  __('Woowa API Key', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'notification_whatsapp_service',
                        'value' => 'woowa'
                    ]
                ])
        ];

        return array_merge($fields, $setup_fields);
    }

    /**
     * Send content
     * @since   1.0.0
     * @param   array  $recipients
     * @param   string $content
     * @param   string $title
     * @return  void
     */
    public function send(array $recipients, $content, $title = '', $recipient_type = 'buyer') {

        $api_key = trim( sejolisa_carbon_get_theme_option('woowa_api_key') );

        foreach($recipients as $recipient) :

            $phone_number = safe_str_replace('+', '+', apply_filters('sejoli/user/phone', $recipient));

            if(empty($phone_number)) :
                continue;
            endif;

            $post_data = array(
                'key'      => $api_key,
                'message'  => $content,
                'phone_no' => $phone_number
            );

            $json_data = json_encode($post_data);

            $response = wp_remote_post('https://notifapi.com/send_message',[
                'headers'   => array(
                    'Content-Type'   => 'application/json; charset=utf-8',
                    'Content-Length' => strlen($json_data)
                ),
                'body' => $json_data
            ]);

            do_action(
                'sejoli/log/write',
                'response woowav',
                array(
                    'post_data' => $post_data,
                    'response'  => (array) wp_remote_retrieve_body($response)
                )
            );

            sleep ( 2 );

        endforeach;
    }
}
