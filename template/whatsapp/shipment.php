
*<?php _e('Pengiriman', 'sejoli'); ?>*
*<?php _e('Penerima', 'sejoli'); ?>* : <?php echo $shipping['receiver']; ?>.
*<?php _e('Telpon / WhatsApp', 'sejoli'); ?>* : <?php echo $shipping['phone']; ?>.
*<?php _e('Kurir', 'sejoli'); ?>* : <?php printf( __('%s %s, ongkos %s', 'sejoli'), urlencode($shipping['courier']), urlencode($shipping['service']), sejolisa_price_format($shipping['cost']) ); ?>.
*<?php _e('Alamat Pengiriman', 'sejoli'); ?>* : <?php echo isset($shipping['address']) ? $shipping['address'] : ''; ?>
<?php if (!empty($district)) : ?>
    <?php
    $parts = explode(',', $district);
    $parts = array_map('trim', $parts);

    if (count($parts) === 5) {
        $wa_district_message  = "\n"."KELURAHAN " . strtoupper($parts[0]) . "\n";
        $wa_district_message .= "KECAMATAN " . strtoupper($parts[1]) . "\n";
        $wa_district_message .= "KOTA " . strtoupper($parts[2]) . "\n";
        $wa_district_message .= "PROPINSI " . strtoupper($parts[3]) . "\n";
        $wa_district_message .= "KODE POS " . $parts[4];

        echo $wa_district_message;
    } else {
        echo "Format alamat tidak sesuai.";
    }
    ?>
<?php endif; ?>

<?php if(isset($meta_data['note']) && !empty($meta_data['note'])) : ?>

*<?php _e('Catatan Pemesanan', 'sejoli'); ?>* : <?php echo $meta_data['note']; ?>
<?php endif; ?>
<?php if(isset($shipping['resi_number'])) : ?>

*<?php _e('No Resi', 'sejoli'); ?>* : <?php echo $shipping['resi_number']; ?>.
<?php endif; ?>