<?php

namespace SejoliSA\Notification;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class BulkNotification extends Main {

    protected $order_data;

    /**
     * Content setup
     * @since   1.1.0
     * @var     array
     */
    protected $content_setup = array();

    /**
     * Recipient data
     * @since   1.1.0
     * @var     array
     */
    protected $recipients;

    /**
     * Attachment for file
     * @since   1.1.0
     * @var     bool|array
     */
    public $attachments = false;

    /**
     * Prepare content for notification
     * @since   1.1.0
     * @return  void
     */
    protected function set_content() {

        // ***********************
		// Setup content for buyer
		// ***********************

        if(false !== $this->content_setup['send-email']) :
    		$this->set_recipient_title  ('buyer', 'email', $this->content_setup['email-title']);
    		$this->set_recipient_content('buyer', 'email', $this->set_notification_content(
                                                                $this->content_setup['email-content'],
                                                                'email',
                                                                'on-hold'
                                                          ));
        endif;

		if(false !== $this->content_setup['send-whatsapp']) :
            $this->set_recipient_content('buyer', 'whatsapp', $this->set_notification_content(
                                                                $this->content_setup['whatsapp-content'],
                                                                'whatsapp',
                                                                'on-hold'
                                                              ));
        endif;

        if(false !== $this->content_setup['send-sms']) :
            $this->set_recipient_content('buyer', 'sms', $this->set_notification_content(
                                                                $this->content_setup['sms-content'],
                                                                'sms',
                                                                'on-hold'
                                                              ));
        endif;

    }

    /**
     * Trigger to send notification
     * @since   1.1.0
     * @param   array   $order_data     Order data
     * @param   array   $content        Content configuration
     * @return  void
     */
    public function trigger(array $order_data, array $content) {

        $this->order_data    = $order_data;
        $media_libraries     = $this->get_media_libraries();
        $this->content_setup = $content;

        $this->prepare($order_data);
        $this->set_content();

        $this->trigger_email($media_libraries['email']);
        $this->trigger_whatsapp($media_libraries['whatsapp']);
        $this->trigger_sms($media_libraries['sms']);

    }

    /**
     * Trigger to send email
     * @since   1.1.0
     * @param   object   $media    Selected media object
     * @return  void
     */
    protected function trigger_email($media) {

        if(false === $this->content_setup['send-email']) :
            return;
        endif;

        // send email for buyer
		$media->set_data([
			'order_data'     => $this->order_data,
			'product_data'   => $this->product_data,
			'buyer_data'     => $this->buyer_data,
			'affiliate_data' => $this->affiliate_data,
		]);

		$media->send(
			array($this->buyer_data->user_email),
			$this->render_shortcode($this->get_recipient_content('buyer', 'email')),
			$this->render_shortcode($this->get_recipient_title('buyer', 'email'))
		);
    }

    /**
     * Trigger to send whatsapp
     * @since   1.1.0
     * @param   object   $media    Selected media object
     * @return  void
     */
    protected function trigger_whatsapp($media) {

        if(false === $this->content_setup['send-whatsapp']) :
            return;
        endif;

        $media->set_data([
            'order_data'     => $this->order_data,
            'product_data'   => $this->product_data,
            'buyer_data'     => $this->buyer_data,
            'affiliate_data' => $this->affiliate_data,
        ]);

        $media->send(
			array($this->buyer_data->meta->phone),
			$this->render_shortcode($this->get_recipient_content('buyer', 'whatsapp'))
        );
    }

    /**
     * Trigger to SMS whatsapp
     * @since   1.1.0
     * @param   object   $media    Selected media object
     * @return  void
     */
    protected function trigger_sms($media) {

        if(false === $this->content_setup['send-sms']) :
            return;
        endif;

        $media->set_data([
            'order_data'     => $this->order_data,
            'product_data'   => $this->product_data,
            'buyer_data'     => $this->buyer_data,
            'affiliate_data' => $this->affiliate_data,
        ]);

        $media->send(
			array($this->buyer_data->meta->phone),
			$this->render_shortcode($this->get_recipient_content('buyer', 'sms'))
        );
    }
}
