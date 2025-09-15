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

<div class="ui text container">
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
            <div class="thankyou-notice">
                <div class="ui stackable grid">
                    <div class="one wide column">
                        <i class="exclamation triangle icon"></i>
                    </div>
                    <div class="twelve wide column">
                        <p><b><?php _e('Wajib', 'sejoli'); ?></b></p>
                        <p>
                            <?php _e('Silahkan cek email anda untuk infomasi selanjutnya.', 'sejoli'); ?>
                        </p>    
                        <br/>
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

                    $product_id            = intval( $order['product_id'] );
                    $is_cod_active         = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_services_active' ) );
                    $markup_ongkir_jne     = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_markup_with_ongkir' ) );
                    $markup_ongkir_sicepat = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_markup_with_ongkir' ) );

                    if( false !== $is_cod_active && true !== $markup_ongkir_jne || false !== $is_cod_active && true !== $markup_ongkir_sicepat ) :
                    
                        if ( isset( $order['meta_data']['markup_price'] ) ) :
                    
                            $payment_gateway  = $order['payment_gateway'];
                            $shipping_service = $order['meta_data']['shipping_data']['service'];
                    
                            if( \str_contains( strtolower( $shipping_service ), 'jne' ) && $payment_gateway == 'cod' ) {
                    
                                $markup_label = sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_markup_label' );
                    
                            } else if( \str_contains( strtolower( $shipping_service ), 'sicepat' ) && $payment_gateway == 'cod' ) {
                    
                                $markup_label = sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_markup_label' );
                    
                            }
                            ?>
                            <tr>
                                <td><?php echo $markup_label; ?>: </td>
                                <td><?php echo sejolisa_price_format($order['meta_data']['markup_price']); ?></td>
                            </tr>
                            <?php
                        endif;
                    
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

                    $fee = apply_filters('sejoli/payment/fee', 0, $order);

                    if ( 0 !== $fee ) :
                        ?>
                        <tr>
                            <td><?php _e('Biaya Transaksi', 'sejoli'); ?></td>
                            <td><?php echo sejolisa_price_format($fee); ?></td>
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
        <div class="catatan">
            <h3 style="margin-top: 10px;"><?php _e('Catatan', 'sejoli'); ?></h3>
            <ul>
                <li><?php _e('Siapkan dana pembayaran Anda sesuai dengan jumlah yang tertera pada invoice.', 'sejoli'); ?></li>
                <li><?php _e('Pembayaran dilakukan dengan metode COD.', 'sejoli'); ?></li>
                <li><?php _e('Pembayaran bisa dilakukan ditempat setelah pesanan Anda tiba.', 'sejoli'); ?></li>
                <li><?php _e('Data pembelian dan petunjuk pembayaran juga sudah kami kirim ke email Anda, silakan cek email dari kami di inbox, promotion, dan atau di folder Spam.', 'sejoli'); ?></li>
            </ul>
        </div>

        <div class="thankyou-info-2">
            <div class="ui stackable grid">
                <div class="wide column">
                    <a target="_blank" href="<?php echo site_url('member-area/login'); ?>" class="submit-button massive ui green button"><?php _e('CEK MEMBER AREA', 'sejoli'); ?></a>
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
