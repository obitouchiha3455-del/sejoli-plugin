*<?php _e('Informasi Transfer', 'sejoli'); ?>*

<?php printf(__('*%s*, nomor rekening %s', 'sejoli'), $payment['bank'], $payment['account']) ?>.
<?php if(!empty($payment['owner'])) : ?>
<?php printf( __('Atas nama *%s*', 'sejoli'), $payment['owner']); ?>.
<?php endif; ?>
<?php if(!empty($payment['info'])) : ?>
<?php echo $payment['info']; ?>.
<?php endif; ?>