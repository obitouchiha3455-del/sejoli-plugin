<?php sejoli_header('login'); ?>

<div class="ui stackable centered grid">
    <div class="five wide column center aligned">
        <div class="ui image">
            <img src="<?php echo sejolisa_logo_url(); ?>" class="image icon">
        </div>
        <?php sejoli_get_template_part('messages.php'); ?>
        <form class="ui large form" method="POST" action="<?php echo sejoli_get_endpoint_url('register'); ?>">

            <div class="ui stacked segment">
                 <?php
                    $display_username = boolval(sejolisa_carbon_get_theme_option('sejoli_registration_display_username'));
                    if(true === $display_username) :
                ?>
                    <div class="required field">
                        <div class="ui left icon input">
                            <i class="user icon"></i>
                            <input type="text" name="user_login" placeholder="<?php _e('Nama Pengguna', 'sejoli'); ?>" value="">
                        </div>
                    </div>
                <?php endif; ?>
                <div class="required field">
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="text" name="full_name" placeholder="<?php _e('Nama Lengkap', 'sejoli'); ?>" value="">
                    </div>
                </div>
                <div class="required field">
                    <div class="ui left icon input">
                        <i class="envelope icon"></i>
                        <input type="email" name="email" placeholder="<?php _e('Alamat Email', 'sejoli'); ?>" value="">
                    </div>
                </div>
                <?php
                    $display_password = boolval(sejolisa_carbon_get_theme_option('sejoli_registration_display_password'));
                    if(true === $display_password) :
                ?>
                    <div class="required field">
                        <div class="ui left icon input">
                            <i class="lock icon"></i>
                            <input type="password" name="password" id="passwordInput" placeholder="<?php _e('Password', 'sejoli'); ?>" autocomplete="false">
                            <i class="eye icon" id="togglePassword" style="cursor: pointer; position: absolute; right: 0; pointer-events: auto; left: auto;"></i>
                        </div>
                    </div>
                    <div class="required field">
                        <div class="ui left icon input">
                            <i class="lock icon"></i>
                            <input type="password" name="confirm_password" id="confirmPasswordInput" placeholder="<?php _e('Konfirmasi Password', 'sejoli'); ?>"  autocomplete="false">
                            <i class="eye icon" id="toggleConfirmPassword" style="cursor: pointer; position: absolute; right: 0; pointer-events: auto; left: auto;"></i>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="required field">
                    <div class="ui left icon input">
                        <i class="whatsapp icon"></i>
                        <input type="number" name="wa_phone" placeholder="<?php _e('Nomor WhatsApp', 'sejoli'); ?>" value="">
                    </div>
                </div>
                <?php
                    $g_recaptcha_enable   = boolval( sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_enabled' ) );
                    $g_recaptcha_register = boolval( sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_register_page' ) );

                    if( true === $g_recaptcha_enable && true === $g_recaptcha_register ) :
                ?>
                    <div class="g-recaptcha-area">
                        <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
                    </div>
                <?php endif; ?>
                <button type='submit' class="ui fluid large teal submit button">
                    Register
                </button>
            </div>
            <?php wp_nonce_field('user-register','sejoli-nonce'); ?>
        </form>

        <div class="ui message">
            <?php _e('Sudah punya akun?', 'sejoli'); ?> <a href="<?php echo sejoli_get_endpoint_url('login'); ?>"><?php _e('Login', 'sejoli'); ?></a>
        </div>
    </div>
</div>

<?php
$g_recaptcha          = boolval(sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_enabled' ));
$g_recaptcha_register = boolval( sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_register_page' ) );
$g_recaptcha_sitekey  = esc_attr(sejolisa_carbon_get_theme_option( 'sejoli_google_recaptcha_sitekey' ));
if( true === $g_recaptcha && true === $g_recaptcha_register && !empty($g_recaptcha_sitekey) ) :
?>
<script>
    jQuery(document).ready(function($){
        
        grecaptcha.ready(() => {
            grecaptcha.execute('<?php echo $g_recaptcha_sitekey ?>', { action: 'register' }).then(token => {
                document.querySelector('#recaptchaResponse').value = token;
            });
        });

    });
</script>
<?php endif; ?>

<?php sejoli_footer('login'); ?>

<script>
    window.onload = function () {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
            
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;

                this.classList.toggle('slash'); 
            });
        }

        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirmPasswordInput');

        if (toggleConfirmPassword && confirmPasswordInput) {
            toggleConfirmPassword.addEventListener('click', function () {

                const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
                confirmPasswordInput.type = type;

                this.classList.toggle('slash'); 
            });
        }
    };
</script>
