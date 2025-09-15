<?php

namespace SejoliSA\Notification;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Reminder extends Main {

    /**
     * Affiliate data
     * @since   1.1.9
     * @var     array
     */
    protected $affiliate;

    /**
     * Recipient data
     * @since   1.1.9
     * @var     array
     */
    protected $recipients;

    /**
     * Commission data
     * @since   1.1.0
     * @var     array
     */
    protected $commission_data = array();

    /**
     * Attachment for file
     * @since   1.1.9
     * @var     bool|array
     */
    public $attachments = false;

    public $setup_data = array();

    /**
     * Construction
     */
    public function __construct() {

    }


    /**
     * Prepare content
     * @since 	1.1.9
	 * @param  	array 	$order_data
	 * @param  	array 	$reminder_data
     * @return  void
     */
    public function setup_data(array $order_data, array $reminder_data) {

        $media_libraries = $this->get_media_libraries();

        $this->prepare($order_data);

        $reminder_media = array();
        $i = 0;

        foreach ( $reminder_data['media'] as $media ) :

            $reminder_media[] = $media;

            $this->set_recipient_title  ('buyer', $reminder_media[$i], $reminder_data['title']);
            $this->set_recipient_content('buyer', $reminder_media[$i], $this->set_notification_content(
                                                                $reminder_data['content'],
                                                                $reminder_media[$i],
                                                                'buyer'
                                                            ));

            $media_libraries[$reminder_media[$i]]->set_data([
                'order_data'     => $this->order_data,
                'product_data'   => $this->product_data,
                'buyer_data'     => $this->buyer_data,
                'affiliate_data' => $this->affiliate_data,
            ]);

            $this->setup_data = array(
                'recipient' => ('email' === $reminder_media[$i] ) ? $this->buyer_data->user_email : $this->buyer_data->meta->phone,
                'content'   => $this->render_shortcode( $this->get_recipient_content('buyer', $reminder_media[$i]) ),
                'title'     => $this->render_shortcode( $this->get_recipient_title('buyer', $reminder_media[$i]) ),
            );

            $i++;

        endforeach;
            
    }

    /**
     * Return reminder data that has been converted
     * @since   1.1.9
     * @return  array
     */
    public function get_data() {
        return $this->setup_data;
    }

    /**
     * Trigger to send notification
     * @since   1.1.9
     * @param   array  $reminder_data   Reminder data
     * @return  void
     */
    public function trigger($reminder_data) {

        $media_libraries = $this->get_media_libraries();
        $media_type      = $reminder_data->media_type;

        $media_libraries[$media_type]->send(
            (array) $reminder_data->recipient,
            ("email" === $media_type) ?
                $reminder_data->content :
                strip_tags($reminder_data->content),
            $reminder_data->title
        );

    }
}
