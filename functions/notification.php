<?php
/**
 * Get email content from given template
 * @since   1.0.0
 * @param   string      $filename   The filename of notification
 * @param   string      $media      Notification media, default will be email
 * @param   null|array  $args       Parsing variables
 * @return  null|string
 */
function sejoli_get_notification_content($filename, $media = 'email', $vars = NULL) {
    $content    = NULL;
    $directory  = apply_filters(
                    'sejoli/'. $media .'/template-directory',
                    SEJOLISA_DIR . 'template/' .$media. '/',
                    $filename,
                    $media,
                    $vars);

    $email_file = $directory . $filename . '.php';

    if(file_exists($email_file)) :

        if(is_array($vars)) :
            extract($vars);
        endif;

        ob_start();
        require $email_file;
        $content = ob_get_contents();
        ob_end_clean();
        
    endif;

    return $content;
}
