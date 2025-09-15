<?php

namespace SejoliSA\NotificationMedia;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class StarSender extends WhatsApp {

    /**
     * Construction
     * @since   1.5.3.1
     */
    public function __construct() {
        add_filter('sejoli/whatsapp/setup-fields', [$this, 'setup_fields'], 1);
    }

    /**
     * Get name of service
     * @since   1.5.3.1
     * @return  string
     */
    public function get_label() {
        return 'starsender';
    }

    /**
     * Add setup fields to whatsapp fields
     * Hooked via filter sejoli/whatsapp/setup-fields, priority 1
     * @since   1.5.3.1
     * @param   array  $fields
     * @return  array
     */
    public function setup_fields(array $fields) {

        $setup_fields = [
            Field::make('text', 'starsender_api_key',  __('StarSender API Key', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'notification_whatsapp_service',
                        'value' => 'starsender'
                    ]
                ])
        ];

        return array_merge($fields, $setup_fields);
    }

    /**
     * Send content
     * @since   1.5.3.1
     * @param   array  $recipients
     * @param   string $content
     * @param   string $title
     * @return  void
     */
    public function send(array $recipients, $content, $title = '', $recipient_type = 'buyer') {

        foreach($recipients as $recipient) :

            $phone_number = safe_str_replace('+', '', apply_filters('sejoli/user/phone', $recipient));

            $url = add_query_arg(array(
                        'message' => rawurldecode($content),
                        'tujuan'  => $phone_number . '@s.whatsapp.net'
                   ), 'https://starsender.online/api/sendText');

            $response = wp_remote_post($url,[
                'headers'   => array(
                    'apikey'    => trim( sejolisa_carbon_get_theme_option('starsender_api_key') )
                ),
            ]);

            do_action('sejoli/log/write', 'response starsender', array(
                'data'     => array(
                    'message' => rawurldecode($content),
                    'tujuan'  => $phone_number . '@s.whatsapp.net'
                ),
                'response' => (array) wp_remote_retrieve_body($response)
            ));

        endforeach;
    }
}
