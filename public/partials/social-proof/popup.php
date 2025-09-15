<?php
/**
 * @since   1.5.0
 * @since   1.5.1.1     Add conditional check for is_product_image_showed
 */

    defined( 'ABSPATH' ) || exit;

    global $post;
    $product = $post;
    $user    = wp_get_current_user();
    $text    = safe_str_replace(
                array(
                    '{{buyer_name}}',
                    '{{product_name}}'
                ),
                array(
                    '<span class="buyer-name">' . $user->display_name . '</span>',
                    '<span class="product-name">' . $product->post_title . '</span>'
                ),
                $this->popup_text
               );
    $has_photo = ( $this->is_avatar_showed || $this->is_product_image_showed ) ? 'has-photo' : '';
?>
<div class="social-proof-container <?php echo $this->position; ?>">
    <input type="hidden" name="social-proof-orders" id='social-proof-orders' value="">
    <section id='social-proof-holder' class="social-proof-holder <?php echo $has_photo; ?> animated">
    <?php if( $this->is_avatar_showed || $this->is_product_image_showed ) : ?>
        <figure class='buyer-photo'>
            <?php echo get_avatar($user); ?>
        </figure>
    <?php endif; ?>
        <div class="buyer-detail">
            <div class="buyer-text"><?php echo $text; ?></div>
            <span class='order-text'>45 menit lalu</span>
        </div>
    </section>
</div>
