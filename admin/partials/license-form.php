<div class="wrap">
    <h1><?php _e('Form input lisensi - Sejoli', 'sejoli'); ?></h1>
    <p>
        <?php _e('Gunakan lisensi yang sah agar bisnis anda berkah', 'sejoli'); ?>
    </p>
    <form class="" action="" method="post">
        <table class='form-table' role='presentation'>
            <tbody>
                <tr>
                    <th scope='row'>
                        <?php _e('Email', 'sejoli'); ?>
                    </th>
                    <td>
                        <input type="email" name="data[user_email]" value="" class='regular-text' required />
                        <p class="description" id="tagline-description"><?php _e('Diisi dengan email yang anda gunakan di sejoli.id', 'sejoli'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope='row'>
                        <?php _e('Password', 'sejoli'); ?>
                    </th>
                    <td>
                        <input type="text" name="data[user_pass]" value="" class='regular-text' required />
                        <p class="description" id="tagline-description"><?php _e('Diisi dengan pass yang anda gunakan di sejoli.id', 'sejoli'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope='row'>
                        <?php _e('Kode Lisensi', 'sejoli'); ?>
                    </th>
                    <td>
                        <input type="text" name="data[license]" value="" class='regular-text' required />
                        <p class="description" id="tagline-description"><?php _e('Diisi dengan kode lisensi yang anda dapatkan', 'sejoli'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class='submit'>
            <button type="submit" name="button" class='button button-primary'><?php _e('Cek kode lisensi', 'sejoli'); ?></button>
        </p>
        <?php wp_nonce_field('sejoli-input-license'); ?>
    </form>
</div>
