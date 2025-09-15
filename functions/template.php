<?php
/**
 * Check if requested page is registered
 * @param  string   $page
 * @return boolean
 */
function sejoli_is_a_member_page($page = '')
{
    $valid = false;

    $request = sejoli_get_request();

    if('' === $page && true === $request['member']) :
        $valid = true;
    elseif($page === $request['view']) :
        $valid = true;
    endif;

    return $valid;
}

/**
 * Return with endpoint url
 * @param  string   $view
 * @param  string   $action     (mandatory)
 * @param  string   $parameter  (mandatory)
 * @return string
 */
function sejoli_get_endpoint_url($view = '', $action = NULL, $parameter = NULL)
{
    $link = home_url();

    /** using permalink **/
    if(get_option('permalink_structure')) :

        $view = rtrim($view, '/');
        $link = home_url().'/member-area/'.$view;

        if(!empty($action)) :
            $link .= '/'.$action;

            if(!empty($parameter)) :
                $link .= '/'.$parameter;
            endif;
        endif;
    else :
        $link   = add_query_arg([
            'member'    => true,
            'view'      => $view,
            'action'    => $action,
            'parameter' => $parameter
        ],$link);
    endif;

    return $link;
}

/**
 * Get current page request
 * @return array
 */
function sejoli_get_request()
{
    return apply_filters('sejoli/get-request',[]);
}

/**
 * Get member area home url
 * @return string
 */
function sejoli_get_home_url()
{
    $link = sejoli_get_endpoint_url('home');

    return apply_filters('sejoli/url/dashboard',$link);
}

/**
 * Get template path and include it if exists
 * @since   1.0.0
 * @param   string  $file
 * @param   array   $vars   Parse variables
 * @return  void
 */
function sejoli_get_template_part($template_file, $vars = array())
{
    $file = SEJOLISA_DIR. '/template/'.$template_file;
    $file = apply_filters('sejoli/locate-template',$file,$template_file);

    if(file_exists($file)) :
        include($file);
    endif;
}

/**
 * Get header template file
 * @param  string $slug
 * @return void
 */
function sejoli_header($slug = '')
{
    $file = 'header';
    $file = ('' !== $slug) ? $file . '-'.$slug : $file;

    sejoli_get_template_part($file.'.php');
}

/**
 * Get footer template file
 * @param  string $slug
 * @return void
 */
function sejoli_footer($slug = '')
{
    $file = 'footer';
    $file = ('' !== $slug) ? $file . '-'.$slug : $file;

    sejoli_get_template_part($file.'.php');
}

/**
 * Get logo
 * @return string
 */
function sejolisa_logo_url()
{
    $attachment_id = intval(sejolisa_carbon_get_theme_option('sejoli_setting_logo'));

    $logo = wp_get_attachment_url( $attachment_id );

    if ( !$logo ) :
        $logo = SEJOLISA_URL . '/template/images/logo.png';
    endif;

    $logo = apply_filters('sejoli/template-logo',$logo);

    return $logo;
}

function sejolisa_desain_logo_url( $post_id )
{
    $attachment_id = intval(sejolisa_carbon_get_post_meta($post_id,'desain_logo'));
    if ( empty( $attachment_id ) ) :
        $attachment_id = intval(sejolisa_carbon_get_theme_option('desain_logo'));
    endif;

    $logo = wp_get_attachment_url( $attachment_id );

    // if ( !$logo ) :
    //     $logo = SEJOLISA_URL.'public/img/default-logo.png';
    // endif;

    $logo = apply_filters('sejoli/desain/logo/'.$post_id.'', $logo, $post_id );

    return $logo;
}

/**
 * Get public asset url
 * @since   1.2.0
 * @param   string           $file   Filename
 * @return  string|false
 */
function sejolisa_get_public_assets($file) {

    if( file_exists( SEJOLISA_DIR . 'public/' . $file ) ):
        return SEJOLISA_URL . 'public/' . $file;
    endif;

    return false;
}
