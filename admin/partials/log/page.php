<?php
    $options = array();
    $path    = WP_CONTENT_DIR . '/sejoli-log/';
    $files   = scandir($path);
    rsort($files);

    foreach($files as $file) :

        if(in_array($file, array('.', '..'))) :
            continue;
        endif;

        $options[$file]  = $file;

    endforeach;
?>
<div id='sejoli-log-holder' class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Log Sejoli', 'sejoli'); ?>
	</h1>
    <table class='form-table' role='presentation'>
        <tbody>
            <tr class='form-field'>
                <th scope="row">
                    <label for="log-files"><?php _e('Berkas log', 'sejoli'); ?></label>
                </th>
                <td>
                    <select class="sejoli-log-files" name="log-files">
                        <option value=""><?php _e('Pilih berkas log', 'sejoli'); ?></option>
                        <?php foreach($options as $key => $label) : ?>
                        <option value='<?php echo $key; ?>'><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="list-log" style="max-height:100vh;overflow:auto;background-color: #ffffff;">

    </div>
</div>

<script type="text/javascript">
(function( $ ) {
    'use strict';

    $('body').on('change', '.sejoli-log-files', function(){

        let file = $(this).val();

        if('' !== file) {

            $.ajax({
                url : '<?php echo admin_url('admin-ajax.php'); ?>',
                type : 'GET',
                dataType: 'html',
                data : {
                    action : 'sejoli-read-log',
                    file : file
                },
                success : function(response) {

                    let content = '<pre data-enlighter-language="html">' + response + '</pre>';

                    $('.list-log').html(content);

                    EnlighterJS.init('pre', {
                            language : 'html',
                            indent : 2
                    });
                }
            })
        }
    });
})(jQuery);
</script>
