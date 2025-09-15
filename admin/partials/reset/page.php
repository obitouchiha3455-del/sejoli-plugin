<div class="wrap">
    <h1><?php _e('Reset Data', 'sejoli'); ?></h1>
    <p>
        <?php _e('Jika anda INGIN menghapus semua data yang ada di website anda sebagai berikut :', 'sejoli'); ?><br />
        <?php _e('Order', 'sejoli'); ?>,
        <?php _e('Affiliasi', 'sejoli'); ?>,
        <?php _e('Komisi', 'sejoli'); ?>,
        <?php _e('Akuisisi', 'sejoli'); ?>,
        <?php _e('Transaksi', 'sejoli'); ?>,
        <?php _e('Kupon', 'sejoli'); ?>,
        <?php _e('Lisensi', 'sejoli'); ?>,
        <?php _e('Pengingat', 'sejoli'); ?>,
        <?php _e('Langganan', 'sejoli'); ?>
    </p>
    <p>
        <?php _e('Untuk data user yang telah terdaftar, tidak dapat dihapus melalui proses ini. Gunakan proses manual.', 'sejoli'); ?>
    </p>
    <p>
        <?php _e('Konfigurasi SEJOLI tidak akan dihapus melalui proses ini. Semua konfigurasi akan tetap disimpan', 'sejoli'); ?>
    </p>
    <p>
        <?php _e('Pastikan anda sudah melakukan BACKUP DATA terlebih dahulu. <br />Pihak SEJOLI TIDAK AKAN BERTANGGUNG JAWAB jika terjadi kesalahan dari proses ini', 'sejoli'); ?>
    </p>
    <form id='sejoli-reset-form' action="" method="post">
        <table class='form-table' role='presentation'>
            <tbody>
                <tr>
                    <th scope='row'>
                        Konfirmasi hapus
                    </th>
                    <td>
                        <input type="text" id="sejoli-user-confirm" name="sejoli-user-confirm" value="" class='regular-text' required />
                        <p class="description" >Isi field diatas dengan HAPUS</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class='submit'>
            <button type="submit" name="button" class='button button-primary' id='sejoli-reset-data-button'><?php _e('Reset data sejoli', 'sejoli'); ?></button>
        </p>
        <?php wp_nonce_field('sejoli-reset-data', 'noncekey'); ?>
    </form>
</div>
<script type="text/javascript">
(function($){

    let close_confirm = false;

    'use strict';

    window.onbeforeunload = function (e) {

        if( ! close_confirm ) return null;

        e = e || window.event;
        //old browsers
        if (e) {e.returnValue = 'Sedang memproses reset data, anda yakin akan keluar?';}
        //safari, chrome(chrome ignores text)
        return 'Sedang memproses reset data, anda yakin akan keluar?';
    };

    $(document).ready(function(){

        $('#sejoli-reset-form').submit(function(){

            let data = new FormData($(this)[0]),
                hapus = $('#sejoli-user-confirm').val(),
                button = $('#sejoli-reset-data-button'),
                notice = $('.sejoli-reset-data-response');

            if('HAPUS' !== hapus ) {

                alert( '<?php _e('Anda belum mengisi isian Konfirmasi hapus dengan benar', 'sejoli'); ?>');

            } else {

                let confirmed = confirm('<?php _e('Kami ingin meyakinkan anda sekali lagi untuk menyetujui proses reset data Sejoli ini. Apakah anda ingin menghapus data terkait Sejoli?', 'sejoli'); ?>');

                if( confirmed ) {
                    $.ajax({
                        url:    '<?php echo admin_url('admin-ajax.php?action=sejoli-reset-data'); ?>',
                        method: 'POST',
                        type:   'POST',
                        dataType: 'json',
                        cache:false,
                        contentType: false,
                        processData: false,
                        data: data,
                        beforeSend: function() {

                            close_confirm = true;

                            button.attr('disabled', true);
                            notice.show()
                                .removeClass('notice-error notice-success')
                                .addClass('notice-info')
                                .html('<p>Mereset data sejoli, harap ditunggu dan <strong>JANGAN MENUTUP</strong> halaman ini</p>');
                        },
                        success: function(response) {

                            button.attr('disabled', false);

                            if(response.success) {
                                button.attr('disabled', false);
                                notice.show()
                                    .removeClass('notice-info notice-error')
                                    .addClass('notice-success').html(response.message);
                            } else {
                                button.attr('disabled', true);
                                notice.show()
                                    .removeClass('notice-info notice-success')
                                    .addClass('notice-error').html(response.message);
                            }

                            close_confirm = false;
                        }
                    });
                }
            }

            return false;
        });
    });
})(jQuery);
</script>
