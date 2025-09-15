<?php

namespace SejoliSA\Front;

class Login
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
     * Construction
    */
    public function __construct( $plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Replace default register url with sejoli register url
     * @since   1.1.5
     * @param   string  $register_url   Default registration url
     * @return  string  Modified registration url
     */
    public function register_url($register_url) {
        return sejoli_get_endpoint_url('register');
    }

    /**
     * Replace default login url with sejoli login url
     * @since   1.1.5
     * @param   string  $login_url   Default login url
     * @return  string  Modified login url
     */
    public function login_url($login_url) {
        return sejoli_get_endpoint_url('login');
    }

    /**
     * Add custom CSS and JS to default WordPress login form
     * Hooked via action login_enqueue_scripts, priority 100
     * @since   1.1.5
     * @return  void
     */
    public function modify_login_form() {

        $image_id = sejolisa_carbon_get_theme_option('sejoli_setting_logo');

        if(!empty($image_id) && function_exists('wp_get_attachment_image_src')) :

            $image = wp_get_attachment_image_src($image_id, 'full');

            if(false !== $image) :

                list($image_url, $width, $height) = $image;

                ?><style type="text/css">
                    #login h1 a, .login h1 a {
                    background-image: url(<?php echo $image_url ?>);
            		height:<?php echo $height; ?>;
            		width:<?php echo $width; ?>;
            		background-size: <?php echo $width; ?> <?php echo $height; ?>;
            		background-repeat: no-repeat;
                    }
                </style>
                <?php

            endif;

        endif;

        wp_enqueue_style(  'sejoli-wp-login', SEJOLISA_URL . 'public/css/wp-login.css', [], $this->version );
        wp_enqueue_script( 'jquery' );
    }

    /**
     * Modify login header url
     * @since   1.1.5
     * @param   string   $url
     * @return  string
     */
    public function login_header_url($url) {
        return sejoli_get_endpoint_url();
    }

    /**
     * Modify login header title
     * @since   1.1.5
     * @param   string    $title
     * @return  string
     */
    public function login_header_title($title) {
        return $title;
    }

    /**
     * Add custom JS script for login
     * Hooked via action login_footer, priority 1
     * @since   1.1.5
     * @return  void
     */
    public function add_js_script() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function(){
            console.log('test');
            jQuery("input[name='redirect_to']").val('<?php echo add_query_arg(array('action' => 'rp'), sejoli_get_endpoint_url('login')); ?>');
        });
        </script>
        <?php
    }

    /**
     * Check if current user is logged in, if user not logged in will be redirect to login page
     * Hooked via action template_redirect, priority 999
     * @since   1.0.0
     * @since   1.5.0   Change way to check if current user can access wp-admin
     * @return void
     */
    public function check_user_login()
    {
        if(true === sejoli_is_a_member_page('login') && is_user_logged_in()) :

            $redirected_url = sejolisa_get_default_member_area_url();

            if(current_user_can('manage_sejoli_orders')) :
                $redirected_url = admin_url();
            endif;

            wp_redirect($redirected_url);
            exit;

        // If current page is login and user is not logged in
        elseif(
            true === sejoli_is_a_member_page('login') ||
            true === sejoli_is_a_member_page('register') &&
            !is_user_logged_in()
        ) :

            return;

        // If current page is member area but user is not logged in
        elseif(
            true === sejoli_is_a_member_page() &&
            !is_user_logged_in()
        ) :

            wp_redirect(sejoli_get_endpoint_url('login'));
            exit;
        endif;
    }

    /**
     * Check login
     * Hooked via action sejoli/login
     * @return void
     */
    public function check_login()
    {
        $messages = array();

        if(isset($_POST['sejoli-nonce']) && wp_verify_nonce($_POST['sejoli-nonce'],'user-login')) :

            $email = $_POST['email'];
            $login = NULL;

            // If user using username for logged in
            if(username_exists($email)) :

                $user  = get_user_by('login', $email);
                $login = $user->user_login;

            endif;

            // If user using user email for logged in
            if(is_email($email) && email_exists($email)) :

                $user  = get_user_by('email', $email);
                $login = $user->user_login;

            endif;

            $data = [
                'user_login'    => $login,
                'user_password' => $_POST['password']
            ];

            $user = wp_signon($data);

            if(is_wp_error($user)) :

                $messages[] = __('Something wrong with your login.','sejoli');

                $messages = array_merge( $messages, $user->get_error_messages());

                do_action('sejoli/set-messages', $messages, 'error');

            else :
                $redirected_url = sejolisa_get_default_member_area_url();

                if( sejolisa_user_can_access_wp_admin() ) :
                    $redirected_url = admin_url();
                endif;

                wp_redirect($redirected_url);
                exit;
            endif;

        endif;
    }

    /**
     * Display info for reset password request
     * Hooked via action sejoli/login/rp, priority 999
     * @since   1.1.5
     * @return  void
     */
    public function info_reset_password() {
        $messages = array();

        $messages[] = __('Silahkan cek email anda untuk informasi terkait pergantian password', 'sejoli');

        do_action('sejoli/set-messages', $messages, 'info');
    }
}
