<?php

namespace SejoliSA\NotificationMedia;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Wanotif extends WhatsApp {

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
        return 'wanotif.id';
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
            Field::make('text', 'wanotif_api_key',  __('Wanotif.id API Key', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'notification_whatsapp_service',
                        'value' => 'wanotif'
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

        foreach($recipients as $recipient) :

            $phone_number = safe_str_replace('+', '', apply_filters('sejoli/user/phone', $recipient));

            do_action('sejoli/log/write', 'prepare wanotif', ['phone_number' => $phone_number, 'content' => $content]);

            $response = wp_remote_post('https://api.wanotif.id/v1/send',[
                'body' => [
                    'Apikey'    => sejolisa_carbon_get_theme_option('wanotif_api_key'),
                    'Phone'     => $phone_number,
                    'Message'   => $content
                ]
            ]);

            do_action('sejoli/log/write', 'response wanotif', (array) wp_remote_retrieve_body($response));

        endforeach;
    }
}
