<table style='margin-top:20px;margin-bottom:0;margin-left:auto;margin-right:auto;width:480px;' cellpadding='5' cellspacing='0' border='1' bordercolor='#444444'>
    <tbody>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php _e('Nama User', 'sejoli'); ?></td>
            <td>{{user-name}}</td>
        </tr>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php _e('Alamat Email', 'sejoli'); ?></td>
            <td>{{user-email}}</td>
        </tr>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php _e('Nomor Telpon', 'sejoli'); ?></td>
            <td>{{user-phone}}</td>
        </tr>
        <?php if('buyer' === $recipient_type) : ?>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php _e('Password Anda', 'sejoli'); ?></td>
            <td>{{user-pass}}</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<p style='margin-top:20px'>
    Login ke <a href='{{memberurl}}'>{{memberurl}}</a>
</p>

<div style='height:10px;'>&nbsp;</div>
