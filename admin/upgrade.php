<?php

namespace SejoliSA\Admin;

class Upgrade {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * Set version that needed the update
     * @since   1.5.1
     * @var     array
     */
    private $updated_versions = array(
        '1.5.1' // Add colum key in sejolisa_orders table
    );

    /**
     * Updated to version
     * @since   1.5.1
     * @var     string
     */
    private $updated_to = NULL;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.5.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     * Check if current sejoli need update or no
     * @since   1.5.1
     * @return  boolean
     */
    protected function do_need_update() {

        $current_version = get_option('sejoli_version');

        foreach( $this->updated_versions as $version ) :

            if( version_compare( $current_version, $version, '<' ) ) :

                $this->updated_to = $version;

                return true;

                break;

            endif;

        endforeach;

        return false;

    }

    /**
     * Do upgrade
     * Hooked via action wp_ajax_sejoli-upgrade-system, priority 1
     * @since   1.5.1
     * @return  string   JSON data
     */
    public function do_upgrade() {

        $response = array(
            'success'   => false,
            'message'   => __('Maaf, anda tidak bisa melakukan upgrade database', 'sejolis')
        );

        $post_data  = wp_parse_args($_POST, array(
            'sejoli-nonce'  => '',
            'step'          => NULLs
        ));

        if(
            wp_verify_nonce( $post_data['sejoli-nonce'], 'sejoli-upgrade-system') &&
            $this->do_need_update()
        ) :

            $response['success'] = true;
            $response['message'] = sprintf( __('Update ke versi %s', 'sejoli'), $this->updated_to );

        endif;

        wp_send_json($response);
        exit;
    }

    /**
	 * Display update notice
	 * Hooked via action admin_notices, priority 2
	 * @since 	1.5.1
	 * @return 	void
	 */
	public function display_notice() {

		if(
            $this->do_need_update() &&
            ! (
                isset($_GET['page']) &&
                'sejoli-system-upgrade' === $_GET['page']
            )
        ) :
			require_once( plugin_dir_path( __FILE__ ) . 'partials/upgrade/notice.php' );
		endif;

	}

    /**
	 * Register upgrade database menu under sejoli main menu
	 * Hooked via action admin_menu, priority 9999999
	 * @since 	1.5.1
	 * @return 	void
	 */
	public function register_admin_menu() {

		add_submenu_page(
			'crb_carbon_fields_container_sejoli.php',
			__('Upgrade Sistem', 'sejoli'),
			__('Upgrade Sistem', 'sejoli'),
			'manage_options',
			'sejoli-system-upgrade',
			[$this, 'display_upgrade_page']
		);

	}

    /**
     * Display system upgrade page
     * @since   1.5.1
     * @return  void
     */
    public function display_upgrade_page() {

        require_once( plugin_dir_path( __FILE__ ) . 'partials/upgrade/page.php' );

    }

    public function update_to_1_5_1() {

    }
}
