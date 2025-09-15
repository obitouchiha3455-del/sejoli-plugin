<div class="wrap">
    <h1><?php _e('Cek Lisensi Anda', 'sejoli'); ?></h1>
    <p>
        <?php _e('Jika anda INGIN melakukan RESET LISENSI, silahkan lakukan pengecekan lisensi anda terlebih dahulu.', 'sejoli'); ?>
    </p>
    <form id='sejoli-validate-license-form' action="" method="post">
        <table class='form-table' role='presentation'>
            <tbody>
                <tr>
                    <th scope='row'>
                        <?php _e('Email', 'sejoli'); ?>
                    </th>
                    <td>
                        <input type="email" name="data[user_email]" value="" class='regular-text sejoli-license-field' required />
                        <p class="description" id="sejoli-user-email"><?php _e('Diisi dengan email yang anda gunakan di sejoli.id', 'sejoli'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope='row'>
                        <?php _e('Password', 'sejoli'); ?>
                    </th>
                    <td>
                        <input type="text" name="data[user_pass]" value="" class='regular-text sejoli-license-field' required />
                        <p class="description" id="sejoli-user-pass"><?php _e('Diisi dengan pass yang anda gunakan di sejoli.id', 'sejoli'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope='row'>
                        <?php _e('Kode Lisensi', 'sejoli'); ?>
                    </th>
                    <td>
                        <input type="text" name="data[license]" value="" class='regular-text sejoli-license-field' required />
                        <p class="description" id="sejoli-license"><?php _e('Diisi dengan kode lisensi yang anda dapatkan', 'sejoli'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class='submit'>
            <button type="submit" name="button" class='button button-primary' id='sejoli-validate-button'><?php _e('Cek kode lisensi', 'sejoli'); ?></button>
            <button type="button" name="button" class='button button-primary' id='sejoli-reset-button' disabled='disabled'><?php _e('Reset lisensi', 'sejoli'); ?></button>
        </p>
        <?php wp_nonce_field('sejoli-validate-license', 'noncekey'); ?>
    </form>
</div>
<script type="text/javascript">
(function($){
    'use strict';

    $(document).ready(function(){

        $('#sejoli-reset-button').attr('disabled', true).hide();

        $('#sejoli-validate-license-form').submit(function(){

            let data = new FormData($(this)[0]),
                button = $('#sejoli-validate-button'),
                button_text = button.html();

            $.ajax({
                url:    '<?php echo admin_url('admin-ajax.php?action=sejoli-validate-license'); ?>',
                method: 'POST',
                type:   'POST',
                dataType: 'json',
                cache:false,
                contentType: false,
                processData: false,
                data: data,
                beforeSend: function() {
                    $('.sejoli-license-response').hide().removeClass('notice-success notice-error');
                    $('#sejoli-reset-button').attr('disabled', true).hide();
                    button.html('Mengecek lisensi...').attr('disabled', true);
                },
                success: function(response) {
                    button.html(button_text).attr('disabled', false);

                    if(response.valid) {
                        $('#sejoli-reset-button').attr('disabled', false).show();
                        $('.sejoli-license-response').show().addClass('notice-success').html(response.message);
                        $('.sejoli-license-field').attr('readonly', true);
                    } else {
                        $('#sejoli-reset-button').attr('disabled', true).hide();
                        $('.sejoli-license-response').show().addClass('notice-error').html(response.message);
                    }
                }
            })

            return false;
        });

        $('#sejoli-reset-button').click(function(){
            let confirmed = confirm('<?php _e('Anda yakin akan melakukan RESET LISENSI? Jika iya, kami akan menghapus semua data sejoli di website ini termasuk data penjualan', 'sejoli'); ?>');

            if(confirmed) {
                let form = $('#sejoli-validate-license-form'),
                    data = new FormData(form[0]),
                    button = $(this),
                    button_text = button.html();

                    $.ajax({
                        url:    '<?php echo admin_url('admin-ajax.php?action=sejoli-reset-license'); ?>',
                        method: 'POST',
                        type:   'POST',
                        dataType: 'json',
                        cache:false,
                        contentType: false,
                        processData: false,
                        data: data,
                        beforeSend: function() {

                            alert("<?php _e('Mohon untuk tidak menutup halaman ini hingga proses selesai', 'sejoli'); ?>");

                            $('.sejoli-license-response').hide().removeClass('notice-success notice-error');
                            $('#sejoli-validate-button').attr('disabled', true);

                            button.html('Mereset lisensi dan menghapus semua data...').attr('disabled', true);
                        },
                        success: function(response) {

                            button.html(button_text).attr('disabled', false);

                            if(response.valid) {

                                $('.sejoli-license-response').show().addClass('notice-success').html(response.message);
                                window.location.href = '<?php echo admin_url('plugins.php?plugin_status=all&paged=1&s'); ?>';

                            } else {
                                $('.sejoli-license-response').show().addClass('notice-error').html(response.message);
                            }
                        }
                    })

                    return false;
            }
        });
    });

})(jQuery);
</script>
