<!DOCTYPE html>
<?php
global $sejolisa;

$order = $sejolisa['order'];

if(!isset($order['product_id'])) :
    wp_die(
        __('Order tidak ada.', 'sejoli'),
        __('Terjadi kesalahan', 'sejoli')
    );
endif;

$order = $sejolisa['order'];

?>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Loading...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
        wp_head();
        sejoli_get_template_part('fb-pixel/loading-page.php');
    ?>
</head>
<body class="sejoli body-loading">
