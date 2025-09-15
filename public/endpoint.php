<?php

namespace SejoliSA\Front;

class Endpoint
{
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
     * Enable UI Framework
     *
     * @since 1.0.0
     * @access private
     * @var string/boolean
     */
    private $enable_framework = false;

	/**
     * Template request
     *
     * @since 1.0.0
     * @access private
     * @var string/boolean
     */
	private $template_file = false;

	/**
     * Action request
     *
     * @since 1.0.0
     * @access private
     * @var string/boolean
     */
	private $view_request = false;

	/**
     * Action request
     *
     * @since 1.0.0
     * @access private
     * @var string/boolean
     */
	private $action_request = false;

	/**
     * Paremeter request
     *
     * @since 1.0.0
     * @access private
     * @var string/boolean
     */
	private $parameter_request = false;

	/**
	 * Set if current request is already checked
	 * @since 	1.5.6
	 * @var 	boolean
	 */
	protected $checked = false;

	/**
	 * All affiliate related template files
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	protected $affiliate_templates = array(
		'affiliasi-bantuan',
		'affiliasi-bonus-editor',
		'affiliasi-facebook-pixel',
		'affiliasi-komisi',
		'affiliasi-kupon',
		'affiliasi-link',
		'affiliasi-order-detail',
		'affiliasi-order-filter',
		'affiliasi-order'
	);

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
    }

    /**
     *  Set end point custom menu
     *  Hooked via action init, priority 999
     *  @since 		1.0.0
     *  @since 		1.5.6	Register endpoint sejoli-api
     *  @access 	public
     *  @return 	void
     */
    public function set_endpoint()
    {
		if(false === sejolisa_check_own_license()) :
			return;
		endif;

        add_rewrite_rule( '^member-area/([^/]*)/([^/]*)/([^/]*)/?',		'index.php?member=1&view=$matches[1]&action=$matches[2]&parameter=$matches[3]','top');
		add_rewrite_rule( '^member-area/([^/]*)/([^/]*)/?',				'index.php?member=1&view=$matches[1]&action=$matches[2]','top');
		add_rewrite_rule( '^member-area/([^/]*)/?',						'index.php?member=1&view=$matches[1]','top');
		add_rewrite_rule( '^member-area/?',								'index.php?member=1&view=home','top');

		add_rewrite_rule( '^checkout/loading$',							'index.php?sejolisa_checkout_page=loading',		'top');
	    add_rewrite_rule( '^checkout/thank-you$',						'index.php?sejolisa_checkout_page=thank-you',  	'top');
		add_rewrite_rule( '^checkout/renew$',							'index.php?sejolisa_checkout_page=renew',  		'top');
		add_rewrite_rule( '^renew/([^/]*)$',							'index.php?sejolisa_checkout_page=renew&order_id=$matches[1]',	'top');

		add_rewrite_rule( '^sejoliapi/([^/]*)/?',						'index.php?sejoli-api=$matches[1]','top');

		add_rewrite_rule( '^confirm$',									'index.php?sejolisa_page=confirm','top');

        flush_rewrite_rules();
    }

    /**
     * Set custom query vars
     * Hooked via filter query_vars, priority 999
     * @since   1.0.0
     * @since 	1.5.6 	Register sejoli-api variabel
     * @access  public
     * @param   array $vars
     * @return  array
     */
    public function set_query_vars($vars)
    {
        $vars[] = 'member';
		$vars[] = 'view';
		$vars[] = 'action';
		$vars[] = 'parameter';
		$vars[] = 'order_id';
		$vars[]	= 'sejoli-api';

        return $vars;
    }

    /**
     * Check parse query and if member-area found, $enable_framework will be true
     * Hooked via action parse_query, priority 999
     * @since 	1.0.0
     * @since 	1.5.6	Add sejoliapi check request
     * @access 	public
     * @return 	void
     */
    public function check_parse_query()
    {
		global $wp_query;

		if(is_admin()) :
			return;
		endif;

        if(isset($wp_query->query_vars['member'])) :

			if(isset($wp_query->query_vars['action']) && !empty($wp_query->query_vars['action'])) :
				$this->action_request = $wp_query->query_vars['action'];
			endif;

			if(isset($wp_query->query_vars['parameter']) && !empty($wp_query->query_vars['parameter'])) :
				$this->parameter_request = $wp_query->query_vars['parameter'];
			endif;

			$view = get_query_var('view');

			$this->template_file = $this->view_request 	= $view;
            $this->enable_framework = true;

		elseif( isset($wp_query->query_vars['sejoli-api']) ) :

			$hook_action = 'sejoli-api/' . sanitize_title($wp_query->query_vars['sejoli-api']);

			if( has_action($hook_action) ) :

				header('Content-Type: application/json');

				do_action( $hook_action );

				exit;

			else :

                wp_die(
					__('Not valid Sejoli API request', 'sejoli'),
					__('Not valid API', 'sejoli')
                );

            endif;

            exit;

        endif;
    }
 
    /**
     * Set to enable or disable framekwork
     * Hooked via action sejoli/enable, priority 999
     * @since   1.0.0
     * @access  public
     * @param   boolean $enable
     * @return  boolean
     */
    public function set_enable_framework($enable = false)
    {
        return $this->enable_framework;
    }

	/**
	 * Set template file
	 * Hooked via filter template_include, priority 999
	 * @since 	1.0.0
	 * @since 	1.4.0 	Add filtering for affiliate template file
	 * @access  public
	 * @param 	string $template_file
	 * @return 	string;
	 */
	public function set_template_part($template_file = '')
	{
		if($this->enable_framework && false !== $this->template_file) :

			$current_user_group  = sejolisa_get_user_group();
			$no_access_affiliate = boolval(sejolisa_carbon_get_theme_option('sejoli_no_access_affiliate'));

			// Need to be factored later
			if(
				!sejolisa_check_user_can_access_affiliate_page() &&
				in_array($this->template_file, $this->affiliate_templates)
			) :
				$this->template_file = 'no-affiliate';
			endif;

			$directory = apply_filters('sejoli/template-directory',SEJOLISA_DIR . 'template/');
			$file      = $directory.$this->template_file.'.php';
			$file      = apply_filters('sejoli/template-file', $file, $this->template_file);

			if(file_exists($file)) :
				$template_file = $file;
			else:
				$template_file = safe_str_replace($this->template_file, '404', $file);
			endif;

		endif;

		return $template_file;
	}

	/**
	 * Get requested data
	 * Hooked via filter sejoli/get-request,priority 999
	 * @param  array  $args [description]
	 * @return array
	 */
	public function get_request($args = array())
	{
		$args 	= wp_parse_args($args,[
			'member'	=> $this->enable_framework,
			'view'		=> $this->view_request,
			'action'	=> $this->action_request,
			'parameter'	=> $this->parameter_request
		]);

		return $args;
	}

	/**
	 * Check page request
	 * Hooked via action template_redirect, priority 999
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function check_page_request()
	{
		$action  = [];
		$request = $this->get_request();

		if(false !== $request['member']) :

			if(false !== $request['view']) :

				$action[]	= $request['view'];

				if(false !== $request['action']) :
					$action[]	= $request['action'];
				endif;

			endif;

		endif;

		if(is_array($action) && 0 < count($action)) :
			do_action( 'sejoli/'.implode('/',$action), $request['parameter']);
		endif;
	}

	/**
	 * Add custom class to wordpress body
	 * Hooked via filter body_class, priority 999
	 * @param 	array $classes
	 * @return 	array
	 */
	public function add_body_classes($classes = array())
	{
		$request = $this->get_request();

		if(false !== $request['member']) :

			$classes[]	= 'sejoli';

			if(false !== $request['view']) :
				$classes[]	= $request['view'];

				if(false !== $request['action']) :
					$classes[]	= sanitize_title($request['view'].' '.$request['action']);

					if(false !== $request['parameter']) :
						$classes[]	= sanitize_title($request['view'].' '.$request['action'].' '.$request['parameter']);
					endif;
				endif;

			endif;
		endif;

		return $classes;
	}
}
