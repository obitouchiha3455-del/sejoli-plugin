<?php
$order_status = apply_filters('sejoli/order/status', []);
$date         = date('Y-m-d',strtotime('-30day')) . ' - ' . date('Y-m-d');
?>
<div id='bulk-notification-holder' class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Notifikasi Massal', 'sejoli'); ?>
	</h1>
    <form class="ui form" action="" method="post">
        <div class="inline field">
            <label for="order-status"><?php _e('Status Order', 'sejoli'); ?></label>
            <select class="ui dropdown" name="order-status">
            <?php foreach($order_status as $status => $label) : ?>
                <option value="<?php echo $status; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
            </select>
        </div>
        <div class="inline field">
            <label for="product"><?php _e('Produk', 'sejoli'); ?></label>
            <select id='product_id' class="" name="product"></select>
        </div>
        <div class="inline field">
            <label for="invoice-day"><?php _e('Tanggal Invoice', 'sejoli'); ?></label>
            <input type="text" name='date-range' value='<?php echo $date; ?>' />
        </div>

        <div class="field">
            <button type="button" name="button" class='button button-primary check-invoice' style='width:100%;font-size:22px;height:auto;padding:6px;'><?php _e('Check Invoice', 'sejoli'); ?></button>
        </div>

        <div id="shortcode-list" style='margin-bottom:1rem; display: none;'>
            <?php
            echo wp_kses_post( '<b>Shortcode</b>: <pre><i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama url halaman member area.', 'sejoli').'">{{memberurl}}</code> <code title="'.__('Shortcode untuk menampilkan informasi akses user.', 'sejoli').'">{{user-access}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nama user.', 'sejoli').'">{{user-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama website.', 'sejoli').'">{{sitename}}</code> <code title="'.__('Shortcode untuk menampilkan url website.', 'sejoli').'">{{siteurl}}</code> <code title="'.__('Shortcode untuk menampilkan ID order.', 'sejoli').'">{{order-id}}</code> <code title="'.__('Shortcode untuk menampilkan nomor invoice.', 'sejoli').'">{{invoice-id}}</code></br></br><code title="'.__('Shortcode untuk menampilkan total order.', 'sejoli').'">{{order-grand-total}}</code><code title="'.__('Shortcode untuk menampilkan nama pembeli.', 'sejoli').'">{{buyer-name}}</code> <code title="'.__('Shortcode untuk menampilkan email pembeli.', 'sejoli').'">{{buyer-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon pembeli.', 'sejoli').'">{{buyer-phone}}</code> <code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli').'">{{product-name}}</code> <code title="'.__('Shortcode untuk menampilkan jumlah produk.', 'sejoli').'">{{quantity}}</code> <code title="'.__('Shortcode untuk menampilkan url halaman konfirmasi pembayaran.', 'sejoli').'">{{confirm-url}}</code></br></br><code title="'.__('Shortcode untuk menampilkan link dokumen attachment konfirmasi pembayaran.', 'sejoli').'">{{confirm-payment-file}}</code> <code title="'.__('Shortcode untuk menampilkan tanggal pembelian.', 'sejoli').'">{{order-day}}</code> <code title="'.__('Shortcode untuk menampilkan masa berakhir pembelian.', 'sejoli').'">{{close-time}}</code> <code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli').'">{{affiliate-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli').'">{{affiliate-phone}}</code></br></br><code title="'.__('Shortcode untuk menampilkan tier affiliasi.', 'sejoli').'">{{affiliate-tier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi komisi.', 'sejoli').'">{{commission}}</code> <code title="'.__('Shortcode untuk menampilkan informasi detail order.', 'sejoli').'">{{order-detail}}</code> <code title="'.__('Shortcode untuk menampilkan informasi meta order.', 'sejoli').'">{{order-meta}}</code> <code title="'.__('Shortcode untuk menampilkan informasi metode pembayaran.', 'sejoli').'">{{payment-gateway}}</code> <code title="'.__('Shortcode untuk menampilkan informasi notifikasi per-produk.', 'sejoli').'">{{product-info}}</code></br></br><code title="'.__('Shortcode untuk menampilkan informasi kurir pengiriman.', 'sejoli').'">{{shipping-courier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nomor resi pengiriman.', 'sejoli').'">{{shipping-number}}</code></i></pre>' );
            ?>
        </div>

        <div class="invoice-report" style='margin-bottom:1rem;'>

        </div>

        <input type="hidden" name="all-invoices" value="">
        <input type="hidden" name="total-invoice" value="">

        <div class="editor checkbox field">
            <div class="ui toggle checkbox">
                <label for="email-notif"><?php _e('Kirim Email', 'sejoli'); ?></label>
                <input type="checkbox" name="send-email" value="1" checked />
            </div>
        </div>
        <div class="editor inline field email-field">
            <label for="email-title"><?php _e('Judul Email', 'sejoli'); ?></label>
            <input type="text" name="email-title" value="<?php echo __('{{buyer-name}}, Order #{{invoice-id}} untuk produk {{product-name}} belum selesai, silahkan lanjutkan pembayarannya', 'sejoli'); ?>" />
        </div>

        <div class="editor inline field email-field">
            <label for="email-content"><?php _e('Isi Email', 'sejoli'); ?></label>
            <?php
            $content = sejoli_get_notification_content('bulk-notification');
            $editor_id = 'email-content';
            $settings = array(
                'textarea_name' => 'email-content',
                'editor_height' => 250,
                'media_buttons' => false,
                'teeny'         => false,
                'quicktags'     => true,
            );
            wp_editor($content, $editor_id, $settings);
            ?>
        </div>

        <div class="editor checkbox field">
            <div class="ui toggle checkbox">
                <label for="email-notif"><?php _e('Kirim Whatsapp', 'sejoli'); ?></label>
                <input type="checkbox" name="send-whatsapp" value="1" checked />
            </div>
        </div>
        <div class="editor inline field whatsapp-field">
            <label for="whatsapp-content"><?php _e('Isi WhatsApp', 'sejoli'); ?></label>
            <textarea name="whatsapp-content" rows="8" cols="80"><?php echo sejoli_get_notification_content('bulk-notification', 'whatsapp'); ?></textarea>
        </div>

        <div class="editor checkbox field">
            <div class="ui toggle checkbox">
                <label for="send-sms"><?php _e('Kirim SMS', 'sejoli'); ?></label>
                <input type="checkbox" name="send-sms" value="1" checked />
            </div>
        </div>
        <div class="editor inline field whatsapp-field">
            <label for="sms-content"><?php _e('Isi SMS', 'sejoli'); ?></label>
            <textarea name="sms-content" rows="8" cols="80"><?php echo sejoli_get_notification_content('bulk-notification', 'sms'); ?></textarea>
        </div>

        <div class="editor field">
            <button type="button" name="button" class='button button-primary send-notification' style='width:100%;font-size:22px;height:auto;padding:6px;'><?php _e('Kirim Notifikasi', 'sejoli'); ?></button>
        </div>

        <div class="ui indicating progress" id='bulk-upload-progress' style='display:none;'>
            <div class="bar"></div>
            <div class="label">Sending</div>
        </div>
        <div class="bulk-process-info">

        </div>
    </form>
</div>
<script id='bulk-message' type="text/x-jsrender">
<div class="ui {{:class}} message" style="display:block;">
    {{:message}}
</div>
</script>
