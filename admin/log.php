<?php

namespace SejoliSA\Admin;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Log {

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
	 * Logger handler
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	Logger
	 */
	protected $logger;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since	1.0.0
	 * @since 	1.2.3 		Register cron job for remove logs
	 * @param   string    	$plugin_name       The name of this plugin.
	 * @param   string    	$version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$log_file     = $this->get_log_file();

		$formatter    = new LineFormatter(
		    null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
		    null, // Datetime format
		    true, // allowInlineLineBreaks option, default false
		    true  // discard empty Square brackets in the end, default false
		);
		$stream       = new StreamHandler( $log_file, Logger::DEBUG );
		$stream->setFormatter($formatter);

		$this->logger = new Logger('sejoli');

		$this->logger->pushHandler($stream);

		// delete coupon post
		if(false === wp_next_scheduled('sejoli/log/delete')) :

			wp_schedule_event(time(), 'daily', 'sejoli/log/delete');

		else :

			$recurring 	= wp_get_schedule('sejoli/log/delete');

			if('daily' !== $recurring) :
				wp_reschedule_event(time(), 'daily', 'sejoli/log/delete');
			endif;

		endif;
	}

	/**
	 * Create htaccess to block any direct access to file log
	 * @since 	1.5.6
	 * @return 	void
	 */
	protected function create_htaccess( $directory, $htaccess ) {

		chmod($directory, 0755);

		$file = fopen( $htaccess, 'w');

		ob_start();

		?>RewriteRule ^.*\.(log)$ - [F,L,NC]<?php

		$content = ob_get_contents();
		ob_end_clean();

		fclose($file);

		chmod($directory, 0444);
	}

	/**
	 * Get log directory path
	 * @since 	1.0.0
	 * @return 	string
	 */
	public function get_log_directory() {

		$log_directory = WP_CONTENT_DIR . '/sejoli-log/';

		if(!file_exists($log_directory)) :
			mkdir($log_directory);
		endif;

		$htaccess = $log_directory . '.htaccess';

		if(!file_exists($htaccess)) :
			$this->create_htaccess( $log_directory, $htaccess );
		endif;

		return $log_directory;
	}

	/**
	 * Get log file
	 * @since 	1.0.0
	 * @return 	string
	 */
	public function get_log_file() {

		$log_directory     = $this->get_log_directory();
		$log_file          = date('Y-m-d') . '.log';

		return $log_directory .  $log_file;
	}

	/**
	 * Write log
	 * Hooked via action sejoli/log/write, 1
	 * @since 	1.0.0
	 * @since 	1.6.0	Fix issue with log writing
	 * @param  	string 	$event
	 * @param  	any 	$args
	 * @return 	void
	 */
    public function write_log($event, $args) {

		$enable_log = boolval(sejolisa_carbon_get_theme_option('sejoli_enable_log'));

		if(true !== $enable_log) :
			return;
		endif;

		$file = $this->get_log_file();

		$directory = $this->get_log_directory();

		chmod($directory, 0755);

		if(file_exists($file)):
			chmod($file, 0777);
		endif;	

		ob_start();

		$this->logger->info( sprintf( 'EVENT [%s] - Args : %s', $event, print_r($args, true) ));

		$content = ob_get_contents();
		ob_end_clean();

		chmod($directory, 0444);

    }

	/**
	 * Add log submenu under sejoli
	 * Hooked via action admin_menu, priority 1010
	 * @since 	1.2.3
	 * @return 	void
	 */
	public function register_admin_menu() {

		add_submenu_page(
            'crb_carbon_fields_container_sejoli.php',
            __('Log', 'sejoli'),
            __('Log', 'sejoli'),
            'manage_sejoli_orders',
            'sejoli-log',
            [$this, 'display_page']
        );
	}

	/**
	 * Register custom CSS and JS for log page
	 * Hooked via action admin_enqueue_scripts, priority 100
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function register_css_and_js() {

		if(isset($_GET['page']) && 'sejoli-log' === $_GET['page']) :

			wp_enqueue_script	( 'enlighterjs', SEJOLISA_URL . 'admin/js/enlighterjs.min.js', 		array(), '3.1.0', true);
			wp_enqueue_style	( 'enlighterjs', SEJOLISA_URL . 'admin/css/enlighterjs.min.css', 	array(), '3.1.0', 'all');

		endif;

	}

	/**
	 * Add inline CSS
	 * Hooked via action admin_head, priority 100
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function set_inline_style() {

		if(isset($_GET['page']) && 'sejoli-log' === $_GET['page']) :
		endif;

	}

	/**
	 * Display page for log
	 * @since 	1.2.3
	 * @return 	void
	 */
	public function display_page() {
		require plugin_dir_path( __FILE__ ) . 'partials/log/page.php';
	}

	/**
	 * Remove sejoli log
	 * Hooked via action sejoli/log/delete, prirotiy 100
	 * @since 	1.2.3
	 * @return 	void
	 */
	public function delete_logs() {

		$path = $this->get_log_directory();

		if ($handle = opendir($path)) :

			while (false !== ($file = readdir($handle))) :

				if(in_array($file, array('.', '..'))) :
					continue;
				endif;

				$last_modified = filemtime($path . $file);

				if(!file_exists($file)):
					return false;
				endif;

				if((time() - $last_modified) > (30 * DAY_IN_SECONDS)) :
					unlink($path . $file);
				endif;

			endwhile;

			closedir($handle);
			
		endif;

		exit;

	}

	/**
	 * Get log content by AJAX
	 * Hooked via action wp_ajax_sejoli-read-log, priority 1
	 * @since 	1.0.0
	 * @since 	1.5.6	Change file and folder permission
	 * @return 	string	HTML content, not json
	 */
	public function get_log_content() {

		$file      = $_GET['file'];
		$path      = $this->get_log_directory();
		$file_path = $path . $file;

		if(file_exists($file_path)):

			chmod( $path, 0755);
			chmod( $file_path, 0775 );

			$myfile = fopen( $file_path, 'r') or die('Unable to open file!');

			echo htmlspecialchars( fread($myfile, filesize($path . $file)) );

			fclose($myfile);

			chmod( $path, 0744);
			chmod( $file_path, 0444 );

		endif;

		exit;
	
	}
}
