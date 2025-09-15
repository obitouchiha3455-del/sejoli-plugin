<?php

namespace SejoliSA\Front;

class Acquisition {

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
	 * Check if affiliate or adminitrator click will be tracked
	 * @since 	1.0.0
	 * @param  	int     	$affiliate_id 	The ID of affiliate
	 * @param  	boolean 	$need_to_check  Given condition if it needs to be checked
	 * @return 	boolean						Condition if current click is need to be calculated
	 */
	protected function can_selected_users_be_able_to_get_tracked(int $affiliate_id, $need_to_check = true) {

		$current_user = intval(get_current_user_id());

		return (
			(
				$need_to_check &&
				$current_user !== $affiliate_id && !current_user_can('manage_options')
			) ||
			!$need_to_check
		);
	}

    /**
     * Add acquisition data to affiliate cookie
     * Hooked via filter sejoli/affiliate/cookie-data, priority 10
     * @since   1.0.0
     * @param   array   $cookie_data    Current cookie data
     * @param   int     $product_id     The ID of affiliate product ID
     * @param   int     $affiliate_id   The ID of affiliate ID
     * @return  array   Manipulated cookie data
     */
    public function add_acquisition_data_to_cookie(array $cookie_data, int $product_id, int $affiliate_id) {

        if(
			isset($_GET['utm_source']) && !empty($_GET['utm_source']) &&
			$this->can_selected_users_be_able_to_get_tracked($affiliate_id, false)
		) :

            $cookie_data['acq'][$product_id] = array();
            $cookie_data['acq'][$product_id]['source'] = $source = $_GET['utm_source'];
            $media = '';

            if(isset($_GET['utm_media']) && !empty($_GET['utm_media'])) :
                $cookie_data['acq'][$product_id]['media'] = $media = $_GET['utm_media'];
            endif;

            sejolisa_update_acquisition_value([
                'affiliate_id' => $affiliate_id,
                'product_id'   => $product_id,
                'source'       => $source,
                'media'        => $media
            ], 'view');

        endif;

        return $cookie_data;
    }

}
