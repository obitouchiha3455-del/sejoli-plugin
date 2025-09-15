<?php
include 'header-thankyou.php';
include 'header-logo.php';

$thumbnail_url = get_the_post_thumbnail_url($order['product']->ID,'full');

$desain_bg_color = sejolisa_carbon_get_post_meta( $order['product']->ID,'desain_bg_color' );
if ( empty( $desain_bg_color ) ) :
    $desain_bg_color = sejolisa_carbon_get_theme_option('desain_bg_color');
endif;
if ( !empty( $desain_bg_color ) ) :
    $inline_styles .= 'background-color: '.$desain_bg_color.';';
endif;
?>
<style>
    .lines span {
        <?php echo $inline_styles; ?>
        /* background-image: none !important; */
    }
    .dots span {
        <?php echo $inline_styles; ?>
        /* background-image: none !important; */
    }
</style>

<div class="thanks-page ui text container">
    <div class="thankyou">
        <span class="top-dots">
            <span class="section dots">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </span>
        </span>
        <span class="checkout-check"><i class="check icon"></i></span>
        <br/>
        <h1 style="text-align:center; margin: 0 0;"><?php echo $order['user']->display_name; ?>,</h1>
        <h2 style="text-align:center; margin: 0 0;"><?php _e('Checkout berhasil', 'sejoli'); ?></h2>
        <div class="thankyou-info-1">
            <div class="thankyou-countdown">
                <div class="ui stackable grid">
                    <div class="one wide column">
                        <p><?php _e('Batas akhir <br/> pembayaran', 'sejoli'); ?></p>
                    </div>
                    <div class="twelve wide column">
                        <div class="countdown-payment">
                            <?php
                            $date = new DateTime( $order['created_at'] );
                            // $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
                            $timer = absint(sejolisa_carbon_get_theme_option('sejoli_countdown_timer'));
                            $date->add(new DateInterval('PT' . $timer . 'H'));
                            ?>
                            <div class="countdown-payment-run" data-datetime="<?php echo $date->format('Y-m-d H:i:s'); ?>"></div>
                            <p><?php _e('Jatuh tempo', 'sejoli'); ?> <?php echo sejolisa_datetime_indonesia( $date->format('Y-m-d H:i:s') ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="thankyou-notice">
                <div class="ui stackable grid">
                    <div class="one wide column">
                        <i class="exclamation triangle icon"></i>
                    </div>
                    <div class="twelve wide column">
                        <p><b><?php _e('Yuk, buruan selesaikan pembayaranmu,', 'sejoli'); ?></b></p>
                        <p>
                            <?php _e('Kami verifikasi pesananmu kurang dari 15 menit setelah pembayaran berhasil dan paling lambat 1x24 jam.', 'sejoli'); ?>
                        </p>    
                    </div>
                </div>
            </div>

            <span class="bottom-lines">
                <span class="section lines">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </span>
        </div>
        <!-- <div class="countdown-payment">
            <h3><?php _e('Segera lakukan pembayaran dalam waktu', 'sejoli'); ?></h3>
            <?php
            $date = new DateTime( $order['created_at'] );
            // $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
            $timer = absint(sejolisa_carbon_get_theme_option('sejoli_countdown_timer'));
            $date->add(new DateInterval('PT' . $timer . 'H'));
            ?>
            <div class="countdown-payment-run" data-datetime="<?php echo $date->format('Y-m-d H:i:s'); ?>"></div>
            <p>(<?php _e('Sebelum', 'sejoli'); ?> <?php echo sejolisa_datetime_indonesia( $date->format('Y-m-d H:i:s') ); ?>)</p>
        </div> -->

        <div class="transfer">
            <h3><?php _e('Tolong transfer ke', 'sejoli'); ?></h3>
            <table class="ui table">
                <tbody>
                    <tr>
                        <td style="width:35%">
                            <?php if(isset($order['payment_info']['logo'])) : ?>
                            <img src="<?php echo $order['payment_info']['logo']; ?>">
                            <?php else : ?>
                            <img src="<?php echo SEJOLISA_URL; ?>public/img/<?php echo $order['payment_info']['bank']; ?>.png">
                            <?php endif; ?>
                        </td>
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
                        <td colspan="2"><?php
                            echo $order['payment_info']['bank'];

                            if(isset($order['payment_info']['owner'])) :
                                echo ' a.n '.$order['payment_info']['owner'];
                            endif;
                        ?></td>
                        <?php endif; ?>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><span><?php _e('No. rekening', 'sejoli'); ?></span><br/><span class="no-rekening price"><?php echo $order['payment_info']['account_number']; ?></span></td>
                        <td><a class="copy-btn" data-target=".no-rekening"><?php _e('Salin', 'sejoli'); ?> <i class="copy outline icon"></i></a></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <span><?php _e('Total Pembayaran', 'sejoli'); ?></span><br/>
                            <span class="price"><?php echo sejolisa_coloring_unique_number( sejolisa_price_format($order['grand_total']) ); ?></span>
                            <span class="total-biaya"><?php echo number_format( $order['grand_total'], 0, '', ''); ?></span>
                        </td>
                        <td><a class="copy-btn" data-target=".total-biaya"><?php _e('Salin', 'sejoli'); ?> <i class="copy outline icon"></i></a></td>
                    </tr>
                </tbody>
            </table>
            <div class="transfer-info">
                <div class="segitiga"></div>
                <?php
                    $currency_type = sejolisa_carbon_get_theme_option('sejoli_currency_type');
                    $currency = '';
                    if( $currency_type === "IDR" ) {
                ?>
                    <p><b><?php _e('Penting!', 'sejoli'); ?></b> <?php _e('Mohon transfer sampai 3 digit terakhir yaitu', 'sejoli'); ?> <b style="font-size: 11px;"><?php echo sejolisa_coloring_unique_number( sejolisa_price_format($order['grand_total']) ); ?></b> <?php _e('Karena sistem kami tidak bisa mengenali pembayaranmu bila jumlahnya tidak sesuai', 'sejoli'); ?></p>
                <?php
                    } elseif( $currency_type === "MYR" ) {
                ?>
                    <p><b><?php _e('Penting!', 'sejoli'); ?></b> <?php _e('Mohon transfer sesuai dengan nominal yang tertera berikut ini: ', 'sejoli'); ?> <b style="font-size: 11px;"><?php echo sejolisa_coloring_unique_number( sejolisa_price_format($order['grand_total']) ); ?></b> <?php _e('Karena sistem kami tidak bisa mengenali pembayaranmu bila jumlahnya tidak sesuai dengan yang tertera', 'sejoli'); ?></p>
                <?php
                    }
                ?>
            </div>

            <span class="bottom-lines">
                <span class="section lines">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </span>

        </div>
        <!-- <div class="catatan">
            <h3><?php _e('Catatan', 'sejoli'); ?></h3>
            <ul>
                <li><?php _e('Setelah melakukan konfirmasi pembayaran, verifikasi pesanan Anda akan kami proses dalam 60 menit dan selambat-lambatnya 1x24 jam.', 'sejoli'); ?></li>
                <li><?php _e('Pembayaran diatas jam 21.00 WIB akan di proses hari berikutnya.', 'sejoli'); ?></li>
                <li><?php _e('Data pembelian dan petunjuk pembayaran juga sudah kami kirim ke email Anda, silakan cek email dari kami di inbox, promotion, dan atau di folder Spam.', 'sejoli'); ?></li>
            </ul>
        </div> -->
        <div class="pesanan-anda">
            <a href="#" class="detail-pesanan-link"><h3 style="text-align:center;"><?php _e('Lihat Detail Pesanan', 'sejoli'); ?> <i class="angle down icon"></i></h3></a>
            <table class="ui table" style="display: none">
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
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="ui stackable grid">
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
                            $total_price_variants = $value['raw_price'] * $quantity;
                            ?>
                            <tr>
                                <td><?php echo ucwords($value['type']); ?>: <?php echo $value['label']; ?></td>
                                <td><?php echo ($total_price_variants > 0) ? sejolisa_price_format($total_price_variants) : ''; ?></td>
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
                        <th><?php _e('Total Pembayaran', 'sejoli'); ?></th>
                        <th><?php echo sejolisa_coloring_unique_number( sejolisa_price_format($order['grand_total']) ); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="thankyou-info-2">
            <div class="ui stackable grid">
                <div class="eight wide column">
                    <p><?php _e('Konfirmasi pembayaran melalui halaman ini:', 'sejoli'); ?></p>
                </div>
                <div class="eight wide column">
                    <a target="_blank" href="<?php echo site_url('confirm'); ?>/?order_id=<?php echo $order['ID']; ?>" class="submit-button massive ui green button"><?php _e('KONFIRMASI', 'sejoli'); ?></a>
                </div>
            </div>
        </div>

        <span class="bottom-dots">
            <span class="section dots">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </span>
        </span>
    </div>
</div>

<?php
include 'footer-secure.php';
include 'footer.php';
