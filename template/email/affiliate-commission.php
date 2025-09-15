<?php if ($affiliate): ?>
<table style='margin-top:20px;margin-bottom:0;margin-left:auto;margin-right:auto;width:480px' cellpadding='5' cellspacing='0' border='1' bordercolor='#444444'>
    <tr>
        <td style='width:120px;font-weight:bold;'>
            <?php _e('Komisi Affiliasi', 'sejoli'); ?>
        </td>
        <td><?php printf(__('%s %s (Tier %s)', 'sejoli'), $affiliate->display_name, $affiliate->commission, $affiliate->tier); ?></td>
    </tr>
</table>
<div style='height:10px;'>&nbsp;</div>
<?php endif; ?>