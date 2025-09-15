<?php
namespace SejoliSA\Front;


class Compatibility
{
    private $selective_plugins = false;
    private $selective_themes  = false;

    private $js_enabled = [
        'src' => [],
        'key' => []
    ];

    private $css_enabled = [

    ];

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
     * Construction
     */
    public function __construct( $plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
		$this->version     = $version;

        // Set selective plugins
        $this->selective_plugins = [
            'query-monitor/query-monitor.php' => [
                'js'    => [
                    'key'   => [
                        'query-monitor'
                    ]
                ],
                'css'   => [
                    'key'   => [
                        'query-monitor'
                    ]
                ]
            ],
            'elementor/elementor.php'   => [
                'css'   => [
                    'key'   => [
                        'font-awesome', 'elementor-icons', 'elementor-animations', 'flatpickr', 'elementor-gallery', 'elementor-frontend',
                        'elementor-icons-shared-0', 'elementor-icons-fa-regular', 'elementor-icons-fa-solid', 'elementor-icons-fa-brands',
                        'elementor-select2', 'editor-preview'
                    ]
                ]
            ]
        ];
    }

    /**
     * Check loaded plugins
     * Hooked via action plugins_loaded,
     * Priority 999
     * @return void
     */
    public function check_loaded_plugins()
    {
        $found_plugins = [];

        // check if a network site
        if(function_exists('get_sites') && class_exists('WP_Site_Query')) :
            $active_plugins = get_site_option('active_sitewide_plugins');
            $found_plugins  = array_intersect_key($this->selective_plugins,$active_plugins);
        endif;

        $active_plugins = get_option('active_plugins');

        foreach($active_plugins as $_plugin) :
            if(array_key_exists($_plugin, $this->selective_plugins)) :
                $found_plugins[]    = $this->selective_plugins[$_plugin];
            endif;
        endforeach;

        if(0 < count($found_plugins)) :
            foreach($found_plugins as $_config) :
                if(isset($_config['css'])) :
                    $this->css_enabled = array_merge_recursive($this->css_enabled,$_config['css']);
                endif;

                if(isset($_config['js'])) :
                    $this->js_enabled  = array_merge_recursive($this->js_enabled,$_config['js']);
                endif;

            endforeach;
        endif;
    }

    /**
     * Check current active theme
     * Hooked via action after_setup_theme
     * Priority 999
     * @return void
     */
    public function check_current_theme()
    {
    }

    /**
     * Modify enabled CSS files
     * Hooked via filter sejoli/css/permissions, priority 999
     * @since  1.0.0
     * @access public
     * @param  array $css_files [description]
     * @return array            [description]
     */
    public function modify_css_enqeueu($css_files)
    {
        $css_files = array_merge_recursive($css_files,$this->css_enabled);
        return $css_files;
    }

    /**
     * Modify enabled JS files
     * Hooked via filter sejoli/js/permissions, priority 999
     * @since  1.0.0
     * @access public
     * @param  array $js_files [description]
     * @return array           [description]
     */
    public function modify_js_enqeueu($js_files)
    {
        $js_files = array_merge_recursive($js_files,$this->js_enabled);
        return $js_files;
    }
}
