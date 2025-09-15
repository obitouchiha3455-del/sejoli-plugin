<?php

namespace SejoliSA\NotificationMedia;

class Main {

    /**
     * Recipient data
     * @since   1.0.0
     * @var     array
     */
    public $recipients = [];

    /**
     * Notification title
     * @since   1.0.0
     * @var     string
     */
    public $title      = NULL;

    /**
     * Notification content
     * @since   1.0.0
     * @var     string
     */
    public $content    = NULL;

    /**
     * All needed data
     * @since   1.0.0
     * @var     array
     */
    public $data = [];

    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Set single recipient
     * @since   1.0.0
     * @param   string $recipient
     */
    protected function set_recipient($recipient) {
        $this->recipients[] = $recipient;
    }

    /**
     * Set multiple recipients
     * @since   1.0.0
     * @param   array $recipients
     */
    protected function set_recipients(array $recipients) {
        $this->recipients = $recipients;
    }

    /**
     * Set notification title value
     * @since   1.0.0
     * @param   string  $title
     */
    protected function set_title($title) {
        $this->title = $title;
    }

    /**
     * Set notification content value
     * @since   1.0.0
     * @param   string  $content
     */
    protected function set_content($content) {
        $this->content = $content;
    }

    /**
     * Set all needed data like product,order, buyer, affiliate etc
     * @param array $data
     */
    public function set_data(array $data) {
        $this->data = $data;
    }

    /**
     * Send notification
     * This parent method only save the data to log
     * @return  void
     */
    protected function send(array $recipients, $content, $title) {

    }
}
