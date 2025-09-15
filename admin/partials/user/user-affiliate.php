<?php ob_start(); ?>

<h3><?php _e('User Affiliasi', 'sejoli'); ?></h3>
<?php $get_affiliate_id = get_the_author_meta( '_affiliate_id', $user->ID ); ?>
<table class="form-table user-affiliate" role="presentation">
    <tr>
        <th><label for="gmail"><?php _e('User Affiliasi', 'sejoli'); ?></label></th>
        <td>
        	<select class="update-order-select" name="affiliate_id">
                <option value=""><?php _e('Pilihan affiliasi', 'sejoli'); ?></option>
                <option value="<?php echo get_the_author_meta( '_affiliate_id', $user->ID ); ?>" <?php selected( get_the_author_meta( '_affiliate_id', $user->ID ), get_the_author_meta( '_affiliate_id', $user->ID ) ); ?>><?php echo get_the_author_meta( 'display_name', $get_affiliate_id ); ?> - <?php echo get_the_author_meta( 'user_email', $get_affiliate_id ); ?></option>
            </select>
        </td>
    </tr>
</table>

<?php
	$html = ob_get_contents();
	ob_end_clean();
?>	