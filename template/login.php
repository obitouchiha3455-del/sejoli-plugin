<?php
sejoli_header('login');
$enable_registration = boolval(sejolisa_carbon_get_theme_option('sejoli_enable_registration'));
?>

<div class="ui stackable centered grid">
    <div class="five wide column center aligned">
        <div class="ui image">
            <img src="<?php echo sejolisa_logo_url(); ?>" class="image icon">
        </div>
        <?php sejoli_get_template_part('messages.php'); ?>
        <form class="ui large form" method="POST" action="<?php echo sejoli_get_endpoint_url('login'); ?>">

            <div class="ui stacked segment">
                <div class="field">
                    <div class="ui left icon input">
                        <i class="envelope icon"></i>
                        <input type="text" name="email" placeholder="<?php _e('Alamat Email', 'sejoli'); ?>">
                    </div>
                </div>
                <div class="field">
                    <div class="ui left icon input">
                        <i class="lock icon"></i>
                        <input type="password" name="password" id="passwordInput" placeholder="<?php _e('Password', 'sejoli'); ?>" autocomplete="current-password">
                        <i class="eye icon" id="togglePassword" style="cursor: pointer; position: absolute; right: 0; pointer-events: auto; left: auto;"></i>
                    </div>
                </div>
                <button type='submit' class="ui fluid large teal submit button">
                    Login
                </button>
            </div>
            <?php wp_nonce_field('user-login','sejoli-nonce'); ?>
        </form>

        <div class="ui message">
            <?php if(false !== $enable_registration) : ?>
            <?php _e('Belum punya akun?', 'sejoli'); ?> <a href="<?php echo sejoli_get_endpoint_url('register'); ?>"><?php _e('Register', 'sejoli'); ?></a> <br />
            <?php endif; ?>

            <?php _e('Lupa password?', 'sejoli'); ?> <a href='<?php echo wp_lostpassword_url(sejoli_get_endpoint_url('login')); ?>'><?php _e('Ganti Password', 'sejoli'); ?></a>
        </div>
    </div>
</div>
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
    };
</script>