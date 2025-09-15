<?php

/**
 * Fired during plugin activation
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli
 * @subpackage Sejoli/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sejoli
 * @subpackage Sejoli/includes
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class SejoliSA_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		\SejoliSA\Model\Acquisition::create_table();
		\SejoliSA\Model\Affiliate::create_table();
		\SejoliSA\Model\Confirmation::create_table();
		\SejoliSA\Model\Coupon::create_table();
		\SejoliSA\Model\License::create_table();
		\SejoliSA\Model\Order::create_table();
		\SejoliSA\Model\Reminder::create_table();
		\SejoliSA\Model\Subscription::create_table();

		flush_rewrite_rules();
	}

}
