<?php
include 'header-thankyou.php';
include 'header-logo.php';

$thumbnail_url = get_the_post_thumbnail_url($order['product']->ID,'full');
?>
<div class="ui text container">
    <div class="thankyou">
        <h2>Halo <?php echo $order['user']->display_name; ?></h2>
        <div class="thankyou-info-1">
            <p><?php _e('Terimakasih banyak untuk pemesanannya, data pemesanan', 'sejoli'); ?> <?php echo $order['user']->display_name; ?> <?php _e('sudah kami terima', 'sejoli'); ?></p>
        </div>
        <div class="pesanan-anda">
            <h3><?php _e('Cek Pesanan Anda', 'sejoli'); ?></h3>
            <table class="ui table">
                <thead>
                    <tr>
                        <th><?php _e('Produk yang anda beli', 'sejoli'); ?></th>
                        <th style='min-width:200px;'><?php _e('Biaya', 'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="ui stackable grid">
                                <?php
                                if ( $thumbnail_url ) :
                                    ?>
                                    <div class="four wide column">
                                        <img src="<?php echo $thumbnail_url; ?>">
                                    </div>
                                    <?php
                                endif;
                                ?>
                                <div class="twelve wide column">
                                    Invoice ID: <?php echo $order['ID']; ?><br>
                                    <?php echo $order['product']->post_title; ?>
                                    <?php if(1 < intval($order['quantity'])) : ?>
                                    X <?php echo $order['quantity']; ?>
                                    <?php endif; ?>
                                    <?php
                                    if ( isset( $order['product']->subscription, $order['product']->subscription['regular'] ) &&
                                        $order['product']->subscription['active'] == 1
                                        ) :
                                        $period = $order['product']->subscription['regular']['period'];
                                        switch ( $period ) {
                                            case 'daily':
                                                $period = __('Hari', 'sejoli');
                                                break;

                                            case 'monthly':
                                                $period = __('Bulan', 'sejoli');
                                                break;

                                            case 'yearly':
                                                $period = __('Tahun', 'sejoli');
                                                break;

                                            default:
                                                $period = __('Hari', 'sejoli');
                                                break;
                                        }
                                        echo '<br>';
                                        _e('Durasi: per ', 'sejoli');
                                        echo $order['product']->subscription['regular']['duration'].' '.$period;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </td>
                        <td><?php
                        $product_price = $order['product']->price;
                        $quantity      = absint($order['quantity']);
                        if(
                            property_exists($order['product'], 'subscription') &&
                            in_array( $order['type'], array('subscription-regular', 'subscription-signup') )
                        ) :
                            $product_price = $order['product']->subscription['regular']['price'];

                        endif;

                            echo sejolisa_price_format( $quantity * $product_price );
                        ?></td>
                    </tr>
                    <?php
                    if ( isset( $order['product']->subscription, $order['product']->subscription['signup'] ) &&
                        $order['product']->subscription['active'] == 1 &&
                        $order['product']->subscription['signup']['active'] == 1 &&
                        'subscription-signup' === $order['type']
                        ) :
                        ?>
                        <tr>
                            <td><?php _e('Biaya Awal','sejoli'); ?></td>
                            <td><?php echo sejolisa_price_format($order['product']->subscription['signup']['fee']); ?></td>
                        </tr>
                        <?php
                    endif;

                    if ( isset( $order['meta_data']['variants'] ) ) :
                        foreach ( $order['meta_data']['variants'] as $key => $value ) :
                            ?>
                            <tr>
                                <td><?php echo ucwords($value['type']); ?>: <?php echo $value['label']; ?></td>
                                <td><?php echo sejolisa_price_format($quantity * $value['raw_price']); ?></td>
                            </tr>
                            <?php
                        endforeach;
                    endif;

                    if ( isset( $order['meta_data']['shipping_data'] ) ) :
                        $style = "";
                        if ( isset( $order['coupon'] ) ) :
                            if( true === boolval($order['coupon']['discount']['free_shipping']) ):
                                $style = "text-decoration: line-through"; 
                            endif;
                        endif;
                        ?>
                        <tr>
                            <td><?php _e('Biaya Pengiriman:', 'sejoli'); ?> <br><?php echo $order['meta_data']['shipping_data']['courier'].' - '.$order['meta_data']['shipping_data']['service']; ?></td>
                            <td style="<?php echo $style; ?>"><?php echo sejolisa_price_format($order['meta_data']['shipping_data']['cost']); ?></td>
                        </tr>
                        <?php
                    endif;

                    if ( isset( $order['coupon'] ) ) :

                        $coupon_value = $order['coupon']['discount']['value'];
                        if ( $order['coupon']['discount']['type'] === 'percentage' ) :
                            $coupon_value = ( $coupon_value / 100 ) * $product_price;
                        endif;

                        if( isset($order['meta_data']['coupon']) && isset($order['meta_data']['coupon']['discount'])) :
                            $coupon_value = $order['meta_data']['coupon']['discount'];
                        endif;

                        ?>
                        <tr>
                            <td><?php _e('Kode diskon:', 'sejoli'); ?> <?php echo $order['coupon']['code']; ?></td>
                            <td>- <?php echo sejolisa_price_format($coupon_value); ?></td>
                        </tr>
                        <?php

                    endif;

                    if ( isset( $order['meta_data']['wallet'] ) ) :
                        ?>
                        <tr>
                            <td><?php _e('Dana di dompet yang anda gunakan:', 'sejoli'); ?></td>
                            <td>- <?php echo sejolisa_price_format($order['meta_data']['wallet']); ?></td>
                        </tr>
                        <?php
                    endif;

                    if(isset($order['meta_data'][$order['payment_gateway']]['unique_code'])):
                        $total_wt_additionalfee = $order['grand_total'] - $order['meta_data'][$order['payment_gateway']]['unique_code'];
                    elseif(isset($order['meta_data']['shipping_data']['cost'])):
                        $total_wt_additionalfee = $order['grand_total'] - $order['meta_data']['shipping_data']['cost'];
                    elseif(isset($order['meta_data']['shipping_data']['cost']) && isset($order['meta_data'][$order['payment_gateway']]['unique_code'])):
                        $total_wt_additionalfee = $order['grand_total'] - $order['meta_data']['shipping_data']['cost'] - $order['meta_data'][$order['payment_gateway']]['unique_code'];
                    endif;

                    if(isset($order['meta_data']['ppn'])) :
                        $price_without_ppn = ($total_wt_additionalfee / (1 + $order['meta_data']['ppn'] / 100));
                        $value_ppn         = $price_without_ppn * $order['meta_data']['ppn'] / 100;
                        ?>
                        <tr>
                            <td><?php _e('PPN', 'sejoli'); ?> <?php echo number_format($order['meta_data']['ppn'], 2, ',', ' ');?>%</td>
                            <td><?php echo sejolisa_price_format($value_ppn); ?></td>
                        </tr>
                        <?php
                    endif;

                    if ( 0 !== $order['meta_data'][$order['payment_gateway']]['unique_code'] ) :
                        ?>
                        <tr>
                            <td><?php _e('Biaya Transaksi', 'sejoli'); ?></td>
                            <td><?php echo sejolisa_price_format($order['meta_data'][$order['payment_gateway']]['unique_code']); ?></td>
                        </tr>
                        <?php
                    endif;
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Total', 'sejoli'); ?></th>
                        <th><?php echo sejolisa_coloring_unique_number( sejolisa_price_format($order['grand_total']) ); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="countdown-payment">
            <h3><?php _e('Segera lakukan pembayaran dalam waktu', 'sejoli'); ?></h3>
            <?php
            $date = new DateTime( $order['created_at'] );
            // $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
            $timer = absint(sejolisa_carbon_get_theme_option('sejoli_countdown_timer'));
            $date->add(new DateInterval('PT' . $timer . 'H'));
            ?>
            <div class="countdown-payment-run" data-datetime="<?php echo $date->format('Y-m-d H:i:s'); ?>"></div>
            <p>(<?php _e('Sebelum', 'sejoli'); ?> <?php echo sejolisa_datetime_indonesia( $date->format('Y-m-d H:i:s') ); ?>)</p>
        </div>
        <div class="transfer">
            <h3><?php _e('Tolong transfer ke', 'sejoli'); ?></h3>
            <table class="ui table">
                <thead>
                    <tr>
                        <th style="width:35%"><?php _e('Nomor Rekening', 'sejoli'); ?></th>
                        <th style="width:35%"><span class="no-rekening"><?php echo $order['payment_info']['account_number']; ?></span></th>
                        <th style="width:30%"><a class="copy-btn" data-target=".no-rekening"><?php _e('Copy', 'sejoli'); ?></a></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('Ke Bank', 'sejoli'); ?></td>
                        <?php if('other' === $order['payment_info']['bank'])  : ?>
                        <td><?php
                            $bank_image = $order['payment_info']['logo'];
                            if(empty($bank_image)) :
                                $bank_image = 'https://via.placeholder.com/150';
                            endif;

                            echo $order['payment_info']['bank_name'];

                            if(isset($order['payment_info']['owner'])) :
                                echo ' a.n '.$order['payment_info']['owner'];
                            endif;
                         ?></td>
                         <td><img src="<?php echo $bank_image; ?>"></td>
                        <?php else : ?>
                        <td><?php
                            echo $order['payment_info']['bank'];

                            if(isset($order['payment_info']['owner'])) :
                                echo ' a.n '.$order['payment_info']['owner'];
                            endif;
                        ?></td>
                        <td>
                            <?php if(isset($order['payment_info']['logo'])) : ?>
                            <img src="<?php echo $order['payment_info']['logo']; ?>">
                            <?php else : ?>
                            <img src="<?php echo SEJOLISA_URL; ?>public/img/<?php echo $order['payment_info']['bank']; ?>.png">
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Total Biaya', 'sejoli'); ?></th>
                        <th>
                            <?php echo sejolisa_coloring_unique_number( sejolisa_price_format($order['grand_total']) ); ?>
                            <span class="total-biaya"><?php echo number_format( $order['grand_total'], 0, '', ''); ?></span>
                        </th>
                        <th><a class="copy-btn" data-target=".total-biaya"><?php _e('Copy', 'sejoli'); ?></a></th>
                    </tr>
                </tfoot>
            </table>
            <div class="transfer-info">
                <div class="segitiga"></div>
                <p><b><?php _e('Penting!', 'sejoli'); ?></b> <?php _e('Mohon transfer sampai 3 digit terakhir yaitu', 'sejoli'); ?> <b><?php echo sejolisa_coloring_unique_number( sejolisa_price_format($order['grand_total']) ); ?></b> <?php _e('Karena sistem kami tidak bisa mengenali pembayaran Anda bila jumlahnya tidak sesuai', 'sejoli'); ?></p>
            </div>
        </div>
        <div class="catatan">
            <h3><?php _e('Catatan', 'sejoli'); ?></h3>
            <ul>
                <li><?php _e('Setelah melakukan konfirmasi pembayaran, verifikasi pesanan Anda akan kami proses dalam 60 menit dan selambat-lambatnya 1x24 jam.', 'sejoli'); ?></li>
                <li><?php _e('Pembayaran diatas jam 21.00 WIB akan di proses hari berikutnya.', 'sejoli'); ?></li>
                <li><?php _e('Data pembelian dan petunjuk pembayaran juga sudah kami kirim ke email Anda, silakan cek email dari kami di inbox, promotion, dan atau di folder Spam.', 'sejoli'); ?></li>
            </ul>
        </div>
        <div class="thankyou-info-2">
            <p><b><?php _e('Wajib:', 'sejoli'); ?></b> <?php _e('Setelah melakukan transfer pembayaran, harap mengkonfirmasi pembayaran Anda melalui halaman ini:', 'sejoli'); ?></p>
        </div>
        <a target="_blank" href="<?php echo site_url('confirm'); ?>/?order_id=<?php echo $order['ID']; ?>" class="submit-button massive ui green button"><?php _e('KONFIRMASI PEMBAYARAN', 'sejoli'); ?></a>
    </div>
</div>

<?php
include 'footer-secure.php';
include 'footer.php';
