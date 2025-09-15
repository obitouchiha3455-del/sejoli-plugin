<?php
/**
 * Get sejolisa respond
 * @since   1.0.0
 * @return  array
 */
function sejolisa_get_respond($type = '') {
    global $sejolisa;

    if(!empty($type) && isset($sejolisa['respond'][$type][0]) ) :
        return $sejolisa['respond'][$type][0];
    endif;

    return $sejolisa['respond'];
}

/**
 * Set respond to global data
 * @since   1.0.0
 * @param   mixed   $data
 * @param   string  $type
 */
function sejolisa_set_respond($data, $type = 'other') {
    global $sejolisa;

    $sejolisa['respond'][$type][] = $data;
}

/**
 * Set message
 * @since   1.0.0
 * @param   string $message
 * @param   string $type
 */
function sejolisa_set_message($message, $type = 'error') {
    global $sejolisa;
    $sejolisa['messages'][$type][] = $message;
}

/**
 * Get $messages
 * @since  1.0.0
 * @param  string $type
 * @return array
 */
function sejolisa_get_messages($type = 'error') {
    global $sejolisa;
    return isset($sejolisa['messages'][$type]) ? $sejolisa['messages'][$type] : [];
}

/**
 * Get email with current domain
 * @since   1.0.0
 * @param   string  $email_name
 * @return  string
 */
function sejolisa_get_email_domain($email_name) {
    return (isset($_SERVER['HTTP_HOST'])) ? $email_name.'@'.$_SERVER['HTTP_HOST'] : 'info@test.com';
}

/**
 * Return all available colors
 * @since   1.0.0
 * @return  array
 */
function sejolisa_get_all_colors() {

    $code = dechex(crc32('payment-confirm'));
    $confirm_color = '#'.substr($code, 0, 6);

    return [
        'completed'       => '#27ae60',
        'paid'            => '#27ae60',
        'shipping'        => '#16a085',
        'in-progress'     => '#2980b9',
        'payment-confirm' => $confirm_color,
        'on-hold'         => '#7f8c8d',
        'cancelled'       => '#d35400',
        'refunded'        => '#c0392b',
        'pending'         => '#7f8c8d',
        'added'           => '#2980b9',
        'inactive'        => '#c0392b',
        'active'          => '#2ecc71',
        'expired'         => '#c0392b'
    ];
}

/**
 * Get hex color code from text
 * @since   1.0.0
 * @param   string  $text   String value that will be converted
 * @return  string  Hex code value
 */
function sejolisa_get_hex_from_text($text) {
    $code = dechex(crc32($text));
    $code = substr($code, 0, 6);
    return '#'.$code;
}

/**
 * Get fixed color by text
 * @since   1.0.0
 * @param   string $text
 * @return  string
 */
function sejolisa_get_text_color($text) {

    $code  = false;
    $color = sejolisa_get_all_colors();

    return isset($color[$text]) ? $color[$text] : sejolisa_get_hex_from_text($text);
}

/**
 * Encrypt and decrypt
 * @since  1.0.0
 * @param  string $action Action between encrypt or decrypt
 * @param  string $string String that will be processed
 * @return string         String result
 */
function sejolisa_encrypt_decrypt($action = 'encrypt', $string = '') {

    $output         = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key     = AUTH_SALT;
    $secret_iv      = SECURE_AUTH_SALT;
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ( $action == 'encrypt' ) :
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    elseif( $action == 'decrypt' ) :
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    endif;

    return $output;
}

/**
 * Parsing given form HTML code to array data to validate subscription form
 * @since   1.0.0
 * @param   string   $code   Autoresponder html code
 * @return  array            Array of response
 */
function sejolisa_parsing_form_html_code($code) {

    $response = [
        'valid'    => false,
        'messages' => [],
        'form'     => [],
        'fields'   => []
    ];

	// strip unneccessary tags
	$form	= strip_tags($code, '<form><input><button>');
	preg_match_all("'<(.*?)>'si", $form, $matches);

	if (
        is_array($matches) &&
        isset($matches[0])
    ) :

		$matches    = $matches[0];
		$html       = stripslashes(join('', $matches));
		$html       = safe_str_replace("</input>","",$html);
		$clean_form = htmlspecialchars(safe_str_replace(array('><', '<input'), array(">\n<", "\t<input"), $html), ENT_NOQUOTES);

		$doc        = new DOMDocument();

		$doc->strictErrorChecking = FALSE;
		$doc->loadHTML($html);

		$xml 	= simplexml_import_dom($doc);

		if ($xml) :

			$form 	= $xml->body->form;

			if ($form) :

				unset($error);

				if(!isset($form['action'])) :

					$response['messages'][]	= __('Kode HTML yang diberikan tidak lengkap. Pada tag FORM tidak terdapat attribut ACTION.', 'sejoli');

				elseif ($form->input) :

                    $response['form'] = [
                        'action'   => $form['action'],
                        'method'   => $form['method']
                    ];

					$dform	= @json_decode(@json_encode($form),1);

					foreach ($form->input as $dinput) :

						$iinput	= @json_decode(@json_encode($dinput),1);
						$input	= $iinput['@attributes'];

						if ('hidden' === $input['type']) :

							$type	= 'hidden';
							$value	= $input['value'];
							$additional_data[] = array($input['name'], $input['value']);

						elseif('submit' === $input['type']) :
							continue;

						elseif(
                            ( isset($input['id']) && FALSE !== stripos($input['id'], 'email') ) ||
                            FALSE !== stripos($input['name'], 'email')
                        ) :
							$type	= "email";
							$value	= "";
							$email_identifier	= $input['name'];

						elseif (
                            ( isset($input['id']) && FALSE !== stripos($input['id'], 'name') ) ||
                            FALSE !== stripos($input['name'], 'name')
                        ) :
							$type	= "name";
							$value	= "";
							$name_identifier = $input['name'];
                        else :
                            $type	= $input['type'];
							$value  = isset($input['value']) ? $input['value'] : '';
						endif;

						$response['fields'][]	= array(
							'name'	=> $input['name'],
							'type'	=> $type,
							'value'	=> $value,
						);

					endforeach;

					// Correct value's
					if (!isset($email_identifier)) :
						$response['messages'][]   = __('Kode HTML form yang anda masukkan tidak memiliki field untuk mengisi alamat email', 'sejoli');
                    else :
                        $response['valid'] = true;
					endif;

				endif;
			else :
				$response['messages'][]   = __('Kode HTML form yang anda masukkan tidak valid', 'sejoli');
			endif;

		endif;
	endif;

    return $response;
}

/**
 * Return with help content link
 * @since   1.0.0
 * @param   string  $page   Section name
 * @return  string  Url of section help
 */
function sejolisa_get_admin_help($page) {
    return plugin_dir_url( dirname( __FILE__ ) ) . 'admin/partials/help/'.$page.'.html?width=753&height=824&keepthis=true';
}

/**
 * Get RGBA color format from Hex
 * @since   1.0.0
 * @param   string  $hex_color      Hex color value
 * @param   string  $transparency   Transparency valu
 * @return  string;
 */
function sejolisa_set_rgba_from_hex($hex_color, $transparency = '0.2') {
    list($r, $g, $b) = sscanf($hex_color, "#%02x%02x%02x");

    return 'rgba(' . $r .', ' . $g . ',' . $b. ', ' . $transparency. ')';
}

/**
 * Get general logo
 * @since   1.2.0
 * @param   string|array $size   Logo size, size parameter is based on wp_get_attachment_image_src, default thumbnail
 * @return  string|false
 */
function sejolisa_get_logo($size = 'thumbnail') {

    $image_id = sejolisa_carbon_get_theme_option('sejoli_setting_logo');

    if(!empty($image_id)) :
        $image = wp_get_attachment_image_src($image_id, $size);
        if(is_array($image) && isset($image[0])) :
            return $image[0];
        endif;
    endif;

    return false;
}

/**
 * Get general member area logo
 * @since   1.2.3
 * @param   string|array $size   Logo size, size parameter is based on wp_get_attachment_image_src, default thumbnail
 * @return  string|false
 */
function sejolisa_get_member_area_logo($size = 'thumbnail') {

    $image_id = sejolisa_carbon_get_theme_option( 'sejoli_setting_member_area_logo' );

    if(!empty($image_id)) :
        $image = wp_get_attachment_image_src($image_id, $size);
        if(is_array($image) && isset($image[0])) :
            return $image[0];
        endif;
    else :
        return sejolisa_get_logo();
    endif;

    return false;
}
