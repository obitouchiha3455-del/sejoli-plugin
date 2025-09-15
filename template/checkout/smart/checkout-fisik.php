<?php
global $post;

include 'header.php';
// include 'header-logo.php';

$product = sejolisa_get_product($post->ID);
$product_description = isset($_GET['description']) ? boolval($_GET['description']) : '';
if($product_description !== '' && false !== $product_description):
        
    $use_checkout_description = true;

elseif($product_description !== '' && true !== $product_description):
        
    $use_checkout_description = false;

else:

    $use_checkout_description = boolval(sejolisa_carbon_get_post_meta($post->ID, 'display_product_description'));

endif;
?>

<div class="ui text container">
    <form id='sejoli-checkout-fisik' class="checkout-fisik" method="POST" action=''>
        <div class="grid-container">
            <div class="grid-item sticky-sidebar">
                <div class="login">
                    <div class="data-holder">
                        <div class="ui fluid placeholder">
                            <div class="paragraph">
                                <div class="line"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if(false !== $use_checkout_description) : ?>
                <div class='deskripsi-produk'>
                    <?php echo apply_filters('the_content', sejolisa_carbon_get_post_meta(get_the_ID(), 'checkout_product_description')); ?>
                </div>
                <?php endif; ?>
                <?php if(false !== $product->form['detail_order']) : ?>
                    <div class="produk-dibeli">
                <?php else: ?>
                    <div class="produk-dibeli" style="border:none;">
                <?php endif; ?>
                
                    <?php if(false !== $product->form['detail_order']) : ?>
                    <?php do_action('sejoli/checkout-template/before-product', $product); ?>

                    <table class="ui unstackable table">
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
                        <tfoot class="detail-section" style="display:none;">
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
                            <?php
                            $enable_quantity = sejolisa_carbon_get_post_meta( get_the_ID(), 'enable_quantity' );
                            if ( $enable_quantity ) :
                            ?>
                            <tr>
                                <th colspan="2">
                                    <div class="ui labeled input quantity">
                                        <input type="button" value="-" class="button-minus" data-field="qty">
                                        <input type="number" step="1" max="" class="qty change-calculate-affect-shipping qty-field" name="qty" id="qty" value="1" min="1" placeholder="Qty">
                                        <input type="button" value="+" class="button-plus" data-field="qty" style="font-size: 21.79px;">
                                    </div>
                                </th>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <th colspan="2" style="padding: 0;">
                                    <input type="hidden" class="qty change-calculate-affect-shipping" name="qty" id="qty" value="1" placeholder="<?php _e('Qty', 'sejoli'); ?>">
                                </th>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th colspan="2">
                                    <span class="secure-tagline-icon"><i class="check circle icon"></i> <?php _e('Secure 100%', 'sejoli'); ?></span>
                                    <?php if(false !== $product->form['warranty_label']) : ?>
                                    <span class="secure-tagline-icon"><i class="check circle icon"></i> <?php _e('Garansi Uang Kembali', 'sejoli'); ?></span>
                                    <?php endif; ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                    <a href="#" class="toggle-details" data-state="hidden">
                        <?php _e('Lihat detail pesanan', 'sejoli'); ?> 
                        <i class="chevron down icon"></i>
                    </a>
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
                </div>
            </div>
            <div class="grid-item">
                <div class="detail-pesanan">
                    <div class='data-holder'>
                        <div class="ui fluid placeholder">
                            <div class="paragraph">
                                <div class="line"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tujuan-pengiriman">
                    <h2 style="margin-top: 1.6em;"><?php _e('Tujuan Pengiriman', 'sejoli'); ?></h2>
                    <div class="ui form">
                        <div class="field">
                            <input type="text" name="nama_penerima" id="nama_penerima" placeholder="<?php _e('Nama Penerima*', 'sejoli'); ?>">
                        </div>
                        <div class="field">
                            <p>
                                <strong><?php _e('PERHATIAN!', 'sejoli'); ?></strong><br />
                                <?php _e('Harap diisi dengan alamat yang lengkap dan jelas, agar memudahkan pengiriman', 'sejoli'); ?><br />
                            </p>
                            <textarea name="alamat_lengkap" id="alamat_lengkap" placeholder="Jln Kedondong, nomor C44 (Rumah hook)\nKomplek Pertamina Klayan \nCirebon \nJawa Barat \n45121"></textarea>
                        </div>

                        <?php if( true === boolval($product->shipping['active']) || true === boolval($product->cod['cod-active']) ) : ?>
                        <div class="field">
                            <select name="kecamatan" id="kecamatan">
                                <option value=""><?php _e('Silakan Ketik Nama Kecamatannya*', 'sejoli'); ?></option>
                            </select>
                        </div>
                        <?php else : ?>
                        <input type="hidden" name="kecamatan" value="-">
                        <?php endif; ?>

                        <?php if( false !== $product->form['email_field']) : ?>
                            <div class="field">
                                <input type="email" name="alamat_email" id="alamat_email" placeholder="<?php _e('Alamat Email*', 'sejoli'); ?>">
                                <div class="alert-holder user-email-alert-holder"></div>
                            </div>
                        <?php endif; ?>
                        <?php if( false !== $product->form['postal_code'] ) : ?>
                        <div class="field">
                            <input type="text" name="kode_pos" id="kode_pos" placeholder="Kode POS*"> 
                        </div>
                        <?php endif; ?>
                        <div class="field">
                            <input type="text" name="nomor_telepon" id="nomor_telepon" placeholder="<?php _e('Nomor Telepon*', 'sejoli'); ?>">
                            <div class="alert-holder user-phone-alert-holder"></div>
                        </div>
                    </div>
                </div>
                <?php
                $cod_active = isset($product->cod) ? $product->cod['cod-active'] : '';
                if( true === boolval($product->shipping['active']) || true === boolval($cod_active) ) :
                    if(true === $product->shipping['own_value']) :
                ?>
                    <div class="metode-pengiriman">
                        <h2><?php _e('Biaya Pengiriman', 'sejoli'); ?></h2>
                        <div class="ui form">
                            <div class="field">
                                <input type="text" name="shipping_own_value" id="shipping_own_value" placeholder="<?php _e('Masukkan sendiri nilai ongkos kirim', 'sejoli'); ?>" value='0' class='change-calculate-affect-shipping'>
                                <p>
                                    <?php _e('Masukkan sendiri nilai ongkos kirim', 'sejoli'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="metode-pengiriman">
                        <h2><?php _e('Pilih Metode Pengiriman', 'sejoli'); ?></h2>
                        <div class="data-holder">
                            <p><?php _e('Pilih Kecamatan terlebih dulu', 'sejoli'); ?></p>
                        </div>
                    </div>
                <?php
                    endif;
                else : ?>
                    <input type="hidden" name="shipping_method" id="shipping_method" value="FREE:::FREE:::0" />
                <?php endif; ?>

                <div class="metode-pembayaran">
                    <h2><?php _e('Pilih Metode Pembayaran', 'sejoli'); ?></h2>
                    <div class="ui doubling data-holder">
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
                    <div class="scroll-toggle">
                        <a href="#" class="toggle-icon"><i class="chevron down icon"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="floating-side">
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
                <?php if(false !== $product->form['detail_order']) : ?>
                <div class="data-holder">
                    <div class="ui fluid placeholder">
                        <div class="paragraph">
                            <div class="line"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <button data-fb-pixel-event="<?php echo isset( $fb_pixel['links']['submit']['type'] ) ? $fb_pixel['links']['submit']['type'] : ''; ?>" type="submit" class="submit-button massive right ui green button"><?php echo $product->form['checkout_button_text']; ?></button>
            </div>
            <div class="affiliate-name">
            </div>
            <div class="alert-holder checkout-alert-holder"></div>
        </div>
    </form>
</div>

<script id="produk-dibeli-template" type="text/x-jsrender">
    {{if product}}
        <tr>
            <td>
                {{if product.image}}
                    <div class="product-row" style="margin-bottom:20px">
                        <img src="{{:product.image}}" alt="{{:product.title}}" class="product-image">
                    </div>
                {{/if}}
                <div class="product-details">
                    <h3>{{:product.title}}</h3>
                    {{if 1 < product.quantity}}
                    x {{:product.quantity}} pcs
                    {{/if}}
                    <input type="hidden" id="product_id" name="product_id" value="{{:product.id}}">
                </div>
            </td>
            <td class="product-price" style="vertical-align: bottom;"><b style="position: relative;">{{:product.price}}</b></td>
        </tr>
    {{/if}}
    {{props variants}}
        <tr class="detail-section" style="display:none;">
            <td>{{:prop.type}}: {{:prop.label}}</td>
            <td>{{:prop.price}}</td>
        </tr>
    {{/props}}
    {{if shipment}}
        <tr class="detail-section" style="display:none;">
            <td><?php _e('Biaya Pengiriman', 'sejoli'); ?></td>
            <td class="free-shipping-price">{{:shipment.value}}</td>
        </tr>
    {{/if}}
    {{if markup_price}}
        <tr class="detail-section" style="display:none;">
            <td>{{:markup_price.label}}</td>
            <td>{{:markup_price.value}}</td>
        </tr>
    {{/if}}
    {{if coupon}}
        <tr class="detail-section" style="display:none;">
            <td>
                Kode diskon: {{:coupon.code}}, <a class="hapus-kupon"><?php _e('Hapus kupon', 'sejoli'); ?></a>
                <input type="hidden" id="coupon" name="coupon" value="{{:coupon.code}}">
            </td>
            <td>{{:coupon.value}}</td>
        </tr>
    {{/if}}
    {{if wallet}}
        <tr class="detail-section" style="display:none;">
            <td>
                <?php _e('Dana di dompet yang anda gunakan', 'sejoli'); ?>
            </td>
            <td>{{:wallet}}</td>
        </tr>
    {{/if}}
    {{if transaction}}
        <tr class="biaya-transaksi detail-section" style="display:none;">
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
                    {{if prop.display_payment == true}}
                        <label><img src="{{>prop.image}}" alt="{{>prop.title}}"></label> 
                    {{else}}
                        <label><img src="{{>prop.image}}" alt="{{>prop.title}}"></label> 
                    {{/if}}
                </div>
                {{if prop.display_payment == true}}
                    <span>{{>prop.title}}</span>
                {{/if}}
            </div>
        {{/props}}
    {{/if}}
</script>
<script id="metode-pengiriman-template" type="text/x-jsrender">
    {{if shipping_methods.length > 0 }}
        <div class="ui form">
            <div class="field">
                <select name="shipping_method" id="shipping_method" class="change-calculate select2-filled">
                    <option value=""><?php  _e('Pilih Metode Pengiriman', 'sejoli'); ?></option>
                    {{props shipping_methods}}
                        <option value="{{>prop.id}}">{{>prop.title}}</option>
                    {{/props}}
                </select>
            </div>
        </div>
    {{else}}
        <p><?php _e('Pilih Kecamatan terlebih dulu', 'sejoli'); ?></p>
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
        <div class="login-form-toggle">
            <?php if(false !== $product->form['detail_order']) : ?>
            <h2><?php _e('Detail Pesanan', 'sejoli'); ?></h2>
            <?php endif; ?>
            <p><?php _e('Hai, kamu akan order menggunakan akun', 'sejoli'); ?> <span class="name">{{:current_user.name}}</span>, <a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php _e('Logout', 'sejoli'); ?></a></p>
        </div>
    {{else}}
        <?php if(false !== $product->form['login_field']) : ?>
        <div class="login-form-toggle">
            <?php if(false !== $product->form['detail_order']) : ?>
            <h2><?php _e('Detail Pesanan', 'sejoli'); ?></h2>
            <?php endif; ?>
            <p><i class="user outline icon"></i><?php _e('Pelanggan Lama?', 'sejoli'); ?> <a><?php _e('Login', 'sejoli'); ?></a></p>
        </div>
        <div class="ui form login-form">
            <h2><?php _e('Login', 'sejoli'); ?></h2>
            <div class="required field">
                <input type="email" name="login_email" id="login_email" placeholder="<?php _e('Alamat Email', 'sejoli'); ?>">
            </div>
            <div class="required field" style="position: relative;"> 
                <input type="password" name="login_password" id="login_password" placeholder="<?php _e('Password', 'sejoli'); ?>" autocomplete='current-password'>
                <i class="eye icon" id="toggleLoginPassword" style="cursor: pointer; position: absolute; right: 0; pointer-events: auto; left: auto; top: 10px;"></i>
            </div>
            <button type="submit" class="submit-login massive right ui green button"><?php _e('LOGIN', 'sejoli'); ?></button>
            <div class="alert-holder login-alert-holder"></div>
        </div>
        <?php endif; ?>
    {{/if}}
</script>

<script id="apply-coupon-template" type="text/x-jsrender">
    <?php if(false !== $product->form['coupon_field']) : ?>
    <div id='kode-diskon-form-toggle' class="kode-diskon-form-toggle">
        <p><img src="<?php echo SEJOLISA_URL; ?>public/img/voucher2.png"> <?php _e('Punya Kupon Diskon ?', 'sejoli'); ?> <a><?php _e('Klik Masukkan Kode', 'sejoli'); ?></a></p>
    </div>
    <div id='kode-diskon-form' class="kode-diskon-form">
        <div class="ui fluid action input" style="height: 42px;">
            <input type="text" name="apply_coupon" id="apply_coupon" placeholder="<?php _e('Masukkan disini kode diskonnya', 'sejoli'); ?>">
            <button type="submit" id='sejoli-submit-coupon' class="submit-coupon massive ui green button"><?php _e('PAKAI', 'sejoli'); ?></button>
        </div>
        <div class="alert-holder coupon-alert-holder"></div>
    </div>
    <?php endif; ?>
</script>

<script id="detail-pesanan-template" type="text/x-jsrender">
    {{if product}}
        {{if product.variation !== '' }}
            <h2><?php _e('Pilih Variasi', 'sejoli'); ?></h2>
            <div class="detail-variation">
                <div class="ui form">
                    <div class="ui stackable grid">
                        {{props product.variation}}
                            <div class="eight wide column" style="padding-bottom: 0;">
                                <div class="{{if prop.required}}required{{/if}} field">
                                    <select name="variants[{{>key}}]" class="select2-filled variations-select2 change-calculate-affect-shipping" placeholder="<?php _e('Pilih', 'sejoli'); ?> {{:prop.label}}">
                                        <option value=""><?php _e('Pilih', 'sejoli'); ?> {{:prop.label}}</option>
                                        {{props prop.options}}
                                            <option value="{{>key}}">
                                                {{:prop.label}}
                                                {{if prop.price }}
                                                (+ {{:prop.price}} )
                                                {{/if}}
                                            </option>
                                        {{/props}}
                                    </select>
                                </div>
                            </div>
                        {{/props}}
                    </div>
                </div>
            </div>
        {{/if}}
        {{if product.fields.note_field === true }}
        <br>
        <div class='catatan-pemesanan'>
            <div class='ui form'>
                <div class="field">
                    <label><?php _e('Catatan pemesanan', 'sejoli'); ?></label>
                    {{if product.fields.note_placeholder !== ''}}
                    <p>{{:product.fields.note_placeholder}}</p>
                    {{/if}}
                    <textarea id='order-note' name='meta_data[note]' placeholder="<?php echo sejolisa_carbon_get_post_meta($post->ID, 'note_field_placeholder_text'); ?>"></textarea>
                </div>
            </div>
        </div>
        {{/if}}
    {{/if}}
</script>

<script id="beli-sekarang-template" type="text/x-jsrender">
    <div class="ui stackable grid">
        <div class="wide column">
            <div id='sejoli-total-bayar' class="total-bayar">
                <h2><?php _e('Total', 'sejoli'); ?></h2>
                <div class="total-holder">
                    <div class="ui placeholder">
                        <div class="paragraph">
                            <div class="line"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<script>
    $(document).ready(function(){
        $('.toggle-icon').on('click', function(e){
            e.preventDefault();

            const dataHolder = $('.ui.doubling.data-holder');
            
            dataHolder.toggleClass('show-all');
            
            $(this).toggleClass('active');

            // Ganti ikon berdasarkan class active
            if ($(this).hasClass('active')) {
                $(this).find('i').removeClass('chevron down icon').addClass('chevron up icon');
            } else {
                $(this).find('i').removeClass('chevron up icon').addClass('chevron down icon');
            }
        });

        $('.toggle-details').on('click', function(e){
            e.preventDefault();

            // Toggle slide pada bagian detail
            $('.detail-section').slideToggle();

            // Ambil elemen tombol
            var $this = $(this);

            // Cek status toggle menggunakan data attribute
            if ($this.attr('data-state') === 'hidden') {
                // Ubah ke status terbuka
                $this.html("<?php _e('Tutup detail pesanan', 'sejoli'); ?> <i class='chevron up icon'></i>");
                $this.attr('data-state', 'shown');
            } else {
                // Ubah ke status tertutup
                $this.html("<?php _e('Lihat detail pesanan', 'sejoli'); ?> <i class='chevron down icon'></i>");
                $this.attr('data-state', 'hidden');
            }
        });
    });

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
    };
</script>

<script type='text/javascript'>
jQuery(document).ready(function($){

    sejoliSaCheckoutFisik.init();

    var textAreas = document.getElementsByTagName('textarea');

    Array.prototype.forEach.call(textAreas, function(elem) {
        elem.placeholder = elem.placeholder.replace(/\\n/g, '\n');
    });
});
</script>
<?php
include 'footer-secure.php';
include 'footer.php';
