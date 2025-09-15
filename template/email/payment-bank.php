<h3><?php _e('Informasi Transfer', 'sejoli'); ?></h3>

<p style='margin-bottom:10px;'>
    <?php printf(__('<strong>%s</strong>, nomor rekening <strong>%s</strong>', 'sejoli'), $payment['bank'], $payment['account']) ?>
    <?php if(!empty($payment['owner'])) : ?>
    <br /><?php printf( __('Atas nama <strong>%s</strong>', 'sejoli'), $payment['owner']); ?>
    <?php endif; ?>
    <?php if(!empty($payment['info'])) : ?>
    <br /><?php echo $payment['info']; ?>
    <?php endif; ?>
</p>
