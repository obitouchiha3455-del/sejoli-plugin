<?php
global $sejolisa;
extract( (array) $sejolisa['subscription']);
$post = get_post($product_id);
setup_postdata($post);

include 'header.php';
include 'header-logo.php';

$product = sejolisa_get_product($product_id);
$use_checkout_description = boolval(sejolisa_carbon_get_post_meta($post->ID, 'display_product_description'));

$display_text_field_full_name    = boolval(sejolisa_carbon_get_post_meta($post->ID, 'display_text_field_full_name'));
$display_text_field_email        = boolval(sejolisa_carbon_get_post_meta($post->ID, 'display_text_field_email'));
$display_text_field_password     = boolval(sejolisa_carbon_get_post_meta($post->ID, 'display_text_field_password'));
$display_text_field_phone_number = boolval(sejolisa_carbon_get_post_meta($post->ID, 'display_text_field_phone_number'));

$parent_order = sejolisa_get_order([
    'ID' => absint($_GET['order_id'])
]);
?>

<div class="ui text container">

    <?php if(false !== $use_checkout_description) : ?>
    <div class='deskripsi-produk'>
        <?php echo apply_filters('the_content', sejolisa_carbon_get_post_meta($post->ID, 'checkout_product_description')); ?>
    </div>
    <?php endif; ?>
    <?php if(false !== $product->form['detail_order']) : ?>
    <div class="produk-dibeli">
        <input type="hidden" id="qty" name="qty" value="<?php echo $parent_order['orders']['quantity']; ?>">
        <?php do_action('sejoli/checkout-template/before-product', $product); ?>
        <table class="ui unstackable table">
            <thead>
                <tr>
                    <th colspan="2" style="text-align:center"><?php _e('Detail Pesanan', 'sejoli'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="ui placeholder">
                            <div class="image header">
                                <div class="line"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="ui placeholder">
                            <div class="paragraph">
                                <div class="line"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th><?php _e('Total', 'sejoli'); ?></th>
                    <th>
                        <div class="total-holder">
                            <div class="ui placeholder">
                                <div class="paragraph">
                                    <div class="line"></div>
                                </div>
                            </div>
                        </div>
                    </th>
                </tr>
                <?php do_action('sejoli/checkout-template/after-product', $product); ?>
                <tr>
                    <th colspan="2">
                        <span class="secure-tagline-icon"><i class="check circle icon"></i> <?php _e('Secure 100%', 'sejoli'); ?></span>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
    <?php if(false !== $product->form['coupon_field']) : ?>
    <div class="kode-diskon">
        <div class="data-holder">
            <div class="ui fluid placeholder">
                <div class="paragraph">
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="login">
        <div class="data-holder">
            <div class="ui fluid placeholder">
                <div class="paragraph">
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="informasi-pribadi">
        <div class="data-holder">
        </div>
    </div>
    <div class="metode-pembayaran">
        <h3 style="margin-bottom: 10px;"><?php _e('Pilih Metode Pembayaran', 'sejoli'); ?></h3>
        <div class="ui doubling grid data-holder">
            <div class="eight wide column">
                <div class="ui placeholder">
                    <div class="paragraph">
                        <div class="line"></div>
                    </div>
                </div>
            </div>
            <div class="eight wide column">
                <div class="ui placeholder">
                    <div class="paragraph">
                        <div class="line"></div>
                    </div>
                </div>
            </div>
            <div class="eight wide column">
                <div class="ui placeholder">
                    <div class="paragraph">
                        <div class="line"></div>
                    </div>
                </div>
            </div>
            <div class="eight wide column">
                <div class="ui placeholder">
                    <div class="paragraph">
                        <div class="line"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        $g_recaptcha_enable   = boolval( sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_enabled' ) );
        $g_recaptcha_checkout = boolval( sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_checkout_page' ) );

        if( true === $g_recaptcha_enable && true === $g_recaptcha_checkout ) :
    ?>
        <div class="g-recaptcha-area">
            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
        </div>
    <?php endif; ?>
    <div class="beli-sekarang element-blockable">
        <div class="data-holder">
            <div class="ui fluid placeholder">
                <div class="paragraph">
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="affiliate-name" style='padding-top:2rem'>

    </div>
    <div class="alert-holder checkout-alert-holder"></div>
</div>
<script id="produk-dibeli-template" type="text/x-jsrender">
    {{if product}}
        <tr>
            <td colspan="2" style="border: none">
                <div class="ui stackable grid">
                    {{if product.image}}
                        <div class="four wide column" style="text-align: left;">
                            <img src="{{:product.image}}">
                        </div>
                    {{/if}}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="ui stackable grid">
                    <div class="twelve wide column">
                        {{:product.title}} X {{:product.quantity}}
                        {{if subscription}}
                            {{if subscription.duration}}
                                <br><?php _e('Durasi', 'sejoli'); ?>: {{:subscription.duration.string}}
                            {{/if}}
                        {{/if}}
                        <input type="hidden" id="product_id" name="product_id" value="{{:product.id}}">
                    </div>
                </div>
            </td>
            <td>{{:product.price}}</td>
        </tr>
    {{/if}}
    {{if subscription}}
        {{if subscription.signup}}
            <tr>
                <td><?php _e('Biaya Awal', 'sejoli'); ?></td>
                <td>{{:subscription.signup.price}}</td>
            </tr>
        {{/if}}
    {{/if}}
    {{if coupon}}
        <tr>
            <td>
                Kode diskon: {{:coupon.code}}, <a class="hapus-kupon"><?php _e('Hapus kupon', 'sejoli'); ?></a>
                <input type="hidden" id="coupon" name="coupon" value="{{:coupon.code}}">
            </td>
            <td>{{:coupon.value}}</td>
        </tr>
    {{/if}}
    {{if transaction}}
        <tr class="biaya-transaksi">
            <td><?php _e('Biaya Transaksi', 'sejoli'); ?></td>
            <td>{{:transaction.value}}</td>
        </tr>
    {{/if}}
    {{if ppn}}
        <tr class="biaya-ppn">
            <td><?php _e('PPN', 'sejoli'); ?> {{:ppn.value}}%</td>
            <td>{{:ppn.total}}</td>
        </tr>
    {{/if}}
</script>
<script id="metode-pembayaran-template" type="text/x-jsrender">
    {{if payment_gateway}}
        {{props payment_gateway}}
            <div class="eight wide column">
                <div class="ui radio checkbox {{if key == 0}}checked{{/if}}">
                    <input type="radio" name="payment_gateway" tabindex="0" class="hidden" value="{{>prop.id}}" {{if key == 0}}checked="checked"{{/if}}>
                    <label><img src="{{>prop.image}}" alt="{{>prop.title}}"></label> 
                    {{if prop.display_payment == true}}
                        <span>{{>prop.title}}</span>
                    {{/if}}
                </div>
            </div>
        {{/props}}
    {{/if}}
</script>
<script id="alert-template" type="text/x-jsrender">
    <div class="ui {{:type}} message">
        <i class="close icon"></i>
        <div class="header">
            {{:type}}
        </div>
        {{if messages}}
            <ul class="list">
                {{props messages}}
                    <li>{{>prop}}</li>
                {{/props}}
            </ul>
        {{/if}}
    </div>
</script>
<script id="login-template" type="text/x-jsrender">
    {{if current_user.id}}
        <div class="login-welcome">
            <p><?php _e('Hai, kamu akan order menggunakan akun', 'sejoli'); ?> <span class="name">{{:current_user.name}}</span>, <a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php _e('Logout', 'sejoli'); ?></a></p>
        </div>
    {{else}}
        <?php if(false !== $product->form['login_field']) : ?>
        <div class="login-form-toggle">
            <p><?php _e('Sudah mempunyai akun ?', 'sejoli'); ?> <a><?php _e('Login', 'sejoli'); ?></a></p>
        </div>
        <form class="ui form login-form">
            <h3><?php _e('Login', 'sejoli'); ?></h3>
            <div class="required field">
                <label><?php _e('Alamat Email', 'sejoli'); ?></label>
                <input type="email" name="login_email" id="login_email" placeholder="<?php _e('Masukkan alamat email yang terdaftar di website ini', 'sejoli'); ?>">
            </div>
            <div class="required field" style="position: relative;">
                <label><?php _e('Password', 'sejoli'); ?></label>
                <input type="password" name="login_password" id="login_password" placeholder="<?php _e('Masukkan password yang anda gunakan untuk website ini', 'sejoli'); ?>" autocomplete='current-password'>
                <i class="eye icon" id="toggleLoginPassword" style="cursor: pointer; position: absolute; right: 0; pointer-events: auto; left: auto; bottom: 1.3em;"></i>
            </div>
            <button type="submit" class="submit-login massive right ui green button"><?php _e('LOGIN', 'sejoli'); ?></button>
            <div class="alert-holder login-alert-holder"></div>
        </form>
        <?php endif; ?>
    {{/if}}
</script>
<script id="apply-coupon-template" type="text/x-jsrender">
    <?php if(false !== $product->form['coupon_field']) : ?>
    <div id='kode-diskon-form-toggle' class="kode-diskon-form-toggle">
        <p><img src="<?php echo SEJOLISA_URL; ?>public/img/voucher.png"> <?php _e('Punya Kode Diskon', 'sejoli'); ?> ? <a><?php _e('Klik Untuk Masukkan Kode', 'sejoli'); ?></a></p>
    </div>
    <div id='kode-diskon-form' class="kode-diskon-form">
        <h3 style="margin-bottom: 0px;font-size: 14px;"><?php _e('Voucher Diskon', 'sejoli'); ?></h3>
        <p><?php _e('Masukkan kode diskon jika memilikinya', 'sejoli'); ?></p>
        <div class="ui fluid action input" style="height: 42px;">
            <input type="text" name="apply_coupon" id="apply_coupon" placeholder="<?php _e('Masukkan disini kode diskonnya', 'sejoli'); ?>">
            <button type="submit" id='sejoli-submit-coupon' class="submit-coupon massive ui green button"><?php _e('PAKAI', 'sejoli'); ?></button>
        </div>
        <div class="alert-holder coupon-alert-holder"></div>
    </form>
    <?php endif; ?>
</script>
<script id="informasi-pribadi-template" type="text/x-jsrender">
    <div class="informasi-pribadi-info">
        <p><?php _e('Isi data-data di bawah untuk bisa mengakses member area serta informasi terkait pembelian.', 'sejoli'); ?></p>
    </div>
    <h3><?php _e('Buat Akun Baru', 'sejoli'); ?></h3>
    <div class="ui form">
        <div class="required field">
            <label><?php _e('Nama Lengkap', 'sejoli'); ?></label>
            <?php if(false !== $display_text_field_full_name) : ?>
            <p><?php _e('Masukkan nama lengkap untuk kemudahan jika suatu saat diperlukan pencarian data.', 'sejoli'); ?></p>
            <?php endif; ?>
            <input type="text" name="user_name" id="user_name" placeholder="<?php _e('Masukkan nama lengkap', 'sejoli'); ?>">
        </div>
        <div class="required field">
            <label><?php _e('Alamat Email', 'sejoli'); ?></label>
            <?php if(false !== $display_text_field_email) : ?>
            <p><?php _e('Kami mengirimkan informasi akses dan transaksi pembelian ke alamat email ini.', 'sejoli'); ?></p>
            <?php endif; ?>
            <input type="email" name="user_email" id="user_email" placeholder="<?php _e('Masukkan email yang aktif digunakan', 'sejoli'); ?>">
            <div class="alert-holder user-email-alert-holder"></div>
        </div>
        <?php if(false !== $product->form['password_field']) : ?>
        <div class="required field" style="position: relative;">
            <label><?php _e('Buat Password', 'sejoli'); ?></label>
            <?php if(false !== $display_text_field_password) : ?>
            <p><?php _e('Tuliskan password yang akan digunakan untuk website ini. Pastikan untuk menyimpan atau mengingat password yang ditulis.', 'sejoli'); ?></p>
            <?php endif; ?>
            <input type="password" name="user_password" id="user_password" placeholder="<?php _e('Buat password untuk website ini', 'sejoli'); ?>" autocomplete='false'>
            <i class="eye icon" id="togglePassword" style="cursor: pointer; position: absolute; right: 5px; pointer-events: auto; left: auto; top:2.3em;"></i>
        </div>
        <?php endif; ?>
        <div class="required field">
            <label><?php _e('Nomor WhatsApp', 'sejoli'); ?></label>
            <?php if(false !== $display_text_field_phone_number) : ?>
            <p><?php _e('Masukkan nomor WhatsApp aktif untuk notifikasi transaksi', 'sejoli'); ?></p>
            <?php endif; ?>
            <input type="text" name="user_phone" id="user_phone" placeholder="<?php _e('Masukkan nomor WhatsApp yang aktif digunakan', 'sejoli'); ?>">
            <div class="alert-holder user-phone-alert-holder"></div>
        </div>
    </div>
</script>
<script id="beli-sekarang-template" type="text/x-jsrender">
    <div class="ui stackable grid">
        <?php if(false !== $product->form['detail_order']) : ?>
        <div class="twelve wide column">
            <h3><?php _e('Ringkasan Pembayaran', 'sejoli'); ?></h3>
        </div>
        <div class="eight wide column">
            <div id='sejoli-total-bayar' class="total-bayar">
                <h4><?php _e('Total Bayar', 'sejoli'); ?></h4>
                <div class="total-holder">
                    <div class="ui placeholder">
                        <div class="paragraph">
                            <div class="line"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="eight wide column">
            <button data-fb-pixel-event="<?php echo isset( $fb_pixel['links']['submit']['type'] ) ? $fb_pixel['links']['submit']['type'] : ''; ?>" type="submit" class="submit-button massive right floated ui green button"><?php echo $product->form['checkout_button_text']; ?></button>
        </div>
    </div>
</script>
<script>
    window.onload = function () {
        const toggleLoginPassword = document.getElementById('toggleLoginPassword');
        const login_password = document.getElementById('login_password');

        if (toggleLoginPassword && login_password) {
            toggleLoginPassword.addEventListener('click', function () {

                const type = login_password.type === 'password' ? 'text' : 'password';
                login_password.type = type;

                this.classList.toggle('slash'); 
            });
        }

        const togglePassword = document.getElementById('togglePassword');
        const user_password = document.getElementById('user_password');

        if (togglePassword && user_password) {
            togglePassword.addEventListener('click', function () {

                const type = user_password.type === 'password' ? 'text' : 'password';
                user_password.type = type;

                this.classList.toggle('slash'); 
            });
        }
    };
</script>
<script type='text/javascript'>
(function($){
    $(document).ready(function(){
        sejoliSaCheckoutRenew.init();
    });
})(jQuery);
</script>
<?php
include 'footer-renew.php';
include 'footer.php';
