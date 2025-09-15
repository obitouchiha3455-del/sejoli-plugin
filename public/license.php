<?php

namespace SejoliSA\Front;

class License {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Set if there is already checked action
	 * @since 	1.0.0
	 * @var 	boolean
	 */
	protected $checked = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     *  Set end point custom menu
     *  Hooked via action init, priority 999
     *  @since 1.0.0
     *  @access public
     *  @return void
     */
    public function set_endpoint()
    {
		add_rewrite_rule( '^sejoli-license/?',				'index.php?sejoli-license=1','top');
		add_rewrite_rule( '^sejoli-validate-license/?',		'index.php?sejoli-validate-license=1','top');
		add_rewrite_rule( '^sejoli-delete-license/?',		'index.php?sejoli-delete-license=1','top');

        flush_rewrite_rules();
    }

    /**
     * Set custom query vars
     * Hooked via filter query_vars, priority 999
     * @since   1.0.0
     * @access  public
     * @param   array $vars
     * @return  array
     */
    public function set_query_vars($vars = array())
    {
        $vars[] = 'sejoli-license';
		$vars[] = 'sejoli-validate-license';
		$vars[] = 'sejoli-delete-license';

        return $vars;
    }

	/**
	 * Register license request
	 * @since 	1.4.1
	 * @param  	array 	$params
	 * @return 	void 	echo JSON response
	 */
	protected function register_license($params) {

		$post_data      = wp_parse_args($params,[
			'license'    => NULL,
			'string'     => NULL,
			'user_email' => NULL,
			'user_pass'  => NULL,
		]);

		$data = [
			'user_email' => trim(sanitize_email($post_data['user_email'])),
			'user_pass'  => trim(sanitize_text_field($post_data['user_pass'])),
			'license'    => trim(sanitize_text_field($post_data['license'])),
			'string'     => trim(sanitize_text_field($post_data['string']))
		];

		$this->checked = true;

		$available = apply_filters('sejoli/license/availability', NULL, $data);

		wp_send_json($available);
	}

	/**
	 * Set order detail for response
	 * @since 	1.4.1
	 * @param 	integer 	$order_id
	 * @return 	array
	 */
	protected function set_order_detail($order_id) {

		$response = array();
		$order_response = sejolisa_get_order(array('ID' => $order_id));

		if(false !== $order_response['valid']) :

			$end_date_timestamp = $end_date = NULL;
			$subscription_response = sejolisa_check_subscription($order_id);

			if(false !== $subscription_response['valid']) :
				$end_date           = $subscription_response['subscription']->end_date;
				$status             = $subscription_response['subscription']->status;
				$end_date_timestamp = strtotime($end_date);
			endif;

			$response = array(
				'order-id'           => $order_response['orders']['ID'],
				'product-id'         => $order_response['orders']['product']->ID,
				'post-name'          => $order_response['orders']['product']->post_name,
				'post-title'         => $order_response['orders']['product']->post_title,
				'end-date'           => $end_date,
				'status'             => $status
			);

		endif;

		return $response;
	}

	/**
	 * Validate license
	 * @since 	1.4.1
	 * @param  	array 	$params
	 * @return 	void 	echo JSON response
	 */
	protected function validate_license($params) {

		$response 	= [
			'valid'   => false,
			'message' => ''
		];

		$post_data      = wp_parse_args($params,[
			'host' 		=> NULL,
			'string'    => NULL,
			'license'	=> NULL,
		]);

		if(!empty($post_data['host']) || !empty($post_data['string']) ):

			$string 			= (empty($post_data['host'])) ? $post_data['string'] : $post_data['host'];
			$check_response 	= sejolisa_get_license_by_string($string);

			if(
				false !== boolval($check_response['valid']) &&
				'active' === $check_response['licenses']['status'] || false !== boolval($check_response['valid']) &&
				'inactive' === $check_response['licenses']['status']
			) :

				$this->checked      = true;
				$license            = $check_response['licenses'];
				$response['valid']  = true;
				$response['detail'] = $this->set_order_detail($license['order_id']);

			else :
				$response['message'] = isset($check_response['messages']) ? $check_response['messages']['error'][0] : '';
			endif;
		else :
			$response['message']	= __('String cant be empty', 'sejoli');
		endif;

		wp_send_json($response);
	}

	/**
	 * Delete license request
	 * @since 	1.4.1
	 * @param  	array 	$params
	 * @return 	void 	echo JSON response
	 */
	protected function delete_license($params) {

		$response 	= [
			'valid'   => false,
			'reason'  => false,
			'message' => ''
		];

		$post_data      = wp_parse_args($params,[
			'license'    => NULL,
			'string'     => NULL,
			'user_email' => NULL,
			'user_pass'  => NULL,
		]);

		$data = [
			'user_email' => trim(sanitize_email($post_data['user_email'])),
			'user_pass'  => trim(sanitize_text_field($post_data['user_pass'])),
			'license'    => trim(sanitize_text_field($post_data['license'])),
			'string'     => trim(sanitize_text_field($post_data['string']))
		];

		$this->checked = true;

		$user = wp_authenticate($data['user_email'], $data['user_pass']);

		if(is_wp_error($user)) :
			$response['reason']  = 'user-not-valid';
			$response['message'] = implode('<br/>', $user->get_error_messages());
		else :

			$found_string 	  = false;
			$license_response = sejolisa_get_license_by_code($data['license']);

			if(false !== $license_response['valid']) :

				foreach($license_response['licenses'] as $license) :

					if($data['string'] === $license->string) :

						sejolisa_reset_licenses(
							array($license->ID),
							true
						);

						$found_string        = true;
						$response['valid']   = true;
						$response['message'] = sprintf(__('Lisensi %s untuk %s telah dihapus', 'sejoli'), $data['license'], $license->string);
						$response['detail']	 = $this->set_order_detail($license->order_id);

						break;

					endif;

				endforeach;

			endif;

			if(false === $found_string) :

				$response['reason']  = 'string-not-found';
				$response['message'] = sprintf(__('Lisensi %s untuk %s tidak ditemukan', 'sejoli'), $data['license'], $data['string']);

			endif;

		endif;

		wp_send_json($response);
	}

    /**
     * Check parse query and if aff found, $enable_framework will be true
     * Hooked via action parse_query, priority 999
     * @since 	1.0.0
     * @since 	1.4.1 	Refactor and add new nmethod to reset license
     * @access 	public
     * @return 	void
     */
    public function check_parse_query()
    {
        global $wp_query;

        if($wp_query) :

			if(is_admin()) :
				return;
			endif;

			if(false !== $this->checked) :
				return;
			endif;

			// Register license
	        if(array_key_exists('sejoli-license', $wp_query->query_vars)) :

				$this->register_license($_POST);

	            exit;

			// Validate license
			elseif(array_key_exists('sejoli-validate-license', $wp_query->query_vars)) :

				$this->validate_license($_GET);

				exit;

			// Delete license
			elseif(array_key_exists('sejoli-delete-license', $wp_query->query_vars)) :

				$this->delete_license($_POST);

				exit;

	        endif;

	    endif;
	    
    }
}
