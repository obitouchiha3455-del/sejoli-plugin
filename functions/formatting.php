<?php
/**
 * Extend strpos function to enable needle as array
 * @param  string   $haystack [description]
 * @param  mixed    $needle   [description]
 * @param  integer  $offset   [description]
 * @return boolean
 */
function sejolisa_strpos_array($haystack,$needle,$offset = 0)
{
    if(!is_array($needle)) :
        $needle = array($needle);
    endif;

    foreach($needle as $query) :
        if(false !== strpos($haystack, $query, $offset)) :
             return true;
        endif;
    endforeach;

    return false;
}

/**
 * Set currency format
 * @since   1.0.0
 * @param   float|integer    $price
 * @param   string           $currency
 * @param   string           $thousand
 * @return  string
 */
function sejolisa_currency_format() {
    
    $currency_type = sejolisa_carbon_get_theme_option('sejoli_currency_type');
    $currency      = '';

    switch ( $currency_type ) {
        case "IDR":
            $currency = 'Rp. ';
            break;

        case "MYR":
            $currency = 'RM ';
            break;

        case "USD":
            $currency = '$ ';
            break;

        default:
            $currency = 'Rp. ';
            break;
    }

    return $currency;

}

/**
 * Set price format
 * @since   1.0.0
 * @param   float|integer    $price
 * @param   string           $currency
 * @param   string           $thousand
 * @return  string
 */
function sejolisa_price_format($price, $currency = '', $thousand = '.') {

    $currency_type               = sejolisa_carbon_get_theme_option('sejoli_currency_type');
    $currency_thousand_sep       = sejolisa_carbon_get_theme_option('sejoli_currency_thousand');
    $currency_decimal_sep        = sejolisa_carbon_get_theme_option('sejoli_currency_decimal');
    $currency_number_of_decimals = sejolisa_carbon_get_theme_option('sejoli_currency_number_of_decimals');
    $price_format                = '';

    $clean_price = preg_replace('/[^0-9.,-]/', '', (string) $price);
    $clean_price = str_replace(',', '.', $clean_price);
    $numeric_price = (float) $clean_price;

    $price_format = number_format($numeric_price, $currency_number_of_decimals, $currency_decimal_sep, $currency_thousand_sep);
    $prefix = (0 > $numeric_price) ? '- ' : '';

    return $prefix . sejolisa_currency_format() . $price_format;
}


/**
 * Set coloring unique number
 * @since   1.0.0
 * @param   string    $price
 */
function sejolisa_coloring_unique_number($price) {

    $price_ = '';
    $price_arr = explode('.',$price);
    $price_arr_count = count($price_arr);
    $price_arr_count_ = $price_arr_count-1;

    if ( $price_arr_count > 2 ) :
        foreach ( $price_arr as $key => $value ) :
            if ( 0 === $key ) :
                $price_ .= $value;
            elseif ( $price_arr_count_ === $key ):
                $price_ .= ".<span class='sejoli-unique-number'>".$value."</span>";
            else:
                $price_ .= '.'.$value;
            endif;
        endforeach;
    else:
        $price_ = $price;
    endif;

    return $price_;

}

if(!function_exists('sejolisa_get_sensored_string')) :

/**
 * Change all chars except first and last
 * @since   1.3.3
 * @since   1.5.6.1
 * @param   string   $string           Given string
 * @param   string   $replace_char     Characater that will replace
 * @return  string   String that has been replaced
 */
function sejolisa_get_sensored_string(string $string, $replace_char = '*') {

    $words = explode(' ', $string);

    foreach($words as $i => $word) :

        $length    = strlen($word);

        if(0 < ($length - 2 )) :
            $words[$i] = substr($word, 0, 1).str_repeat('*', $length - 2).substr($word, $length - 1, 1);
        else :
            $words[$i] = 'A*******';
        endif;

    endforeach;

    return implode(' ', $words);
}

endif;


/**
 * Remove harm data from
 * @since   1.6.1
 * @param   array $data
 * @return  array
 */
function sejolisa_remove_harm_data($data) {

    $data = (array) $data;

    if (isset($data['affiliate'])) :

        $data['affiliate'] = (array) $data['affiliate'];
        if (isset($data['affiliate']['data'])) {
            $data['affiliate']['data'] = (array) $data['affiliate']['data'];
        }
        unset(
            $data['affiliate']['allcaps'],
            $data['affiliate']['cap_key'],
            $data['affiliate']['caps'],
            $data['affiliate']['roles'],
            $data['affiliate']['data']['user_pass']
        );

    endif;

    if (isset($data['user'])) :

        $data['user'] = (array) $data['user'];
        if (isset($data['user']['data'])) {
            $data['user']['data'] = (array) $data['user']['data'];
        }
        unset(
            $data['user']['allcaps'],
            $data['user']['cap_key'],
            $data['user']['caps'],
            $data['user']['roles'],
            $data['user']['data']['user_pass']
        );

    endif;

    if(isset($data['product'])) :

        $data['product'] = (array) $data['product'];

        unset(
            $data['product']['access_code'],
            $data['product']['files'],
            $data['product']['post_content'],
            $data['product']['post_excerpt'],
            $data['product']['post_password']
        );

    endif;

    return $data;
}

function safe_str_replace($search, $replace, $subject) {
    if (!is_array($search) && !is_string($search)) $search = '';
    if (!is_array($replace) && !is_string($replace)) $replace = '';
    if (!is_array($subject) && !is_string($subject)) $subject = '';

    return str_replace($search, $replace, $subject);
}

function safe_unserialize($data) {
    return is_string($data) ? unserialize($data) : null;
}

function safe_strtoupper($value) {
    return is_string($value) ? strtoupper($value) : '';
}

function safe_strtotime($value) {
    return is_string($value) && trim($value) !== '' ? strtotime($value) : false;
}