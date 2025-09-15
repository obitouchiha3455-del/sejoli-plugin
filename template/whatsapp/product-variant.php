*<?php _e('Variasi produk', 'sejoli'); ?>*
<?php foreach($variants as $_variant) : ?>
*<?php echo ucfirst($_variant['type']); ?>* : <?php echo $_variant['label']; ?>.
<?php endforeach; ?>
