<?php printf(__('Halo %s', 'sejoli'),'{{buyer-name}}' ); ?>

<?php _e('Sekedar mengingatkan saja, bahwa ada invoice dari anda yang belum selesai untuk dibayarkan. Berikut informasinya :', 'sejoli'); ?>

{{order-detail}}
{{order-meta}}

<?php printf(__('Segera dibayarkan ya %s, karena ini kesempatan terakhir %s sebelum kami batalkan invoicenya', 'sejoli'), '{{buyer-name}}', '{{buyer-name}}'); ?>
