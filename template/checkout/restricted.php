<?php
include 'header.php';
include 'header-logo.php';

global $post;

?>
<div class="ui text closed container">
    <div class="ui segment">
        <h3 class='ui header'><?php _e('Penjualan tertutup', 'sejoli'); ?></h3>
        <?php echo wpautop(sejolisa_carbon_get_post_meta($post->ID, 'user_group_buy_restricted_message')); ?>
    </div>
</div>
<?php
include 'footer.php';
