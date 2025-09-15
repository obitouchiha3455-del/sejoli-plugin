<div class="wrap">
    <h1><?php _e('Upgrade Sistem', 'sejoli'); ?></h1>
    <?php if( $this->do_need_update() ) : ?>
    <p>
        <a href='#' id='sejoli-upgrade-system-trigger' class='button button-primary'><?php _e('Klik disini untuk upgrade sistem anda', 'sejoli'); ?></a>
    </p>
    <div id='sejoli-upgrade-result' style='display:none'>
        <h2><?php _e('Upgrade info', 'sejoli'); ?></h2>
    </div>
    <?php else : ?>
    <p>
        <?php _e('Database sejoli anda sudah diperbaharui. Alhamdulillah :)', 'sejoli'); ?>
    </p>
    <?php endif; ?>
</div>
<script type="text/javascript">
(function(){

    let trigger = document.getElementById('sejoli-upgrade-system-trigger'),
        info = document.getElementById('sejoli-upgrade-result'),
        do_upgrade,
        next_step = false;

    do_upgrade = function() {

        let data = new FormData(),
            xhr = new XMLHttpRequest();

        data.append('step', next_step );
        data.append('sejoli-nonce', '<?php echo wp_create_nonce('sejoli-upgrade-system'); ?>');

        xhr.open('POST', '<?php echo admin_url('admin-ajax.php?action=sejoli-upgrade-system'); ?>');
        xhr.onload = function() {

            if( xhr.status === 200 ) {

                let response = JSON.parse( xhr.responseText );

                if( response.success ) {

                    let message = document.createElement('p')

                    message.innerHTML = response.message;

                    info.appendChild(message);

                } else {
                    alert(response.message);
                }
            }
        }

        trigger.setAttribute('disabled', true);
        info.style.display = 'block';

        xhr.send( data );
    }

    trigger.addEventListener('click', function(){
        do_upgrade();
        return false;
    });
})();
</script>
