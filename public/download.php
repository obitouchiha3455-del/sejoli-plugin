<?php

namespace SejoliSA\Front;

class Download {

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
	 * User ID from download link
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	integer
	 */
	protected $user_id;

	/**
	 * Product ID from download link
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	integer
	 */
	protected $product_id;

	/**
	 * Attachment ID from download link
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	integer
	 */
	protected $attachment_id;

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
		add_rewrite_rule( '^member-download/([^/]*)/?',						'index.php?member-download=1&download-key=$matches[1]','top');

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
        $vars[] = 'member-download';
		$vars[] = 'download-key';

        return $vars;
    }

	/**
	 * Generate download file
	 * @since 	1.0.0
	 * @return 	void
	 */
	protected function generate_download_file($speed = null, $multipart = true) {

		$file           = get_post($this->attachment_id);
		$default_upload = wp_upload_dir();
		$file_info      = pathinfo($file->guid);
		$home_path      = $default_upload['basedir'];
		$path           = $home_path .'/' . get_post_meta($this->attachment_id, '_wp_attached_file', true);

		while (ob_get_level() > 0) :
			ob_end_clean();
		endwhile;

		if (false !== is_file($path = realpath($path)) ) :

			$file  = @fopen($path, 'rb');
			$size  = sprintf('%u', filesize($path));
			$speed = (empty($speed) === true) ? 1024 : floatval($speed);

			if (true === is_resource($file) ) :

				set_time_limit(0);

				if (strlen(session_id()) > 0) :
					session_write_close();
				endif;

				if ($multipart === true) :

					$range = array(0, $size - 1);

					if (array_key_exists('HTTP_RANGE', $_SERVER) === true) :

						$range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $_SERVER['HTTP_RANGE'])));

						if (empty($range[1]) === true) :
							$range[1] = $size - 1;
						endif;

						foreach ($range as $key => $value) :
							$range[$key] = max(0, min($value, $size - 1));
						endforeach;

						if (($range[0] > 0) || ($range[1] < ($size - 1))) :
							header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
						endif;

					endif;

					header('Accept-Ranges: bytes');
					header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
				else :
					$range = array(0, $size - 1);
				endif;

				header('Pragma: public');
				header('Cache-Control: public, no-cache');
				header('Content-Type: application/octet-stream');
				header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
				header('Content-Disposition: attachment; filename="' . $file_info['basename'] . '"');
				header('Content-Transfer-Encoding: binary');

				if ($range[0] > 0) :
					fseek($file, $range[0]);
				endif;

				while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL)) :
					echo fread($file, round($speed * 1024)); flush(); sleep(1);
				endwhile;

				fclose($file);
			endif;

			exit();
		else :
			header(sprintf('%s %03u %s', 'HTTP/1.1', 404, 'Not Found'), true, 404);
		endif;

		exit;
	}

    /**
     * Check parse query and if member-download found, it will create download process
     * Hooked via action parse_query, priority 999
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function check_parse_query()
    {
		global $wp_query;

		if(!$wp_query):
			return;
		endif;

        if(
			!is_admin() &&
			is_array($wp_query->query) &&
			array_key_exists('member-download', $wp_query->query) &&
            true === boolval($wp_query->query['member-download']) &&
            !empty(get_query_var('download-key'))
        ) :
			$can_access = true;

            list($user_id, $product_id, $attachment_id) = explode(':::', sejolisa_encrypt_decrypt('decrypt', get_query_var('download-key')));

			if(!is_user_logged_in()) :
				$can_access = false;
				$content 	= sprintf(
								__('Maaf, anda harus login terlebih dahulu untuk bisa mendownload file ini.<br>Silahkan login <a href="%s">disini</a>', 'sejoli'),
								home_url('member-area')
							  );
			endif;

			if(get_current_user_id() !== intval($user_id) ):
				$can_access = false;
				$content 	= __('Maaf, anda tidak berhak untuk mendownload file ini', 'sejoli');
			endif;

			if(true !== sejolisa_does_user_have_access($user_id, $product_id)) :
				$can_access = false;
				$content 	= __('Maaf, anda tidak berhak untuk mendownload file ini karena anda belum membeli produk yang terkait', 'sejoli');
			endif;

			if(true === $can_access) :
				$this->attachment_id = $attachment_id;
				$this->generate_download_file();
			else :
				wp_die(
					$content,
					__('Tidak diperbolehkan untuk mendownload', 'sejoli')
				);
			endif;

            exit;

        endif;
    }

}
