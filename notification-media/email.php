<?php

namespace SejoliSA\NotificationMedia;

class Email extends Main {

    /**
     * Attachment for file
     * @var [type]
     */
    public $attachments = false;

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
    public function send(array $recipients, $content, $title, $recipient_type = array('admin', 'affiliate', 'buyer'), $attachments = array()) {

        $this->set_recipients($recipients);
        $this->set_title($title);
        $this->set_content($content);

        $buyer_email           = isset($this->data['buyer_data']->data) ? $this->data['buyer_data']->data->user_email : '';
        $buyer_affiliate_email = isset($this->data['affiliate_data']->data) ? $this->data['affiliate_data']->data->user_email : '';;
        $buyer_affiliate       = isset($this->data['affiliate_data']) ? $this->data['affiliate_data'] : '';

        // Buyer Non Admin & Non Affiliate
        if( in_array( $buyer_email, $recipients ) && isset($buyer_affiliate) ) :
            $attachments = apply_filters('sejoli/notification/email/attachments', $this->attachments, $this->data);
        // Buyer Non Admin & Affiliate
        elseif( in_array( $buyer_email, $recipients ) && in_array( $buyer_affiliate_email, $recipients ) ):
            $attachments = null;
        endif;

        ob_start();
        include SEJOLISA_DIR . '/template/email/template.php';
        $content = ob_get_clean();

        $logo        = '';
        $upload_logo = sejolisa_carbon_get_theme_option('notification_email_logo');

        if($upload_logo) :
            $image = wp_get_attachment_image_src($upload_logo, 'medium');
            if($image) :
                $logo = sprintf('<img src="%s" alt="%s" />', $image[0], get_bloginfo('name'));
            endif;
        endif;

        $is_sent = wp_mail(
            safe_str_replace([
                    '{{affiliate-email}}'
                ],[
                    $buyer_affiliate_email
                ],
                $this->recipients
            ),
            $this->title,
            safe_str_replace([
                    '{{logo}}',
                    '{{content}}',
                    '{{footer}}',
                    '{{copyright}}'
                ],[
                    $logo,
                    wpautop($this->content),
                    sejolisa_carbon_get_theme_option('notification_email_footer'),
                    sejolisa_carbon_get_theme_option('notification_email_copyright')
                ],
                $content
            ),
            [
                'Content-Type: text/html; charset=UTF-8',
                sprintf('From: %s <%s>', sejolisa_carbon_get_theme_option('notification_email_from_name'), sejolisa_carbon_get_theme_option('notification_email_from_address')),
                sprintf('Reply-top: %s <%s>', sejolisa_carbon_get_theme_option('notification_email_reply_name'), sejolisa_carbon_get_theme_option('notification_email_reply_address'))
            ],
            $attachments
        );

        do_action('sejoli/email/send', $attachments);

    }
}
