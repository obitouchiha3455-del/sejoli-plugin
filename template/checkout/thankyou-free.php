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
                        <th><?php _e('Pesanan anda', 'sejoli'); ?></th>
                        <th><?php _e('Biaya', 'sejoli'); ?></th>
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
                        <td><?php echo sejolisa_price_format( $order['product']->price ); ?></td>
                    </tr>
                    <?php

                    if ( isset( $order['meta_data']['variants'] ) ) :
                        foreach ( $order['meta_data']['variants'] as $key => $value ) :
                            ?>
                            <tr>
                                <td><?php echo ucwords($value['type']); ?>: <?php echo $value['label']; ?></td>
                                <td><?php echo sejolisa_price_format($value['raw_price']); ?></td>
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

                    if ( isset( $order['meta_data']['manual']['unique_code'] ) ) :
                        ?>
                        <tr>
                            <td><?php _e('Biaya Transaksi', 'sejoli'); ?></td>
                            <td><?php echo sejolisa_price_format($order['meta_data']['manual']['unique_code']); ?></td>
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
        <h2><?php _e('Wajib', 'sejoli'); ?></h2>
        <div class="thankyou-info-1">
            <p><?php _e('Silahkan cek email anda untuk infomasi selanjutnya.', 'sejoli'); ?></p>
        </div>
        <hr />
        <a target="_blank" href="<?php echo site_url('member-area/login'); ?>" class="submit-button massive ui green button"><?php _e('CEK MEMBER AREA', 'sejoli'); ?></a>
    </div>
</div>

<?php
include 'footer-secure.php';
include 'footer.php';
