<h3><?php _e('Pengiriman', 'sejoli'); ?></h3>
<table style='margin-top:20px;margin-bottom:0;margin-left:auto;margin-right:auto;width:480px' cellpadding='5' cellspacing='0' border='1' bordercolor='#444444'>
    <tr>
        <td style='width:120px;font-weight:bold;'><?php _e('Penerima', 'sejoli'); ?></td>
        <td><?php echo $shipping['receiver']; ?></td>
    </tr>
    <td style='width:120px;font-weight:bold;'><?php _e('Telpon / WhatsApp', 'sejoli'); ?></td>
    <td><?php echo $shipping['phone']; ?></td>
    <tr>
        <td style='width:120px;font-weight:bold;'><?php _e('Kurir', 'sejoli'); ?></td>
        <td><?php printf( __('%s %s, ongkos %s', 'sejoli'), $shipping['courier'], $shipping['service'], sejolisa_price_format($shipping['cost']) ); ?></td>
    </tr>
    <tr>
        <td style='width:120px;font-weight:bold;'><?php _e('Alamat Pengiriman', 'sejoli'); ?></td>
        <td>
            <?php echo isset($shipping['address']) ? $shipping['address'] : ''; ?>
            <?php if (!empty($district)) : ?>
            <br /><br />
            <?php
            $parts = explode(',', $district);
            $parts = array_map('trim', $parts); // Hilangkan spasi ekstra

            if (count($parts) === 5) {
                printf("KELURAHAN %s%s", strtoupper($parts[0]), PHP_EOL);
                printf("KECAMATAN %s%s", strtoupper($parts[1]), PHP_EOL);
                printf("KOTA %s%s", strtoupper($parts[2]), PHP_EOL);
                printf("PROPINSI %s%s", strtoupper($parts[3]), PHP_EOL);
                printf("KODE POS %s%s", $parts[4], PHP_EOL);
            } else {
                echo "Format distrik tidak sesuai.";
            }
            ?>
        <?php endif; ?>

        </td>
    </tr>
    <?php if(isset($meta_data['note']) && !empty($meta_data['note'])) : ?>
    <tr>
        <td style='width:120px;font-weight:bold;'><?php _e('Catatan Pemesanan', 'sejoli'); ?></td>
        <td><?php echo $meta_data['note']; ?></td>
    </tr>
    <?php endif; ?>
    <?php if(isset($shipping['resi_number'])) : ?>
    <tr>
        <td style='width:120px;font-weight:bold;'><?php _e('No Resi', 'sejoli'); ?></td>
        <td><?php echo $shipping['resi_number']; ?></td>
    </tr>
    <?php endif; ?>
</table>
<div style='height:10px;'>&nbsp;</div>
