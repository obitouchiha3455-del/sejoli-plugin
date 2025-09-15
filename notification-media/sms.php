<?php

namespace SejoliSA\NotificationMedia;

class SMS extends Main {

    public $args;

    /**
     * Construction
     */
    public function __construct() {
    }

    /**
     * Send the notification
     * @since   1.0.0
     * @return  void
     */
    public function send(array $recipients, $content, $title = '') {

        $selected_service   = sejolisa_carbon_get_theme_option('notification_sms_service');
        $available_services = apply_filters('sejoli/sms/available-services', []);

        do_action('sejoli/log/write', 'prepare sms', ['selected service' => $selected_service] );

        if(
            !empty($selected_service) &&
            isset($available_services[$selected_service]) &&
            !empty($content)
        ) :

            $available_services[$selected_service]->send($recipients, $content, $title);

        endif;

    }
}
