<?php

namespace SejoliSA\Front;

class MemberMessage {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.3.2
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.2
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Available messages
	 * @since	1.0.0
	 * @var 	array
	 */
	protected $messages = array();

	/**
	 * Available user product access
	 * @since 	1.0.0
	 * @var 	array
	 */
	protected $user_access = array();

	/**
	 * Available user's message after comparing between $this->messages and $this->user_access
	 * @since	1.0.0
	 * @var 	array
	 */
	protected $user_messages = array();

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.3.2
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

    }

	/**
	 * Get product association
	 * @since 	1.0.0
	 * @param  	integer $message_id
	 * @return 	array
	 */
	protected function get_product_association($message_id) {

		$products             = array();
		$product_associations = sejolisa_carbon_get_post_meta($message_id, 'product_association');

		foreach( (array) $product_associations as $data) :
			$products[] = $data['id'];
		endforeach;

		return $products;
	}

	/**
	 * Set single message
	 * @since 	1.0.0
	 * @param 	integer $message_id
	 * @return 	array
	 */
	protected function set_message($message_id) {

		$message = get_post($message_id);

		return array(
			'title'   => $message->post_title,
			'content' => sejolisa_carbon_get_post_meta($message_id, 'message'),
			'style'	  => sejolisa_carbon_get_post_meta($message_id, 'style'),
			'icon'	  => sejolisa_carbon_get_post_meta($message_id, 'icon')
		);
	}

	/**
	 * Prepare all available messages
	 * @since 	1.0.0
	 * @return 	void
	 */
	protected function get_messages() {

		$current_time = current_time('timestamp');

		$message_ids  = \SejoliSA\Model\Post::set_args(array(
							'fields'    => 'ids',
							'post_type' => SEJOLI_MESSAGE_CPT,
						))
						->set_total(999)
						->add_cache()
						->get();

		foreach( (array) $message_ids as $message_id ) :

			$end_show = sejolisa_carbon_get_post_meta($message_id, 'end_show');

			if(
				empty($end_show) ||
				$current_time <= strtotime($end_show)
			) :
				$product_associations = $this->get_product_association($message_id);

				if(0 < count($product_associations)) :

					foreach($product_associations as $product_id) :

						if( !array_key_exists($product_id, $this->messages)) :
							$this->messages[$product_id] = array();
						endif;

						$this->messages[$product_id][$message_id] = $this->set_message($message_id);

					endforeach;

				else :

					if( !array_key_exists(0, $this->messages)) :
						$this->messages[0] = array();
					endif;

					$this->messages[0][$message_id] = $this->set_message($message_id);

				endif;

			endif;

		endforeach;
	}

	/**
	 * Set users messages with compare $this->messages and $this->user_access
	 * @since 	1.0.0
	 * @return 	void
	 */
	protected function set_user_messages() {

		if(array_key_exists(0, $this->messages)) :
			$this->user_messages += $this->messages[0];
		endif;

		foreach( (array) $this->user_access as $product_id => $access) :

			if(array_key_exists($product_id, $this->messages)) :
				$this->user_messages += $this->messages[$product_id];
			endif;

		endforeach;
	}

	/**
	 * Prepare member messages by
	 * 1. checking current page, if is member-area
	 * 2. checking available member messages
	 * 3. checking current user product and subscription order
	 * Hooked via action template_redirect, priority 999
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function prepare_messages() {
		global $post;

		if(
			(
				sejoli_is_a_member_page() ||
				is_page_template('sejoli-member-page.php')
			) &&
			is_user_logged_in()
		) :

			$this->get_messages();
			$this->user_access = sejolisa_get_user_access_products(get_current_user_id());
			$this->set_user_messages();

		endif;

	}

	/**
	 * Display member message if any
	 * Hooked via action sejoli/member-area/header
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_messages() {

		if(0 < count($this->user_messages)) :
			foreach($this->user_messages as $message) :
				sejoli_get_template_part( 'other/message.php', array('message' => $message) );
			endforeach;
		endif;

	}
}
