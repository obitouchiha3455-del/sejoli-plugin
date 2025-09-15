<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ridwan-arifandi.com
 * @since             1.0.0
 * @package           Sejoli
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli
 * Plugin URI:        https://sejoli.co.id
 * Description:       Beautiful and powerful membership and affiliate system for WordPress. This is a standalone version, don't need to use any ecommerce plugins.
 * Version:           1.14.7
 * Requires PHP: 	    8.0
 * Author:            Sejoli
 * Author URI:        https://sejoli.co.id
 * Text Domain:       sejoli
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $sejolisa;

$sejolisa = [
	'order'        => NULL,
	'orders'       => [],
	'total-order' => [],
	'product'      => NULL,
	'products'     => [],
	'subscription' => NULL,
	'users'        => [],
	'respond'      => [],
	'messages' => [
		'info'    => [],
		'error'   => [],
		'success' => []
	]
];

/**
 * Currently plugin version.
 * Start at version 1.3.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

define( 'SEJOLISA_VERSION', 		'1.14.7' );

if(!defined('SEJOLISA_MODE')) :
define( 'SEJOLISA_MODE',			'production');
endif;

define( 'SEJOLISA_DIR',	 			plugin_dir_path(__FILE__));
define( 'SEJOLISA_URL',		 		plugin_dir_url(__FILE__));
define( 'SEJOLI_PRODUCT_CPT', 		'sejoli-product');
define( 'SEJOLI_ACCESS_CPT', 		'sejoli-access');
define( 'SEJOLI_REMINDER_CPT', 		'sejoli-reminder');
define( 'SEJOLI_MESSAGE_CPT',		'sejoli-memmessage');
define( 'SEJOLI_USER_GROUP_CPT',	'sejoli-user-group');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (strpos($errstr, '_load_textdomain_just_in_time') !== false) {
        return true;
    }
    return false;
});

if(version_compare(PHP_VERSION, '7.2.1') < 0 && !class_exists( 'WP_CLI' )) :
	add_action('admin_notices', 'sejolisa_error_php_message', 1);

	/**
	 * Display error message when PHP version is lower than 7.2.0
	 * Hooked via admin_notices, priority 1
	 * @return 	void
	 */
	function sejolisa_error_php_message() {
		?>
		<div class="notice notice-error">
			<h2>SEJOLI TIDAK BISA DIGUNAKAN DI HOSTING ANDA</h2>
			<p>
				Versi PHP anda tidak didukung oleh SEJOLI dan HARUS diupdate. Update versi PHP anda ke versi yang terbaru. <br >
				Minimal versi PHP adalah 7.2.1 dan versi PHP anda adalah <?php echo PHP_VERSION; ?>
			</p>
			<p>
				Jika anda menggunakan cpanel, anda bisa ikuti langkah ini <a href='https://www.rumahweb.com/journal/memilih-versi-php-melalui-cpanel/' target="_blank" class='button'>Update Versi PHP</a>
			</p>
			<p>
				Jika anda masih kesulitan untuk update versi PHP anda, anda bisa meminta bantuan pada CS hosting anda.
			</p>
		</div>
		<?php
	}

else :

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-sejoli-activator.php
	 */
	function activate_sejoli() {
		require_once SEJOLISA_DIR . '/includes/class-sejoli-activator.php';
		SejoliSA_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-sejoli-deactivator.php
	 */
	function deactivate_sejoli() {
		require_once SEJOLISA_DIR . '/includes/class-sejoli-deactivator.php';
		SejoliSA_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_sejoli' );
	register_deactivation_hook( __FILE__, 'deactivate_sejoli' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require SEJOLISA_DIR . '/third-parties/autoload.php';
	require SEJOLISA_DIR . '/includes/class-sejoli.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_sejoli() {

		$plugin = new Sejoli();
		$plugin->run();

	}

	run_sejoli();

	if(!function_exists('__debug')) :
	function __debug()
	{
		$bt     = debug_backtrace();
		$caller = array_shift($bt);
		$args   = [
			"file"  => $caller["file"],
			"line"  => $caller["line"],
			"args"  => func_get_args()
		];

		if ( class_exists( 'WP_CLI' ) || sejoli_is_ajax_request() ) :
			?><pre><?php print_r($args); ?></pre><?php
		else :
			do_action('qm/info', $args);
		endif;
	}
	endif;

	if(!function_exists('__print_debug')) :
	function __print_debug()
	{
		$bt     = debug_backtrace();
		$caller = array_shift($bt);
		$args   = [
			"file"  => $caller["file"],
			"line"  => $caller["line"],
			"args"  => func_get_args()
		];

		if('production' !== SEJOLISA_MODE) :
			?><pre><?php print_r($args); ?></pre><?php
		endif;
	}
	endif;

	/**
	 * Plugin update checker
	 */
	require_once(SEJOLISA_DIR . 'third-parties/sejoli-plugin-updater/update.php');
	
	$sejoli_plugin_update = new \Sejoli_Plugin_Updater\Update("https://bitbucket.org/orangerdev-team/sejoli-standalon-main-plugin", "master/", "sejoli", "bitbucket", __FILE__);
	
endif;
