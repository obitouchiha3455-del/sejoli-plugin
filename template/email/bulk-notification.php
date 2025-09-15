<p><?php printf(__('Halo %s', 'sejoli'),'{{buyer-name}}' ); ?></p>

<p><?php _e('Sekedar mengingatkan saja, bahwa ada invoice dari anda yang belum selesai untuk dibayarkan. Berikut informasinya :', 'sejoli'); ?></p>

{{order-detail}}
{{order-meta}}

<p><?php printf(__('Segera dibayarkan ya %s, karena ini kesempatan terakhir %s sebelum kami batalkan invoicenya', 'sejoli'), '{{buyer-name}}', '{{buyer-name}}'); ?></p>
