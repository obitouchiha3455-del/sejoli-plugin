<table style='margin-top:20px;margin-bottom:0;margin-left:auto;margin-right:auto;width:480px;' cellpadding='5' cellspacing='0' border='1' bordercolor='#444444'>
    <tbody>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php _e('No. Invoice', 'sejoli'); ?></td>
            <td>{{invoice-id}}</td>
        </tr>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php _e('PPN', 'sejoli'); ?> {{ppn}}%</td>
            <td>{{ppn_total}}</td>
        </tr>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php _e('Total Pembayaran', 'sejoli'); ?></td>
            <td>{{order-grand-total}}</td>
        </tr>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php _e('Produk', 'sejoli'); ?></td>
            <td>{{product-name}} X{{quantity}}</td>
        </tr>
    </tbody>
</table>
