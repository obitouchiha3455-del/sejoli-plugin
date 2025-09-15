<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

$image         = sejolisa_get_logo();
$sidebar_image = sejolisa_get_member_area_logo('full');

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body <?php body_class('sejoli-member-area'); ?>>
    <div class="ui visible inverted left vertical menu sidebar sejolisa-memberarea-menu">
        <?php if($sidebar_image) : ?>
        <div class="item header-menu">
            <a class="ui logo icon image" href="#"><img src='<?php echo $sidebar_image; ?>' alt='' style='max-width:100%;max-height:120px;display: inline-block;margin-bottom:15px;height:auto'/></a><br />
            <a href="#" style='font-size:12px;display:inline-block;padding-left:10px;'> <?php echo sejolisa_carbon_get_theme_option('sejoli_member_area_name'); ?></a>
        </div>
        <?php endif; ?>
        <?php require_once('menu.php'); ?>
    </div>
    <div class="pusher sejolisa-memberarea-content">
        <div class="sejolisa-menubars">
            <button class="ui black icon button sidebar-toggle">
                <i class="bars icon"></i> Menu
            </button>
        </div>
        <div class="ui basic segment">

        <?php do_action('sejoli/member-area/header'); ?>
